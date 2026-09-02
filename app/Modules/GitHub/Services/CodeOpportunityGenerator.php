<?php

declare(strict_types=1);

namespace App\Modules\GitHub\Services;

use App\Modules\GitHub\Models\RepositoryAudit;
use App\Modules\Opportunity\Models\Opportunity;
use App\Modules\Opportunity\Models\Recommendation;
use App\Modules\Projects\Models\Project;

class CodeOpportunityGenerator
{
    /**
     * Generate code-connected Opportunity and Recommendation records from repository audit findings.
     *
     * @return array<int, Opportunity>
     */
    public function generate(Project $project, RepositoryAudit $audit): array
    {
        $findings = $audit->raw_metrics['findings'] ?? [];
        $createdOpportunities = [];

        foreach ($findings as $finding) {
            $impact = match ($finding['severity'] ?? 'medium') {
                'critical' => 'critical',
                'high' => 'high',
                'low' => 'low',
                default => 'medium',
            };

            // Heuristic difficulty based on category
            $difficulty = match ($finding['category'] ?? 'architecture') {
                'runtime' => 'low', // bumping version is fast quick win
                'dependencies' => 'low', // package migration is usually straightforward
                'architecture' => 'high', // structural refactor takes effort
                'testing' => 'medium', // writing suites is medium effort
                default => 'medium',
            };

            $filePathNote = !empty($finding['file_path']) ? " (Target File: {$finding['file_path']})" : '';

            $opportunity = Opportunity::updateOrCreate(
                [
                    'project_id' => $project->id,
                    'title' => $finding['title'],
                ],
                [
                    'description' => $finding['description'] . $filePathNote,
                    'category' => $finding['category'] ?? 'technical_debt',
                    'impact' => $impact,
                    'difficulty' => $difficulty,
                ]
            );

            // Create actionable recommendation
            Recommendation::updateOrCreate(
                [
                    'project_id' => $project->id,
                    'opportunity_id' => $opportunity->id,
                    'title' => 'Remediation Step: ' . $finding['title'],
                ],
                [
                    'description' => $finding['recommended_action'] ?? 'Execute refactoring step to resolve technical debt.',
                    'why_it_matters' => 'Eliminates codebase regression risks and security exposures.',
                    'why_now' => 'Prevents compounded technical debt and development slowdowns.',
                    'potential_impact' => $impact,
                    'difficulty' => $difficulty,
                    'suggested_action' => $finding['recommended_action'] ?? 'Refactor and modernize code.',
                ]
            );

            $createdOpportunities[] = $opportunity;
        }

        return $createdOpportunities;
    }
}
