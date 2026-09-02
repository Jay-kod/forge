<?php

declare(strict_types=1);

namespace App\Modules\Opportunity\Services;

use App\Modules\Opportunity\Models\Opportunity;
use App\Modules\Projects\Models\Project;

class OpportunityRankingService
{
    /**
     * Rank and group project opportunities into the 2x2 Impact vs Effort matrix.
     */
    public function rank(Project $project): array
    {
        $opportunities = $project->opportunities()->get();

        $quickWins = [];
        $majorProjects = [];
        $fillIns = [];
        $thanklessTasks = [];

        foreach ($opportunities as $opp) {
            match ($opp->quadrant) {
                'quick_wins' => $quickWins[] = $opp,
                'major_projects' => $majorProjects[] = $opp,
                'fill_ins' => $fillIns[] = $opp,
                'thankless_tasks' => $thanklessTasks[] = $opp,
            };
        }

        // Sort each quadrant by priority score descending
        $sorter = fn ($a, $b) => $b->priority_score <=> $a->priority_score;
        usort($quickWins, $sorter);
        usort($majorProjects, $sorter);
        usort($fillIns, $sorter);
        usort($thanklessTasks, $sorter);

        return [
            'total_opportunities' => count($opportunities),
            'quick_wins' => $quickWins,
            'major_projects' => $majorProjects,
            'fill_ins' => $fillIns,
            'thankless_tasks' => $thanklessTasks,
            'top_recommendation' => $quickWins[0] ?? $majorProjects[0] ?? null,
        ];
    }
}
