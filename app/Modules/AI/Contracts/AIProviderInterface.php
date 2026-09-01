<?php

declare(strict_types=1);

namespace App\Modules\AI\Contracts;

use App\Modules\AI\DTOs\AIRequest;
use App\Modules\AI\DTOs\AIResponse;
use App\Modules\AI\DTOs\WorkloadEstimate;
use App\Modules\AI\Enums\WorkloadClass;

interface AIProviderInterface
{
    public function identifier(): string;
    public function complete(AIRequest $request): AIResponse;
    public function estimate(AIRequest $request): WorkloadEstimate;
    public function isAvailable(): bool;
    public function supports(WorkloadClass $workload): bool;
}
