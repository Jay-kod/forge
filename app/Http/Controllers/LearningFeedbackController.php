<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Modules\Consent\Services\LearningSystem;
use App\Modules\Projects\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LearningFeedbackController extends Controller
{
    public function __construct(
        protected LearningSystem $learningSystem
    ) {}

    /**
     * Ingest anonymous user feedback or recommendation evaluation signal.
     */
    public function store(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $validated = $request->validate([
            'category' => ['required', 'string', 'max:80'],
            'signal_type' => ['required', 'string', 'in:quality_feedback,recommendation_accepted,recommendation_rejected,stage_regenerated'],
            'rating' => ['nullable', 'numeric'],
            'reason' => ['nullable', 'string', 'max:255'],
            'stage_type' => ['nullable', 'string', 'max:80'],
        ]);

        $meta = [
            'stage_type' => $validated['stage_type'] ?? null,
            'reason' => $validated['reason'] ?? null,
            'project_workflow_mode' => is_object($project->workflow_mode) ? $project->workflow_mode->value : $project->workflow_mode,
        ];

        $value = isset($validated['rating']) ? (float) $validated['rating'] : 1.0;

        $signal = $this->learningSystem->recordSignal(
            $request->user(),
            $validated['category'],
            $validated['signal_type'],
            $meta,
            $value
        );

        return response()->json([
            'success' => true,
            'recorded' => $signal !== null,
            'message' => $signal !== null
                ? 'Thank you! Your feedback helps FORGE improve without storing private data.'
                : 'Feedback processed (AI improvement telemetry currently disabled in your privacy settings).',
        ]);
    }
}
