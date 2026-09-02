<?php

declare(strict_types=1);

namespace App\Modules\Research\DTOs;

use App\Modules\Research\Models\ResearchSession;

readonly class ResearchResult
{
    /**
     * @param RawSource[] $sources
     * @param array<string, mixed> $findings
     */
    public function __construct(
        public ResearchSession $session,
        public array $sources,
        public array $findings,
        public int $creditsConsumed
    ) {}
}
