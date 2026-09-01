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

        $this->app->singleton(\App\Modules\AI\Services\AIOrchestrator::class, function ($app) {
            $orchestrator = new \App\Modules\AI\Services\AIOrchestrator(
                $app->make(\App\Modules\Credits\Contracts\CreditServiceInterface::class),
                $app->make(\Psr\Log\LoggerInterface::class)
            );
            $orchestrator->registerProvider(new \App\Modules\AI\Providers\AnthropicProvider());
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
    }
}
