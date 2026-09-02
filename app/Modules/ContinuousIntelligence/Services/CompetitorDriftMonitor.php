<?php

declare(strict_types=1);

namespace App\Modules\ContinuousIntelligence\Services;

use App\Modules\Discovery\Models\Competitor;
use App\Modules\Notifications\Services\AlertService;
use App\Modules\Projects\Models\Project;

class CompetitorDriftMonitor
{
    public function __construct(
        protected AlertService $alertService
    ) {}

    /**
     * Inspect a project's competitors and codebase for strategic drift.
     *
     * @return array{analyzed_competitors: int, drifts_detected: int, alerts_dispatched: int}
     */
    public function monitorProject(Project $project): array
    {
        $project->loadMissing(['competitors', 'repositoryAudit']);

        $analyzed = 0;
        $drifts = 0;
        $alerts = 0;

        // 1. Competitor Analysis & Drift Checking
        foreach ($project->competitors as $competitor) {
            $analyzed++;
            $drift = $this->checkCompetitorDrift($project, $competitor);

            if ($drift) {
                $drifts++;
                $this->alertService->dispatch(
                    project: $project,
                    type: 'competitor_drift',
                    severity: $drift['severity'],
                    title: $drift['title'],
                    message: $drift['message'],
                    data: $drift['data']
                );
                $alerts++;
            }
        }

        // 2. Codebase Stale Inspection
        if ($project->repositoryAudit) {
            $audit = $project->repositoryAudit;
            if ($audit->created_at->lt(now()->subDays(14))) {
                $drifts++;
                $this->alertService->dispatch(
                    project: $project,
                    type: 'codebase_drift',
                    severity: 'info',
                    title: 'Repository Audit Outdated',
                    message: "The repository audit for {$audit->repo_full_name} is over 14 days old. Re-running the code audit is recommended to identify newly introduced dependency vulnerabilities.",
                    data: ['repo' => $audit->repo_full_name, 'last_audit' => $audit->created_at->toIso8601String()]
                );
                $alerts++;
            }
        }

        return [
            'analyzed_competitors' => $analyzed,
            'drifts_detected' => $drifts,
            'alerts_dispatched' => $alerts,
        ];
    }

    /**
     * Check if a competitor exhibits pricing, positioning, or feature drift.
     *
     * @return array{title: string, message: string, severity: string, data: array<string, mixed>}|null
     */
    public function checkCompetitorDrift(Project $project, Competitor $competitor): ?array
    {
        $pricingRaw = $competitor->pricing;
        $pricing = strtolower(is_array($pricingRaw) ? json_encode($pricingRaw) : (string) ($pricingRaw ?? ''));
        $strengths = is_array($competitor->strengths) ? implode(' ', $competitor->strengths) : (string) ($competitor->strengths ?? '');

        // Heuristic 1: Freemium / Pricing shift
        if (str_contains($pricing, 'freemium') || str_contains($pricing, 'free tier')) {
            return [
                'title' => "Aggressive Pricing Shift by {$competitor->name}",
                'message' => "{$competitor->name} has adopted a freemium or zero-barrier pricing strategy. Review your value proposition and pricing tiering.",
                'severity' => 'warning',
                'data' => [
                    'competitor_id' => $competitor->id,
                    'competitor_name' => $competitor->name,
                    'pricing' => $competitor->pricing,
                ],
            ];
        }

        // Heuristic 2: AI / Automation pivot in strengths
        if (stripos($strengths, 'ai') !== false || stripos($strengths, 'automation') !== false) {
            return [
                'title' => "{$competitor->name} Pushing AI & Automation Capabilities",
                'message' => "{$competitor->name} is heavily advertising autonomous capabilities. Evaluate whether to accelerate your Quick Win opportunities.",
                'severity' => 'info',
                'data' => [
                    'competitor_id' => $competitor->id,
                    'competitor_name' => $competitor->name,
                ],
            ];
        }

        return null;
    }
}
