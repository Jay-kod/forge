<?php

declare(strict_types=1);

namespace App\Modules\Identity\Contracts;

use App\Models\User;
use Laravel\Socialite\Contracts\User as SocialiteUser;

interface AuthenticationServiceInterface
{
    public function findOrCreateSocialUser(string $provider, SocialiteUser $socialUser): User;
}
