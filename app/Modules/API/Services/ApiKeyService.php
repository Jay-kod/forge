<?php

declare(strict_types=1);

namespace App\Modules\API\Services;

use App\Models\User;
use App\Modules\API\Models\ApiKey;
use App\Modules\Organizations\Models\Organization;
use DateTimeInterface;
use Illuminate\Support\Str;
use RuntimeException;

class ApiKeyService
{
    /**
     * Generate a new cryptographically secure API key.
     *
     * @return array{plain_token: string, api_key: ApiKey}
     */
    public function createKey(
        User $user,
        string $name,
        ?Organization $organization = null,
        array $abilities = ['*'],
        ?DateTimeInterface $expiresAt = null
    ): array {
        $secret = Str::random(40);
        $plainToken = "forge_live_{$secret}";
        $prefix = substr($plainToken, 0, 16);
        $keyHash = hash('sha256', $plainToken);

        $apiKey = ApiKey::create([
            'user_id' => $user->id,
            'organization_id' => $organization?->id,
            'name' => $name,
            'key_hash' => $keyHash,
            'prefix' => $prefix,
            'abilities' => $abilities,
            'expires_at' => $expiresAt,
        ]);

        return [
            'plain_token' => $plainToken,
            'api_key' => $apiKey,
        ];
    }

    /**
     * Authenticate an incoming request by verifying bearer token against SHA-256 hash.
     */
    public function authenticate(string $plainToken): ?ApiKey
    {
        $plainToken = trim($plainToken);
        if (!str_starts_with($plainToken, 'forge_live_')) {
            return null;
        }

        $hash = hash('sha256', $plainToken);
        /** @var ApiKey|null $apiKey */
        $apiKey = ApiKey::with(['user', 'organization'])->where('key_hash', $hash)->first();

        if (!$apiKey || !$apiKey->isValid()) {
            return null;
        }

        $apiKey->update(['last_used_at' => now()]);

        return $apiKey;
    }

    /**
     * Revoke an API key.
     */
    public function revokeKey(ApiKey $apiKey, User $actor): void
    {
        if ($apiKey->user_id !== $actor->id && !$actor->isAdmin()) {
            if (!$apiKey->organization || !$apiKey->organization->hasRole($actor, ['owner', 'admin'])) {
                throw new RuntimeException('Unauthorized to revoke this API key.');
            }
        }

        $apiKey->delete();
    }
}
