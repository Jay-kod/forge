<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\GitHub;

use App\Models\User;
use App\Modules\GitHub\Models\RepositoryAudit;
use App\Modules\GitHub\Services\CodeOpportunityGenerator;
use App\Modules\Opportunity\Services\OpportunityScoringService;
use App\Modules\Projects\Enums\ProjectStatus;
use App\Modules\Projects\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CodeOpportunityTest extends TestCase
{
    use RefreshDatabase;

    public function test_generator_converts_audit_findings_into_opportunity_matrix_items(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'E-Commerce Monolith Modernization',
            'status' => ProjectStatus::ACTIVE,
        ]);

        $audit = RepositoryAudit::create([
            'project_id' => $project->id,
            'repo_full_name' => 'acme/ecommerce-api',
            'primary_language' => 'PHP',
            'detected_framework' => 'Laravel',
            'raw_metrics' => [
                'findings' => [
                    [
                        'id' => 'TD-PHP-EOL',
                        'severity' => 'critical',
                        'category' => 'runtime',
                        'title' => 'End-of-Life PHP Runtime (7.4)',
                        'description' => 'targets PHP 7.4 with no security updates',
                        'recommended_action' => 'Upgrade to PHP 8.2+ and fix deprecated methods',
                        'file_path' => 'composer.json',
                    ],
                    [
                        'id' => 'TD-TEST-MISSING',
                        'severity' => 'high',
                        'category' => 'testing',
                        'title' => 'Zero Automated Test Coverage Detected',
                        'description' => 'No test suites found',
                        'recommended_action' => 'Add Pest/PHPUnit tests for checkout domain',
                        'file_path' => null,
                    ],
                ],
            ],
        ]);

        $generator = new CodeOpportunityGenerator();
        $opportunities = $generator->generate($project, $audit);

        $this->assertCount(2, $opportunities);

        // Verify opportunities in database
        $this->assertDatabaseHas('opportunities', [
            'project_id' => $project->id,
            'title' => 'End-of-Life PHP Runtime (7.4)',
            'impact' => 'critical',
            'difficulty' => 'low',
        ]);

        $this->assertDatabaseHas('opportunities', [
            'project_id' => $project->id,
            'title' => 'Zero Automated Test Coverage Detected',
            'impact' => 'high',
            'difficulty' => 'medium',
        ]);

        // Verify recommendations
        $this->assertDatabaseHas('recommendations', [
            'title' => 'Remediation Step: End-of-Life PHP Runtime (7.4)',
            'suggested_action' => 'Upgrade to PHP 8.2+ and fix deprecated methods',
        ]);

        // Verify priority matrix placement via OpportunityRankingService
        $rankingService = new \App\Modules\Opportunity\Services\OpportunityRankingService();
        $matrix = $rankingService->rank($project);

        // Critical impact + low difficulty = Quick Win
        $quickWinTitles = collect($matrix['quick_wins'])->pluck('title')->toArray();
        $this->assertContains('End-of-Life PHP Runtime (7.4)', $quickWinTitles);
    }
}
