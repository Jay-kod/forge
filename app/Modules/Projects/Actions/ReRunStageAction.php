<?php

declare(strict_types=1);

namespace App\Modules\Projects\Actions;

use App\Models\User;
use App\Modules\Credits\Services\CreditService;
use App\Modules\Product\Actions\ExecuteStageAction;
use App\Modules\Product\Models\WorkflowStage;
use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Models\ProjectVersion;
use App\Modules\Projects\Services\ProjectVersionService;
use RuntimeException;

class ReRunStageAction
{
    public function __construct(
        protected ProjectVersionService $versionService,
        protected ExecuteStageAction $executeStageAction,
        protected CreditService $creditService
    ) {}

    /**
     * Re-run an intelligence stage safely by snapshotting current state prior to re-execution.
     *
     * @return array{stage: WorkflowStage, version: ProjectVersion}
     */
    public function execute(User $user, Project $project, WorkflowStage $stage, ?string $note = null): array
    {
        // 1. Snapshot current project state so approved decisions/documents are never overwritten
        $snapshotNote = $note ?? "Prior state before re-running stage: {$stage->stage_type->label()}";
        $version = $this->versionService->createSnapshot($project, 'regeneration', $snapshotNote);

        // 2. Atomic credit deduction for intelligence re-run (15 credits)
        $cost = 15;
        if ($this->creditService->getBalance($user) < $cost) {
            throw new RuntimeException("Insufficient credits to re-run {$stage->stage_type->label()}. 15 credits required.");
        }

        $reservation = $this->creditService->reserve($user, $cost, 'stage.rerun', (string) $project->id, $project->id);

        try {
            // 3. Reset stage state
            $stage->update([
                'status' => 'active',
                'approved_at' => null,
            ]);

            // 4. Execute stage
            $this->executeStageAction->execute($user, $project, $stage);

            // 5. Confirm credit deduction
            $this->creditService->confirm($reservation);

            return [
                'stage' => $stage->fresh(),
                'version' => $version,
            ];
        } catch (\Throwable $e) {
            // Release reserved credits on failure
            $this->creditService->release($reservation, $e->getMessage());
            throw $e;
        }
    }
}
