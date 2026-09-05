<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Strategy;

use App\Models\User;
use App\Modules\AI\Contracts\AIProviderInterface;
use App\Modules\AI\DTOs\AIRequest;
use App\Modules\AI\DTOs\AIResponse;
use App\Modules\AI\DTOs\WorkloadEstimate;
use App\Modules\AI\Enums\WorkloadClass;
use App\Modules\AI\Services\AIOrchestrator;
use App\Modules\AI\Services\AIOutputValidator;
use App\Modules\Credits\Services\CreditService;
use App\Modules\Projects\Enums\ProjectType;
use App\Modules\Projects\Models\Project;
use App\Modules\Strategy\Actions\ChallengeAssumptionsAction;
use App\Modules\Strategy\Actions\GenerateStrategyAction;
use App\Modules\Strategy\Enums\StrategicRecommendation;
use App\Modules\Strategy\Services\CreativeStrategyService;
use App\Modules\Strategy\Services\StrategyEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Psr\Log\NullLogger;
use Tests\TestCase;

class StrategyEngineTest extends TestCase
{
    use RefreshDatabase;

    protected StrategyEngine $strategyEngine;
    protected User $user;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->project = Project::create([
            'user_id' => $this->user->id,
            'title' => 'Nexus Logistics',
            'description' => 'Real-time dispatch optimization for African inter-city freight',
            'classification' => ProjectType::NEW_PRODUCT,
            'status' => \App\Modules\Projects\Enums\ProjectStatus::ACTIVE,
        ]);

        $creditService = app(CreditService::class);
        $creditService->grant($this->user, 100);

        // Mock AI provider that returns valid strategy JSON
        $mockProvider = new class implements AIProviderInterface {
            public function identifier(): string { return 'mock'; }
            public function isAvailable(): bool { return true; }
            public function supports(WorkloadClass $workload): bool { return true; }
            public function estimate(AIRequest $request): WorkloadEstimate {
                return new WorkloadEstimate(5, 1.0, $request->workloadClass, 'mock-model');
            }
            public function complete(AIRequest $request): AIResponse {
                if ($request->operationType === 'strategy.challenge') {
                    $json = json_encode([
                        'overall_risk_score' => 0.42,
                        'summary' => 'Moderate defensibility with distribution hurdles.',
                        'challenges' => [
                            [
                                'assumption' => 'Drivers have constant smartphone data connectivity',
                                'challenge' => 'Cell towers along inter-city corridors have dead zones',
                                'evidence_ref' => 'Network coverage maps',
                                'severity' => 'HIGH',
                                'recommended_action' => 'Implement offline-first sync architecture',
                            ]
                        ],
                        'defensibility_flags' => ['Carrier switching costs', 'Offline dispatch queue'],
                    ]);
                } else {
                    $json = json_encode([
                        'recommendation' => 'BUILD_WITH_MODIFICATIONS',
                        'posture_title' => 'Offline-Resilient Dispatch Wedge',
                        'rationale' => 'Focusing on offline resilience provides instant differentiation against incumbents.',
                        'core_differentiators' => ['Offline mesh dispatch', 'Local SMS fallback bridge'],
                        'go_to_market_steps' => ['Direct fleet onboarding in Mombasa', 'Driver referral incentive'],
                        'moats' => ['Proprietary offline queue synchronization protocol'],
                    ]);
                }

                return new AIResponse((string) $json, 'mock', 'mock-model', 500, 300, 5, 1.0);
            }
        };

        $orchestrator = new AIOrchestrator($creditService, new NullLogger());
        $orchestrator->registerProvider($mockProvider);

        $validator = new AIOutputValidator();
        $challengeAction = new ChallengeAssumptionsAction($orchestrator, $validator);
        $generateAction = new GenerateStrategyAction($orchestrator, $validator);
        $creativeService = new CreativeStrategyService($orchestrator);

        $this->strategyEngine = new StrategyEngine($challengeAction, $generateAction, $creativeService);
    }

    public function test_challenge_assumptions_returns_structured_risk_and_challenges(): void
    {
        $result = $this->strategyEngine->challengeAssumptions($this->user, $this->project);

        $this->assertEquals(0.42, $result->overallRiskScore);
        $this->assertStringContainsString('Moderate defensibility', $result->summary);
        $this->assertCount(1, $result->challenges);
        $this->assertEquals('HIGH', $result->challenges[0]['severity']);
        $this->assertEquals('Implement offline-first sync architecture', $result->challenges[0]['recommended_action']);
        $this->assertContains('Offline dispatch queue', $result->defensibilityFlags);
    }

    public function test_generate_strategy_synthesizes_verdict_posture_and_markdown(): void
    {
        $result = $this->strategyEngine->generateStrategy($this->user, $this->project);

        $this->assertEquals(StrategicRecommendation::BUILD_WITH_MODIFICATIONS, $result->recommendation);
        $this->assertEquals('Build with Recommended Modifications', $result->recommendation->label());
        $this->assertEquals('Offline-Resilient Dispatch Wedge', $result->postureTitle);
        $this->assertCount(2, $result->coreDifferentiators);
        $this->assertStringContainsString('Offline mesh dispatch', $result->coreDifferentiators[0]);
        $this->assertStringContainsString('# Strategic Recommendation & Posture: Nexus Logistics', $result->markdownReport);
    }

    public function test_creative_strategy_angles(): void
    {
        $angles = $this->strategyEngine->getCreativeAngles($this->user, $this->project);

        $this->assertCount(3, $angles);
        $this->assertArrayHasKey('angle', $angles[0]);
        $this->assertArrayHasKey('rationale', $angles[0]);
    }
}
