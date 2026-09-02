<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\GitHub;

use App\Models\User;
use App\Modules\GitHub\Models\RepositoryAudit;
use App\Modules\GitHub\Services\TechnicalDebtAuditor;
use App\Modules\Projects\Enums\ProjectStatus;
use App\Modules\Projects\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TechnicalDebtAuditorTest extends TestCase
{
    use RefreshDatabase;

    public function test_auditor_flags_legacy_php_and_abandoned_packages(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'Legacy Monolith Refactoring',
            'status' => ProjectStatus::ACTIVE,
        ]);

        $audit = RepositoryAudit::create([
            'project_id' => $project->id,
            'repo_full_name' => 'acme/legacy-app',
            'primary_language' => 'PHP',
            'detected_framework' => 'Laravel',
            'manifests' => [
                'composer' => [
                    'require' => [
                        'php' => '^7.4',
                        'swiftmailer/swiftmailer' => '^6.0',
                    ],
                ],
            ],
            'raw_metrics' => [
                'has_tests' => false,
                'has_env_example' => false,
            ],
        ]);

        $auditor = new TechnicalDebtAuditor();
        $findings = $auditor->audit($audit);

        $this->assertNotEmpty($findings);

        $findingIds = array_column($findings, 'id');
        $this->assertContains('TD-PHP-EOL', $findingIds);
        $this->assertContains('TD-DEP-SWIFTMAILER_SWIFTMAILER', $findingIds);
        $this->assertContains('TD-TEST-MISSING', $findingIds);
        $this->assertContains('TD-ENV-MISSING', $findingIds);

        // Verify scores were reduced due to debt
        $this->assertLessThan(60, $audit->fresh()->code_health_score);
        $this->assertGreaterThan(40, $audit->fresh()->technical_debt_score);
    }
}
