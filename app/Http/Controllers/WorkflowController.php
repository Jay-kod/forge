<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Modules\Product\Actions\ExecuteStageAction;
use App\Modules\Product\Jobs\ExecuteStageJob;
use App\Modules\Product\Models\WorkflowStage;
use App\Modules\Projects\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WorkflowController extends Controller
{
    public function __construct(
        protected ExecuteStageAction $executeStageAction
    ) {}

    /**
     * Advance a workflow stage either synchronously or asynchronously via background queue.
     */
    public function advance(Request $request, Project $project, WorkflowStage $stage): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $project);

        $isAsync = $request->wantsJson() || $request->boolean('async');

        if ($isAsync && config('queue.default') !== 'sync') {
            $stage->update(['status' => 'processing', 'started_at' => now()]);
            ExecuteStageJob::dispatch($request->user(), $project, $stage);

            return response()->json([
                'status' => 'processing',
                'message' => "Stage {$stage->stage_type->label()} queued for background execution.",
                'stage_id' => $stage->id,
            ]);
        }

        // Direct execution (synchronous or sync driver)
        $this->executeStageAction->execute($request->user(), $project, $stage);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'completed',
                'message' => "Stage {$stage->stage_type->label()} completed.",
                'stage_id' => $stage->id,
            ]);
        }

        return redirect()->route('projects.show', $project)->with('success', "Stage {$stage->stage_type->label()} completed.");
    }

    /**
     * Real-time polling endpoint for workflow stage progression.
     */
    public function status(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $project->load(['workflow.stages' => fn($q) => $q->orderBy('order')]);

        $stages = $project->workflow?->stages ?? collect();
        $totalStages = $stages->count();
        $completedStages = $stages->where('status', 'completed')->count();
        $percent = $totalStages > 0 ? (int) round(($completedStages / $totalStages) * 100) : 0;

        $activeStage = $stages->firstWhere('status', 'processing')
            ?? $stages->firstWhere('status', 'active');

        return response()->json([
            'project_id' => $project->id,
            'project_status' => $project->status->value,
            'current_stage' => $project->current_stage,
            'active_stage' => $activeStage ? [
                'id' => $activeStage->id,
                'stage_type' => $activeStage->stage_type->value,
                'label' => $activeStage->stage_type->label(),
                'status' => $activeStage->status,
                'started_at' => $activeStage->started_at?->toIso8601String(),
            ] : null,
            'percent_complete' => $percent,
            'is_complete' => $completedStages === $totalStages && $totalStages > 0,
            'stages' => $stages->map(fn($s) => [
                'id' => $s->id,
                'stage_type' => $s->stage_type->value,
                'label' => $s->stage_type->label(),
                'status' => $s->status,
                'summary' => $s->content['summary'] ?? null,
                'completed_at' => $s->completed_at?->toIso8601String(),
            ]),
        ]);
    }

    public function approve(Request $request, Project $project, WorkflowStage $stage): RedirectResponse
    {
        $this->authorize('update', $project);

        $stage->update([
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
        ]);

        if ($project->organization_id) {
            app(\App\Modules\Organizations\Services\AuditLogService::class)->record(
                'stage.approved',
                $request->user(),
                $project->organization,
                'workflow_stage',
                $stage->id,
                ['stage' => $stage->stage_type->value]
            );
        }

        return redirect()->route('projects.show', $project)->with('success', "Stage {$stage->stage_type->label()} approved.");
    }

    public function decide(Request $request, Project $project, WorkflowStage $stage): RedirectResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'question' => ['required', 'string'],
            'selected_option' => ['required', 'string'],
            'rationale' => ['nullable', 'string'],
        ]);

        $stage->decisions()->create([
            'project_id' => $project->id,
            'question' => $validated['question'],
            'selected_option' => $validated['selected_option'],
            'rationale' => $validated['rationale'] ?? null,
            'status' => 'decided',
            'decided_at' => now(),
        ]);

        return redirect()->route('projects.show', $project)->with('success', 'Decision recorded.');
    }
}
