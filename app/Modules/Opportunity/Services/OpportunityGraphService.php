<?php

declare(strict_types=1);

namespace App\Modules\Opportunity\Services;

use App\Modules\Projects\Models\Project;

class OpportunityGraphService
{
    /**
     * Build nodes and edges for the Interactive Opportunity Graph.
     *
     * @return array{nodes: array<int, array<string, mixed>>, edges: array<int, array{source: string, target: string, type: string, label: ?string}>, metrics: array<string, int>}
     */
    public function build(Project $project): array
    {
        $project->loadMissing([
            'opportunities.recommendations',
            'competitors',
            'evidence.sources',
            'repositoryAudit',
        ]);

        $nodes = [];
        $edges = [];

        // 1. Root Node: Project (Center at 400, 300)
        $rootId = "project-{$project->id}";
        $nodes[] = [
            'id' => $rootId,
            'type' => 'project',
            'label' => $project->title,
            'category' => 'project',
            'details' => [
                'description' => $project->description,
                'classification' => $project->classification?->label() ?? 'General',
                'status' => $project->status?->value ?? 'ACTIVE',
            ],
            'x' => 450,
            'y' => 300,
            'radius' => 32,
            'color' => '#6366f1', // Indigo
        ];

        // 2. Repository Node (if connected)
        if ($project->repositoryAudit) {
            $audit = $project->repositoryAudit;
            $repoId = "repo-{$audit->id}";
            $nodes[] = [
                'id' => $repoId,
                'type' => 'repository',
                'label' => $audit->repo_full_name,
                'category' => 'codebase',
                'details' => [
                    'language' => $audit->primary_language,
                    'framework' => $audit->detected_framework,
                    'health_score' => $audit->code_health_score,
                    'debt_score' => $audit->technical_debt_score,
                ],
                'x' => 450,
                'y' => 120,
                'radius' => 24,
                'color' => '#0ea5e9', // Sky blue
            ];

            $edges[] = [
                'source' => $rootId,
                'target' => $repoId,
                'type' => 'connected_codebase',
                'label' => 'Audited Repo',
            ];
        }

        // 3. Competitor Nodes (arranged in arc on top-left)
        $competitors = $project->competitors;
        $compCount = $competitors->count();
        $compIndex = 0;

        foreach ($competitors as $comp) {
            $compId = "comp-{$comp->id}";
            $angle = 135 + ($compIndex * (90 / max(1, $compCount)));
            $rad = deg2rad($angle);
            $dist = 220;

            $nodes[] = [
                'id' => $compId,
                'type' => 'competitor',
                'label' => $comp->name,
                'category' => 'competitor',
                'details' => [
                    'url' => $comp->url,
                    'pricing_model' => $comp->pricing_model,
                    'strengths' => $comp->strengths,
                    'weaknesses' => $comp->weaknesses,
                ],
                'x' => round(450 + cos($rad) * $dist),
                'y' => round(300 + sin($rad) * $dist),
                'radius' => 20,
                'color' => '#f43f5e', // Rose
            ];

            $edges[] = [
                'source' => $rootId,
                'target' => $compId,
                'type' => 'competes_with',
                'label' => 'Competitor',
            ];

            $compIndex++;
        }

        // 4. Opportunity Nodes (arranged radially around right & bottom)
        $opportunities = $project->opportunities;
        $oppCount = $opportunities->count();
        $oppIndex = 0;

        foreach ($opportunities as $opp) {
            $oppId = "opp-{$opp->id}";
            $angle = -45 + ($oppIndex * (220 / max(1, $oppCount)));
            $rad = deg2rad($angle);
            $dist = 240;

            $color = match ($opp->quadrant) {
                'quick_wins' => '#10b981',      // Emerald
                'major_projects' => '#3b82f6',  // Blue
                'fill_ins' => '#f59e0b',        // Amber
                'thankless_tasks' => '#64748b', // Slate
                default => '#8b5cf6',           // Purple
            };

            $oppX = round(450 + cos($rad) * $dist);
            $oppY = round(300 + sin($rad) * $dist);

            $nodes[] = [
                'id' => $oppId,
                'type' => 'opportunity',
                'label' => $opp->title,
                'category' => $opp->quadrant,
                'details' => [
                    'description' => $opp->description,
                    'quadrant' => $opp->quadrant_label,
                    'impact' => $opp->impact,
                    'difficulty' => $opp->difficulty,
                    'priority_score' => $opp->priority_score,
                ],
                'x' => $oppX,
                'y' => $oppY,
                'radius' => 22,
                'color' => $color,
            ];

            $edges[] = [
                'source' => $rootId,
                'target' => $oppId,
                'type' => 'delivers_value',
                'label' => $opp->quadrant_label,
            ];

            // 5. Connect Recommendations as satellite nodes
            $recs = $opp->recommendations;
            $recIndex = 0;
            foreach ($recs as $rec) {
                $recId = "rec-{$rec->id}";
                $recAngle = $angle + ($recIndex === 0 ? 25 : -25);
                $recRad = deg2rad($recAngle);

                $nodes[] = [
                    'id' => $recId,
                    'type' => 'recommendation',
                    'label' => $rec->title,
                    'category' => 'recommendation',
                    'details' => [
                        'description' => $rec->description,
                        'suggested_action' => $rec->suggested_action,
                        'why_it_matters' => $rec->why_it_matters,
                    ],
                    'x' => round($oppX + cos($recRad) * 85),
                    'y' => round($oppY + sin($recRad) * 85),
                    'radius' => 14,
                    'color' => '#8b5cf6', // Violet
                ];

                $edges[] = [
                    'source' => $oppId,
                    'target' => $recId,
                    'type' => 'implemented_by',
                    'label' => 'Action',
                ];

                $recIndex++;
            }

            $oppIndex++;
        }

        return [
            'nodes' => $nodes,
            'edges' => $edges,
            'metrics' => [
                'total_nodes' => count($nodes),
                'total_edges' => count($edges),
                'opportunities' => $opportunities->count(),
                'quick_wins' => $opportunities->where('quadrant', 'quick_wins')->count(),
                'major_projects' => $opportunities->where('quadrant', 'major_projects')->count(),
                'competitors' => $competitors->count(),
            ],
        ];
    }
}
