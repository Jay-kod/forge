<?php

declare(strict_types=1);

namespace App\Modules\Research\Contracts;

use App\Modules\Research\DTOs\RawSource;
use App\Modules\Research\DTOs\ResearchQuery;

interface WebSearchProviderInterface
{
    /**
     * @return RawSource[]
     */
    public function search(ResearchQuery $query): array;
}
