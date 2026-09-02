<?php

declare(strict_types=1);

namespace App\Modules\AI\Providers;

use App\Modules\AI\Contracts\AIProviderInterface;
use App\Modules\AI\DTOs\AIRequest;
use App\Modules\AI\DTOs\AIResponse;
use App\Modules\AI\DTOs\WorkloadEstimate;
use App\Modules\AI\Enums\WorkloadClass;

class OpenAIProvider implements AIProviderInterface
{
    public function __construct(
        protected ?string $apiKey = null
    ) {
        $this->apiKey = $apiKey ?? config('services.openai.key');
    }

    public function identifier(): string
    {
        return 'openai';
    }

    public function complete(AIRequest $request): AIResponse
    {
        $model = match ($request->workloadClass) {
            WorkloadClass::LIGHT => 'gpt-4o-mini',
            WorkloadClass::STANDARD => 'gpt-4o',
            WorkloadClass::DEEP, WorkloadClass::EXTREME => 'o3',
        };

        // Provider implementation stub — returns synthesized response
        // Real HTTP call to OpenAI API will be activated when API keys are configured
        return new AIResponse(
            content: "Synthesized intelligence analysis from evidence for: {$request->prompt}",
            provider: $this->identifier(),
            model: $model,
            inputTokens: 1100,
            outputTokens: 750,
            creditsConsumed: $request->workloadClass->defaultCredits(),
            latencySeconds: 2.0,
            metadata: ['workload' => $request->workloadClass->value]
        );
    }

    public function estimate(AIRequest $request): WorkloadEstimate
    {
        return new WorkloadEstimate(
            credits: $request->workloadClass->defaultCredits(),
            estimatedLatencySeconds: match ($request->workloadClass) {
                WorkloadClass::LIGHT => 2.0,
                WorkloadClass::STANDARD => 12.0,
                WorkloadClass::DEEP => 40.0,
                WorkloadClass::EXTREME => 90.0,
            },
            workloadClass: $request->workloadClass,
            recommendedModel: 'gpt-4o'
        );
    }

    public function isAvailable(): bool
    {
        return !empty($this->apiKey);
    }

    public function supports(WorkloadClass $workload): bool
    {
        return true;
    }
}
