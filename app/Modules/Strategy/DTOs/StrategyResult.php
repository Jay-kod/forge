<?php

declare(strict_types=1);

namespace App\Modules\Strategy\DTOs;

use App\Modules\Strategy\Enums\StrategicRecommendation;

readonly class StrategyResult
{
    /**
     * @param array<int, string> $coreDifferentiators
     * @param array<int, string> $go_to_market_steps
     * @param array<int, string> $moats
     * @param array<string, mixed> $rawPayload
     */
    public function __construct(
        public StrategicRecommendation $recommendation,
        public string $postureTitle,
        public string $rationale,
        public array $coreDifferentiators,
        public array $go_to_market_steps,
        public array $moats,
        public string $markdownReport,
        public array $rawPayload = []
    ) {}

    public function toArray(): array
    {
        return [
            'recommendation' => $this->recommendation->value,
            'recommendation_label' => $this->recommendation->label(),
            'posture_title' => $this->postureTitle,
            'rationale' => $this->rationale,
            'core_differentiators' => $this->coreDifferentiators,
            'go_to_market_steps' => $this->go_to_market_steps,
            'moats' => $this->moats,
            'markdown_report' => $this->markdownReport,
        ];
    }
}
