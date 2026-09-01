<?php

declare(strict_types=1);

namespace App\Modules\AI\Enums;

enum WorkloadClass: string
{
    case LIGHT = 'LIGHT';
    case STANDARD = 'STANDARD';
    case DEEP = 'DEEP';
    case EXTREME = 'EXTREME';

    public function defaultCredits(): int
    {
        return match ($this) {
            self::LIGHT => 1,
            self::STANDARD => 10,
            self::DEEP => 20,
            self::EXTREME => 40,
        };
    }
}
