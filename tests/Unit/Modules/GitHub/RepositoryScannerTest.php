<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\GitHub;

use App\Models\User;
use App\Modules\GitHub\Models\GitHubConnection;
use App\Modules\GitHub\Models\RepositoryAudit;
use App\Modules\GitHub\Services\GitHubClientService;
use App\Modules\GitHub\Services\RepositoryScannerService;
use App\Modules\Projects\Enums\ProjectStatus;
use App\Modules\Projects\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepositoryScannerTest extends TestCase
{
    use RefreshDatabase;

    public function test_scanner_detects_language_framework_and_architecture(): void
    {
        $user = User::factory()->create();
        GitHubConnection::create([
            'user_id' => $user->id,
            'github_username' => 'forge-dev',
            'access_token' => 'dummy_token',
        ]);

        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'E-Commerce Backend Modernization',
            'status' => ProjectStatus::ACTIVE,
        ]);

        $scanner = new RepositoryScannerService(new GitHubClientService());

        $audit = $scanner->scan($project, 'forge-dev/ecommerce-api', 'main');

        $this->assertInstanceOf(RepositoryAudit::class, $audit);
        $this->assertEquals($project->id, $audit->project_id);
        $this->assertEquals('forge-dev/ecommerce-api', $audit->repo_full_name);
        $this->assertEquals('PHP', $audit->primary_language);
        $this->assertEquals('Laravel', $audit->detected_framework);
        $this->assertNotEmpty($audit->manifests);
        $this->assertGreaterThan(0, $audit->code_health_score);
        $this->assertLessThanOrEqual(100, $audit->code_health_score);
    }
}
