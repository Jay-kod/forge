<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Modules\Billing\Services\SubscriptionService;
use App\Modules\Identity\Services\AuthenticationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function __construct(
        protected AuthenticationService $authService,
        protected SubscriptionService $subscriptionService
    ) {}

    public function showLogin(): Response
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * Handle standard email & password authentication.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended(route('projects.index'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Handle new user registration.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(8)],
            'technical_level' => ['nullable', 'string', 'in:non_developer,vibe_coder,developer,senior_developer'],
        ]);

        $name = trim($validated['first_name'] . ' ' . ($validated['last_name'] ?? ''));

        $user = User::create([
            'name' => $name,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'user',
            'technical_level' => $validated['technical_level'] ?? 'vibe_coder',
            'email_verified_at' => now(),
        ]);

        $user->creditAccount()->create([
            'balance' => 25,
            'lifetime_granted' => 25,
            'lifetime_consumed' => 0,
        ]);

        $this->subscriptionService->provisionFreePlan($user);

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->route('projects.index');
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
     * Supports multiple persona roles: founder, developer, admin.
     */
    public function demoLogin(Request $request): RedirectResponse
    {
        $persona = $request->input('persona', 'founder');

        $personas = [
            'founder' => [
                'email' => 'founder@forge.local',
                'name' => 'Adaeze Founder',
                'role' => 'user',
                'technical_level' => 'vibe_coder',
                'credits' => 200,
            ],
            'developer' => [
                'email' => 'developer@forge.local',
                'name' => 'Liam Engineer',
                'role' => 'user',
                'technical_level' => 'senior_developer',
                'credits' => 200,
            ],
            'admin' => [
                'email' => 'admin@forge.local',
                'name' => 'Forge Administrator',
                'role' => 'admin',
                'technical_level' => 'developer',
                'credits' => 500,
            ],
        ];

        $config = $personas[$persona] ?? $personas['founder'];

        $user = User::firstOrCreate(
            ['email' => $config['email']],
            [
                'name' => $config['name'],
                'role' => $config['role'],
                'technical_level' => $config['technical_level'],
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        if (!$user->creditAccount()->exists()) {
            $user->creditAccount()->create([
                'balance' => $config['credits'],
                'lifetime_granted' => $config['credits'],
                'lifetime_consumed' => 0,
            ]);
        }

        if (!$user->subscription()->exists()) {
            $this->subscriptionService->provisionFreePlan($user);
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        if ($user->role === 'admin' && $persona === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('projects.index');
    }
}
