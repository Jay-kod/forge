<?php

declare(strict_types=1);

namespace App\Modules\Identity\Enums;

enum TechnicalLevel: string
{
    case NON_DEVELOPER = 'non_developer';
    case VIBE_CODER = 'vibe_coder';
    case DEVELOPER = 'developer';
    case SENIOR_DEVELOPER = 'senior_developer';

    public function label(): string
    {
        return match ($this) {
            self::NON_DEVELOPER => 'Non-Developer / Business',
            self::VIBE_CODER => 'Vibe Coder / AI Builder',
            self::DEVELOPER => 'Software Developer',
            self::SENIOR_DEVELOPER => 'Senior Engineer / Architect',
        };
    }
}
