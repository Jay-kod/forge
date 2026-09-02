<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Research;

use App\Models\User;
use App\Modules\Credits\Actions\ConfirmCreditsAction;
use App\Modules\Credits\Actions\GrantCreditsAction;
use App\Modules\Credits\Actions\ReleaseCreditsAction;
use App\Modules\Credits\Actions\ReserveCreditsAction;
use App\Modules\Credits\Services\CreditService;
use App\Modules\Discovery\Services\CompetitorAnalysisService;
use App\Modules\Discovery\Services\DiscoveryService;
use App\Modules\Evidence\Models\Evidence;
use App\Modules\Evidence\Services\EvidenceService;
use App\Modules\Projects\Enums\ProjectStatus;
use App\Modules\Projects\Models\Project;
use App\Modules\Research\Actions\RefreshResearchAction;
use App\Modules\Research\Models\ResearchSession;
use App\Modules\Research\Services\ResearchEngine;
use App\Modules\Research\Services\WebSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResearchRefreshTest extends TestCase
{
    use RefreshDatabase;

    protected CreditService $creditService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->creditService = new CreditService(
            new GrantCreditsAction(),
            new ReserveCreditsAction(),
            new ConfirmCreditsAction(),
            new ReleaseCreditsAction()
        );
    }

    public function test_freshness_indicators_accurately_classify_age(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'Freshness Test',
            'status' => ProjectStatus::ACTIVE,
        ]);

        // Fresh session (0 days)
        $freshSession = ResearchSession::create([
            'project_id' => $project->id,
            'type' => 'market',
            'status' => 'completed',
            'completed_at' => now(),
        ]);
        $this->assertEquals('fresh', $freshSession->freshness);

        // Aging session (45 days)
        $agingSession = ResearchSession::create([
            'project_id' => $project->id,
            'type' => 'market',
            'status' => 'completed',
            'completed_at' => now()->subDays(45),
        ]);
        $this->assertEquals('aging', $agingSession->freshness);

        // Stale session (100 days)
        $staleSession = ResearchSession::create([
            'project_id' => $project->id,
            'type' => 'market',
            'status' => 'completed',
            'completed_at' => now()->subDays(100),
        ]);
        $this->assertEquals('stale', $staleSession->freshness);
    }

    public function test_refresh_research_action_creates_new_session_and_deducts_credits(): void
    {
        $user = User::factory()->create();
        $this->creditService->grant($user, 100);

        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'E-Commerce Analytics Engine',
            'description' => 'Real-time revenue attribution software for Shopify merchants',
            'status' => ProjectStatus::ACTIVE,
        ]);

        $action = new RefreshResearchAction(
            $this->creditService,
            new ResearchEngine(new WebSearchService()),
            new EvidenceService(),
            new CompetitorAnalysisService(),
            new DiscoveryService()
        );

        $session1 = $action->execute($user, $project);
        $this->assertEquals(85, $this->creditService->getBalance($user));
        $this->assertCount(1, $project->researchSessions);

        $session2 = $action->execute($user, $project);
        $this->assertEquals(70, $this->creditService->getBalance($user));
        $this->assertCount(2, $project->fresh()->researchSessions);
        $this->assertNotEquals($session1->id, $session2->id);
    }
}
