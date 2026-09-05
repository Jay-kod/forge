<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use App\Models\User;
use App\Modules\AI\Services\ByokService;
use App\Modules\API\Models\ApiKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnifiedApiKeyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_unified_api_keys_and_byok_page(): void
    {
        $user = User::factory()->create();

        // Create an API key
        ApiKey::create([
            'user_id' => $user->id,
            'name' => 'CLI Token',
            'prefix' => 'frg_live_test',
            'key_hash' => hash('sha256', 'plain_test_token'),
            'abilities' => ['projects:read', 'projects:write'],
        ]);

        // Create a BYOK credential
        app(ByokService::class)->storeCredential(
            user: $user,
            provider: 'anthropic',
            plainKey: 'sk-ant-api03-test-unified-key',
            label: 'Production Claude Key'
        );

        $response = $this->actingAs($user)->get('/settings/api-keys');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Settings/ApiKeys')
            ->has('api_keys', 1)
            ->has('byok_credentials', 1)
            ->has('supported_providers')
        );
    }

    public function test_byok_web_route_redirects_to_unified_api_keys_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/settings/byok');

        $response->assertRedirect(route('api-keys.index', ['tab' => 'byok']));
    }

    public function test_byok_json_route_returns_credentials(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/settings/byok');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'supported_providers',
            'credentials',
        ]);
    }
}
