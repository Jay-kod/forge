<?php

declare(strict_types=1);

namespace App\Modules\AI\Providers;

use App\Modules\AI\Contracts\AIProviderInterface;
use App\Modules\AI\DTOs\AIRequest;
use App\Modules\AI\DTOs\AIResponse;
use App\Modules\AI\DTOs\WorkloadEstimate;
use App\Modules\AI\Enums\WorkloadClass;

class AnthropicProvider implements AIProviderInterface
{
    public function __construct(
        protected ?string $apiKey = null
    ) {
        $this->apiKey = $apiKey ?? config('services.anthropic.key');
    }

    public function identifier(): string
    {
        return 'anthropic';
    }

    public function complete(AIRequest $request): AIResponse
    {
        // Provider implementation stub
        $model = match ($request->workloadClass) {
            WorkloadClass::LIGHT => 'claude-3-5-haiku-20241022',
            WorkloadClass::STANDARD => 'claude-3-7-sonnet-20250219',
            WorkloadClass::DEEP, WorkloadClass::EXTREME => 'claude-opus-4-6',
        };

        return new AIResponse(
            content: "Synthesized intelligence analysis from evidence for: {$request->prompt}",
            provider: $this->identifier(),
            model: $model,
            inputTokens: 1200,
            outputTokens: 800,
            creditsConsumed: $request->workloadClass->defaultCredits(),
            latencySeconds: 1.5,
            metadata: ['workload' => $request->workloadClass->value]
        );
    }

    public function estimate(AIRequest $request): WorkloadEstimate
    {
        return new WorkloadEstimate(
            credits: $request->workloadClass->defaultCredits(),
            estimatedLatencySeconds: match ($request->workloadClass) {
                WorkloadClass::LIGHT => 3.0,
                WorkloadClass::STANDARD => 15.0,
                WorkloadClass::DEEP => 45.0,
                WorkloadClass::EXTREME => 120.0,
            },
            workloadClass: $request->workloadClass,
            recommendedModel: 'claude-3-7-sonnet'
        );
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function supports(WorkloadClass $workload): bool
    {
        return true;
    }
}
