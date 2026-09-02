<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Modules\API\Services\ApiKeyService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    public function __construct(
        protected ApiKeyService $apiKeyService
    ) {}

    public function handle(Request $request, Closure $next, ?string $ability = null): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Missing Bearer token in Authorization header.',
            ], 401);
        }

        $apiKey = $this->apiKeyService->authenticate($token);

        if (!$apiKey) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Invalid or expired FORGE API key.',
            ], 401);
        }

        if ($ability && !$apiKey->hasAbility($ability)) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => "This API key lacks the '{$ability}' ability.",
            ], 403);
        }

        // Bind user to request and Auth facade for Policy checks
        \Illuminate\Support\Facades\Auth::setUser($apiKey->user);
        $request->setUserResolver(fn() => $apiKey->user);
        $request->attributes->set('api_key', $apiKey);

        return $next($request);
    }
}
