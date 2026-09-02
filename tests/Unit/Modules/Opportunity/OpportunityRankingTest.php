<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Opportunity;

use App\Models\User;
use App\Modules\Opportunity\Models\Opportunity;
use App\Modules\Opportunity\Services\OpportunityRankingService;
use App\Modules\Projects\Enums\ProjectStatus;
use App\Modules\Projects\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpportunityRankingTest extends TestCase
{
    use RefreshDatabase;

    protected OpportunityRankingService $rankingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rankingService = new OpportunityRankingService();
    }

    public function test_opportunity_quadrants_are_correctly_classified(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'Growth Opportunities Test',
            'status' => ProjectStatus::ACTIVE,
        ]);

        $quickWin = Opportunity::create([
            'project_id' => $project->id,
            'title' => 'One-Click Checkout',
            'description' => 'Reduce cart abandonment',
            'impact' => 'high',
            'difficulty' => 'low',
            'confidence_score' => 0.95,
        ]);

        $majorProject = Opportunity::create([
            'project_id' => $project->id,
            'title' => 'Custom Native Mobile Apps',
            'description' => 'Build native iOS and Android apps from scratch',
            'impact' => 'critical',
            'difficulty' => 'extreme',
            'confidence_score' => 0.80,
        ]);

        $fillIn = Opportunity::create([
            'project_id' => $project->id,
            'title' => 'Dark Mode Toggle',
            'description' => 'Add toggle in user settings',
            'impact' => 'low',
            'difficulty' => 'low',
            'confidence_score' => 0.90,
        ]);

        $thankless = Opportunity::create([
            'project_id' => $project->id,
            'title' => 'Legacy XML Export',
            'description' => 'Support obsolete legacy data format',
            'impact' => 'low',
            'difficulty' => 'extreme',
            'confidence_score' => 0.50,
        ]);

        $this->assertEquals('quick_wins', $quickWin->quadrant);
        $this->assertEquals('major_projects', $majorProject->quadrant);
        $this->assertEquals('fill_ins', $fillIn->quadrant);
        $this->assertEquals('thankless_tasks', $thankless->quadrant);

        $this->assertGreaterThan(50, $quickWin->priority_score);
    }

    public function test_ranking_service_sorts_opportunities_by_priority(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'Prioritization Test',
            'status' => ProjectStatus::ACTIVE,
        ]);

        Opportunity::create([
            'project_id' => $project->id,
            'title' => 'Urgent Conversion Win',
            'description' => 'Quick win with high confidence',
            'impact' => 'critical',
            'difficulty' => 'low',
            'confidence_score' => 0.95,
        ]);

        Opportunity::create([
            'project_id' => $project->id,
            'title' => 'Moderate Quick Win',
            'description' => 'Medium effort win',
            'impact' => 'high',
            'difficulty' => 'medium',
            'confidence_score' => 0.80,
        ]);

        $ranked = $this->rankingService->rank($project);

        $this->assertEquals(2, $ranked['total_opportunities']);
        $this->assertCount(2, $ranked['quick_wins']);
        $this->assertEquals('Urgent Conversion Win', $ranked['top_recommendation']->title);
    }
}
