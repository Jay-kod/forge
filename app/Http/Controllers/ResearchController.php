<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Modules\Projects\Models\Project;
use App\Modules\Research\Actions\RefreshResearchAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ResearchController extends Controller
{
    /**
     * Re-run an updated research sweep for the project.
     */
    public function refresh(
        Request $request,
        Project $project,
        RefreshResearchAction $refreshAction
    ): RedirectResponse {
        $this->authorize('update', $project);

        $refreshAction->execute($request->user(), $project);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Market research and evidence registry refreshed successfully.');
    }
}
