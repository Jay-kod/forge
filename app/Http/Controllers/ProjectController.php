<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Modules\Projects\Actions\CreateProjectAction;
use App\Modules\Projects\Enums\ProjectStatus;
use App\Modules\Projects\Enums\WorkflowMode;
use App\Modules\Projects\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function __construct(
        protected CreateProjectAction $createProjectAction
    ) {}

    public function index(Request $request): Response
    {
        $projects = Project::where('user_id', $request->user()->id)
            ->with(['workflow.stages', 'context'])
            ->orderByDesc('updated_at')
            ->get();

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Projects/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'goal' => ['required', 'string', 'min:5', 'max:5000'],
            'title' => ['nullable', 'string', 'max:255'],
            'mode' => ['nullable', 'string', 'in:automatic,page_by_page'],
        ]);

        $mode = ($validated['mode'] ?? 'page_by_page') === 'automatic'
            ? WorkflowMode::AUTOMATIC
            : WorkflowMode::PAGE_BY_PAGE;

        $project = $this->createProjectAction->execute(
            user: $request->user(),
            userInput: $validated['goal'],
            title: $validated['title'] ?? null,
            mode: $mode
        );

        return redirect()->route('projects.show', $project);
    }

    public function show(Request $request, Project $project): Response
    {
        $this->authorize('view', $project);

        $project->load([
            'context',
            'workflow.stages.decisions',
            'discovery',
            'competitors',
            'evidence.sources',
            'opportunities.recommendations',
            'documents',
            'versions',
        ]);

        return Inertia::render('Projects/Show', [
            'project' => $project,
        ]);
    }

    public function archive(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $project->update(['status' => ProjectStatus::ARCHIVED]);

        return redirect()->route('projects.index')->with('success', 'Project archived successfully.');
    }
}
