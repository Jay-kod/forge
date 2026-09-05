<?php

declare(strict_types=1);

namespace App\Modules\Product\Jobs;

use App\Models\User;
use App\Modules\Product\Actions\ExecuteStageAction;
use App\Modules\Product\Models\WorkflowStage;
use App\Modules\Projects\Models\Project;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExecuteStageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 180;

    public function __construct(
        public User $user,
        public Project $project,
        public WorkflowStage $stage
    ) {
        $this->queue = $this->determineQueue($stage->stage_type->value);
    }

    /**
     * Execute stage intelligence on queue worker.
     */
    public function handle(ExecuteStageAction $executeStageAction): void
    {
        Log::info("Executing stage job: {$this->stage->stage_type->value} on queue [{$this->queue}] for project #{$this->project->id}");

        $this->stage->update([
            'status' => 'processing',
            'started_at' => $this->stage->started_at ?? now(),
        ]);

        try {
            $nextStage = $executeStageAction->execute($this->user, $this->project, $this->stage);

            Log::info("Stage job {$this->stage->stage_type->value} completed successfully.", [
                'project_id' => $this->project->id,
                'next_stage' => $nextStage?->stage_type->value,
            ]);
        } catch (Exception $e) {
            Log::error("Stage job execution failed: {$e->getMessage()}", [
                'project_id' => $this->project->id,
                'stage_id' => $this->stage->id,
                'stage_type' => $this->stage->stage_type->value,
                'trace' => $e->getTraceAsString(),
            ]);

            $this->stage->update([
                'status' => 'failed',
                'content' => [
                    'error' => $e->getMessage(),
                    'failed_at' => now()->toIso8601String(),
                ],
            ]);

            throw $e;
        }
    }

    /**
     * Route stage execution to dedicated queue worker channels.
     */
    protected function determineQueue(string $stageType): string
    {
        $researchStages = [
            'understanding',
            'discovery',
            'research',
            'competitors',
            'business_analysis',
            'geographic_research',
            'market_comparison',
            'situation_analysis',
            'competitor_comparison',
            'ux_analysis',
            'website_audit',
            'repo_inspection',
        ];

        $exportStages = [
            'package',
            'export',
            'github_export',
        ];

        if (in_array($stageType, $researchStages, true)) {
            return 'research';
        }

        if (in_array($stageType, $exportStages, true)) {
            return 'export';
        }

        return 'ai';
    }
}
