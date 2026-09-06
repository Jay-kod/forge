<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Modules\API\Models\ApiKey;
use App\Modules\Consent\Models\ConsentRecord;
use App\Modules\AI\Models\ByokCredential;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    /**
     * Display consolidated account settings.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $githubConnection = $user->githubConnection;
        $apiKeys = ApiKey::where('user_id', $user->id)->get(['id', 'name', 'last_used_at', 'created_at']);
        $byokCredentials = ByokCredential::where('user_id', $user->id)->get(['id', 'provider', 'label', 'is_active', 'created_at']);
        $consent = ConsentRecord::where('user_id', $user->id)->latest()->first();

        return Inertia::render('Settings/Index', [
            'profile' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->value ?? 'user',
                'technical_level' => $user->technical_level?->value ?? 'non_developer',
                'referral_code' => $user->referral_code,
                'created_at' => $user->created_at?->toFormattedDateString(),
            ],
            'github' => $githubConnection ? [
                'username' => $githubConnection->github_username,
                'avatar_url' => $githubConnection->avatar_url,
                'scope' => $githubConnection->scope,
            ] : null,
            'apiKeys' => $apiKeys,
            'byok' => $byokCredentials,
            'consent' => $consent ? [
                'telemetry_enabled' => $consent->telemetry_enabled ?? true,
                'allow_model_training' => $consent->allow_model_training ?? false,
                'data_retention_days' => $consent->data_retention_days ?? 90,
            ] : [
                'telemetry_enabled' => true,
                'allow_model_training' => false,
                'data_retention_days' => 90,
            ],
        ]);
    }

    /**
     * Update user profile / technical level.
     */
    public function updateProfile(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'technical_level' => ['nullable', 'string', 'in:non_developer,vibe_coder,developer,senior_developer'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'technical_level' => $validated['technical_level'] ?? $user->technical_level,
        ]);

        return back()->with('success', 'Profile updated successfully.');
    }
}
