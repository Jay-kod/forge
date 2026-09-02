<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Modules\AI\Services\ByokService;
use App\Modules\Organizations\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ByokController extends Controller
{
    public function __construct(
        protected ByokService $byokService
    ) {}

    /**
     * List registered BYOK credentials (all keys masked).
     */
    public function index(Request $request): JsonResponse
    {
        $orgId = $request->query('organization_id');
        $org = null;
        if ($orgId) {
            $org = Organization::findOrFail((int) $orgId);
            if (!$org->hasMember($request->user())) {
                abort(403, 'Unauthorized access to organization credentials.');
            }
        }

        $credentials = $this->byokService->listCredentials($request->user(), $org);

        if ($request->wantsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'supported_providers' => ByokService::SUPPORTED_PROVIDERS,
                'credentials' => $credentials,
            ]);
        }

        return \Inertia\Inertia::render('Settings/Byok', [
            'supported_providers' => ByokService::SUPPORTED_PROVIDERS,
            'credentials' => $credentials,
            'organizations' => $request->user()->organizations()->get(['organizations.id', 'organizations.name']),
        ]);
    }

    /**
     * Store or update an encrypted BYOK credential.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'provider' => ['required', 'string', 'in:openai,anthropic,gemini'],
            'api_key' => ['required', 'string', 'min:10'],
            'organization_id' => ['nullable', 'exists:organizations,id'],
            'label' => ['nullable', 'string', 'max:100'],
        ]);

        $org = null;
        if (!empty($validated['organization_id'])) {
            $org = Organization::findOrFail((int) $validated['organization_id']);
            if (!$org->hasRole($request->user(), ['owner', 'admin'])) {
                abort(403, 'Insufficient permissions to configure organization credentials.');
            }
        }

        $credential = $this->byokService->storeCredential(
            user: $request->user(),
            provider: $validated['provider'],
            plainKey: $validated['api_key'],
            organization: $org,
            label: $validated['label'] ?? null
        );

        return response()->json([
            'success' => true,
            'credential' => $credential,
            'message' => "Encrypted credential for " . ucfirst($credential->provider) . " saved securely.",
        ], 201);
    }

    /**
     * Remove a BYOK credential.
     */
    public function destroy(Request $request, string $provider): JsonResponse
    {
        $orgId = $request->query('organization_id');
        $org = null;
        if ($orgId) {
            $org = Organization::findOrFail((int) $orgId);
        }

        $deleted = $this->byokService->removeCredential($request->user(), $provider, $org);

        return response()->json([
            'success' => $deleted,
            'message' => $deleted ? "Credential removed." : "Credential not found.",
        ]);
    }
}
