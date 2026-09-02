<?php

declare(strict_types=1);

namespace App\Modules\Product\Jobs;

use App\Modules\Product\Actions\ExecuteStageAction;
use App\Modules\Projects\Enums\ProjectStatus;
use App\Modules\Projects\Models\Project;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RunAutomaticWorkflowJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Project $project
    ) {}

    /**
     * Execute all workflow stages automatically in sequence.
     */
    public function handle(ExecuteStageAction $executeStageAction): void
    {
        $this->project->load(['workflow.stages', 'user']);

        $user = $this->project->user;
        if (!$user || !$this->project->workflow) {
            Log::warning('Automatic workflow execution aborted: missing user or workflow', [
                'project_id' => $this->project->id,
            ]);
            return;
        }

        $stages = $this->project->workflow->stages()->orderBy('order')->get();

        foreach ($stages as $stage) {
            // If already completed or skipped, continue
            if ($stage->status === 'completed' || $stage->status === 'skipped') {
                continue;
            }

            try {
                $executeStageAction->execute($user, $this->project, $stage);
            } catch (Exception $e) {
                Log::error('Automatic workflow stage execution failed', [
                    'project_id' => $this->project->id,
                    'stage_id' => $stage->id,
                    'stage_type' => $stage->stage_type->value,
                    'error' => $e->getMessage(),
                ]);

                $stage->update(['status' => 'failed']);
                throw $e;
            }
        }

        $this->project->update(['status' => ProjectStatus::COMPLETED]);
    }
}
