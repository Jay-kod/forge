<?php

declare(strict_types=1);

namespace App\Modules\Projects\Services;

use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Models\ProjectVersion;

class ProjectVersionService
{
    /**
     * Create an immutable snapshot of the project's current state.
     */
    public function createSnapshot(Project $project, string $createdBy = 'system', ?string $note = null): ProjectVersion
    {
        $project->loadMissing([
            'context',
            'workflow.stages.decisions',
            'documents',
            'opportunities.recommendations',
            'competitors',
            'repositoryAudit',
            'websiteAnalysis',
        ]);

        $nextVersion = ((int) $project->versions()->max('version')) + 1;

        $snapshot = [
            'project' => [
                'id' => $project->id,
                'title' => $project->title,
                'description' => $project->description,
                'classification' => $project->classification->value,
                'status' => $project->status->value,
                'current_stage' => $project->current_stage,
            ],
            'context' => $project->context?->toArray(),
            'stages' => $project->workflow?->stages->map(fn ($s) => [
                'id' => $s->id,
                'stage_type' => $s->stage_type->value,
                'status' => $s->status,
                'order' => $s->order,
                'content' => $s->content,
                'approved_at' => $s->approved_at?->toIso8601String(),
            ])->toArray() ?? [],
            'documents' => $project->documents->map(fn ($d) => [
                'id' => $d->id,
                'type' => $d->type,
                'title' => $d->title,
                'content' => $d->content,
                'version' => $d->version,
                'status' => $d->status,
            ])->toArray(),
            'opportunities' => $project->opportunities->map(fn ($o) => [
                'id' => $o->id,
                'title' => $o->title,
                'category' => $o->category,
                'impact' => $o->impact,
                'difficulty' => $o->difficulty,
                'quadrant' => $o->quadrant,
                'priority_score' => $o->priority_score,
            ])->toArray(),
            'competitors' => $project->competitors->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'url' => $c->url,
                'pricing_model' => $c->pricing_model,
                'strengths' => $c->strengths,
                'weaknesses' => $c->weaknesses,
            ])->toArray(),
            'repository_audit' => $project->repositoryAudit ? [
                'repo_full_name' => $project->repositoryAudit->repo_full_name,
                'primary_language' => $project->repositoryAudit->primary_language,
                'detected_framework' => $project->repositoryAudit->detected_framework,
                'code_health_score' => $project->repositoryAudit->code_health_score,
                'technical_debt_score' => $project->repositoryAudit->technical_debt_score,
                'security_score' => $project->repositoryAudit->security_score,
            ] : null,
            'website_analysis' => $project->websiteAnalysis ? [
                'url' => $project->websiteAnalysis->url,
                'performance_score' => $project->websiteAnalysis->performance_score,
                'seo_score' => $project->websiteAnalysis->seo_score,
            ] : null,
            'timestamp' => now()->toIso8601String(),
        ];

        return ProjectVersion::create([
            'project_id' => $project->id,
            'version' => $nextVersion,
            'snapshot' => $snapshot,
            'created_by' => $createdBy,
            'note' => $note ?? "Milestone Snapshot v{$nextVersion}",
            'created_at' => now(),
        ]);
    }

    /**
     * Compute visual and structural diff between two project version snapshots.
     */
    public function compare(ProjectVersion $v1, ProjectVersion $v2): array
    {
        $s1 = $v1->snapshot ?? [];
        $s2 = $v2->snapshot ?? [];

        // Compare documents
        $docs1 = collect($s1['documents'] ?? [])->keyBy('type');
        $docs2 = collect($s2['documents'] ?? [])->keyBy('type');

        $documentDiffs = [];
        $allDocTypes = $docs1->keys()->merge($docs2->keys())->unique();

        foreach ($allDocTypes as $type) {
            $d1 = $docs1->get($type);
            $d2 = $docs2->get($type);

            if (!$d1 && $d2) {
                $documentDiffs[] = [
                    'type' => $type,
                    'status' => 'added',
                    'title' => $d2['title'],
                    'old_length' => 0,
                    'new_length' => strlen($d2['content'] ?? ''),
                ];
            } elseif ($d1 && !$d2) {
                $documentDiffs[] = [
                    'type' => $type,
                    'status' => 'removed',
                    'title' => $d1['title'],
                    'old_length' => strlen($d1['content'] ?? ''),
                    'new_length' => 0,
                ];
            } else {
                $c1 = $d1['content'] ?? '';
                $c2 = $d2['content'] ?? '';
                $isChanged = ($c1 !== $c2);

                $documentDiffs[] = [
                    'type' => $type,
                    'status' => $isChanged ? 'modified' : 'unchanged',
                    'title' => $d2['title'] ?? $d1['title'],
                    'old_length' => strlen($c1),
                    'new_length' => strlen($c2),
                    'char_difference' => strlen($c2) - strlen($c1),
                ];
            }
        }

        // Compare Opportunities Count
        $oppsCount1 = count($s1['opportunities'] ?? []);
        $oppsCount2 = count($s2['opportunities'] ?? []);

        // Compare Code health score if exists
        $health1 = $s1['repository_audit']['code_health_score'] ?? null;
        $health2 = $s2['repository_audit']['code_health_score'] ?? null;

        return [
            'version_old' => $v1->version,
            'version_new' => $v2->version,
            'created_at_old' => $v1->created_at?->toIso8601String(),
            'created_at_new' => $v2->created_at?->toIso8601String(),
            'documents' => $documentDiffs,
            'opportunities_change' => $oppsCount2 - $oppsCount1,
            'code_health_delta' => ($health1 !== null && $health2 !== null) ? ($health2 - $health1) : null,
        ];
    }

    /**
     * Get a chronological timeline of all approved decisions and version milestones.
     */
    public function getDecisionTimeline(Project $project): array
    {
        $timeline = [];

        // 1. Gather all approved decisions from stages
        $stages = $project->workflow?->stages()->with('decisions')->get() ?? collect();

        foreach ($stages as $stage) {
            if ($stage->approved_at) {
                $timeline[] = [
                    'type' => 'stage_approval',
                    'title' => "Approved {$stage->stage_type->label()}",
                    'description' => $stage->content['summary'] ?? "Completed intelligence execution for {$stage->stage_type->value}.",
                    'stage_type' => $stage->stage_type->value,
                    'timestamp' => $stage->approved_at->toIso8601String(),
                ];
            }

            foreach ($stage->decisions as $decision) {
                $timeline[] = [
                    'type' => 'strategic_decision',
                    'title' => $decision->decision,
                    'description' => $decision->reasoning,
                    'confidence_score' => $decision->confidence_score,
                    'status' => $decision->status,
                    'timestamp' => $decision->created_at->toIso8601String(),
                ];
            }
        }

        // 2. Gather all project version snapshots
        $versions = $project->versions()->orderBy('created_at')->get();
        foreach ($versions as $version) {
            $timeline[] = [
                'type' => 'version_snapshot',
                'title' => "Project Snapshot v{$version->version}",
                'description' => $version->note ?? "Captured state snapshot v{$version->version}",
                'version' => $version->version,
                'created_by' => $version->created_by,
                'timestamp' => $version->created_at->toIso8601String(),
            ];
        }

        // Sort chronologically ascending
        usort($timeline, fn ($a, $b) => strcmp($a['timestamp'], $b['timestamp']));

        return $timeline;
    }
}
