<?php

declare(strict_types=1);

namespace App\Modules\Credits\Actions;

use App\Modules\Credits\Enums\TransactionType;
use App\Modules\Credits\Models\CreditAccount;
use App\Modules\Credits\Models\CreditTransaction;
use Illuminate\Support\Facades\DB;

class ReleaseCreditsAction
{
    public function execute(CreditTransaction $reservation, ?string $reason = null): CreditTransaction
    {
        return DB::transaction(function () use ($reservation, $reason) {
            /** @var CreditAccount $account */
            $account = CreditAccount::where('id', $reservation->credit_account_id)->lockForUpdate()->firstOrFail();

            $releasedAmount = abs($reservation->amount);
            $account->balance += $releasedAmount;
            $account->save();

            return CreditTransaction::create([
                'credit_account_id' => $account->id,
                'type' => TransactionType::RELEASE,
                'amount' => $releasedAmount,
                'balance_after' => $account->balance,
                'reference_type' => $reservation->reference_type,
                'reference_id' => $reservation->reference_id,
                'description' => "Released {$releasedAmount} reserved credits" . ($reason ? ": {$reason}" : ''),
                'project_id' => $reservation->project_id,
            ]);
        });
    }
}
