<?php

declare(strict_types=1);

namespace App\Modules\Identity\Services;

use App\Models\User;
use App\Modules\Billing\Services\SubscriptionService;
use App\Modules\Credits\Actions\GrantCreditsAction;
use App\Modules\Identity\Contracts\AuthenticationServiceInterface;
use App\Modules\Identity\Models\SocialAccount;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class AuthenticationService implements AuthenticationServiceInterface
{
    public function __construct(
        protected GrantCreditsAction $grantCreditsAction,
        protected SubscriptionService $subscriptionService
    ) {}

    public function findOrCreateSocialUser(string $provider, SocialiteUser $socialUser): User
    {
        return DB::transaction(function () use ($provider, $socialUser) {
            $account = SocialAccount::where('provider', $provider)
                ->where('provider_id', $socialUser->getId())
                ->first();

            if ($account) {
                $account->update([
                    'provider_token' => $socialUser->token,
                    'provider_refresh' => $socialUser->refreshToken ?? null,
                ]);

                return $account->user;
            }

            // Find existing user by email or create new
            $user = User::firstOrCreate(
                ['email' => $socialUser->getEmail()],
                [
                    'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
                    'avatar_url' => $socialUser->getAvatar(),
                    'email_verified_at' => now(),
                ]
            );

            $user->socialAccounts()->create([
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'provider_token' => $socialUser->token,
                'provider_refresh' => $socialUser->refreshToken ?? null,
            ]);

            // Auto-provision Free Explorer plan and welcome credits
            $this->subscriptionService->provisionFreePlan($user);

            return $user;
        });
    }
}
