<?php

declare(strict_types=1);

namespace App\Modules\Identity\Services;

use App\Models\User;
use App\Modules\Credits\Contracts\CreditServiceInterface;
use App\Modules\Identity\Models\Referral;
use Illuminate\Support\Facades\DB;

class ReferralService
{
    public function __construct(
        protected CreditServiceInterface $credits
    ) {}

    /**
     * Link a referee to a referrer using a referral code.
     */
    public function applyReferralCode(User $referee, string $code): bool
    {
        $code = strtoupper(trim($code));

        $referrer = User::where('referral_code', $code)->first();

        if (!$referrer || $referrer->id === $referee->id) {
            return false;
        }

        // Only link if referee does not already have a referrer
        if ($referee->referred_by_id) {
            return false;
        }

        return DB::transaction(function () use ($referrer, $referee) {
            $referee->referred_by_id = $referrer->id;
            $referee->save();

            Referral::firstOrCreate(
                ['referrer_id' => $referrer->id, 'referred_id' => $referee->id],
                ['status' => 'pending', 'reward_credits' => 50]
            );

            return true;
        });
    }

    /**
     * Activate referral reward when referee launches their first project.
     */
    public function activateReferralOnFirstProject(User $referee): void
    {
        if (!$referee->referred_by_id) {
            return;
        }

        DB::transaction(function () use ($referee) {
            $referral = Referral::where('referred_id', $referee->id)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            if (!$referral) {
                return;
            }

            $referral->update([
                'status' => 'activated',
                'activated_at' => now(),
            ]);

            $referrer = User::find($referral->referrer_id);

            // Grant 50 bonus credits to both parties
            if ($referrer) {
                $this->credits->grant(
                    user: $referrer,
                    amount: $referral->reward_credits,
                    referenceType: 'referral_activation_reward',
                    referenceId: (string) $referee->id
                );
            }

            $this->credits->grant(
                user: $referee,
                amount: $referral->reward_credits,
                referenceType: 'welcome_referral_bonus',
                referenceId: (string) $referral->referrer_id
            );
        });
    }
}
