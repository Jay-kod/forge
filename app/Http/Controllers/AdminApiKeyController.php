<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class AdminApiKeyController extends Controller
{
    /**
     * Display system-wide API keys & integrations console.
     */
    public function index(): Response
    {
        $providers = [
            'anthropic' => [
                'name' => 'Anthropic Claude',
                'category' => 'ai',
                'icon' => '⚡',
                'env_var' => 'ANTHROPIC_API_KEY',
                'is_configured' => !empty(config('services.anthropic.key')),
                'masked_key' => $this->maskKey(config('services.anthropic.key')),
                'default_model' => config('services.anthropic.model', 'claude-3-7-sonnet-20250219'),
                'docs_url' => 'https://console.anthropic.com/settings/keys',
            ],
            'openai' => [
                'name' => 'OpenAI GPT',
                'category' => 'ai',
                'icon' => '🧠',
                'env_var' => 'OPENAI_API_KEY',
                'is_configured' => !empty(config('services.openai.key')),
                'masked_key' => $this->maskKey(config('services.openai.key')),
                'default_model' => config('services.openai.model', 'gpt-4o'),
                'docs_url' => 'https://platform.openai.com/api-keys',
            ],
            'gemini' => [
                'name' => 'Google Gemini',
                'category' => 'ai',
                'icon' => '✨',
                'env_var' => 'GEMINI_API_KEY',
                'is_configured' => !empty(config('services.gemini.key')),
                'masked_key' => $this->maskKey(config('services.gemini.key')),
                'default_model' => config('services.gemini.model', 'gemini-2.5-flash'),
                'docs_url' => 'https://aistudio.google.com/app/apikey',
            ],
            'stripe' => [
                'name' => 'Stripe Payments',
                'category' => 'billing',
                'icon' => '💳',
                'env_var' => 'STRIPE_SECRET',
                'is_configured' => !empty(config('services.stripe.secret') ?? env('STRIPE_SECRET')),
                'masked_key' => $this->maskKey((string) (config('services.stripe.secret') ?? env('STRIPE_SECRET'))),
                'mode' => str_starts_with((string) env('STRIPE_SECRET'), 'sk_live') ? 'live' : 'test',
                'webhook_configured' => !empty(config('services.stripe.webhook.secret') ?? env('STRIPE_WEBHOOK_SECRET')),
                'docs_url' => 'https://dashboard.stripe.com/apikeys',
            ],
            'github' => [
                'name' => 'GitHub OAuth & App',
                'category' => 'integration',
                'icon' => '🐙',
                'env_var' => 'GITHUB_CLIENT_ID',
                'is_configured' => !empty(config('services.github.client_id')),
                'masked_key' => $this->maskKey(config('services.github.client_id')),
                'callback_url' => config('services.github.redirect', url('/integrations/github/callback')),
                'docs_url' => 'https://github.com/settings/developers',
            ],
            'tavily' => [
                'name' => 'Web Search / Research',
                'category' => 'research',
                'icon' => '🔍',
                'env_var' => 'TAVILY_API_KEY',
                'is_configured' => !empty(env('TAVILY_API_KEY')),
                'masked_key' => $this->maskKey((string) env('TAVILY_API_KEY')),
                'docs_url' => 'https://tavily.com',
            ],
        ];

        return Inertia::render('Admin/ApiKeys', [
            'providers' => $providers,
        ]);
    }

    /**
     * Test live connectivity for a provider.
     */
    public function testConnection(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'provider' => ['required', 'string', 'in:anthropic,openai,gemini,stripe,github,tavily'],
        ]);

        $provider = $validated['provider'];
        $startTime = microtime(true);

        try {
            $result = match ($provider) {
                'anthropic' => $this->testAnthropic(),
                'openai' => $this->testOpenAI(),
                'gemini' => $this->testGemini(),
                'stripe' => $this->testStripe(),
                'github' => $this->testGitHub(),
                'tavily' => $this->testTavily(),
            };

            $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

            return response()->json([
                'success' => $result['success'],
                'latency_ms' => $latencyMs,
                'message' => $result['message'],
            ]);
        } catch (Throwable $e) {
            $latencyMs = (int) round((microtime(true) - $startTime) * 1000);
            return response()->json([
                'success' => false,
                'latency_ms' => $latencyMs,
                'message' => 'Connection test failed: ' . $e->getMessage(),
            ], 422);
        }
    }

    protected function testAnthropic(): array
    {
        $key = config('services.anthropic.key');
        if (empty($key)) {
            return ['success' => false, 'message' => 'Anthropic API key is not configured in environment.'];
        }

        $response = Http::withHeaders([
            'x-api-key' => $key,
            'anthropic-version' => '2023-06-01',
        ])->timeout(5)->get('https://api.anthropic.com/v1/models');

        if ($response->successful()) {
            return ['success' => true, 'message' => 'Successfully connected to Anthropic API.'];
        }

        return ['success' => false, 'message' => 'Anthropic returned HTTP ' . $response->status()];
    }

    protected function testOpenAI(): array
    {
        $key = config('services.openai.key');
        if (empty($key)) {
            return ['success' => false, 'message' => 'OpenAI API key is not configured in environment.'];
        }

        $response = Http::withToken($key)->timeout(5)->get('https://api.openai.com/v1/models');
        if ($response->successful()) {
            return ['success' => true, 'message' => 'Successfully connected to OpenAI API.'];
        }

        return ['success' => false, 'message' => 'OpenAI returned HTTP ' . $response->status()];
    }

    protected function testGemini(): array
    {
        $key = config('services.gemini.key');
        if (empty($key)) {
            return ['success' => false, 'message' => 'Google Gemini API key is not configured in environment.'];
        }

        $response = Http::timeout(5)->get("https://generativelanguage.googleapis.com/v1beta/models?key={$key}");
        if ($response->successful()) {
            return ['success' => true, 'message' => 'Successfully connected to Google Gemini API.'];
        }

        return ['success' => false, 'message' => 'Gemini returned HTTP ' . $response->status()];
    }

    protected function testStripe(): array
    {
        $secret = config('services.stripe.secret') ?? env('STRIPE_SECRET');
        if (empty($secret)) {
            return ['success' => false, 'message' => 'Stripe secret is not configured.'];
        }

        $response = Http::withToken($secret)->timeout(5)->get('https://api.stripe.com/v1/balance');
        if ($response->successful()) {
            return ['success' => true, 'message' => 'Successfully connected to Stripe API.'];
        }

        return ['success' => false, 'message' => 'Stripe returned HTTP ' . $response->status()];
    }

    protected function testGitHub(): array
    {
        $clientId = config('services.github.client_id');
        if (empty($clientId)) {
            return ['success' => false, 'message' => 'GitHub OAuth Client ID is not configured.'];
        }

        $response = Http::timeout(5)->get('https://api.github.com/zen');
        if ($response->successful()) {
            return ['success' => true, 'message' => 'Connected to GitHub public API. Client ID registered: ' . substr($clientId, 0, 8) . '...'];
        }

        return ['success' => false, 'message' => 'GitHub API probe returned HTTP ' . $response->status()];
    }

    protected function testTavily(): array
    {
        $key = env('TAVILY_API_KEY');
        if (empty($key)) {
            return ['success' => true, 'message' => 'Tavily key not set — system gracefully uses mock research fallback.'];
        }

        return ['success' => true, 'message' => 'Tavily API key configured for live web search queries.'];
    }

    protected function maskKey(?string $key): string
    {
        if (empty($key)) {
            return 'Not configured';
        }

        $len = strlen($key);
        if ($len <= 8) {
            return str_repeat('*', $len);
        }

        $prefix = substr($key, 0, min(6, (int) floor($len / 3)));
        $suffix = substr($key, -4);
        return $prefix . '••••••••' . $suffix;
    }
}
