<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Billing;

use App\Models\User;
use App\Modules\Billing\Models\Plan;
use App\Modules\Billing\Models\Subscription;
use App\Modules\Billing\Services\EntitlementService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EntitlementTest extends TestCase
{
    use RefreshDatabase;

    protected EntitlementService $entitlementService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->entitlementService = new EntitlementService();
    }

    public function test_free_user_has_free_entitlements_and_limits(): void
    {
        $user = User::factory()->create();
        $freePlan = Plan::where('slug', 'free')->first();

        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $freePlan->id,
            'status' => 'active',
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
        ]);

        // Free plan can create projects with limit of 1
        $this->assertTrue($this->entitlementService->can($user, 'project.create'));
        $this->assertEquals(1, $this->entitlementService->getLimit($user, 'project.create'));

        // Free plan has basic research and page_by_page workflow
        $this->assertTrue($this->entitlementService->can($user, 'research.basic'));
        $this->assertTrue($this->entitlementService->can($user, 'workflow.page_by_page'));

        // Free plan cannot do deep research, package export, or automatic workflow
        $this->assertFalse($this->entitlementService->can($user, 'research.deep'));
        $this->assertFalse($this->entitlementService->can($user, 'export.package'));
        $this->assertFalse($this->entitlementService->can($user, 'workflow.automatic'));
    }

    public function test_pro_user_has_unlimited_entitlements(): void
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

        // Pro user has unlimited project creation
        $this->assertTrue($this->entitlementService->can($user, 'project.create'));
        $this->assertNull($this->entitlementService->getLimit($user, 'project.create'));

        // Pro user has deep research, package export, clean PDF, and automatic workflow
        $this->assertTrue($this->entitlementService->can($user, 'research.deep'));
        $this->assertTrue($this->entitlementService->can($user, 'export.package'));
        $this->assertTrue($this->entitlementService->can($user, 'export.pdf.clean'));
        $this->assertTrue($this->entitlementService->can($user, 'workflow.automatic'));
    }

    public function test_user_without_subscription_falls_back_to_free_plan(): void
    {
        $user = User::factory()->create();

        // Should resolve default free plan
        $this->assertTrue($this->entitlementService->can($user, 'project.create'));
        $this->assertEquals(1, $this->entitlementService->getLimit($user, 'project.create'));
        $this->assertFalse($this->entitlementService->can($user, 'research.deep'));
    }
}
