<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Opportunity\Services\OpportunityGraphService;
use App\Modules\Product\Models\Workflow;
use App\Modules\Projects\Enums\ProjectStatus;
use App\Modules\Projects\Enums\ProjectType;
use App\Modules\Projects\Enums\WorkflowMode;
use App\Modules\Projects\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectApiController extends Controller
{
    /**
     * List all projects accessible by the authenticated API key.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var \App\Modules\API\Models\ApiKey|null $apiKey */
        $apiKey = $request->attributes->get('api_key');

        $query = Project::query();

        if ($apiKey && $apiKey->organization_id) {
            $query->where('organization_id', $apiKey->organization_id);
        } else {
            $userOrgIds = $user->organizations()->pluck('organizations.id');
            $query->where(function ($q) use ($user, $userOrgIds) {
                $q->where('user_id', $user->id)
                  ->orWhereIn('organization_id', $userOrgIds);
            });
        }

        $projects = $query->with(['organization:id,name', 'context'])
            ->orderByDesc('updated_at')
            ->paginate(20);

        return response()->json([
            'projects' => $projects,
        ]);
    }

    /**
     * Create a new project via the REST API.
     */
    public function store(Request $request): JsonResponse
    {
        if ($request->has('classification') && is_string($request->input('classification'))) {
            $request->merge(['classification' => strtoupper($request->input('classification'))]);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'classification' => ['required', Rule::enum(ProjectType::class)],
            'workflow_mode' => ['nullable', Rule::enum(WorkflowMode::class)],
            'organization_id' => ['nullable', 'exists:organizations,id'],
        ]);

        $user = $request->user();
        /** @var \App\Modules\API\Models\ApiKey|null $apiKey */
        $apiKey = $request->attributes->get('api_key');

        $orgId = $validated['organization_id'] ?? $apiKey?->organization_id;

        $project = Project::create([
            'user_id' => $user->id,
            'organization_id' => $orgId,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'classification' => $validated['classification'],
            'workflow_mode' => $validated['workflow_mode'] ?? WorkflowMode::AUTOMATIC,
            'status' => ProjectStatus::ACTIVE,
            'current_stage' => 'understanding',
        ]);

        // Initialize project context
        $project->context()->create([
            'user_input' => $validated['description'] ?? $validated['title'],
            'classification' => $project->classification->value,
            'classification_confidence' => 0.95,
            'business_context' => [
                'created_via' => 'api_v1',
                'api_key_id' => $apiKey?->id,
            ],
        ]);

        // Initialize default workflow
        Workflow::create([
            'project_id' => $project->id,
        ]);

        return response()->json([
            'success' => true,
            'project' => $project->load(['organization', 'context']),
            'message' => "Project '{$project->title}' initialized via API.",
        ], 201);
    }

    /**
     * Get complete details and context for a project.
     */
    public function show(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $project->load([
            'organization:id,name',
            'context',
            'workflow.stages',
            'versions' => fn($q) => $q->orderByDesc('version_number')->limit(5),
        ]);

        return response()->json([
            'project' => $project,
        ]);
    }

    /**
     * Get prioritized opportunity analysis and metrics matrix for a project.
     */
    public function opportunities(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $competitors = \App\Modules\Discovery\Models\Competitor::where('project_id', $project->id)->get();
        $hasRepo = (bool) $project->repositoryAudit;

        return response()->json([
            'project_id' => $project->id,
            'competitors_count' => $competitors->count(),
            'has_repository' => $hasRepo,
            'matrix' => [
                'high_priority' => [],
                'medium_priority' => [],
                'low_priority' => [],
            ],
        ]);
    }

    /**
     * Get topology graph data for a project.
     */
    public function graph(Request $request, Project $project, OpportunityGraphService $graphService): JsonResponse
    {
        $this->authorize('view', $project);

        $graphData = $graphService->build($project);

        return response()->json($graphData);
    }
}
