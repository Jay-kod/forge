<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Opportunity;

use App\Models\User;
use App\Modules\Discovery\Models\Competitor;
use App\Modules\GitHub\Models\RepositoryAudit;
use App\Modules\Opportunity\Models\Opportunity;
use App\Modules\Opportunity\Models\Recommendation;
use App\Modules\Opportunity\Services\OpportunityGraphService;
use App\Modules\Projects\Enums\ProjectStatus;
use App\Modules\Projects\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpportunityGraphTest extends TestCase
{
    use RefreshDatabase;

    public function test_graph_service_builds_connected_nodes_and_edges(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'HealthTech Patient Portal',
            'classification' => \App\Modules\Projects\Enums\ProjectType::NEW_PRODUCT,
            'status' => ProjectStatus::ACTIVE,
        ]);

        // Add Competitor
        Competitor::create([
            'project_id' => $project->id,
            'name' => 'MediPortal Competitor',
            'url' => 'https://mediportal.example.com',
            'pricing_model' => 'Subscription',
        ]);

        // Add Opportunity
        $opp = Opportunity::create([
            'project_id' => $project->id,
            'title' => 'One-Click Telehealth Video Integration',
            'description' => 'Direct WebRTC video integration for real-time appointments',
            'category' => 'feature',
            'impact' => 'high',
            'difficulty' => 'low',
        ]);

        // Add Recommendation
        Recommendation::create([
            'project_id' => $project->id,
            'opportunity_id' => $opp->id,
            'title' => 'Implement WebRTC Patient Rooms',
            'description' => 'Use WebRTC for secure video appointments',
            'suggested_action' => 'Deploy WebRTC server',
            'why_it_matters' => 'Increases patient engagement and retention',
            'potential_impact' => 'high',
            'difficulty' => 'low',
        ]);

        // Add Repository Audit
        RepositoryAudit::create([
            'project_id' => $project->id,
            'repo_full_name' => 'healthtech/patient-portal',
            'primary_language' => 'TypeScript',
            'detected_framework' => 'Next.js',
            'code_health_score' => 88,
            'technical_debt_score' => 20,
            'security_score' => 95,
        ]);

        $graphService = new OpportunityGraphService();
        $graph = $graphService->build($project);

        $this->assertNotEmpty($graph['nodes']);
        $this->assertNotEmpty($graph['edges']);

        // Check node types
        $nodeTypes = array_column($graph['nodes'], 'type');
        $this->assertContains('project', $nodeTypes);
        $this->assertContains('competitor', $nodeTypes);
        $this->assertContains('opportunity', $nodeTypes);
        $this->assertContains('recommendation', $nodeTypes);
        $this->assertContains('repository', $nodeTypes);

        // Check edge connection
        $edgeTypes = array_column($graph['edges'], 'type');
        $this->assertContains('connected_codebase', $edgeTypes);
        $this->assertContains('competes_with', $edgeTypes);
        $this->assertContains('delivers_value', $edgeTypes);
        $this->assertContains('implemented_by', $edgeTypes);

        // Check metrics
        $this->assertEquals(5, $graph['metrics']['total_nodes']);
        $this->assertEquals(4, $graph['metrics']['total_edges']);
    }

    public function test_graph_endpoint_returns_json_for_authorized_user(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'FinTech Wallet',
            'status' => ProjectStatus::ACTIVE,
        ]);

        $response = $this->actingAs($user)->getJson(route('projects.graph', $project));

        $response->assertOk()
            ->assertJsonStructure([
                'nodes',
                'edges',
                'metrics' => [
                    'total_nodes',
                    'total_edges',
                ],
            ]);
    }
}
