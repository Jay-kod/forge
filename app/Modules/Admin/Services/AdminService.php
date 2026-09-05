<?php

declare(strict_types=1);

namespace App\Modules\Admin\Services;

use App\Models\User;
use App\Modules\Billing\Models\Plan;
use App\Modules\Credits\Contracts\CreditServiceInterface;
use App\Modules\Credits\Models\CreditAccount;
use App\Modules\Identity\Enums\UserRole;
use App\Modules\Projects\Models\Project;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminService
{
    public function __construct(
        protected CreditServiceInterface $creditService
    ) {}

    /**
     * Get system-wide KPI metrics and subscription plan statistics.
     */
    public function getDashboardStats(): array
    {
        return [
            'total_users' => User::count(),
            'total_projects' => Project::count(),
            'total_credits_balance' => (int) CreditAccount::sum('balance'),
            'total_credits_consumed' => (int) CreditAccount::sum('lifetime_consumed'),
            'plans' => Plan::withCount('subscriptions')->get(),
        ];
    }

    /**
     * Fetch paginated users filtered by search term.
     */
    public function getPaginatedUsers(?string $search, int $perPage = 15): LengthAwarePaginator
    {
        $usersQuery = User::with(['creditAccount', 'subscription.plan'])
            ->orderByDesc('created_at');

        if (!empty($search)) {
            $usersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $usersQuery->paginate($perPage)->withQueryString();
    }

    /**
     * Grant credits to a user account with admin attribution.
     */
    public function grantCredits(User $admin, User $user, int $amount, ?string $reason = null): void
    {
        $this->creditService->grant(
            user: $user,
            amount: $amount,
            referenceType: 'admin_grant',
            referenceId: 'admin_' . $admin->id,
            description: $reason ?? 'Admin manual grant'
        );
    }

    /**
     * Update a user's role.
     */
    public function updateUserRole(User $user, UserRole $role): void
    {
        $user->update(['role' => $role]);
    }
}
