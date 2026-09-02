<?php

declare(strict_types=1);

namespace App\Modules\Consent\Enums;

enum ConsentType: string
{
    case ANALYTICS = 'analytics';
    case PRODUCT_IMPROVEMENT = 'product_improvement';
    case AI_IMPROVEMENT = 'ai_improvement';
    case MARKETING = 'marketing';
}
