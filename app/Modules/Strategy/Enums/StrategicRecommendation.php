<?php

declare(strict_types=1);

namespace App\Modules\Strategy\Enums;

enum StrategicRecommendation: string
{
    case BUILD = 'BUILD';
    case BUILD_WITH_MODIFICATIONS = 'BUILD_WITH_MODIFICATIONS';
    case CONSIDER_ALTERNATIVE = 'CONSIDER_ALTERNATIVE';
    case DO_NOT_BUILD_YET = 'DO_NOT_BUILD_YET';

    public function label(): string
    {
        return match ($this) {
            self::BUILD => 'Proceed to Build',
            self::BUILD_WITH_MODIFICATIONS => 'Build with Recommended Modifications',
            self::CONSIDER_ALTERNATIVE => 'Consider Alternative Approach',
            self::DO_NOT_BUILD_YET => 'Do Not Build Yet (De-risk First)',
        };
    }

    public function riskLevel(): string
    {
        return match ($this) {
            self::BUILD => 'Low',
            self::BUILD_WITH_MODIFICATIONS => 'Moderate',
            self::CONSIDER_ALTERNATIVE => 'High',
            self::DO_NOT_BUILD_YET => 'Critical',
        };
    }

    public function actionRequired(): string
    {
        return match ($this) {
            self::BUILD => 'Immediate technical specification and PRD drafting.',
            self::BUILD_WITH_MODIFICATIONS => 'Adjust feature scope and positioning before engineering investments.',
            self::CONSIDER_ALTERNATIVE => 'Pivot value proposition to target uncovered market niches.',
            self::DO_NOT_BUILD_YET => 'Validate demand signals with minimal pre-sale or customer interviews.',
        };
    }
}
