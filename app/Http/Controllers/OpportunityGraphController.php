<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Modules\Opportunity\Services\OpportunityGraphService;
use App\Modules\Projects\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OpportunityGraphController extends Controller
{
    public function __construct(
        protected OpportunityGraphService $graphService
    ) {}

    /**
     * Retrieve the full interactive Opportunity Graph data for a project.
     */
    public function show(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $graph = $this->graphService->build($project);

        return response()->json($graph);
    }
}
