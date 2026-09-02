<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Modules\Organizations\Models\Organization;
use App\Modules\Organizations\Services\OrganizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function __construct(
        protected OrganizationService $orgService
    ) {}

    /**
     * List all organizations the authenticated user belongs to.
     */
    public function index(Request $request): JsonResponse|\Inertia\Response
    {
        $user = $request->user();

        $organizations = $user->organizations()
            ->with(['owner:id,name,email', 'members:id,name,email', 'creditAccount', 'projects'])
            ->get();

        if ($request->wantsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'organizations' => $organizations,
            ]);
        }

        return \Inertia\Inertia::render('Organizations/Index', [
            'organizations' => $organizations,
        ]);
    }

    /**
     * Create a new organization with team credit pooling.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'plan' => ['nullable', 'string', 'in:starter,business,enterprise'],
        ]);

        $organization = $this->orgService->createOrganization(
            $request->user(),
            $validated['name'],
            $validated['plan'] ?? 'business'
        );

        return response()->json([
            'success' => true,
            'organization' => $organization->load(['members', 'creditAccount']),
            'message' => "Organization '{$organization->name}' created successfully with 100 starter team credits.",
        ], 201);
    }

    /**
     * Retrieve details for an organization including member roster and pooled balance.
     */
    public function show(Request $request, Organization $organization): JsonResponse
    {
        if (!$organization->hasMember($request->user())) {
            abort(403, 'Unauthorized. You are not a member of this organization.');
        }

        $organization->load([
            'owner:id,name,email',
            'members:id,name,email',
            'projects:id,organization_id,title,status,created_at',
            'creditAccount',
            'invitations' => fn($q) => $q->whereNull('accepted_at'),
        ]);

        return response()->json([
            'organization' => $organization,
            'role' => $organization->getRole($request->user()),
            'pooled_balance' => $this->orgService->getBalance($organization),
        ]);
    }

    /**
     * Invite a new team member.
     */
    public function invite(Request $request, Organization $organization): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'role' => ['nullable', 'string', 'in:admin,member,viewer'],
        ]);

        try {
            $invitation = $this->orgService->inviteMember(
                $organization,
                $request->user(),
                $validated['email'],
                $validated['role'] ?? 'member'
            );

            return response()->json([
                'success' => true,
                'invitation' => $invitation,
                'message' => "Invitation generated for {$invitation->email}.",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Accept an invitation using the unique token.
     */
    public function acceptInvite(Request $request, string $token): JsonResponse
    {
        try {
            $organization = $this->orgService->acceptInvitation($token, $request->user());

            return response()->json([
                'success' => true,
                'organization' => $organization,
                'message' => "Successfully joined {$organization->name}.",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove a member from the organization.
     */
    public function removeMember(Request $request, Organization $organization, User $user): JsonResponse
    {
        try {
            $this->orgService->removeMember($organization, $request->user(), $user);

            return response()->json([
                'success' => true,
                'message' => "Member removed successfully.",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update a member's role.
     */
    public function updateRole(Request $request, Organization $organization, User $user): JsonResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', 'in:admin,member,viewer'],
        ]);

        try {
            $this->orgService->updateMemberRole($organization, $request->user(), $user, $validated['role']);

            return response()->json([
                'success' => true,
                'message' => "Member role updated to {$validated['role']}.",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
