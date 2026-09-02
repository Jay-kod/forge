<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Modules\Product\Actions\ExecuteStageAction;
use App\Modules\Product\Models\WorkflowStage;
use App\Modules\Projects\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WorkflowController extends Controller
{
    public function __construct(
        protected ExecuteStageAction $executeStageAction
    ) {}

    public function advance(Request $request, Project $project, WorkflowStage $stage): RedirectResponse
    {
        $this->authorize('update', $project);

        $this->executeStageAction->execute($request->user(), $project, $stage);

        return redirect()->route('projects.show', $project)->with('success', "Stage {$stage->stage_type->label()} completed.");
    }

    public function approve(Request $request, Project $project, WorkflowStage $stage): RedirectResponse
    {
        $this->authorize('update', $project);

        $stage->update([
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
        ]);

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
