<?php

declare(strict_types=1);

namespace App\Modules\GitHub\Services;

use App\Modules\GitHub\Models\RepositoryAudit;
use App\Modules\Projects\Models\Project;

class RepositoryScannerService
{
    public function __construct(
        protected GitHubClientService $github
    ) {}

    /**
     * Scan a repository, detect architecture, frameworks, and parse manifests.
     */
    public function scan(Project $project, string $repoFullName, string $branch = 'main'): RepositoryAudit
    {
        $tree = $this->github->getRepositoryTree($project->user, $repoFullName, $branch);
        $paths = array_column($tree, 'path');

        $language = $this->detectPrimaryLanguage($paths);
        $framework = $this->detectFramework($paths, $language, $project, $repoFullName, $branch);
        $architecture = $this->detectArchitecture($paths);
        $manifests = $this->parseManifests($paths, $project, $repoFullName, $branch);

        // Calculate initial baseline health scores
        $hasTests = !empty(array_filter($paths, fn ($p) => str_contains($p, 'tests/') || str_contains($p, '__tests__/') || str_contains($p, 'spec/')));
        $hasEnvExample = in_array('.env.example', $paths, true);
        $hasGitignore = in_array('.gitignore', $paths, true);

        $healthScore = 80;
        if ($hasTests) $healthScore += 10; else $healthScore -= 20;
        if ($hasEnvExample) $healthScore += 5; else $healthScore -= 10;
        if ($hasGitignore) $healthScore += 5;

        $healthScore = max(15, min(98, $healthScore));
        $debtScore = max(10, 100 - $healthScore);
        $securityScore = $hasEnvExample ? 88 : 65;

        $rawMetrics = [
            'total_blobs' => count(array_filter($tree, fn ($i) => ($i['type'] ?? '') === 'blob')),
            'has_tests' => $hasTests,
            'has_env_example' => $hasEnvExample,
            'has_gitignore' => $hasGitignore,
            'manifest_files_found' => array_keys($manifests),
        ];

        return RepositoryAudit::updateOrCreate(
            ['project_id' => $project->id],
            [
                'repo_full_name' => $repoFullName,
                'default_branch' => $branch,
                'primary_language' => $language,
                'detected_framework' => $framework,
                'architecture_pattern' => $architecture,
                'file_count' => count($paths),
                'manifests' => $manifests,
                'code_health_score' => $healthScore,
                'technical_debt_score' => $debtScore,
                'security_score' => $securityScore,
                'raw_metrics' => $rawMetrics,
            ]
        );
    }

    protected function detectPrimaryLanguage(array $paths): string
    {
        $extensions = [];
        foreach ($paths as $path) {
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            if (!empty($ext)) {
                $extensions[$ext] = ($extensions[$ext] ?? 0) + 1;
            }
        }

        arsort($extensions);
        $topExt = array_key_first($extensions);

        return match ($topExt) {
            'php' => 'PHP',
            'ts', 'tsx' => 'TypeScript',
            'js', 'jsx' => 'JavaScript',
            'py' => 'Python',
            'go' => 'Go',
            'java' => 'Java',
            'rb' => 'Ruby',
            'dart' => 'Dart',
            'cs' => 'C#',
            'rs' => 'Rust',
            default => 'Full Stack Polyglot',
        };
    }

    protected function detectFramework(array $paths, string $language, Project $project, string $repo, string $branch): string
    {
        if (in_array('artisan', $paths, true)) {
            return 'Laravel';
        }

        if (in_array('bin/console', $paths, true)) {
            return 'Symfony';
        }

        if ($language === 'PHP' && in_array('composer.json', $paths, true)) {
            $content = $this->github->getFileContent($project->user, $repo, 'composer.json', $branch);
            if ($content && str_contains($content, 'laravel/framework')) return 'Laravel';
            if ($content && str_contains($content, 'symfony/framework-bundle')) return 'Symfony';
        }

        if (in_array('manage.py', $paths, true)) {
            return 'Django';
        }

        if (in_array('pubspec.yaml', $paths, true)) {
            return 'Flutter';
        }

        if (in_array('go.mod', $paths, true)) {
            return 'Go Standard / Microservices';
        }

        foreach ($paths as $path) {
            if (str_starts_with($path, 'next.config.')) {
                return 'Next.js';
            }
            if (str_starts_with($path, 'nuxt.config.')) {
                return 'Nuxt';
            }
        }

        if (in_array('package.json', $paths, true)) {
            $content = $this->github->getFileContent($project->user, $repo, 'package.json', $branch);
            if ($content && str_contains($content, '"next"')) return 'Next.js';
            if ($content && str_contains($content, '"@nestjs/core"')) return 'NestJS';
            if ($content && str_contains($content, '"react"')) return 'React';
            if ($content && str_contains($content, '"vue"')) return 'Vue';
            if ($content && str_contains($content, '"express"')) return 'Express';
        }

        if (in_array('composer.json', $paths, true)) {
            $content = $this->github->getFileContent($project->user, $repo, 'composer.json', $branch);
            if ($content && str_contains($content, 'laravel/framework')) return 'Laravel';
            if ($content && str_contains($content, 'symfony/framework-bundle')) return 'Symfony';
        }

        return 'Custom / Native';
    }

    protected function detectArchitecture(array $paths): string
    {
        // 1. Modular Monolith
        foreach ($paths as $path) {
            if (str_contains($path, 'app/Modules/') || str_contains($path, 'src/modules/')) {
                return 'Modular Monolith';
            }
        }

        // 2. Microservices
        $hasDockerCompose = in_array('docker-compose.yml', $paths, true);
        $hasServicesDir = in_array('services/', $paths, true) || in_array('apps/', $paths, true);
        if ($hasDockerCompose && $hasServicesDir) {
            return 'Microservices';
        }

        // 3. Serverless
        if (in_array('serverless.yml', $paths, true) || in_array('template.yaml', $paths, true)) {
            return 'Serverless';
        }

        // 4. Jamstack
        foreach ($paths as $path) {
            if (str_starts_with($path, 'pages/') || str_starts_with($path, 'src/pages/')) {
                return 'Jamstack / Frontend First';
            }
        }

        return 'Layered MVC Monolith';
    }

    protected function parseManifests(array $paths, Project $project, string $repo, string $branch): array
    {
        $manifests = [];

        if (in_array('composer.json', $paths, true)) {
            $content = $this->github->getFileContent($project->user, $repo, 'composer.json', $branch);
            if ($content) {
                $decoded = json_decode($content, true);
                if (is_array($decoded)) {
                    $manifests['composer'] = [
                        'require' => $decoded['require'] ?? [],
                        'require_dev' => $decoded['require-dev'] ?? [],
                    ];
                }
            }
        }

        if (in_array('package.json', $paths, true)) {
            $content = $this->github->getFileContent($project->user, $repo, 'package.json', $branch);
            if ($content) {
                $decoded = json_decode($content, true);
                if (is_array($decoded)) {
                    $manifests['npm'] = [
                        'dependencies' => $decoded['dependencies'] ?? [],
                        'dev_dependencies' => $decoded['devDependencies'] ?? [],
                    ];
                }
            }
        }

        return $manifests;
    }
}
