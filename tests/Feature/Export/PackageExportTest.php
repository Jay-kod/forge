<?php

declare(strict_types=1);

namespace Tests\Feature\Export;

use App\Models\User;
use App\Modules\Billing\Models\Plan;
use App\Modules\Billing\Models\Subscription;
use App\Modules\Projects\Actions\CreateProjectAction;
use App\Modules\Projects\Enums\WorkflowMode;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackageExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_free_user_cannot_download_package_without_entitlement(): void
    {
        $user = User::factory()->create();

        $project = app(CreateProjectAction::class)->execute(
            user: $user,
            userInput: 'Building a smart CRM for legal firms',
            title: 'LawFlow CRM',
            mode: WorkflowMode::PAGE_BY_PAGE
        );

        $response = $this->actingAs($user)->get(route('export.package', $project));

        $response->assertStatus(402);
    }

    public function test_pro_user_can_download_ai_development_package(): void
    {
        $user = User::factory()->create();
        $proPlan = Plan::where('slug', 'pro')->first();

        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $proPlan->id,
            'status' => 'active',
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
        ]);

        $project = app(CreateProjectAction::class)->execute(
            user: $user,
            userInput: 'Building a smart CRM for legal firms',
            title: 'LawFlow CRM',
            mode: WorkflowMode::PAGE_BY_PAGE
        );

        $response = $this->actingAs($user)->get(route('export.package', $project));

        $response->assertStatus(200);
        $this->assertEquals('application/zip', $response->headers->get('content-type'));
        $this->assertStringContainsString('forge-lawflow-crm-package.zip', (string) $response->headers->get('content-disposition'));
    }

    public function test_unauthorized_user_cannot_download_another_users_package(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        $proPlan = Plan::where('slug', 'pro')->first();
        Subscription::create([
            'user_id' => $stranger->id,
            'plan_id' => $proPlan->id,
            'status' => 'active',
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
        ]);

        $project = app(CreateProjectAction::class)->execute(
            user: $owner,
            userInput: 'Private project idea',
            title: 'Confidential App',
            mode: WorkflowMode::PAGE_BY_PAGE
        );

        $response = $this->actingAs($stranger)->get(route('export.package', $project));

        $response->assertForbidden();
    }
}
