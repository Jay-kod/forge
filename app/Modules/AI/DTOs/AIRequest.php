<?php

declare(strict_types=1);

namespace App\Modules\AI\DTOs;

use App\Models\User;
use App\Modules\AI\Enums\WorkloadClass;

readonly class AIRequest
{
    public function __construct(
        public User $user,
        public string $prompt,
        public string $operationType,
        public WorkloadClass $workloadClass = WorkloadClass::STANDARD,
        public array $context = [],
        public array $evidence = [],
        public ?int $projectId = null,
        public array $options = []
    ) {}
}
