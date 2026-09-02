<?php

declare(strict_types=1);

namespace Tests\Feature\Export;

use App\Models\User;
use App\Modules\Billing\Services\SubscriptionService;
use App\Modules\Projects\Enums\ProjectStatus;
use App\Modules\Projects\Enums\ProjectType;
use App\Modules\Projects\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GrowthPlanPdfExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_download_project_growth_plan_pdf(): void
    {
        $user = User::factory()->create();
        app(SubscriptionService::class)->provisionFreePlan($user);

        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'Fitness Gym Growth Initiative',
            'description' => 'Scale local gym membership through corporate wellness packages',
            'classification' => ProjectType::BUSINESS_GROWTH,
            'status' => ProjectStatus::ACTIVE,
        ]);

        $project->documents()->create([
            'title' => 'Comprehensive Growth Plan',
            'type' => 'growth_plan',
            'content' => 'Executive Growth Levers and 90-Day Milestones.',
            'version' => 1,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($user)->get(route('export.growth-plan', $project));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_unauthorized_user_cannot_download_growth_plan_pdf(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        $project = Project::create([
            'user_id' => $owner->id,
            'title' => 'Secret Enterprise Growth Strategy',
            'description' => 'Confidential business growth roadmap',
            'classification' => ProjectType::BUSINESS_GROWTH,
            'status' => ProjectStatus::ACTIVE,
        ]);

        $response = $this->actingAs($stranger)->get(route('export.growth-plan', $project));

        $response->assertStatus(403);
    }
}
