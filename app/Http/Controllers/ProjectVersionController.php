<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Modules\Product\Models\WorkflowStage;
use App\Modules\Projects\Actions\ReRunStageAction;
use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Models\ProjectVersion;
use App\Modules\Projects\Services\ProjectVersionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectVersionController extends Controller
{
    public function __construct(
        protected ProjectVersionService $versionService
    ) {}

    /**
     * List all historical versions and snapshots of a project.
     */
    public function index(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $versions = $project->versions()
            ->orderByDesc('version')
            ->get(['id', 'project_id', 'version', 'created_by', 'note', 'created_at']);

        return response()->json([
            'versions' => $versions,
        ]);
    }

    /**
     * Compare two specific project versions and return document diffs.
     */
    public function diff(Request $request, Project $project, ProjectVersion $v1, ProjectVersion $v2): JsonResponse
    {
        $this->authorize('view', $project);

        if ($v1->project_id !== $project->id || $v2->project_id !== $project->id) {
            abort(404, 'Version does not belong to project.');
        }

        $comparison = $this->versionService->compare($v1, $v2);

        return response()->json($comparison);
    }

    /**
     * Get the full chronological decision timeline for a project.
     */
    public function timeline(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $timeline = $this->versionService->getDecisionTimeline($project);

        return response()->json([
            'timeline' => $timeline,
        ]);
    }

    /**
     * Re-run an intelligence stage, snapshotting current state into a new version.
     */
    public function rerun(
        Request $request,
        Project $project,
        WorkflowStage $stage,
        ReRunStageAction $reRunStageAction
    ): JsonResponse {
        $this->authorize('update', $project);

        if ($stage->workflow->project_id !== $project->id) {
            abort(404, 'Stage does not belong to project.');
        }

        $note = $request->input('note');

        try {
            $result = $reRunStageAction->execute($request->user(), $project, $stage, $note);

            return response()->json([
                'success' => true,
                'stage' => $result['stage'],
                'version' => $result['version'],
                'message' => "Successfully re-ran {$stage->stage_type->label()} and archived snapshot v{$result['version']->version}.",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
