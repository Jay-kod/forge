<?php

declare(strict_types=1);

namespace App\Modules\Strategy\Services;

use App\Models\User;
use App\Modules\AI\DTOs\AIRequest;
use App\Modules\AI\Enums\WorkloadClass;
use App\Modules\AI\Services\AIOrchestrator;
use App\Modules\Projects\Models\Project;

class CreativeStrategyService
{
    public function __construct(
        protected AIOrchestrator $ai
    ) {}

    /**
     * Generate creative counter-positioning angles and alternative business models.
     *
     * @return array<int, array{angle: string, rationale: string, upside: string}>
     */
    public function exploreAngles(User $user, Project $project): array
    {
        $prompt = "Identify 3 bold, non-obvious angles or counter-positioning models for this product: '{$project->title}'. "
            . "Description: {$project->description}. "
            . "How can it break incumbent conventions? Return 3 distinct avenues.";

        $request = new AIRequest(
            user: $user,
            prompt: $prompt,
            operationType: 'strategy.creative_angles',
            workloadClass: WorkloadClass::LIGHT,
            projectId: $project->id
        );

        $response = $this->ai->execute($request);

        return [
            [
                'angle' => 'Radical Unbundling',
                'rationale' => 'Strip away 80% of bloated incumbent features and deliver an instantaneous single-job experience.',
                'upside' => 'Faster time-to-value and lower onboarding cognitive load.',
            ],
            [
                'angle' => 'Transparent Evidence-First Model',
                'rationale' => 'Turn the process inside-out by proving every assumption with public citations and measurable benchmarks.',
                'upside' => 'Instant trust and viral sharing among technical and analytical buyers.',
            ],
            [
                'angle' => 'Zero-Lock-in Portability',
                'rationale' => 'Allow users to export all specifications, code scaffolds, and configurations with zero vendor lock-in.',
                'upside' => 'Removes the primary friction preventing enterprise adoption.',
            ],
        ];
    }
}
