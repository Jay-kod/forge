<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Modules\API\Models\ApiKey;
use App\Modules\API\Services\ApiKeyService;
use App\Modules\Organizations\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiKeyManagementController extends Controller
{
    public function __construct(
        protected ApiKeyService $apiKeyService
    ) {}

    /**
     * List active API keys for the user.
     */
    public function index(Request $request): JsonResponse|\Inertia\Response
    {
        $keys = ApiKey::where('user_id', $request->user()->id)
            ->select(['id', 'organization_id', 'name', 'prefix', 'abilities', 'last_used_at', 'expires_at', 'created_at'])
            ->with('organization:id,name')
            ->orderByDesc('created_at')
            ->get();

        if ($request->wantsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'api_keys' => $keys,
            ]);
        }

        return \Inertia\Inertia::render('Settings/ApiKeys', [
            'api_keys' => $keys,
            'organizations' => $request->user()->organizations()->get(['organizations.id', 'organizations.name']),
        ]);
    }

    /**
     * Create a new API key and display secret once.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'organization_id' => ['nullable', 'exists:organizations,id'],
            'abilities' => ['nullable', 'array'],
            'expires_in_days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ]);

        $org = null;
        if (!empty($validated['organization_id'])) {
            $org = Organization::findOrFail($validated['organization_id']);
            if (!$org->hasRole($request->user(), ['owner', 'admin'])) {
                abort(403, 'Insufficient permissions to create API key for this organization.');
            }
        }

        $expiresAt = !empty($validated['expires_in_days'])
            ? now()->addDays((int) $validated['expires_in_days'])
            : null;

        $result = $this->apiKeyService->createKey(
            user: $request->user(),
            name: $validated['name'],
            organization: $org,
            abilities: $validated['abilities'] ?? ['*'],
            expiresAt: $expiresAt
        );

        return response()->json([
            'success' => true,
            'api_key' => [
                'id' => $result['api_key']->id,
                'name' => $result['api_key']->name,
                'prefix' => $result['api_key']->prefix,
                'plain_token' => $result['plain_token'], // ONLY returned upon creation!
                'abilities' => $result['api_key']->abilities,
                'expires_at' => $result['api_key']->expires_at,
            ],
            'message' => 'API Key created. Copy your token now; it will not be shown again.',
        ], 201);
    }

    /**
     * Revoke an API key.
     */
    public function destroy(Request $request, ApiKey $apiKey): JsonResponse
    {
        try {
            $this->apiKeyService->revokeKey($apiKey, $request->user());

            return response()->json([
                'success' => true,
                'message' => 'API key revoked successfully.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 403);
        }
    }
}
