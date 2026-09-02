<?php

declare(strict_types=1);

namespace App\Modules\Organizations\Services;

use App\Models\User;
use App\Modules\Credits\Enums\TransactionType;
use App\Modules\Credits\Models\CreditAccount;
use App\Modules\Credits\Models\CreditTransaction;
use App\Modules\Organizations\Models\Organization;
use App\Modules\Organizations\Models\OrganizationInvitation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class OrganizationService
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}
    /**
     * Create a new organization, assign owner, and provision pooled credit account.
     */
    public function createOrganization(User $owner, string $name, string $plan = 'business'): Organization
    {
        return DB::transaction(function () use ($owner, $name, $plan) {
            $baseSlug = Str::slug($name);
            $slug = $baseSlug;
            $counter = 1;
            while (Organization::where('slug', $slug)->exists()) {
                $slug = "{$baseSlug}-{$counter}";
                $counter++;
            }

            $organization = Organization::create([
                'name' => $name,
                'slug' => $slug,
                'owner_id' => $owner->id,
                'plan' => $plan,
                'settings' => [
                    'credit_pooling_enabled' => true,
                    'member_limit' => 20,
                ],
            ]);

            // Add owner to members pivot with 'owner' role
            $organization->members()->attach($owner->id, ['role' => 'owner']);

            // Provision pooled organization credit account
            \App\Modules\Organizations\Models\OrganizationCreditAccount::create([
                'organization_id' => $organization->id,
                'balance' => 100, // Initial team starter allowance
                'lifetime_granted' => 100,
                'lifetime_consumed' => 0,
            ]);

            return $organization;
        });
    }

    /**
     * Invite a new member by email with specified role.
     */
    public function inviteMember(
        Organization $organization,
        User $actor,
        string $email,
        string $role = 'member'
    ): OrganizationInvitation {
        if (!$organization->hasRole($actor, ['owner', 'admin'])) {
            throw new RuntimeException('Only organization owners and admins can invite members.');
        }

        // Check if user is already an active member
        $existingUser = User::where('email', $email)->first();
        if ($existingUser && $organization->hasMember($existingUser)) {
            throw new RuntimeException("User {$email} is already a member of this organization.");
        }

        $invitation = OrganizationInvitation::updateOrCreate(
            [
                'organization_id' => $organization->id,
                'email' => $email,
                'accepted_at' => null,
            ],
            [
                'role' => $role,
                'token' => OrganizationInvitation::generateToken(),
                'expires_at' => now()->addDays(7),
            ]
        );

        $this->auditLogService->record('member.invited', $actor, $organization, 'invitation', $invitation->id, [
            'email' => $email,
            'role' => $role,
        ]);

        return $invitation;
    }

    /**
     * Accept an invitation using the secure token.
     */
    public function acceptInvitation(string $token, User $user): Organization
    {
        /** @var OrganizationInvitation|null $invitation */
        $invitation = OrganizationInvitation::where('token', $token)->first();

        if (!$invitation) {
            throw new RuntimeException('Invalid or expired invitation token.');
        }

        if ($invitation->isExpired()) {
            throw new RuntimeException('This invitation has expired.');
        }

        if ($invitation->isAccepted()) {
            throw new RuntimeException('This invitation has already been accepted.');
        }

        $invitation->accept($user);

        $this->auditLogService->record('member.joined', $user, $invitation->organization, 'user', $user->id, [
            'role' => $invitation->role,
        ]);

        return $invitation->organization;
    }

    /**
     * Remove a member from the organization.
     */
    public function removeMember(Organization $organization, User $actor, User $memberToRemove): void
    {
        if ($memberToRemove->id === $organization->owner_id) {
            throw new RuntimeException('Cannot remove the organization owner.');
        }

        if (!$organization->hasRole($actor, ['owner', 'admin']) && $actor->id !== $memberToRemove->id) {
            throw new RuntimeException('Insufficient permissions to remove this member.');
        }

        $organization->members()->detach($memberToRemove->id);

        $this->auditLogService->record('member.removed', $actor, $organization, 'user', $memberToRemove->id);
    }

    /**
     * Update a member's role within the organization.
     */
    public function updateMemberRole(Organization $organization, User $actor, User $member, string $newRole): void
    {
        if ($member->id === $organization->owner_id) {
            throw new RuntimeException('Cannot modify the role of the organization owner.');
        }

        if (!$organization->hasRole($actor, ['owner', 'admin'])) {
            throw new RuntimeException('Insufficient permissions to modify member roles.');
        }

        $organization->members()->updateExistingPivot($member->id, ['role' => $newRole]);

        $this->auditLogService->record('role.updated', $actor, $organization, 'user', $member->id, [
            'new_role' => $newRole,
        ]);
    }

    /**
     * Get the available pooled balance for an organization.
     */
    public function getBalance(Organization $organization): int
    {
        return (int) (\App\Modules\Organizations\Models\OrganizationCreditAccount::where('organization_id', $organization->id)->value('balance') ?? 0);
    }

    /**
     * Grant credits to an organization's pooled credit account.
     */
    public function grantCredits(Organization $organization, int $amount, string $reason): \App\Modules\Organizations\Models\OrganizationCreditAccount
    {
        return DB::transaction(function () use ($organization, $amount, $reason) {
            $account = \App\Modules\Organizations\Models\OrganizationCreditAccount::firstOrCreate(
                ['organization_id' => $organization->id],
                ['balance' => 0, 'lifetime_granted' => 0, 'lifetime_consumed' => 0]
            );

            $account->balance += $amount;
            $account->lifetime_granted += $amount;
            $account->save();

            \App\Modules\Organizations\Models\OrganizationCreditTransaction::create([
                'organization_credit_account_id' => $account->id,
                'type' => 'grant',
                'amount' => $amount,
                'balance_after' => $account->balance,
                'reference_type' => 'org.grant',
                'description' => $reason,
            ]);

            return $account;
        });
    }
}
