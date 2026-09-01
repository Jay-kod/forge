<?php

declare(strict_types=1);

namespace App\Modules\Product\Enums;

enum WorkflowStageType: string
{
    case UNDERSTANDING = 'understanding';
    case DISCOVERY = 'discovery';
    case RESEARCH = 'research';
    case COMPETITORS = 'competitors';
    case CHALLENGE = 'challenge';
    case STRATEGY = 'strategy';
    case PRD = 'prd';
    case ARCHITECTURE = 'architecture';
    case PACKAGE = 'package';
    case EXPORT = 'export';

    public function label(): string
    {
        return match ($this) {
            self::UNDERSTANDING => '1. Understanding & Context',
            self::DISCOVERY => '2. Existence Discovery',
            self::RESEARCH => '3. Real-World Market Research',
            self::COMPETITORS => '4. Competitive Landscape',
            self::CHALLENGE => '5. Analysis & Challenge',
            self::STRATEGY => '6. Strategic Recommendation',
            self::PRD => '7. Evidence-Linked PRD',
            self::ARCHITECTURE => '8. System Architecture',
            self::PACKAGE => '9. AI Development Package',
            self::EXPORT => '10. Blueprint & Export',
        };
    }
}
