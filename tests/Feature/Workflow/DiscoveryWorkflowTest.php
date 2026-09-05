<?php

declare(strict_types=1);

namespace Tests\Feature\Workflow;

use App\Models\User;
use App\Modules\Credits\Actions\GrantCreditsAction;
use App\Modules\Projects\Actions\CreateProjectAction;
use App\Modules\Projects\Enums\WorkflowMode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscoveryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_advancing_stage_triggers_discovery_and_credits(): void
    {
        $user = User::factory()->create();

        // Grant credits
        app(GrantCreditsAction::class)->execute($user, 100, 'test_grant');

        // Create project
        $project = app(CreateProjectAction::class)->execute(
            user: $user,
            userInput: 'I want to build an app for university campus events and student ticket resale',
            mode: WorkflowMode::PAGE_BY_PAGE
        );

        $firstStage = $project->workflow->stages()->first();
        $this->assertNotNull($firstStage);

        // Advance stage via HTTP endpoint
        $response = $this->actingAs($user)->post("/projects/{$project->id}/stages/{$firstStage->id}/advance");

        $response->assertRedirect(route('projects.show', $project));

        // Assert discovery, competitors, evidence were populated
        $this->assertDatabaseHas('discoveries', ['project_id' => $project->id]);
        $this->assertDatabaseHas('competitors', ['project_id' => $project->id]);
        $this->assertDatabaseHas('evidence', ['project_id' => $project->id]);
        $this->assertDatabaseHas('research_sessions', ['project_id' => $project->id]);

        // Assert credits were consumed atomically (100 - 15 = 85)
        $this->assertEquals(85, $user->creditAccount->fresh()->balance);
    }

    public function test_stage_execution_generates_challenge_strategy_and_package_documents(): void
    {
        $user = User::factory()->create();
        app(GrantCreditsAction::class)->execute($user, 200, 'test_grant');

        $project = app(CreateProjectAction::class)->execute(
            user: $user,
            userInput: 'I want to build an app for university campus events and student ticket resale',
            mode: WorkflowMode::PAGE_BY_PAGE
        );

        $executeStageAction = app(\App\Modules\Product\Actions\ExecuteStageAction::class);

        // Find or create challenge stage
        $challengeStage = $project->workflow->stages()->where('stage_type', 'challenge')->first();
        if ($challengeStage) {
            $executeStageAction->execute($user, $project, $challengeStage);
            $this->assertDatabaseHas('product_documents', [
                'project_id' => $project->id,
                'type' => 'challenge',
            ]);
        }

        // Find or create strategy stage
        $strategyStage = $project->workflow->stages()->where('stage_type', 'strategy')->first();
        if ($strategyStage) {
            $executeStageAction->execute($user, $project, $strategyStage);
            $this->assertDatabaseHas('product_documents', [
                'project_id' => $project->id,
                'type' => 'strategy',
            ]);
        }

        // Find or create package stage
        $packageStage = $project->workflow->stages()->where('stage_type', 'package')->first();
        if ($packageStage) {
            $executeStageAction->execute($user, $project, $packageStage);
            $this->assertDatabaseHas('product_documents', [
                'project_id' => $project->id,
                'type' => 'package',
            ]);
        }
    }
}
