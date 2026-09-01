<?php

declare(strict_types=1);

namespace App\Modules\AI\DTOs;

use App\Modules\AI\Enums\WorkloadClass;

readonly class AIResponse
{
    public function __construct(
        public string $content,
        public string $provider,
        public string $model,
        public int $inputTokens,
        public int $outputTokens,
        public int $creditsConsumed,
        public float $latencySeconds,
        public array $metadata = []
    ) {}
}
