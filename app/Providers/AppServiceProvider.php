<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            \App\Modules\Billing\Contracts\EntitlementServiceInterface::class,
            \App\Modules\Billing\Services\EntitlementService::class
        );

        $this->app->singleton(
            \App\Modules\Credits\Contracts\CreditServiceInterface::class,
            \App\Modules\Credits\Services\CreditService::class
        );

        $this->app->singleton(
            \App\Modules\Projects\Contracts\ClassificationServiceInterface::class,
            \App\Modules\Projects\Services\ClassificationService::class
        );

        $this->app->singleton(
            \App\Modules\Research\Contracts\WebSearchProviderInterface::class,
            \App\Modules\Research\Services\WebSearchService::class
        );

        $this->app->singleton(\App\Modules\AI\Services\AIOutputValidator::class);

        $this->app->singleton(
            \App\Modules\Strategy\Contracts\StrategyServiceInterface::class,
            \App\Modules\Strategy\Services\StrategyEngine::class
        );

        $this->app->singleton(
            \App\Modules\Blueprint\Contracts\BlueprintServiceInterface::class,
            \App\Modules\Blueprint\Services\BlueprintService::class
        );

        $this->app->singleton(\App\Modules\AI\Services\AIOrchestrator::class, function ($app) {
            $orchestrator = new \App\Modules\AI\Services\AIOrchestrator(
                $app->make(\App\Modules\Credits\Contracts\CreditServiceInterface::class),
                $app->make(\Psr\Log\LoggerInterface::class)
            );
            $orchestrator->registerProvider(new \App\Modules\AI\Providers\AnthropicProvider());
            $orchestrator->registerProvider(new \App\Modules\AI\Providers\OpenAIProvider());
            $orchestrator->registerProvider(new \App\Modules\AI\Providers\GeminiProvider());
            return $orchestrator;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Gate::policy(
            \App\Modules\Projects\Models\Project::class,
            \App\Modules\Projects\Policies\ProjectPolicy::class
        );

        \Illuminate\Support\Facades\RateLimiter::for('auth', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(5)->by($request->ip());
        });

        \Illuminate\Support\Facades\RateLimiter::for('ai', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(15)->by($request->user()?->id ?: $request->ip());
        });

        \Illuminate\Support\Facades\RateLimiter::for('export', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });
    }
}
