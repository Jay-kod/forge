<?php

declare(strict_types=1);

namespace App\Modules\Projects\DTOs;

use App\Modules\Projects\Enums\ProjectType;

readonly class ClassificationResult
{
    public function __construct(
        public ProjectType $classification,
        public float $confidence,
        public string $reasoning,
        public array $suggestedStages = []
    ) {}
}
