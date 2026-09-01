<?php

declare(strict_types=1);

namespace App\Modules\Projects\Enums;

enum ProjectStatus: string
{
    case ACTIVE = 'active';
    case ARCHIVED = 'archived';
    case COMPLETED = 'completed';
}
