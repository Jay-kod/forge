<?php

declare(strict_types=1);

namespace App\Modules\Research\Enums;

enum SourceType: string
{
    case OFFICIAL = 'official';
    case GOVERNMENT = 'government';
    case RESEARCH = 'research';
    case DOCUMENTATION = 'documentation';
    case PUBLICATION = 'publication';
    case INDUSTRY = 'industry';
    case COMMUNITY = 'community';
    case WEAK = 'weak';

    public function reliabilityScore(): float
    {
        return match ($this) {
            self::OFFICIAL, self::GOVERNMENT, self::RESEARCH => 0.95,
            self::DOCUMENTATION => 0.90,
            self::PUBLICATION, self::INDUSTRY => 0.75,
            self::COMMUNITY => 0.50,
            self::WEAK => 0.25,
        };
    }
}
