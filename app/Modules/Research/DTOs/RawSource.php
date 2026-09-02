<?php

declare(strict_types=1);

namespace App\Modules\Research\DTOs;

use App\Modules\Research\Enums\SourceType;

readonly class RawSource
{
    public function __construct(
        public string $url,
        public string $title,
        public string $snippet,
        public SourceType $sourceType = SourceType::PUBLICATION,
        public ?string $publicationDate = null,
        public float $reliabilityScore = 0.75
    ) {}
}
