<?php

declare(strict_types=1);

namespace Tests\Feature\Workflow;

use App\Models\User;
use App\Modules\Projects\Enums\ProjectStatus;
use App\Modules\Projects\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_poll_workflow_status(): void
    {
        $user = User::factory()->create();
        $project = app(\App\Modules\Projects\Actions\CreateProjectAction::class)->execute(
            user: $user,
            userInput: 'Test AI Product for Status Polling',
            mode: \App\Modules\Projects\Enums\WorkflowMode::PAGE_BY_PAGE
        );

        $response = $this->actingAs($user)->getJson("/projects/{$project->id}/workflow/status");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'project_id',
            'percent_complete',
            'active_stage',
            'stages',
        ]);

        $this->assertEquals($project->id, $response->json('project_id'));
    }

    public function test_unauthorized_user_cannot_access_workflow_status(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        $project = Project::create([
            'user_id' => $owner->id,
            'title' => 'Private Project',
            'type' => 'new_product',
            'status' => ProjectStatus::ACTIVE,
        ]);

        $response = $this->actingAs($stranger)->getJson("/projects/{$project->id}/workflow/status");
        $response->assertStatus(403);
    }
}
