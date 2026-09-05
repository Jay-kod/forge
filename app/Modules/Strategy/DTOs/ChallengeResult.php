<?php

declare(strict_types=1);

namespace App\Modules\Strategy\DTOs;

readonly class ChallengeResult
{
    /**
     * @param array<int, array{
     *     assumption: string,
     *     challenge: string,
     *     evidence_ref: ?string,
     *     severity: string,
     *     recommended_action: string
     * }> $challenges
     */
    public function __construct(
        public array $challenges,
        public float $overallRiskScore,
        public string $summary,
        public array $defensibilityFlags
    ) {}

    public function toArray(): array
    {
        return [
            'challenges' => $this->challenges,
            'overall_risk_score' => $this->overallRiskScore,
            'summary' => $this->summary,
            'defensibility_flags' => $this->defensibilityFlags,
        ];
    }
}
