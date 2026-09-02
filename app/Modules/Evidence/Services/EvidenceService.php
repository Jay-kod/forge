<?php

declare(strict_types=1);

namespace App\Modules\Evidence\Services;

use App\Modules\Evidence\Enums\ConfidenceLevel;
use App\Modules\Evidence\Models\Evidence;
use App\Modules\Projects\Models\Project;
use App\Modules\Research\Models\ResearchSource;
use Illuminate\Support\Facades\DB;

class EvidenceService
{
    /**
     * Synthesize and register evidence claims from research sources.
     *
     * @param ResearchSource[] $sources
     * @return Evidence[]
     */
    public function registerEvidenceFromSources(Project $project, array $sources): array
    {
        if (empty($sources)) {
            return [];
        }

        $createdEvidence = [];

        DB::transaction(function () use ($project, $sources, &$createdEvidence) {
            foreach ($sources as $source) {
                // Calculate confidence based on source reliability
                $confidence = $this->calculateConfidence($source);
                $claim = $source->content_summary ?: "Market finding derived from {$source->title}";

                $evidence = Evidence::create([
                    'project_id' => $project->id,
                    'claim' => $claim,
                    'confidence' => $confidence,
                    'confidence_score' => $source->reliability_score ?? 0.80,
                    'category' => 'market',
                ]);

                // Link evidence to source
                $evidence->sources()->attach($source->id, [
                    'relevance' => "Direct citation from {$source->title}",
                ]);

                $createdEvidence[] = $evidence;
            }
        });

        return $createdEvidence;
    }

    /**
     * Determine ConfidenceLevel from source properties.
     */
    public function calculateConfidence(ResearchSource $source): ConfidenceLevel
    {
        $score = $source->reliability_score ?? 0.75;

        if ($score >= 0.90) {
            return ConfidenceLevel::STRONGLY_SUPPORTED;
        }

        if ($score >= 0.75) {
            return ConfidenceLevel::PROBABLE;
        }

        if ($score >= 0.50) {
            return ConfidenceLevel::INFERRED;
        }

        return ConfidenceLevel::ASSUMPTION;
    }
}
