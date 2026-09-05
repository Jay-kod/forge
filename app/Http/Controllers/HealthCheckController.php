<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthCheckController extends Controller
{
    /**
     * Check deep system health (Database, Cache, Storage, AI Services).
     */
    public function __invoke(): JsonResponse
    {
        $checks = [];
        $isHealthy = true;

        // 1. Database Connectivity
        try {
            DB::connection()->getPdo();
            $checks['database'] = 'ok';
        } catch (Throwable $e) {
            $checks['database'] = 'down: ' . $e->getMessage();
            $isHealthy = false;
        }

        // 2. Cache / Redis Operation
        try {
            $cacheKey = 'forge_health_check_' . microtime(true);
            Cache::put($cacheKey, 'ok', 10);
            $val = Cache::get($cacheKey);
            Cache::forget($cacheKey);

            if ($val === 'ok') {
                $checks['cache'] = 'ok';
            } else {
                $checks['cache'] = 'degraded: write/read mismatch';
                $isHealthy = false;
            }
        } catch (Throwable $e) {
            $checks['cache'] = 'down: ' . $e->getMessage();
            $isHealthy = false;
        }

        // 3. Storage Writable
        try {
            $storageWritable = is_writable(storage_path('framework/cache'))
                && is_writable(storage_path('logs'));
            $checks['storage'] = $storageWritable ? 'ok' : 'degraded: storage path not writable';
            if (!$storageWritable) {
                $isHealthy = false;
            }
        } catch (Throwable $e) {
            $checks['storage'] = 'down: ' . $e->getMessage();
            $isHealthy = false;
        }

        // 4. AI Provider Key Status
        $aiConfigured = !empty(config('services.anthropic.key'))
            || !empty(config('services.openai.key'))
            || !empty(config('services.gemini.key'));
        $checks['ai'] = $aiConfigured ? 'configured' : 'mock_fallback';

        return response()->json([
            'status' => $isHealthy ? 'ok' : 'unhealthy',
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
        ], $isHealthy ? 200 : 503);
    }
}
