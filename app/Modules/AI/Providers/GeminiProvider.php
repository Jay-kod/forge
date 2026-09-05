<?php

declare(strict_types=1);

namespace App\Modules\AI\Providers;

use App\Modules\AI\Contracts\AIProviderInterface;
use App\Modules\AI\DTOs\AIRequest;
use App\Modules\AI\DTOs\AIResponse;
use App\Modules\AI\DTOs\WorkloadEstimate;
use App\Modules\AI\Enums\WorkloadClass;

class GeminiProvider implements AIProviderInterface
{
    public function __construct(
        protected ?string $apiKey = null
    ) {
        $this->apiKey = $apiKey ?? config('services.gemini.key');
    }

    public function identifier(): string
    {
        return 'gemini';
    }

    public function complete(AIRequest $request): AIResponse
    {
        $model = match ($request->workloadClass) {
            WorkloadClass::LIGHT => 'gemini-2.5-flash',
            WorkloadClass::STANDARD => 'gemini-2.5-pro',
            WorkloadClass::DEEP, WorkloadClass::EXTREME => 'gemini-1.5-pro',
        };

        // Provider implementation stub — returns synthesized intelligence response
        return new AIResponse(
            content: "Synthesized intelligence analysis from evidence for: {$request->prompt}",
            provider: $this->identifier(),
            model: $model,
            inputTokens: 1050,
            outputTokens: 700,
            creditsConsumed: $request->workloadClass->defaultCredits(),
            latencySeconds: 1.2,
            metadata: ['workload' => $request->workloadClass->value]
        );
    }

    public function estimate(AIRequest $request): WorkloadEstimate
    {
        return new WorkloadEstimate(
            credits: $request->workloadClass->defaultCredits(),
            estimatedLatencySeconds: match ($request->workloadClass) {
                WorkloadClass::LIGHT => 1.5,
                WorkloadClass::STANDARD => 8.0,
                WorkloadClass::DEEP => 30.0,
                WorkloadClass::EXTREME => 75.0,
            },
            workloadClass: $request->workloadClass,
            recommendedModel: 'gemini-2.5-pro'
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
