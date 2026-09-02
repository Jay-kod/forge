<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use App\Models\User;
use App\Modules\API\Models\ApiKey;
use App\Modules\API\Services\ApiKeyService;
use App\Modules\Organizations\Services\OrganizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiKeyAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_key_generation_and_hashing(): void
    {
        $user = User::factory()->create();
        $apiKeyService = app(ApiKeyService::class);

        $result = $apiKeyService->createKey($user, 'CI Pipeline Key');

        $this->assertArrayHasKey('plain_token', $result);
        $this->assertArrayHasKey('api_key', $result);
        $this->assertStringStartsWith('forge_live_', $result['plain_token']);

        /** @var ApiKey $keyRecord */
        $keyRecord = $result['api_key'];
        $this->assertEquals(hash('sha256', $result['plain_token']), $keyRecord->key_hash);
        $this->assertTrue($keyRecord->isValid());
    }

    public function test_api_requests_without_key_return_401(): void
    {
        $res = $this->getJson('/api/v1/projects');

        $res->assertStatus(401)
            ->assertJsonPath('error', 'Unauthorized');
    }

    public function test_api_requests_with_invalid_token_return_401(): void
    {
        $res = $this->withHeader('Authorization', 'Bearer forge_live_invalidtoken12345678901234567890')
            ->getJson('/api/v1/projects');

        $res->assertStatus(401)
            ->assertJsonPath('error', 'Unauthorized');
    }

    public function test_valid_api_key_authenticates_and_updates_last_used(): void
    {
        $user = User::factory()->create();
        $result = app(ApiKeyService::class)->createKey($user, 'Production Bot');

        $res = $this->withHeader('Authorization', 'Bearer ' . $result['plain_token'])
            ->getJson('/api/v1/projects');

        $res->assertOk();
        $this->assertNotNull($result['api_key']->fresh()->last_used_at);
    }

    public function test_api_key_management_web_endpoints(): void
    {
        $user = User::factory()->create();

        // 1. Create API key via web endpoint
        $createRes = $this->actingAs($user)->postJson(route('api-keys.store'), [
            'name' => 'GitHub Action Deployer',
            'expires_in_days' => 30,
        ]);

        $createRes->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['api_key' => ['id', 'plain_token', 'prefix']]);

        $keyId = $createRes->json('api_key.id');
        $apiKey = ApiKey::find($keyId);

        // 2. List API keys
        $listRes = $this->actingAs($user)->getJson(route('api-keys.index'));
        $listRes->assertOk()
            ->assertJsonCount(1, 'api_keys');

        // 3. Revoke API key
        $deleteRes = $this->actingAs($user)->deleteJson(route('api-keys.destroy', $apiKey));
        $deleteRes->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('api_keys', ['id' => $keyId]);
    }
}
