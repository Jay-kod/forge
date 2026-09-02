<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\AI;

use App\Models\User;
use App\Modules\AI\Contracts\AIProviderInterface;
use App\Modules\AI\DTOs\AIRequest;
use App\Modules\AI\DTOs\AIResponse;
use App\Modules\AI\DTOs\WorkloadEstimate;
use App\Modules\AI\Enums\WorkloadClass;
use App\Modules\AI\Services\AIOrchestrator;
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

class AIOrchestrationTest extends TestCase
{
    use RefreshDatabase;

    protected CreditService $creditService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->creditService = new CreditService(
            new GrantCreditsAction(),
            new ReserveCreditsAction(),
            new ConfirmCreditsAction(),
            new ReleaseCreditsAction()
        );
    }

    public function test_orchestrator_routes_to_supported_available_provider(): void
    {
        $user = User::factory()->create();
        $this->creditService->grant($user, 100);

        // Provider that only supports LIGHT
        $lightOnlyProvider = new class implements AIProviderInterface {
            public function identifier(): string { return 'light-specialist'; }
            public function isAvailable(): bool { return true; }
            public function supports(WorkloadClass $workload): bool { return $workload === WorkloadClass::LIGHT; }
            public function estimate(AIRequest $request): WorkloadEstimate {
                return new WorkloadEstimate(1, 1.0, $request->workloadClass, 'light-model');
            }
            public function complete(AIRequest $request): AIResponse {
                return new AIResponse('Light result', $this->identifier(), 'light-model', 10, 10, 1, 0.5);
            }
        };

        // Provider that supports DEEP
        $deepProvider = new class implements AIProviderInterface {
            public function identifier(): string { return 'deep-specialist'; }
            public function isAvailable(): bool { return true; }
            public function supports(WorkloadClass $workload): bool { return $workload === WorkloadClass::DEEP; }
            public function estimate(AIRequest $request): WorkloadEstimate {
                return new WorkloadEstimate(20, 5.0, $request->workloadClass, 'deep-model');
            }
            public function complete(AIRequest $request): AIResponse {
                return new AIResponse('Deep reasoning result', $this->identifier(), 'deep-model', 100, 100, 20, 2.0);
            }
        };

        $orchestrator = new AIOrchestrator($this->creditService, new NullLogger());
        $orchestrator->registerProvider($lightOnlyProvider);
        $orchestrator->registerProvider($deepProvider);

        // Request DEEP workload
        $deepReq = new AIRequest($user, 'Deep market query', 'research.deep', WorkloadClass::DEEP);
        $deepRes = $orchestrator->execute($deepReq);

        $this->assertEquals('deep-specialist', $deepRes->provider);
        $this->assertEquals('Deep reasoning result', $deepRes->content);
        $this->assertEquals(80, $this->creditService->getBalance($user));

        // Request LIGHT workload
        $lightReq = new AIRequest($user, 'Quick query', 'summary', WorkloadClass::LIGHT);
        $lightRes = $orchestrator->execute($lightReq);

        $this->assertEquals('light-specialist', $lightRes->provider);
        $this->assertEquals(79, $this->creditService->getBalance($user));
    }

    public function test_orchestrator_skips_unavailable_providers(): void
    {
        $user = User::factory()->create();
        $this->creditService->grant($user, 50);

        $offlineProvider = new class implements AIProviderInterface {
            public function identifier(): string { return 'offline'; }
            public function isAvailable(): bool { return false; }
            public function supports(WorkloadClass $workload): bool { return true; }
            public function estimate(AIRequest $request): WorkloadEstimate {
                return new WorkloadEstimate(10, 1.0, $request->workloadClass, 'offline-model');
            }
            public function complete(AIRequest $request): AIResponse {
                throw new Exception('Should not be called');
            }
        };

        $onlineProvider = new class implements AIProviderInterface {
            public function identifier(): string { return 'online'; }
            public function isAvailable(): bool { return true; }
            public function supports(WorkloadClass $workload): bool { return true; }
            public function estimate(AIRequest $request): WorkloadEstimate {
                return new WorkloadEstimate(10, 1.0, $request->workloadClass, 'online-model');
            }
            public function complete(AIRequest $request): AIResponse {
                return new AIResponse('Online answer', $this->identifier(), 'online-model', 50, 50, 10, 1.0);
            }
        };

        $orchestrator = new AIOrchestrator($this->creditService, new NullLogger());
        $orchestrator->registerProvider($offlineProvider);
        $orchestrator->registerProvider($onlineProvider);

        $request = new AIRequest($user, 'Prompt', 'operation', WorkloadClass::STANDARD);
        $response = $orchestrator->execute($request);

        $this->assertEquals('online', $response->provider);
    }
}
