<?php

declare(strict_types=1);

namespace App\Modules\Consent\Services;

use App\Models\User;
use App\Modules\Consent\Enums\ConsentType;
use App\Modules\Consent\Models\ConsentRecord;
use Illuminate\Support\Collection;

class ConsentService
{
    /**
     * Record or update user consent for a specific category.
     */
    public function recordConsent(
        User $user,
        ConsentType|string $type,
        bool $granted,
        string $version = '1.0',
        ?string $ipAddress = null
    ): ConsentRecord {
        $typeValue = $type instanceof ConsentType ? $type->value : $type;

        $record = ConsentRecord::where('user_id', $user->id)
            ->where('consent_type', $typeValue)
            ->latest('id')
            ->first();

        if ($record) {
            $record->update([
                'granted' => $granted,
                'version' => $version,
                'ip_address' => $ipAddress ?? $record->ip_address,
                'granted_at' => $record->granted_at ?? now(),
                'revoked_at' => $granted ? null : now(),
            ]);

            return $record;
        }

        return ConsentRecord::create([
            'user_id' => $user->id,
            'consent_type' => $typeValue,
            'granted' => $granted,
            'version' => $version,
            'ip_address' => $ipAddress,
            'granted_at' => now(),
            'revoked_at' => $granted ? null : now(),
        ]);
    }

    /**
     * Revoke a specific consent category.
     */
    public function revokeConsent(User $user, ConsentType|string $type): void
    {
        $this->recordConsent($user, $type, false);
    }

    /**
     * Check if user has explicitly granted an active consent.
     */
    public function hasConsent(User $user, ConsentType|string $type): bool
    {
        $typeValue = $type instanceof ConsentType ? $type->value : $type;

        $record = ConsentRecord::where('user_id', $user->id)
            ->where('consent_type', $typeValue)
            ->latest('id')
            ->first();

        return $record !== null && $record->granted && $record->revoked_at === null;
    }

    /**
     * Get a map of all consent types and their current status for the user.
     */
    public function getUserConsents(User $user): array
    {
        $consents = [];

        foreach (ConsentType::cases() as $type) {
            $record = ConsentRecord::where('user_id', $user->id)
                ->where('consent_type', $type->value)
                ->latest('id')
                ->first();

            $consents[$type->value] = [
                'type' => $type->value,
                'granted' => $record !== null && $record->granted && $record->revoked_at === null,
                'version' => $record?->version ?? '1.0',
                'updated_at' => $record?->updated_at?->toIso8601String() ?? null,
            ];
        }

        return $consents;
    }

    /**
     * Get full auditable consent history for compliance.
     */
    public function getConsentAuditTrail(User $user): Collection
    {
        return ConsentRecord::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();
    }
}
