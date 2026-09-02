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
}
