<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Admin;

use App\Models\User;
use App\Modules\Admin\Services\AdminService;
use App\Modules\Credits\Contracts\CreditServiceInterface;
use App\Modules\Identity\Enums\UserRole;
use App\Modules\Projects\Enums\ProjectType;
use App\Modules\Projects\Models\Project;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AdminService $adminService;
    protected User $admin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $this->regularUser = User::factory()->create(['role' => UserRole::USER]);

        $this->adminService = new AdminService(
            app(CreditServiceInterface::class)
        );
    }

    public function test_get_dashboard_stats_returns_metrics(): void
    {
        Project::create([
            'user_id' => $this->regularUser->id,
            'title' => 'Test Project',
            'classification' => ProjectType::NEW_PRODUCT,
            'status' => \App\Modules\Projects\Enums\ProjectStatus::ACTIVE,
        ]);

        $stats = $this->adminService->getDashboardStats();

        $this->assertArrayHasKey('total_users', $stats);
        $this->assertArrayHasKey('total_projects', $stats);
        $this->assertArrayHasKey('total_credits_balance', $stats);
        $this->assertArrayHasKey('total_credits_consumed', $stats);
        $this->assertArrayHasKey('plans', $stats);
        $this->assertGreaterThanOrEqual(2, $stats['total_users']);
        $this->assertGreaterThanOrEqual(1, $stats['total_projects']);
    }

    public function test_get_paginated_users_and_search_filter(): void
    {
        $users = $this->adminService->getPaginatedUsers(null, 10);
        $this->assertGreaterThanOrEqual(2, $users->total());

        $filtered = $this->adminService->getPaginatedUsers($this->regularUser->email, 10);
        $this->assertEquals(1, $filtered->total());
        $this->assertEquals($this->regularUser->id, $filtered->items()[0]->id);
    }

    public function test_grant_credits_increases_balance_with_audit_attribution(): void
    {
        $this->adminService->grantCredits($this->admin, $this->regularUser, 75, 'Reward for beta feedback');

        $creditAccount = $this->regularUser->creditAccount()->first();
        $this->assertNotNull($creditAccount);
        $this->assertGreaterThanOrEqual(75, $creditAccount->balance);

        $tx = $creditAccount->transactions()->latest('id')->first();
        $this->assertNotNull($tx);
        $this->assertEquals(75, $tx->amount);
        $this->assertEquals('admin_grant', $tx->reference_type);
        $this->assertStringContainsString('admin_' . $this->admin->id, $tx->reference_id);
    }

    public function test_update_user_role(): void
    {
        $this->assertEquals(UserRole::USER, $this->regularUser->role);

        $this->adminService->updateUserRole($this->regularUser, UserRole::ADMIN);
        $this->regularUser->refresh();

        $this->assertEquals(UserRole::ADMIN, $this->regularUser->role);
    }
}
