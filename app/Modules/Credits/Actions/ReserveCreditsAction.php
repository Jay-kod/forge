<?php

declare(strict_types=1);

namespace App\Modules\Credits\Actions;

use App\Models\User;
use App\Modules\Credits\Enums\TransactionType;
use App\Modules\Credits\Models\CreditAccount;
use App\Modules\Credits\Models\CreditTransaction;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReserveCreditsAction
{
    public function execute(
        User $user,
        int $amount,
        string $referenceType,
        ?string $referenceId = null,
        ?int $projectId = null
    ): CreditTransaction {
        return DB::transaction(function () use ($user, $amount, $referenceType, $referenceId, $projectId) {
            /** @var CreditAccount|null $account */
            $account = CreditAccount::where('user_id', $user->id)->lockForUpdate()->first();

            if (!$account || $account->balance < $amount) {
                throw new RuntimeException("Insufficient credits balance. Required: {$amount}, Available: " . ($account->balance ?? 0));
            }

            $account->balance -= $amount;
            $account->save();

            return CreditTransaction::create([
                'credit_account_id' => $account->id,
                'type' => TransactionType::RESERVATION,
                'amount' => -$amount,
                'balance_after' => $account->balance,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => "Reserved {$amount} credits for {$referenceType}",
                'project_id' => $projectId,
            ]);
        });
    }
}
