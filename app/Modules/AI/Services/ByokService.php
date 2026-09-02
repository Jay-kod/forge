<?php

declare(strict_types=1);

namespace App\Modules\AI\Services;

use App\Models\User;
use App\Modules\AI\Models\ByokCredential;
use App\Modules\Organizations\Models\Organization;
use Illuminate\Database\Eloquent\Collection;
use RuntimeException;

class ByokService
{
    /**
     * Supported LLM providers for Bring Your Own Key.
     */
    public const SUPPORTED_PROVIDERS = ['openai', 'anthropic', 'gemini'];

    /**
     * Store or update an encrypted BYOK credential for a user or organization.
     */
    public function storeCredential(
        User $user,
        string $provider,
        string $plainKey,
        ?Organization $organization = null,
        ?string $label = null
    ): ByokCredential {
        $provider = strtolower(trim($provider));

        if (!in_array($provider, self::SUPPORTED_PROVIDERS, true)) {
            throw new RuntimeException("Unsupported BYOK provider '{$provider}'. Supported: " . implode(', ', self::SUPPORTED_PROVIDERS));
        }

        if (strlen($plainKey) < 10) {
            throw new RuntimeException("Invalid API key format for provider '{$provider}'.");
        }

        $query = ByokCredential::where('provider', $provider);

        if ($organization) {
            $query->where('organization_id', $organization->id);
        } else {
            $query->where('user_id', $user->id)->whereNull('organization_id');
        }

        $credential = $query->first();

        if ($credential) {
            $credential->update([
                'api_key' => $plainKey,
                'label' => $label ?? $credential->label,
                'is_active' => true,
                'last_validated_at' => now(),
            ]);
            return $credential;
        }

        return ByokCredential::create([
            'user_id' => $user->id,
            'organization_id' => $organization?->id,
            'provider' => $provider,
            'api_key' => $plainKey,
            'label' => $label ?? (ucfirst($provider) . ' Production Key'),
            'is_active' => true,
            'last_validated_at' => now(),
        ]);
    }

    /**
     * Retrieve decrypted plaintext API key for an LLM workload.
     */
    public function getPlainKey(User $user, string $provider, ?Organization $organization = null): ?string
    {
        $provider = strtolower(trim($provider));

        // 1. Check organization level BYOK key first if organization context provided
        if ($organization) {
            $orgKey = ByokCredential::where('organization_id', $organization->id)
                ->where('provider', $provider)
                ->where('is_active', true)
                ->first();

            if ($orgKey) {
                return $orgKey->api_key;
            }
        }

        // 2. Fall back to user level BYOK key
        $userKey = ByokCredential::where('user_id', $user->id)
            ->whereNull('organization_id')
            ->where('provider', $provider)
            ->where('is_active', true)
            ->first();

        return $userKey?->api_key;
    }

    /**
     * Check if a valid BYOK credential exists.
     */
    public function hasCredential(User $user, string $provider, ?Organization $organization = null): bool
    {
        return $this->getPlainKey($user, $provider, $organization) !== null;
    }

    /**
     * Delete a BYOK credential.
     */
    public function removeCredential(User $user, string $provider, ?Organization $organization = null): bool
    {
        $query = ByokCredential::where('provider', $provider);

        if ($organization) {
            if (!$organization->hasRole($user, ['owner', 'admin'])) {
                throw new RuntimeException('Unauthorized to remove organization credentials.');
            }
            $query->where('organization_id', $organization->id);
        } else {
            $query->where('user_id', $user->id)->whereNull('organization_id');
        }

        $credential = $query->first();

        return $credential ? (bool) $credential->delete() : false;
    }

    /**
     * List registered BYOK credentials (with masked keys).
     *
     * @return Collection<int, ByokCredential>
     */
    public function listCredentials(User $user, ?Organization $organization = null): Collection
    {
        $query = ByokCredential::query();

        if ($organization) {
            $query->where('organization_id', $organization->id);
        } else {
            $query->where('user_id', $user->id)->whereNull('organization_id');
        }

        return $query->get();
    }
}
