<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Modules\Identity\Services\AuthenticationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function __construct(
        protected AuthenticationService $authService
    ) {}

    public function showLogin(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function redirect(string $provider): RedirectResponse
    {
        if (!in_array($provider, ['google', 'github'], true)) {
            abort(404);
        }

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        if (!in_array($provider, ['google', 'github'], true)) {
            abort(404);
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
            $user = $this->authService->findOrCreateSocialUser($provider, $socialUser);

            Auth::login($user, remember: true);
            request()->session()->regenerate();

            return redirect()->intended(route('projects.index'));
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Authentication failed: ' . $e->getMessage());
        }
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Demo login for local development / testing without live OAuth credentials.
     */
    public function demoLogin(Request $request): RedirectResponse
    {
        $user = User::firstOrCreate(
            ['email' => 'founder@forge.local'],
            [
                'name' => 'Adaeze Founder',
                'role' => 'user',
                'technical_level' => 'vibe_coder',
                'email_verified_at' => now(),
            ]
        );

        if (!$user->creditAccount()->exists()) {
            $user->creditAccount()->create([
                'balance' => 200,
                'lifetime_granted' => 200,
                'lifetime_consumed' => 0,
            ]);
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->route('projects.index');
    }
}
