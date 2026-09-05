<?php

declare(strict_types=1);

namespace Tests\Feature\E2E;

use App\Models\User;
use App\Modules\Billing\Contracts\EntitlementServiceInterface;
use App\Modules\Credits\Actions\GrantCreditsAction;
use App\Modules\Product\Actions\ExecuteStageAction;
use App\Modules\Projects\Enums\WorkflowMode;
use App\Modules\Projects\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FullProductJourneyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    public function test_complete_product_lifecycle_journey(): void
    {
        // 1. User Registration & Provisioning
        $user = User::factory()->create([
            'email' => 'founder@example.com',
            'name' => 'Tech Founder',
        ]);

        // Grant credits & pro plan entitlement for export
        app(GrantCreditsAction::class)->execute($user, 500, 'welcome_grant');
        $proPlan = \App\Modules\Billing\Models\Plan::where('slug', 'pro')->first();
        \App\Modules\Billing\Models\Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $proPlan->id,
            'status' => 'active',
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
        ]);

        $this->assertTrue(app(EntitlementServiceInterface::class)->can($user, 'export.package'));

        // 2. Project Creation via Web Request
        $projectResponse = $this->actingAs($user)->post('/projects', [
            'title' => 'Nexus Cloud Broker',
            'goal' => 'An automated intelligent broker and cost optimizer for hybrid cloud resources',
            'mode' => 'page_by_page',
        ]);

        $projectResponse->assertRedirect();
        $project = Project::where('user_id', $user->id)->first();
        $this->assertNotNull($project);
        $this->assertEquals('Nexus Cloud Broker', $project->title);
        $this->assertNotNull($project->workflow);

        // 3. Workflow Progression: Discovery Stage
        $stages = $project->workflow->stages()->orderBy('order')->get();
        $this->assertNotEmpty($stages);

        $firstStage = $stages->first();
        $advanceResponse = $this->actingAs($user)->post("/projects/{$project->id}/stages/{$firstStage->id}/advance");
        $advanceResponse->assertRedirect();

        // 4. Poll Workflow Status Endpoint
        $statusResponse = $this->actingAs($user)->getJson("/projects/{$project->id}/workflow/status");
        $statusResponse->assertStatus(200);
        $this->assertEquals($project->id, $statusResponse->json('project_id'));
        $this->assertIsArray($statusResponse->json('stages'));

        // 5. Execute Remaining Stages to Assemble Package
        $executeStageAction = app(ExecuteStageAction::class);
        foreach ($stages->slice(1) as $stage) {
            $executeStageAction->execute($user, $project, $stage);
        }

        // 6. Generate HMAC Signed Download URL for AI Package
        $signedUrlResponse = $this->actingAs($user)->postJson("/projects/{$project->id}/export/signed-url", [
            'type' => 'package',
        ]);

        $signedUrlResponse->assertStatus(200);
        $signedUrl = $signedUrlResponse->json('url');
        $this->assertNotEmpty($signedUrl);
        $this->assertStringContainsString('signature=', $signedUrl);

        // 7. Download AI Package Using Signed URL
        $downloadResponse = $this->get($signedUrl);
        $downloadResponse->assertStatus(200);
        $this->assertStringContainsString('application/zip', $downloadResponse->headers->get('Content-Type') ?? '');

        // 8. Verify Project Version Snapshot & Auditing
        $project->refresh();
        $this->assertGreaterThan(0, $project->versions()->count());
        $this->assertLessThan(500, $user->creditAccount->fresh()->balance);
    }
}
