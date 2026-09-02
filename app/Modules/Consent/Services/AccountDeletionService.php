<?php

declare(strict_types=1);

namespace App\Modules\Consent\Services;

use App\Models\User;
use App\Modules\AI\Models\ByokCredential;
use App\Modules\API\Models\ApiKey;
use App\Modules\Credits\Models\CreditAccount;
use App\Modules\Credits\Models\CreditTransaction;
use App\Modules\GitHub\Models\GitHubConnection;
use App\Modules\Organizations\Models\AuditLog;
use App\Modules\Organizations\Models\Organization;
use App\Modules\Projects\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

class AccountDeletionService
{
    /**
     * Purge user account and execute Right to Be Forgotten per GDPR Article 17.
     */
    public function deleteAccount(User $user, ?string $password = null, bool $bypassPasswordCheck = false): void
    {
        if (!$bypassPasswordCheck) {
            if (!$password || !Hash::check($password, $user->password)) {
                throw new InvalidArgumentException('Invalid confirmation password provided.');
            }
        }

        DB::transaction(function () use ($user) {
            $userId = $user->id;

            // 1. Purge all personal projects (and their cascading workflows, stages, contexts)
            $projects = Project::where('user_id', $userId)->get();
            foreach ($projects as $project) {
                $project->delete();
            }

            // 2. Revoke and purge third-party credentials (OAuth tokens and BYOK API keys)
            GitHubConnection::where('user_id', $userId)->delete();
            ByokCredential::where('user_id', $userId)->delete();
            ApiKey::where('user_id', $userId)->delete();

            // 3. Remove user from all organizations
            $user->organizations()->detach();

            // For owned organizations, reassign or purge if no other members
            $ownedOrgs = Organization::where('owner_id', $userId)->get();
            foreach ($ownedOrgs as $org) {
                $otherMember = $org->members()->where('users.id', '!=', $userId)->first();
                if ($otherMember) {
                    $org->update(['owner_id' => $otherMember->id]);
                    $org->members()->updateExistingPivot($otherMember->id, ['role' => 'owner']);
                } else {
                    $org->delete();
                }
            }

            // 4. Anonymize audit logs (retain event history for legal compliance without PII)
            AuditLog::where('user_id', $userId)->update([
                'user_id' => null,
                'ip_address' => '0.0.0.0',
            ]);

            // 5. Anonymize credit records (retain transaction integrity for ledger accounting)
            $creditAccount = CreditAccount::where('user_id', $userId)->first();
            if ($creditAccount) {
                CreditTransaction::where('credit_account_id', $creditAccount->id)->update([
                    'description' => '[Redacted - Account Purged]',
                ]);
            }

            // 6. Terminate active subscription if present
            if ($user->subscription) {
                $user->subscription->update([
                    'status' => 'canceled',
                    'ends_at' => now(),
                ]);
            }

            // 7. Delete user record permanently
            User::where('id', $userId)->delete();
        });
    }
}
