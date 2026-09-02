<?php

declare(strict_types=1);

namespace App\Modules\ContinuousIntelligence\Services;

use App\Modules\Notifications\Services\AlertService;
use App\Modules\Product\Models\ProductDocument;
use App\Modules\Projects\Models\Project;

class IntelligenceDigestService
{
    public function __construct(
        protected AlertService $alertService
    ) {}

    /**
     * Synthesize and store a periodic Intelligence Digest document for a project.
     */
    public function generateForProject(Project $project): ProductDocument
    {
        $project->loadMissing([
            'opportunities.recommendations',
            'competitors',
            'repositoryAudit',
            'workflow.stages.decisions',
        ]);

        $opportunities = $project->opportunities;
        $quickWins = $opportunities->where('quadrant', 'quick_wins');
        $competitors = $project->competitors;
        $audit = $project->repositoryAudit;

        // Recent decisions made
        $recentDecisions = 0;
        if ($project->workflow) {
            foreach ($project->workflow->stages as $stg) {
                $recentDecisions += $stg->decisions()
                    ->where('created_at', '>=', now()->subDays(7))
                    ->count();
            }
        }

        // Build Markdown Content
        $dateStr = now()->format('F j, Y');
        $classificationLabel = $project->classification?->label() ?? 'General';
        $md = "# Strategic Intelligence Digest — {$dateStr}\n\n";
        $md .= "**Project:** {$project->title}\n";
        $md .= "**Classification:** {$classificationLabel}\n";
        $md .= "**Status:** Active Living Workspace\n\n";

        $md .= "---\n\n";
        $md .= "## 1. Executive Summary & Velocity\n\n";
        $md .= "- **Decisions Made (Last 7 Days):** {$recentDecisions}\n";
        $md .= "- **Total Active Opportunities:** {$opportunities->count()}\n";
        $md .= "- **Unblocked Quick Wins:** {$quickWins->count()}\n";
        $md .= "- **Monitored Competitors:** {$competitors->count()}\n\n";

        $md .= "## 2. Top High-Impact Quick Wins\n\n";
        if ($quickWins->isNotEmpty()) {
            foreach ($quickWins->take(3) as $qw) {
                $md .= "### 🚀 {$qw->title}\n";
                $md .= "{$qw->description}\n\n";
                $rec = $qw->recommendations->first();
                if ($rec) {
                    $md .= "- **Recommended Next Action:** {$rec->suggested_action}\n";
                    $md .= "- **Why It Matters:** {$rec->why_it_matters}\n\n";
                }
            }
        } else {
            $md .= "_No immediate quick wins categorized. Focus on advancing active roadmap milestones._\n\n";
        }

        $md .= "## 3. Competitive Landscape & Market Signals\n\n";
        if ($competitors->isNotEmpty()) {
            foreach ($competitors->take(4) as $comp) {
                $pricing = is_array($comp->pricing) ? json_encode($comp->pricing) : ($comp->pricing ?? 'Unknown');
                $md .= "- **{$comp->name}:** Pricing model: `{$pricing}`. Category: `{$comp->category}`.\n";
            }
            $md .= "\n";
        } else {
            $md .= "_No active competitor signals tracked. Discovery stages pending execution._\n\n";
        }

        if ($audit) {
            $md .= "## 4. Codebase Health & Technical Integrity\n\n";
            $md .= "- **Audited Repository:** `{$audit->repo_full_name}`\n";
            $md .= "- **Primary Stack:** {$audit->primary_language} ({$audit->detected_framework})\n";
            $md .= "- **Code Health Score:** {$audit->code_health_score} / 100\n";
            $md .= "- **Technical Debt Score:** {$audit->technical_debt_score} / 100\n";
            $md .= "- **Security Score:** {$audit->security_score} / 100\n\n";
        }

        $md .= "## 5. Strategic Directives for Coming Week\n\n";
        $md .= "1. Focus execution on the highest-priority Quick Wins identified above.\n";
        $md .= "2. Re-evaluate competitor pricing signals to defend your core value proposition.\n";
        $md .= "3. Keep technical debt below 25 by resolving flagged dependency warnings.\n\n";

        // Determine version number
        $latestDigest = ProductDocument::where('project_id', $project->id)
            ->where('type', 'intelligence_digest')
            ->orderByDesc('version')
            ->first();

        $version = $latestDigest ? $latestDigest->version + 1 : 1;

        // Persist Document
        $doc = ProductDocument::create([
            'project_id' => $project->id,
            'type' => 'intelligence_digest',
            'title' => "Weekly Intelligence Digest v{$version} ({$dateStr})",
            'content' => $md,
            'version' => $version,
            'status' => 'approved',
        ]);

        // Dispatch In-App Alert
        $this->alertService->dispatch(
            project: $project,
            type: 'digest_published',
            severity: 'info',
            title: "Weekly Strategic Intelligence Digest v{$version} Ready",
            message: "New weekly intelligence synthesis compiled. {$quickWins->count()} quick wins and {$competitors->count()} competitor signals updated.",
            data: ['document_id' => $doc->id, 'version' => $version]
        );

        return $doc;
    }
}
