<?php

declare(strict_types=1);

namespace App\Modules\Research\DTOs;

readonly class ResearchQuery
{
    public function __construct(
        public string $query,
        public string $type = 'market',
        public ?string $geography = null,
        public int $maxSources = 5
    ) {}
}
