<?php

declare(strict_types=1);

namespace App\Modules\Billing\Contracts;

use App\Models\User;

interface EntitlementServiceInterface
{
    public function can(User $user, string $capability): bool;
    public function getLimit(User $user, string $capability): ?int;
}
