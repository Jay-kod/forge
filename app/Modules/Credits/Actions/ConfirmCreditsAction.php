<?php

declare(strict_types=1);

namespace App\Modules\Credits\Actions;

use App\Modules\Credits\Enums\TransactionType;
use App\Modules\Credits\Models\CreditAccount;
use App\Modules\Credits\Models\CreditTransaction;
use Illuminate\Support\Facades\DB;

class ConfirmCreditsAction
{
    public function execute(CreditTransaction $reservation): CreditTransaction
    {
        return DB::transaction(function () use ($reservation) {
            /** @var CreditAccount $account */
            $account = CreditAccount::where('id', $reservation->credit_account_id)->lockForUpdate()->firstOrFail();

            $consumedAmount = abs($reservation->amount);
            $account->lifetime_consumed += $consumedAmount;
            $account->save();

            return CreditTransaction::create([
                'credit_account_id' => $account->id,
                'type' => TransactionType::CONSUMPTION,
                'amount' => -$consumedAmount,
                'balance_after' => $account->balance,
                'reference_type' => $reservation->reference_type,
                'reference_id' => $reservation->reference_id,
                'description' => "Confirmed consumption of {$consumedAmount} credits",
                'project_id' => $reservation->project_id,
            ]);
        });
    }
}
