<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Modules\Identity\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminApiKeyTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_user_cannot_access_admin_api_keys(): void
    {
        $user = User::factory()->create(['role' => UserRole::USER]);

        $response = $this->actingAs($user)->get('/admin/api-keys');
        $response->assertStatus(403);
    }

    public function test_admin_can_view_system_api_keys_dashboard(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        $response = $this->actingAs($admin)->get('/admin/api-keys');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/ApiKeys')
            ->has('providers')
            ->has('providers.anthropic')
            ->has('providers.openai')
            ->has('providers.gemini')
            ->has('providers.stripe')
            ->has('providers.github')
        );
    }

    public function test_admin_can_test_provider_connectivity(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        // Mock external HTTP call for the test probe
        Http::fake([
            'api.anthropic.com/*' => Http::response(['data' => []], 200),
            'api.openai.com/*' => Http::response(['data' => []], 200),
            'api.stripe.com/*' => Http::response(['object' => 'balance'], 200),
            'api.github.com/*' => Http::response('Keep it logically awesome.', 200),
            '*' => Http::response([], 200),
        ]);

        config(['services.anthropic.key' => 'sk-ant-test-mock-key']);

        $response = $this->actingAs($admin)->postJson('/admin/api-keys/test', [
            'provider' => 'anthropic',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'latency_ms',
            'message',
        ]);
        $this->assertTrue($response->json('success'));
    }

    public function test_test_connection_fails_for_invalid_provider(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        $response = $this->actingAs($admin)->postJson('/admin/api-keys/test', [
            'provider' => 'invalid_provider',
        ]);

        $response->assertStatus(422);
    }
}
