<?php

declare(strict_types=1);

namespace App\Modules\Context\Enums;

enum KnowledgeClassification: string
{
    case CONFIRMED = 'confirmed';
    case INFERRED = 'inferred';
    case ASSUMED = 'assumed';
    case UNKNOWN = 'unknown';
    case CONFLICTING = 'conflicting';
}
