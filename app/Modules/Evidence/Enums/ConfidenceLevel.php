<?php

declare(strict_types=1);

namespace App\Modules\Evidence\Enums;

enum ConfidenceLevel: string
{
    case VERIFIED = 'verified';
    case STRONGLY_SUPPORTED = 'strongly_supported';
    case PROBABLE = 'probable';
    case INFERRED = 'inferred';
    case ASSUMPTION = 'assumption';
    case UNKNOWN = 'unknown';
    case CONFLICTING = 'conflicting';

    public function label(): string
    {
        return match ($this) {
            self::VERIFIED => 'Verified (Multiple Reliable Sources)',
            self::STRONGLY_SUPPORTED => 'Strongly Supported (Primary Source)',
            self::PROBABLE => 'Probable (Industry Consensus)',
            self::INFERRED => 'Inferred (Logical Deduction)',
            self::ASSUMPTION => 'Working Assumption (Unverified)',
            self::UNKNOWN => 'Unknown (Insufficient Evidence)',
            self::CONFLICTING => 'Conflicting Evidence',
        };
    }
}
