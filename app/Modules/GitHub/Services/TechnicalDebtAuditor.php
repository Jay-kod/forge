<?php

declare(strict_types=1);

namespace App\Modules\GitHub\Services;

use App\Modules\GitHub\Models\RepositoryAudit;

class TechnicalDebtAuditor
{
    /**
     * Audit a repository audit record for technical debt, security flags, and runtime deprecations.
     *
     * @return array<int, array{id: string, severity: string, category: string, title: string, description: string, recommended_action: string, file_path: ?string}>
     */
    public function audit(RepositoryAudit $audit): array
    {
        $findings = [];
        $healthScore = 85;
        $securityScore = 90;

        $manifests = $audit->manifests ?? [];
        $composer = $manifests['composer'] ?? [];
        $npm = $manifests['npm'] ?? [];
        $metrics = $audit->raw_metrics ?? [];

        // 1. Runtime Version Checks
        if (isset($composer['require']['php'])) {
            $phpVersion = (string) $composer['require']['php'];
            if (preg_match('/[<~^]?\s*([57]\.\d|8\.[01])\b/', $phpVersion)) {
                $findings[] = [
                    'id' => 'TD-PHP-EOL',
                    'severity' => 'critical',
                    'category' => 'runtime',
                    'title' => "End-of-Life PHP Runtime ({$phpVersion})",
                    'description' => "The repository targets PHP {$phpVersion}, which has reached official end-of-life and receives no official security patches.",
                    'recommended_action' => 'Upgrade to PHP 8.2 or 8.3 and modernize deprecated syntax features.',
                    'file_path' => 'composer.json',
                ];
                $healthScore -= 25;
                $securityScore -= 20;
            }
        }

        if (isset($npm['dependencies']['next'])) {
            $nextVersion = (string) $npm['dependencies']['next'];
            if (preg_match('/[<~^]?\s*([0-9]|1[0-2])\./', $nextVersion)) {
                $findings[] = [
                    'id' => 'TD-NEXT-LEGACY',
                    'severity' => 'high',
                    'category' => 'runtime',
                    'title' => "Legacy Next.js Version ({$nextVersion})",
                    'description' => "Next.js {$nextVersion} lacks App Router performance enhancements and Server Components optimizations.",
                    'recommended_action' => 'Upgrade to Next.js 14+ with App Router architecture.',
                    'file_path' => 'package.json',
                ];
                $healthScore -= 15;
            }
        }

        // 2. Deprecated / Abandoned Dependencies Check
        $deprecatedComposer = [
            'swiftmailer/swiftmailer' => 'SwiftMailer is abandoned; migrate to symfony/mailer.',
            'zendframework/' => 'Zend Framework is abandoned; migrate to laminas.',
            'fzaninotto/faker' => 'fzaninotto/faker is abandoned; migrate to fakerphp/faker.',
        ];

        foreach ($deprecatedComposer as $dep => $advice) {
            foreach (array_keys($composer['require'] ?? []) as $pkg) {
                if (str_contains($pkg, $dep)) {
                    $findings[] = [
                        'id' => 'TD-DEP-' . strtoupper(str_replace(['/', '-'], '_', $pkg)),
                        'severity' => 'high',
                        'category' => 'dependencies',
                        'title' => "Abandoned Dependency: {$pkg}",
                        'description' => "{$pkg} is officially abandoned and receives no security updates.",
                        'recommended_action' => $advice,
                        'file_path' => 'composer.json',
                    ];
                    $securityScore -= 15;
                    $healthScore -= 10;
                }
            }
        }

        // 3. Testing & Architecture Debt
        if (empty($metrics['has_tests'])) {
            $findings[] = [
                'id' => 'TD-TEST-MISSING',
                'severity' => 'high',
                'category' => 'testing',
                'title' => 'Zero Automated Test Coverage Detected',
                'description' => 'No test directory or automated test suites (tests/, spec/, __tests__/) were detected in the repository structure.',
                'recommended_action' => 'Establish Pest/PHPUnit or Vitest/Jest test suites for critical domain services and checkout workflows.',
                'file_path' => null,
            ];
            $healthScore -= 20;
        }

        // 4. Configuration & Secrets Hygiene
        if (empty($metrics['has_env_example'])) {
            $findings[] = [
                'id' => 'TD-ENV-MISSING',
                'severity' => 'medium',
                'category' => 'architecture',
                'title' => 'Missing Environment Template (.env.example)',
                'description' => 'A template environment file is missing, causing developer onboarding friction and deployment ambiguities.',
                'recommended_action' => 'Commit a sanitized .env.example with documented configuration parameters.',
                'file_path' => '.env.example',
            ];
            $healthScore -= 10;
        }

        // Clamp scores
        $finalHealth = max(15, min(98, $healthScore));
        $finalDebt = max(5, min(95, 100 - $finalHealth));
        $finalSecurity = max(20, min(98, $securityScore));

        // Save findings and updated scores to the audit record
        $currentMetrics = $audit->raw_metrics ?? [];
        $currentMetrics['findings'] = $findings;

        $audit->update([
            'code_health_score' => $finalHealth,
            'technical_debt_score' => $finalDebt,
            'security_score' => $finalSecurity,
            'raw_metrics' => $currentMetrics,
        ]);

        return $findings;
    }
}
