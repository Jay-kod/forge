<?php

declare(strict_types=1);

namespace Tests\Feature\AI;

use App\Models\User;
use App\Modules\AI\DTOs\AIRequest;
use App\Modules\AI\DTOs\AIResponse;
use App\Modules\AI\Models\ByokCredential;
use App\Modules\AI\Services\AIOrchestrator;
use App\Modules\AI\Services\ByokService;
use App\Modules\Credits\Services\CreditService;
use App\Modules\Projects\Enums\ProjectStatus;
use App\Modules\Projects\Enums\ProjectType;
use App\Modules\Projects\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ByokTest extends TestCase
{
    use RefreshDatabase;

    public function test_byok_keys_are_stored_encrypted_and_never_exposed_raw(): void
    {
        $user = User::factory()->create();
        $byokService = app(ByokService::class);

        $credential = $byokService->storeCredential(
            user: $user,
            provider: 'openai',
            plainKey: 'sk-proj-supersecretkey1234567890abcdef'
        );

        $this->assertInstanceOf(ByokCredential::class, $credential);
        $this->assertEquals('openai', $credential->provider);

        // 1. Raw DB payload must NOT contain the plain key (AES-256 encrypted)
        $rawRow = DB::table('byok_credentials')->where('id', $credential->id)->first();
        $this->assertNotEquals('sk-proj-supersecretkey1234567890abcdef', $rawRow->api_key);

        // 2. Service decrypts correctly on demand
        $decrypted = $byokService->getPlainKey($user, 'openai');
        $this->assertEquals('sk-proj-supersecretkey1234567890abcdef', $decrypted);

        // 3. Serialized JSON hides plaintext and appends masked key
        $json = $credential->toArray();
        $this->assertArrayNotHasKey('api_key', $json);
        $this->assertArrayHasKey('masked_key', $json);
        $this->assertStringStartsWith('sk-p••••••••', $json['masked_key']);
    }

    public function test_byok_slashes_credit_costs_in_ai_orchestrator(): void
    {
        $user = User::factory()->create();
        $creditService = app(CreditService::class);
        $creditService->grant($user, 50, 'testing');

        $byokService = app(ByokService::class);
        $byokService->storeCredential($user, 'openai', 'sk-proj-customer-key-9999');

        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'BYOK Venture',
            'classification' => ProjectType::NEW_PRODUCT,
            'status' => ProjectStatus::ACTIVE,
        ]);

        $mockProvider = new class implements \App\Modules\AI\Contracts\AIProviderInterface {
            public function identifier(): string { return 'openai'; }
            public function isAvailable(): bool { return true; }
            public function supports(\App\Modules\AI\Enums\WorkloadClass $class): bool { return true; }
            public function estimate(\App\Modules\AI\DTOs\AIRequest $request): \App\Modules\AI\DTOs\WorkloadEstimate {
                return new \App\Modules\AI\DTOs\WorkloadEstimate(
                    credits: 15,
                    estimatedLatencySeconds: 2.0,
                    workloadClass: \App\Modules\AI\Enums\WorkloadClass::STANDARD,
                    recommendedModel: 'gpt-4o'
                );
            }
            public function complete(\App\Modules\AI\DTOs\AIRequest $request): \App\Modules\AI\DTOs\AIResponse {
                return new \App\Modules\AI\DTOs\AIResponse(
                    content: 'Success with BYOK',
                    provider: 'openai',
                    model: 'gpt-4o',
                    inputTokens: 500,
                    outputTokens: 300,
                    creditsConsumed: 1,
                    latencySeconds: 1.2
                );
            }
        };

        $orchestrator = new AIOrchestrator($creditService, new \Psr\Log\NullLogger());
        $orchestrator->registerProvider($mockProvider);

        $request = new AIRequest(
            user: $user,
            prompt: 'Analyze modern market dynamics',
            operationType: 'research.explore',
            projectId: $project->id
        );

        $response = $orchestrator->execute($request);

        $this->assertInstanceOf(AIResponse::class, $response);

        // Standard operation costs 15 credits. Under BYOK, it is slashed to 1 nominal credit!
        // Initial 50 credits - 1 credit = 49 credits remaining!
        $this->assertEquals(49, $creditService->getBalance($user));
    }

    public function test_byok_web_controller_endpoints(): void
    {
        $user = User::factory()->create();

        // 1. Store via POST /settings/byok
        $storeRes = $this->actingAs($user)->postJson(route('byok.store'), [
            'provider' => 'anthropic',
            'api_key' => 'sk-ant-api03-live-test-key-5555',
            'label' => 'Claude 3.5 Sonnet BYOK',
        ]);

        $storeRes->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('credential.provider', 'anthropic');

        // 2. List via GET /settings/byok
        $listRes = $this->actingAs($user)->getJson(route('byok.index'));
        $listRes->assertOk()
            ->assertJsonCount(1, 'credentials')
            ->assertJsonPath('credentials.0.masked_key', 'sk-a••••••••5555');

        // 3. Delete via DELETE /settings/byok/{provider}
        $deleteRes = $this->actingAs($user)->deleteJson(route('byok.destroy', 'anthropic'));
        $deleteRes->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('byok_credentials', ['user_id' => $user->id, 'provider' => 'anthropic']);
    }
}
