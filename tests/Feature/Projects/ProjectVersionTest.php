<?php

declare(strict_types=1);

namespace Tests\Feature\Projects;

use App\Models\User;
use App\Modules\Credits\Services\CreditService;
use App\Modules\Product\Models\ProductDocument;
use App\Modules\Projects\Actions\CreateProjectAction;
use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Models\ProjectVersion;
use App\Modules\Projects\Services\ProjectVersionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectVersionTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_snapshot_persists_full_project_state(): void
    {
        $user = User::factory()->create();
        $project = app(CreateProjectAction::class)->execute($user, 'Build an automated logistics dispatch platform');

        ProductDocument::create([
            'project_id' => $project->id,
            'type' => 'prd',
            'title' => 'Logistics PRD v1',
            'content' => 'Full logistics specifications',
            'version' => 1,
            'status' => 'approved',
        ]);

        $versionService = app(ProjectVersionService::class);
        $v1 = $versionService->createSnapshot($project, 'user', 'Initial specifications snapshot');

        $this->assertInstanceOf(ProjectVersion::class, $v1);
        $this->assertEquals(2, $v1->version);
        $this->assertEquals($project->id, $v1->project_id);
        $this->assertNotEmpty($v1->snapshot['documents']);
        $this->assertEquals('Logistics PRD v1', $v1->snapshot['documents'][0]['title']);

        // Next snapshot should increment version to 3
        $v2 = $versionService->createSnapshot($project, 'system', 'Second checkpoint');
        $this->assertEquals(3, $v2->version);

        // Compare v1 and v2
        $comparison = $versionService->compare($v1, $v2);
        $this->assertEquals(2, $comparison['version_old']);
        $this->assertEquals(3, $comparison['version_new']);
    }

    public function test_stage_rerun_snapshots_prior_state_and_deducts_credits(): void
    {
        $user = User::factory()->create();
        $creditService = app(CreditService::class);
        $creditService->grant($user, 50, 'test_allowance');

        $project = app(CreateProjectAction::class)->execute($user, 'B2B billing SaaS platform');
        $stage = $project->workflow->stages()->first();

        // Execute stage initially (costs 15 credits: 50 -> 35)
        app(\App\Modules\Product\Actions\ExecuteStageAction::class)->execute($user, $project, $stage);
        $this->assertEquals(35, $creditService->getBalance($user));

        // Re-run the stage: archives prior state as snapshot and deducts 15 credits (35 -> 20)
        $response = $this->actingAs($user)->postJson(
            route('workflow.rerun', ['project' => $project, 'stage' => $stage]),
            ['note' => 'Re-evaluating with updated market assumptions']
        );

        $response->assertOk()
            ->assertJsonPath('success', true);

        // Prior state must be archived as a version snapshot
        $this->assertDatabaseHas('project_versions', [
            'project_id' => $project->id,
            'version' => 2,
            'created_by' => 'regeneration',
        ]);

        // Credits must be deducted atomically (35 - 15 = 20)
        $this->assertEquals(20, $creditService->getBalance($user));
    }

    public function test_timeline_endpoint_returns_chronological_decisions(): void
    {
        $user = User::factory()->create();
        $project = app(CreateProjectAction::class)->execute($user, 'FinTech cross-border remittance app');

        // Approve a stage to generate timeline event
        $stage = $project->workflow->stages()->first();
        $stage->update([
            'status' => 'completed',
            'approved_at' => now(),
            'content' => ['summary' => 'Initial scope approved.'],
        ]);

        // Create snapshot
        app(ProjectVersionService::class)->createSnapshot($project, 'user', 'Scope approval milestone');

        $response = $this->actingAs($user)->getJson(route('projects.timeline', $project));

        $response->assertOk()
            ->assertJsonStructure(['timeline']);

        $timeline = $response->json('timeline');
        $this->assertNotEmpty($timeline);

        $types = array_column($timeline, 'type');
        $this->assertContains('stage_approval', $types);
        $this->assertContains('version_snapshot', $types);
    }

    public function test_unauthorized_user_cannot_rerun_stage(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        $project = app(CreateProjectAction::class)->execute($owner, 'Confidential AI platform');
        $stage = $project->workflow->stages()->first();

        $response = $this->actingAs($stranger)->postJson(
            route('workflow.rerun', ['project' => $project, 'stage' => $stage])
        );

        $response->assertForbidden();
    }
}
