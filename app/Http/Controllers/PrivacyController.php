<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Modules\Consent\Enums\ConsentType;
use App\Modules\Consent\Services\ConsentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PrivacyController extends Controller
{
    public function __construct(
        protected ConsentService $consentService,
        protected \App\Modules\Consent\Services\UserDataExportService $exportService,
        protected \App\Modules\Consent\Services\AccountDeletionService $deletionService
    ) {}

    /**
     * Display privacy settings and active consent statuses.
     */
    public function index(Request $request): JsonResponse|Response
    {
        $user = $request->user();
        $consents = $this->consentService->getUserConsents($user);
        $history = $this->consentService->getConsentAuditTrail($user);

        if ($request->wantsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'consents' => $consents,
                'audit_history' => $history,
            ]);
        }

        return Inertia::render('Settings/Privacy', [
            'consents' => $consents,
            'audit_history' => $history,
        ]);
    }

    /**
     * Record an update to a user consent preference.
     */
    public function updateConsent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'consent_type' => ['required', 'string', 'in:analytics,product_improvement,ai_improvement,marketing'],
            'granted' => ['required', 'boolean'],
            'version' => ['nullable', 'string', 'max:20'],
        ]);

        $record = $this->consentService->recordConsent(
            $request->user(),
            ConsentType::from($validated['consent_type']),
            (bool) $validated['granted'],
            $validated['version'] ?? '1.0',
            $request->ip()
        );

        return response()->json([
            'success' => true,
            'message' => 'Privacy preference updated successfully.',
            'record' => $record,
            'consents' => $this->consentService->getUserConsents($request->user()),
        ]);
    }

    /**
     * Download comprehensive GDPR-compliant user data archive.
     */
    public function exportData(Request $request): \Illuminate\Http\Response
    {
        return $this->exportService->exportDownload($request->user());
    }

    /**
     * Purge user account and all personal assets.
     */
    public function destroyAccount(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = $request->user();

        try {
            $this->deletionService->deleteAccount($user, $validated['password']);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        \Illuminate\Support\Facades\Auth::forgetUser();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->wantsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'success' => true,
                'message' => 'Your account and data have been permanently purged.',
            ]);
        }

        return redirect('/')->with('success', 'Your account has been deleted.');
    }
}
