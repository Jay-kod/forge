<?php

declare(strict_types=1);

namespace App\Modules\Projects\Actions;

use App\Models\User;
use App\Modules\Billing\Contracts\EntitlementServiceInterface;
use App\Modules\Context\Models\ProjectContext;
use App\Modules\Product\Models\Workflow;
use App\Modules\Projects\Contracts\ClassificationServiceInterface;
use App\Modules\Projects\Enums\ProjectStatus;
use App\Modules\Projects\Enums\WorkflowMode;
use App\Modules\Projects\Models\Project;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CreateProjectAction
{
    public function __construct(
        protected EntitlementServiceInterface $entitlements,
        protected ClassificationServiceInterface $classificationService
    ) {}

    public function execute(
        User $user,
        string $userInput,
        ?string $title = null,
        WorkflowMode $mode = WorkflowMode::PAGE_BY_PAGE
    ): Project {
        // 1. Entitlement check
        $limit = $this->entitlements->getLimit($user, 'project.create');
        if ($limit !== null) {
            $activeCount = Project::where('user_id', $user->id)
                ->where('status', ProjectStatus::ACTIVE)
                ->count();

            if ($activeCount >= $limit) {
                throw new RuntimeException("Project limit reached for your current plan ({$limit} active projects). Please upgrade to create more projects.");
            }
        }

        return DB::transaction(function () use ($user, $userInput, $title, $mode) {
            // 2. Classify situation
            $classification = $this->classificationService->classify($userInput);

            // Generate title from input if not provided
            $derivedTitle = $title ?: $this->deriveTitle($userInput, $classification->classification->label());

            // 3. Create Project
            $project = Project::create([
                'user_id' => $user->id,
                'title' => $derivedTitle,
                'description' => $userInput,
                'classification' => $classification->classification,
                'status' => ProjectStatus::ACTIVE,
                'workflow_mode' => $mode,
                'current_stage' => 'understanding',
            ]);

            // 4. Create Project Context
            ProjectContext::create([
                'project_id' => $project->id,
                'user_input' => $userInput,
                'classification' => $classification->classification->value,
                'classification_confidence' => $classification->confidence,
                'user_understanding' => [
                    'technical_level' => $user->technical_level?->value ?? 'vibe_coder',
                    'reasoning' => $classification->reasoning,
                ],
                'business_context' => [],
                'product_context' => [],
                'geographic_context' => [],
                'existing_system' => [],
                'goals' => ['primary' => $userInput],
            ]);

            // 5. Initialize Workflow & Stages
            $workflow = Workflow::create([
                'project_id' => $project->id,
                'mode' => $mode,
                'status' => 'active',
            ]);

            $order = 1;
            foreach ($classification->suggestedStages as $stageType) {
                $workflow->stages()->create([
                    'stage_type' => $stageType,
                    'order' => $order,
                    'status' => $order === 1 ? 'active' : 'pending',
                    'version' => 1,
                    'started_at' => $order === 1 ? now() : null,
                ]);
                $order++;
            }

            // 6. Create initial version snapshot
            $project->versions()->create([
                'version' => 1,
                'snapshot' => [
                    'project' => $project->toArray(),
                    'classification' => $classification->classification->value,
                    'stages' => $classification->suggestedStages,
                ],
                'created_by' => 'system',
                'note' => 'Project initialized from input inquiry',
            ]);

            return $project;
        });
    }

    protected function deriveTitle(string $input, string $defaultLabel): string
    {
        $words = explode(' ', trim($input));
        if (count($words) <= 6) {
            return ucfirst(trim($input));
        }

        return ucfirst(implode(' ', array_slice($words, 0, 5))) . '...';
    }
}
