<?php

declare(strict_types=1);

namespace App\Modules\Consent\Services;

use App\Models\User;
use App\Modules\Consent\Enums\ConsentType;
use App\Modules\Consent\Models\LearningSignal;

class LearningSystem
{
    /**
     * Forbidden metadata keys to guarantee zero user or project PII leakage.
     */
    private const FORBIDDEN_KEYS = [
        'user_id',
        'project_id',
        'email',
        'user_email',
        'name',
        'user_name',
        'ip_address',
        'token',
        'password',
        'secret',
    ];

    public function __construct(
        protected ConsentService $consentService
    ) {}

    /**
     * Record an anonymized behavioral or quality learning signal.
     * Respects user consent: returns null if user opted out of AI improvement.
     */
    public function recordSignal(
        User $user,
        string $category,
        string $signalType,
        array $contextMeta = [],
        float $value = 1.0
    ): ?LearningSignal {
        // Strict Consent Gate: Never collect learning signals without explicit opt-in
        if (!$this->consentService->hasConsent($user, ConsentType::AI_IMPROVEMENT)) {
            return null;
        }

        // PII Scrubbing: Sanitize metadata
        $sanitizedMeta = $this->scrubPii($contextMeta);

        return LearningSignal::create([
            'category' => $category,
            'signal_type' => $signalType,
            'context_meta' => $sanitizedMeta,
            'value' => $value,
        ]);
    }

    /**
     * Aggregate learning insights for a recommendation category.
     */
    public function getCategoryMetrics(string $category): array
    {
        $accepted = LearningSignal::where('category', $category)
            ->where('signal_type', 'recommendation_accepted')
            ->count();

        $rejected = LearningSignal::where('category', $category)
            ->where('signal_type', 'recommendation_rejected')
            ->count();

        $totalInteractions = $accepted + $rejected;
        $acceptanceRate = $totalInteractions > 0
            ? round(($accepted / $totalInteractions) * 100, 1)
            : 50.0;

        return [
            'category' => $category,
            'accepted_count' => $accepted,
            'rejected_count' => $rejected,
            'total_interactions' => $totalInteractions,
            'acceptance_rate_percent' => $acceptanceRate,
            'weight_modifier' => $this->calculateWeightModifier($acceptanceRate),
        ];
    }

    /**
     * Calculate opportunity weight boost factor based on acceptance rate.
     */
    public function calculateWeightModifier(float $acceptanceRatePercent): float
    {
        if ($acceptanceRatePercent >= 75.0) {
            return 1.25; // Boost highly accepted strategic patterns
        } elseif ($acceptanceRatePercent <= 30.0) {
            return 0.75; // Downweight frequently rejected recommendations
        }

        return 1.0;
    }

    /**
     * Deeply scrub metadata array of any PII keys.
     */
    private function scrubPii(array $meta): array
    {
        $scrubbed = [];

        foreach ($meta as $key => $val) {
            $lowerKey = strtolower((string) $key);

            if (in_array($lowerKey, self::FORBIDDEN_KEYS, true)) {
                continue;
            }

            if (is_array($val)) {
                $scrubbed[$key] = $this->scrubPii($val);
            } else {
                $scrubbed[$key] = $val;
            }
        }

        return $scrubbed;
    }
}
