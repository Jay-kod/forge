<?php

declare(strict_types=1);

namespace App\Modules\Projects\Enums;

enum WorkflowMode: string
{
    case AUTOMATIC = 'automatic';
    case PAGE_BY_PAGE = 'page_by_page';
}
