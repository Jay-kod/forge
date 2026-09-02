<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\AI;

use App\Models\User;
use App\Modules\AI\Contracts\AIProviderInterface;
use App\Modules\AI\DTOs\AIRequest;
use App\Modules\AI\DTOs\AIResponse;
use App\Modules\AI\DTOs\WorkloadEstimate;
use App\Modules\AI\Enums\WorkloadClass;
use App\Modules\AI\Exceptions\AIValidationException;
use App\Modules\AI\Providers\AnthropicProvider;
use App\Modules\AI\Providers\OpenAIProvider;
use App\Modules\AI\Services\AIOrchestrator;
use App\Modules\AI\Services\AIOutputValidator;
use App\Modules\Credits\Actions\ConfirmCreditsAction;
use App\Modules\Credits\Actions\GrantCreditsAction;
use App\Modules\Credits\Actions\ReleaseCreditsAction;
use App\Modules\Credits\Actions\ReserveCreditsAction;
use App\Modules\Credits\Services\CreditService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Psr\Log\NullLogger;
use RuntimeException;
use Tests\TestCase;

class AIProviderTest extends TestCase
{
    use RefreshDatabase;

    public function test_openai_provider_routing_and_estimates(): void
    {
        $provider = new OpenAIProvider(apiKey: 'sk-test-key');

        $this->assertEquals('openai', $provider->identifier());
        $this->assertTrue($provider->isAvailable());

        $user = User::factory()->create();

        // LIGHT workload
        $lightReq = new AIRequest(user: $user, prompt: 'Quick summary', operationType: 'test', workloadClass: WorkloadClass::LIGHT);
        $resLight = $provider->complete($lightReq);
        $this->assertEquals('gpt-4o-mini', $resLight->model);
        $this->assertEquals(1, $resLight->creditsConsumed);

        // STANDARD workload
        $standardReq = new AIRequest(user: $user, prompt: 'PRD synthesis', operationType: 'test', workloadClass: WorkloadClass::STANDARD);
        $resStandard = $provider->complete($standardReq);
        $this->assertEquals('gpt-4o', $resStandard->model);
        $this->assertEquals(10, $resStandard->creditsConsumed);

        // DEEP workload
        $deepReq = new AIRequest(user: $user, prompt: 'Market research deep analysis', operationType: 'test', workloadClass: WorkloadClass::DEEP);
        $resDeep = $provider->complete($deepReq);
        $this->assertEquals('o3', $resDeep->model);
        $this->assertEquals(20, $resDeep->creditsConsumed);
    }

    public function test_output_validator_validates_json_and_cleans_markdown(): void
    {
        $validator = new AIOutputValidator();

        $markdownJson = "```json\n{\"title\": \"Campus Event Tool\", \"score\": 95}\n```";
        $parsed = $validator->validateJson($markdownJson, ['title', 'score']);

        $this->assertEquals('Campus Event Tool', $parsed['title']);
        $this->assertEquals(95, $parsed['score']);
    }

    public function test_output_validator_throws_on_missing_required_fields(): void
    {
        $validator = new AIOutputValidator();

        $this->expectException(AIValidationException::class);
        $this->expectExceptionMessage('AI JSON response is missing required fields: summary');

        $validator->validateJson('{"title": "Sample"}', ['title', 'summary']);
    }

    public function test_output_validator_validates_text(): void
    {
        $validator = new AIOutputValidator();

        $text = 'This is a valid piece of architecture analysis text.';
        $validated = $validator->validateText($text, 10, 500);

        $this->assertEquals($text, $validated);

        $this->expectException(AIValidationException::class);
        $validator->validateText('Too short', 20, 500);
    }

    public function test_ai_orchestrator_fallback_and_credit_refund_on_failure(): void
    {
        $creditService = new CreditService(
            new GrantCreditsAction(),
            new ReserveCreditsAction(),
            new ConfirmCreditsAction(),
            new ReleaseCreditsAction()
        );

        $user = User::factory()->create();
        $creditService->grant($user, 50);

        // Create a failing primary provider
        $failingPrimary = new class implements AIProviderInterface {
            public function identifier(): string { return 'failing-primary'; }
            public function isAvailable(): bool { return true; }
            public function supports(WorkloadClass $workload): bool { return true; }
            public function estimate(AIRequest $request): WorkloadEstimate {
                return new WorkloadEstimate(10, 1.0, $request->workloadClass, 'model-a');
            }
            public function complete(AIRequest $request): AIResponse {
                throw new Exception('Primary provider timeout');
            }
        };

        // Create a successful secondary fallback provider
        $successfulFallback = new class implements AIProviderInterface {
            public function identifier(): string { return 'working-fallback'; }
            public function isAvailable(): bool { return true; }
            public function supports(WorkloadClass $workload): bool { return true; }
            public function estimate(AIRequest $request): WorkloadEstimate {
                return new WorkloadEstimate(10, 1.0, $request->workloadClass, 'model-b');
            }
            public function complete(AIRequest $request): AIResponse {
                return new AIResponse(
                    content: 'Success from fallback',
                    provider: 'working-fallback',
                    model: 'model-b',
                    inputTokens: 100,
                    outputTokens: 100,
                    creditsConsumed: 10,
                    latencySeconds: 1.0
                );
            }
        };

        $orchestrator = new AIOrchestrator($creditService, new NullLogger());
        $orchestrator->registerProvider($failingPrimary);
        $orchestrator->registerProvider($successfulFallback);

        $request = new AIRequest(
            user: $user,
            prompt: 'Test prompt',
            operationType: 'stage.test',
            workloadClass: WorkloadClass::STANDARD
        );

        // Should complete successfully via fallback
        $response = $orchestrator->execute($request);
        $this->assertEquals('Success from fallback', $response->content);
        $this->assertEquals('working-fallback', $response->provider);
        // 50 - 10 = 40
        $this->assertEquals(40, $creditService->getBalance($user));

        // Now test double failure -> credits refunded
        $failingSecondary = new class implements AIProviderInterface {
            public function identifier(): string { return 'failing-secondary'; }
            public function isAvailable(): bool { return true; }
            public function supports(WorkloadClass $workload): bool { return true; }
            public function estimate(AIRequest $request): WorkloadEstimate {
                return new WorkloadEstimate(10, 1.0, $request->workloadClass, 'model-c');
            }
            public function complete(AIRequest $request): AIResponse {
                throw new Exception('Secondary provider 500 error');
            }
        };

        $failingOrchestrator = new AIOrchestrator($creditService, new NullLogger());
        $failingOrchestrator->registerProvider($failingPrimary);
        $failingOrchestrator->registerProvider($failingSecondary);

        try {
            $failingOrchestrator->execute($request);
            $this->fail('Expected exception was not thrown');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('Credits have been refunded', $e->getMessage());
        }

        // Credits must be untouched (still 40)
        $this->assertEquals(40, $creditService->getBalance($user));
    }
}
