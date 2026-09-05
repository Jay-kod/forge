<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Modules\Admin\Services\AdminService;
use App\Modules\Identity\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    public function __construct(
        protected AdminService $adminService
    ) {}

    /**
     * Admin dashboard with system metrics, plan breakdown, and user management.
     */
    public function dashboard(Request $request): Response
    {
        $search = $request->query('search');
        $users = $this->adminService->getPaginatedUsers($search);
        $stats = $this->adminService->getDashboardStats();

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
        $reason = $validated['reason'] ?? null;

        $this->adminService->grantCredits(
            admin: $request->user(),
            user: $user,
            amount: $amount,
            reason: $reason
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

        $role = $validated['role'] === 'admin' ? UserRole::ADMIN : UserRole::USER;
        $this->adminService->updateUserRole($user, $role);

        return back()->with('success', "Updated role for {$user->name} to {$validated['role']}.");
    }
}
