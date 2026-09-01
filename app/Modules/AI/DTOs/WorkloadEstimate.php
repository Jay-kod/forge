<?php

declare(strict_types=1);

namespace App\Modules\AI\DTOs;

use App\Modules\AI\Enums\WorkloadClass;

readonly class WorkloadEstimate
{
    public function __construct(
        public int $credits,
        public float $estimatedLatencySeconds,
        public WorkloadClass $workloadClass,
        public string $recommendedModel
    ) {}
}
