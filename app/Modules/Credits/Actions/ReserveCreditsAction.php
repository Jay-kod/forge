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
            $account = null;

            if ($projectId) {
                $project = \App\Modules\Projects\Models\Project::find($projectId);
                if ($project && $project->organization_id) {
                    $orgAccount = \App\Modules\Organizations\Models\OrganizationCreditAccount::where('organization_id', $project->organization_id)->lockForUpdate()->first();
                    if (!$orgAccount || $orgAccount->balance < $amount) {
                        throw new RuntimeException("Insufficient organization pooled credits. Required: {$amount}, Available: " . ($orgAccount->balance ?? 0));
                    }

                    $orgAccount->balance -= $amount;
                    $orgAccount->save();

                    // Record in organization credit transactions
                    \App\Modules\Organizations\Models\OrganizationCreditTransaction::create([
                        'organization_credit_account_id' => $orgAccount->id,
                        'user_id' => $user->id,
                        'type' => 'reservation',
                        'amount' => -$amount,
                        'balance_after' => $orgAccount->balance,
                        'reference_type' => $referenceType,
                        'reference_id' => $referenceId,
                        'description' => "Reserved {$amount} pooled credits for {$referenceType}",
                        'project_id' => $projectId,
                    ]);

                    // Provide CreditTransaction reference for pipeline compatibility
                    $userAccount = CreditAccount::firstOrCreate(['user_id' => $user->id], ['balance' => 0]);
                    return CreditTransaction::create([
                        'credit_account_id' => $userAccount->id,
                        'type' => TransactionType::RESERVATION,
                        'amount' => -$amount,
                        'balance_after' => $orgAccount->balance,
                        'reference_type' => $referenceType,
                        'reference_id' => $referenceId,
                        'description' => "Reserved {$amount} pooled credits for {$referenceType}",
                        'project_id' => $projectId,
                    ]);
                }
            }

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
