<?php

declare(strict_types=1);

namespace App\Modules\Credits\Actions;

use App\Models\User;
use App\Modules\Credits\Enums\TransactionType;
use App\Modules\Credits\Models\CreditAccount;
use App\Modules\Credits\Models\CreditTransaction;
use Illuminate\Support\Facades\DB;

class GrantCreditsAction
{
    public function execute(
        User $user,
        int $amount,
        string $referenceType = 'manual_grant',
        ?string $referenceId = null,
        ?string $description = null,
        ?int $projectId = null
    ): CreditTransaction {
        return DB::transaction(function () use ($user, $amount, $referenceType, $referenceId, $description, $projectId) {
            /** @var CreditAccount $account */
            $account = CreditAccount::lockForUpdate()->firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0, 'lifetime_granted' => 0, 'lifetime_consumed' => 0]
            );

            $account->balance += $amount;
            $account->lifetime_granted += $amount;
            $account->save();

            return CreditTransaction::create([
                'credit_account_id' => $account->id,
                'type' => TransactionType::GRANT,
                'amount' => $amount,
                'balance_after' => $account->balance,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description ?? "Granted {$amount} credits",
                'project_id' => $projectId,
            ]);
        });
    }
}
