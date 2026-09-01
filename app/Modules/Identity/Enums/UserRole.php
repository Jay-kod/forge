<?php

declare(strict_types=1);

namespace App\Modules\Identity\Enums;

enum UserRole: string
{
    case USER = 'user';
    case ADMIN = 'admin';

    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }
}
