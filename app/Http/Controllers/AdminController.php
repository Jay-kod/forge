<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Modules\Billing\Models\Plan;
use App\Modules\Credits\Contracts\CreditServiceInterface;
use App\Modules\Credits\Models\CreditAccount;
use App\Modules\Credits\Models\CreditTransaction;
use App\Modules\Identity\Enums\UserRole;
use App\Modules\Projects\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    public function __construct(
        protected CreditServiceInterface $creditService
    ) {}

    /**
     * Admin dashboard with system metrics, plan breakdown, and user management.
     */
    public function dashboard(Request $request): Response
    {
        $search = $request->query('search');

        $usersQuery = User::with(['creditAccount', 'subscription.plan'])
            ->orderByDesc('created_at');

        if (!empty($search)) {
            $usersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $usersQuery->paginate(15)->withQueryString();

        $stats = [
            'total_users' => User::count(),
            'total_projects' => Project::count(),
            'total_credits_balance' => (int) CreditAccount::sum('balance'),
            'total_credits_consumed' => (int) CreditAccount::sum('lifetime_consumed'),
            'plans' => Plan::withCount('subscriptions')->get(),
        ];

        return Inertia::render('Admin/Dashboard', [
            'users' => $users,
            'stats' => $stats,
            'filters' => ['search' => $search],
        ]);
    }

    /**
     * Admin manual credit grant to a user account.
     */
    public function grantCredits(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1', 'max:50000'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $amount = (int) $validated['amount'];
        $reason = $validated['reason'] ?? 'Admin manual grant';

        $this->creditService->grant(
            user: $user,
            amount: $amount,
            referenceType: 'admin_grant',
            referenceId: 'admin_' . $request->user()->id,
            description: $reason
        );

        return back()->with('success', "Successfully granted {$amount} credits to {$user->name}.");
    }

    /**
     * Update user role (e.g. promote to admin or demote to user).
     */
    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'in:user,admin'],
        ]);

        $user->update([
            'role' => $validated['role'] === 'admin' ? UserRole::ADMIN : UserRole::USER,
        ]);

        return back()->with('success', "Updated role for {$user->name} to {$validated['role']}.");
    }
}
