<?php

declare(strict_types=1);

namespace App\Modules\Credits\Services;

use App\Models\User;
use App\Modules\Credits\Actions\ConfirmCreditsAction;
use App\Modules\Credits\Actions\GrantCreditsAction;
use App\Modules\Credits\Actions\ReleaseCreditsAction;
use App\Modules\Credits\Actions\ReserveCreditsAction;
use App\Modules\Credits\Contracts\CreditServiceInterface;
use App\Modules\Credits\Models\CreditAccount;
use App\Modules\Credits\Models\CreditTransaction;

class CreditService implements CreditServiceInterface
{
    public function __construct(
        protected GrantCreditsAction $grantCreditsAction,
        protected ReserveCreditsAction $reserveCreditsAction,
        protected ConfirmCreditsAction $confirmCreditsAction,
        protected ReleaseCreditsAction $releaseCreditsAction,
    ) {}

    public function getBalance(User $user): int
    {
        return (int) (CreditAccount::where('user_id', $user->id)->value('balance') ?? 0);
    }

    public function hasCredits(User $user, int $required): bool
    {
        return $this->getBalance($user) >= $required;
    }

    public function grant(
        User $user,
        int $amount,
        string $referenceType = 'manual',
        ?string $referenceId = null,
        ?string $description = null,
        ?int $projectId = null
    ): CreditTransaction {
        return $this->grantCreditsAction->execute($user, $amount, $referenceType, $referenceId, $description, $projectId);
    }

    public function reserve(
        User $user,
        int $amount,
        string $referenceType,
        ?string $referenceId = null,
        ?int $projectId = null
    ): CreditTransaction {
        return $this->reserveCreditsAction->execute($user, $amount, $referenceType, $referenceId, $projectId);
    }

    public function confirm(CreditTransaction $reservation): CreditTransaction
    {
        return $this->confirmCreditsAction->execute($reservation);
    }

    public function release(CreditTransaction $reservation, ?string $reason = null): CreditTransaction
    {
        return $this->releaseCreditsAction->execute($reservation, $reason);
    }
}
