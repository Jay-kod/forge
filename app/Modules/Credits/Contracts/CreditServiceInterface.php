<?php

declare(strict_types=1);

namespace App\Modules\Credits\Contracts;

use App\Models\User;
use App\Modules\Credits\Models\CreditTransaction;

interface CreditServiceInterface
{
    public function getBalance(User $user): int;
    public function hasCredits(User $user, int $required): bool;
    public function grant(User $user, int $amount, string $referenceType = 'manual', ?string $referenceId = null, ?string $description = null, ?int $projectId = null): CreditTransaction;
    public function reserve(User $user, int $amount, string $referenceType, ?string $referenceId = null, ?int $projectId = null): CreditTransaction;
    public function confirm(CreditTransaction $reservation): CreditTransaction;
    public function release(CreditTransaction $reservation, ?string $reason = null): CreditTransaction;
}
