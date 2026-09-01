<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Modules\AI\DTOs\AIRequest;
use App\Modules\AI\Enums\WorkloadClass;
use App\Modules\AI\Services\AIOrchestrator;
use App\Modules\Product\Models\WorkflowStage;
use App\Modules\Projects\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WorkflowController extends Controller
{
    public function __construct(
        protected AIOrchestrator $ai
    ) {}

    public function advance(Request $request, Project $project, WorkflowStage $stage): RedirectResponse
    {
        $this->authorize('update', $project);

        // Run AI intelligence for this stage if needed
        $stageContent = $stage->content;

        if (empty($stageContent)) {
            $aiRequest = new AIRequest(
                user: $request->user(),
                prompt: "Analyze {$stage->stage_type->value} for project: {$project->title}. Context: {$project->description}",
                operationType: "stage.{$stage->stage_type->value}",
                workloadClass: match ($stage->stage_type->value) {
                    'understanding', 'discovery' => WorkloadClass::STANDARD,
                    'research', 'competitors', 'challenge', 'strategy' => WorkloadClass::DEEP,
                    'prd', 'architecture', 'package' => WorkloadClass::DEEP,
                    default => WorkloadClass::STANDARD,
                },
                projectId: $project->id
            );

            $aiResponse = $this->ai->execute($aiRequest);

            $stage->update([
                'status' => 'completed',
                'content' => [
                    'analysis' => $aiResponse->content,
                    'model' => $aiResponse->model,
                    'provider' => $aiResponse->provider,
                ],
                'completed_at' => now(),
            ]);
        }

        // Activate next stage if exists
        $nextStage = $project->workflow->stages()
            ->where('order', '>', $stage->order)
            ->orderBy('order')
            ->first();

        if ($nextStage) {
            $nextStage->update([
                'status' => 'active',
                'started_at' => now(),
            ]);
            $project->update(['current_stage' => $nextStage->stage_type->value]);
        } else {
            $project->update(['status' => 'completed']);
        }

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
