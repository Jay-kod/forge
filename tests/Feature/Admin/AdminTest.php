<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Modules\Credits\Actions\GrantCreditsAction;
use App\Modules\Identity\Enums\UserRole;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guest_is_redirected_from_admin(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_is_forbidden_from_admin(): void
    {
        $user = User::factory()->create(['role' => UserRole::USER]);

        $response = $this->actingAs($user)->get('/admin');
        $response->assertForbidden();
    }

    public function test_admin_can_access_dashboard(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);
    }

    public function test_admin_can_grant_credits_to_user(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $targetUser = User::factory()->create();

        $response = $this->actingAs($admin)->post("/admin/users/{$targetUser->id}/credits", [
            'amount' => 150,
            'reason' => 'VIP early access grant',
        ]);

        $response->assertRedirect();
        $this->assertEquals(150, $targetUser->creditAccount->fresh()->balance);
    }

    public function test_admin_can_update_user_role(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $targetUser = User::factory()->create(['role' => UserRole::USER]);

        $response = $this->actingAs($admin)->post("/admin/users/{$targetUser->id}/role", [
            'role' => 'admin',
        ]);

        $response->assertRedirect();
        $this->assertEquals(UserRole::ADMIN, $targetUser->fresh()->role);
    }
}
