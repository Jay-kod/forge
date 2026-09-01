<?php

declare(strict_types=1);

namespace App\Modules\AI\Services;

use App\Modules\AI\Contracts\AIProviderInterface;
use App\Modules\AI\DTOs\AIRequest;
use App\Modules\AI\DTOs\AIResponse;
use App\Modules\Credits\Contracts\CreditServiceInterface;
use Exception;
use Psr\Log\LoggerInterface;
use RuntimeException;

class AIOrchestrator
{
    /** @var AIProviderInterface[] */
    protected array $providers = [];

    public function __construct(
        protected CreditServiceInterface $credits,
        protected LoggerInterface $logger
    ) {}

    public function registerProvider(AIProviderInterface $provider): void
    {
        $this->providers[$provider->identifier()] = $provider;
    }

    public function execute(AIRequest $request): AIResponse
    {
        $provider = $this->selectProvider($request);
        $estimate = $provider->estimate($request);

        // 1. Atomically reserve credits
        $reservation = $this->credits->reserve(
            user: $request->user,
            amount: $estimate->credits,
            referenceType: $request->operationType,
            projectId: $request->projectId
        );

        $startTime = microtime(true);

        try {
            // 2. Execute with selected provider
            $response = $provider->complete($request);

            // 3. Atomically confirm credits consumption
            $this->credits->confirm($reservation);

            $this->logger->info('AI operation completed', [
                'operation' => $request->operationType,
                'provider' => $provider->identifier(),
                'credits' => $estimate->credits,
                'duration' => microtime(true) - $startTime,
            ]);

            return $response;

        } catch (Exception $e) {
            // 4. On failure, attempt fallback provider or refund reserved credits
            $this->logger->warning('AI primary provider failed, attempting fallback', [
                'primary' => $provider->identifier(),
                'error' => $e->getMessage(),
            ]);

            $fallback = $this->selectFallbackProvider($provider, $request);

            if ($fallback) {
                try {
                    $response = $fallback->complete($request);
                    $this->credits->confirm($reservation);
                    return $response;
                } catch (Exception $fallbackError) {
                    $this->logger->error('AI fallback provider also failed', [
                        'fallback' => $fallback->identifier(),
                        'error' => $fallbackError->getMessage(),
                    ]);
                }
            }

            // Refund credits on complete failure
            $this->credits->release($reservation, "AI operation failed: {$e->getMessage()}");
            throw new RuntimeException("AI processing failed. Credits have been refunded. Error: {$e->getMessage()}", 0, $e);
        }
    }

    protected function selectProvider(AIRequest $request): AIProviderInterface
    {
        foreach ($this->providers as $provider) {
            if ($provider->isAvailable() && $provider->supports($request->workloadClass)) {
                return $provider;
            }
        }

        // Fallback to first available provider
        foreach ($this->providers as $provider) {
            if ($provider->isAvailable()) {
                return $provider;
            }
        }

        throw new RuntimeException('No AI provider is currently available.');
    }

    protected function selectFallbackProvider(AIProviderInterface $failed, AIRequest $request): ?AIProviderInterface
    {
        foreach ($this->providers as $provider) {
            if ($provider->identifier() !== $failed->identifier() && $provider->isAvailable() && $provider->supports($request->workloadClass)) {
                return $provider;
            }
        }

        return null;
    }
}
