<?php

declare(strict_types=1);

namespace App\Modules\Discovery\Enums;

enum DiscoveryVerdict: string
{
    case BUILD_AS_PROPOSED = 'build_as_proposed';
    case BUILD_WITH_MODIFICATIONS = 'build_with_modifications';
    case CONSIDER_ALTERNATIVE = 'consider_alternative';
    case DO_NOT_BUILD_YET = 'do_not_build_yet';

    public function label(): string
    {
        return match ($this) {
            self::BUILD_AS_PROPOSED => 'Build As Proposed',
            self::BUILD_WITH_MODIFICATIONS => 'Build With Modifications',
            self::CONSIDER_ALTERNATIVE => 'Consider An Alternative Approach',
            self::DO_NOT_BUILD_YET => 'Do Not Build This Version Yet',
        };
    }
}
