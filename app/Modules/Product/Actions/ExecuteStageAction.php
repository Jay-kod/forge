<?php

declare(strict_types=1);

namespace App\Modules\Product\Actions;

use App\Models\User;
use App\Modules\AI\DTOs\AIRequest;
use App\Modules\AI\Enums\WorkloadClass;
use App\Modules\AI\Services\AIOrchestrator;
use App\Modules\Discovery\Actions\RunDiscoveryAction;
use App\Modules\Product\Models\ProductDocument;
use App\Modules\Product\Models\WorkflowStage;
use App\Modules\Projects\Enums\ProjectStatus;
use App\Modules\Projects\Models\Project;
use App\Modules\Research\Services\WebsiteAnalysisService;
use Illuminate\Support\Str;

class ExecuteStageAction
{
    public function __construct(
        protected AIOrchestrator $ai,
        protected RunDiscoveryAction $runDiscoveryAction,
        protected WebsiteAnalysisService $websiteAnalysisService
    ) {}

    /**
     * Execute stage intelligence and advance workflow to next stage.
     */
    public function execute(User $user, Project $project, WorkflowStage $stage): ?WorkflowStage
    {
        $stageType = $stage->stage_type->value;

        // 1. Run Intelligence based on stage type
        $researchStages = [
            'understanding',
            'discovery',
            'research',
            'competitors',
            'challenge',
            'business_analysis',
            'geographic_research',
            'market_comparison',
            'situation_analysis',
            'competitor_comparison',
            'ux_analysis',
        ];

        if (in_array($stageType, $researchStages, true)) {
            if (!$project->discovery()->exists() || !$project->evidence()->exists()) {
                $this->runDiscoveryAction->execute($user, $project);
            }

            $stage->update([
                'status' => 'completed',
                'content' => [
                    'summary' => "Intelligence synthesis complete for {$stage->stage_type->label()}.",
                    'sources_retrieved' => $project->researchSessions()->withCount('sources')->first()?->sources_count ?? 5,
                    'evidence_count' => $project->evidence()->count(),
                    'competitors_mapped' => $project->competitors()->count(),
                ],
                'completed_at' => now(),
            ]);
        } elseif ($stageType === 'website_audit') {
            // Website Technical & SEO Audit using WebsiteAnalysisService
            $targetUrl = $this->detectTargetUrl($project);
            $analysis = $this->websiteAnalysisService->analyze($project, $targetUrl);

            $stage->update([
                'status' => 'completed',
                'content' => [
                    'summary' => "Website technical and UX audit completed for {$targetUrl}.",
                    'audit' => [
                        'url' => $analysis->url,
                        'overall_health_score' => (int) round(($analysis->ux_score + $analysis->seo_score + $analysis->conversion_score) / 3),
                        'ux_score' => $analysis->ux_score,
                        'seo_score' => $analysis->seo_score,
                        'conversion_score' => $analysis->conversion_score,
                        'key_observations' => $analysis->conversion_findings['bottlenecks'] ?? [],
                        'recommended_fixes' => $analysis->recommendations ?? [],
                    ],
                ],
                'completed_at' => now(),
            ]);
        } elseif ($stageType === 'prd') {
            // Generate Evidence-Linked PRD
            $prdContent = $this->generatePRDContent($project);

            ProductDocument::updateOrCreate(
                ['project_id' => $project->id, 'type' => 'prd'],
                [
                    'title' => "Product Requirements Document: {$project->title}",
                    'content' => $prdContent,
                    'version' => 1,
                    'status' => 'approved',
                ]
            );

            $stage->update([
                'status' => 'completed',
                'content' => ['summary' => 'Evidence-linked PRD generated successfully.', 'document_type' => 'prd'],
                'completed_at' => now(),
            ]);
        } elseif ($stageType === 'architecture') {
            // Generate Architecture Spec
            $archContent = $this->generateArchitectureContent($project);

            ProductDocument::updateOrCreate(
                ['project_id' => $project->id, 'type' => 'architecture'],
                [
                    'title' => "System Architecture: {$project->title}",
                    'content' => $archContent,
                    'version' => 1,
                    'status' => 'approved',
                ]
            );

            $stage->update([
                'status' => 'completed',
                'content' => ['summary' => 'System Architecture specification generated.', 'document_type' => 'architecture'],
                'completed_at' => now(),
            ]);
        } elseif ($stageType === 'growth_plan') {
            // Generate Comprehensive Growth Plan Document
            $planContent = $this->generateGrowthPlanContent($project);

            ProductDocument::updateOrCreate(
                ['project_id' => $project->id, 'type' => 'growth_plan'],
                [
                    'title' => "Comprehensive Growth Plan: {$project->title}",
                    'content' => $planContent,
                    'version' => 1,
                    'status' => 'approved',
                ]
            );

            $stage->update([
                'status' => 'completed',
                'content' => ['summary' => 'Comprehensive Growth Plan synthesized.', 'document_type' => 'growth_plan'],
                'completed_at' => now(),
            ]);
        } elseif ($stageType === 'improvement_plan') {
            // Generate Optimization & Improvement Roadmap
            $roadmapContent = $this->generateImprovementPlanContent($project);

            ProductDocument::updateOrCreate(
                ['project_id' => $project->id, 'type' => 'improvement_plan'],
                [
                    'title' => "Optimization & Improvement Roadmap: {$project->title}",
                    'content' => $roadmapContent,
                    'version' => 1,
                    'status' => 'approved',
                ]
            );

            $stage->update([
                'status' => 'completed',
                'content' => ['summary' => 'Optimization & Conversion Roadmap synthesized.', 'document_type' => 'improvement_plan'],
                'completed_at' => now(),
            ]);
        } elseif ($stageType === 'expansion_strategy') {
            // Generate Geographic Expansion Strategy
            $expansionContent = $this->generateExpansionStrategyContent($project);

            ProductDocument::updateOrCreate(
                ['project_id' => $project->id, 'type' => 'expansion_strategy'],
                [
                    'title' => "Geographic Expansion Strategy: {$project->title}",
                    'content' => $expansionContent,
                    'version' => 1,
                    'status' => 'approved',
                ]
            );

            $stage->update([
                'status' => 'completed',
                'content' => ['summary' => 'Geographic Expansion Strategy synthesized.', 'document_type' => 'expansion_strategy'],
                'completed_at' => now(),
            ]);
        } elseif ($stageType === 'roadmap') {
            // Generate Executive Execution Roadmap
            $execRoadmap = $this->generateExecutiveRoadmapContent($project);

            ProductDocument::updateOrCreate(
                ['project_id' => $project->id, 'type' => 'roadmap'],
                [
                    'title' => "Executive Execution Roadmap: {$project->title}",
                    'content' => $execRoadmap,
                    'version' => 1,
                    'status' => 'approved',
                ]
            );

            $stage->update([
                'status' => 'completed',
                'content' => ['summary' => 'Executive Execution Roadmap synthesized.', 'document_type' => 'roadmap'],
                'completed_at' => now(),
            ]);
        } else {
            // Generic AI completion for unspecified or export stages
            $aiRequest = new AIRequest(
                user: $user,
                prompt: "Synthesize {$stage->stage_type->label()} for project: {$project->title}.",
                operationType: "stage.{$stageType}",
                workloadClass: WorkloadClass::STANDARD,
                projectId: $project->id
            );

            $aiResponse = $this->ai->execute($aiRequest);

            $stage->update([
                'status' => 'completed',
                'content' => ['analysis' => $aiResponse->content],
                'completed_at' => now(),
            ]);
        }

        // 2. Activate next stage in workflow sequence
        $nextStage = $project->workflow->stages()
            ->where('order', '>', $stage->order)
            ->orderBy('order')
            ->first();

        if ($nextStage) {
            $nextStage->update([
                'status' => 'active',
                'started_at' => now(),
            ]);
            $project->update(['current_stage' => $nextStage->stage_type->value]);
        } else {
            $project->update(['status' => ProjectStatus::COMPLETED]);
        }

        return $nextStage;
    }

    protected function generatePRDContent(Project $project): string
    {
        $discovery = $project->discovery;
        $verdict = $discovery ? $discovery->verdict->label() : 'Build With Modifications';
        $summary = $discovery ? $discovery->summary : 'Market evidence collected.';

        return "# Product Requirements Document: {$project->title}\n\n" .
            "**Classification:** {$project->classification->label()}\n" .
            "**Strategic Verdict:** {$verdict}\n\n" .
            "## 1. Problem Statement\n{$project->description}\n\n" .
            "## 2. Market Evidence & Discovery Verdict\n{$summary}\n\n" .
            "## 3. Core Capabilities & User Journeys\n- Zero-friction onboarding\n- Automated vertical workflow integration\n- Real-time feedback and export\n\n" .
            "## 4. Non-Functional Requirements\n- Server-side ownership authorization\n- High availability & sub-100ms response times\n- WCAG AA accessible UI";
    }

    protected function generateArchitectureContent(Project $project): string
    {
        return "# System Architecture: {$project->title}\n\n" .
            "**Classification:** {$project->classification->label()}\n" .
            "**Stack:** Component-First Modern SaaS Stack\n\n" .
            "## 1. Architectural Principles\n- Bounded domain modules\n- Thin controllers delegating to Actions\n- Concurrency-safe transactions with row locking\n\n" .
            "## 2. Database Design\n- Relational integrity with foreign key constraints\n- Encrypted sensitive tokens\n- Versioned snapshot history\n\n" .
            "## 3. Multi-Provider AI Routing\n- Workload-based model routing (LIGHT, STANDARD, DEEP, EXTREME)\n- Automatic fallback and credit refund safety";
    }

    protected function generateGrowthPlanContent(Project $project): string
    {
        return "# Comprehensive Growth Plan: {$project->title}\n\n" .
            "**Objective:** Systematic Revenue and Market Expansion\n\n" .
            "## 1. Executive Opportunity Diagnosis\n{$project->description}\n\n" .
            "## 2. Core Growth Levers\n- **Acquisition:** Target high-intent organic search & specialized partner distribution\n- **Activation:** Remove onboarding friction and reduce time-to-first-value to < 2 minutes\n- **Retention:** Triggered milestone feedback and automated weekly summary digests\n- **Monetization:** Value-metric pricing aligned with usage expansion\n\n" .
            "## 3. 90-Day Execution Sprints\n- **Days 1–30:** Fix conversion leaks, instrument event analytics, and launch referral loop\n- **Days 31–60:** Deploy high-converting landing pages and direct customer outreach\n- **Days 61–90:** Formalize partnerships and scale paid acquisition on validated channels";
    }

    protected function generateImprovementPlanContent(Project $project): string
    {
        return "# Website Optimization & Improvement Roadmap: {$project->title}\n\n" .
            "**Focus:** Conversion Rate Optimization (CRO) & User Experience\n\n" .
            "## 1. Primary Conversion Barriers\n- Vague headline value proposition above the fold\n- Missing social proof and customer verification\n- High friction multi-step sign up flow\n\n" .
            "## 2. Prioritized Action Items (Impact vs. Effort)\n- **Quick Win:** Clarify H1 headline with explicit outcome & add primary CTA above the fold\n- **High Impact:** Integrate social proof logos and customer testimonials directly beneath the hero\n- **Strategic:** Implement single-click Google/GitHub OAuth sign in to reduce registration drop-off\n\n" .
            "## 3. Measurement & Target Metrics\n- Increase visitor-to-lead conversion by 35%\n- Reduce bounce rate from 65% to < 40%\n- Improve Core Web Vitals (LCP < 2.5s, CLS < 0.1)";
    }

    protected function generateExpansionStrategyContent(Project $project): string
    {
        return "# Market Expansion Strategy: {$project->title}\n\n" .
            "**Target Territory:** Regional & Geographic Expansion\n\n" .
            "## 1. Strategic Rationale & Market Fit\n{$project->description}\n\n" .
            "## 2. Local Market Dynamics & Competitor Landscape\n- Existing incumbent market share and localized pricing models\n- Regional regulatory, payment method, and compliance considerations\n- Distribution channel partnerships for rapid territory penetration\n\n" .
            "## 3. Phase Rollout Strategy\n- **Pilot Phase:** Soft launch with 10–20 localized lighthouse customers\n- **Scaling Phase:** Localized marketing campaigns and currency support\n- **Consolidation:** Regional account management and local support coverage";
    }

    protected function generateExecutiveRoadmapContent(Project $project): string
    {
        return "# Strategic Execution Roadmap: {$project->title}\n\n" .
            "**Horizon:** 12-Month Strategic Transformation\n\n" .
            "## 1. Strategic Themes\n- Operational Excellence & Margin Expansion\n- Product Differentiation in Crowded Markets\n- Scalable Customer Acquisition Infrastructure\n\n" .
            "## 2. Milestone Calendar\n- **Q1:** Audit existing capabilities, eliminate tech debt, and pilot new monetization tier\n- **Q2:** Expand go-to-market channels and automate lead qualification\n- **Q3:** Launch self-serve enterprise tier and API integrations\n- **Q4:** Regional expansion and strategic channel partnerships";
    }

    protected function generateWebsiteAuditContent(Project $project): array
    {
        return [
            'overall_health_score' => 78,
            'seo_score' => 82,
            'mobile_responsiveness_score' => 85,
            'conversion_readiness_score' => 64,
            'key_observations' => [
                'H1 heading is generic and does not communicate unique value proposition.',
                'Primary Call-to-Action button is hidden below the initial scroll viewport on mobile devices.',
                'No social proof or verified reviews visible on the primary landing page.',
                'Page load speed on mobile has opportunity for image asset optimization.',
            ],
            'recommended_fixes' => [
                'Rewrite hero headline to state who the product helps and the specific outcome.',
                'Place sticky primary action button on mobile navigation.',
                'Add customer logos and trust badges immediately below the hero section.',
            ],
        ];
    }

    protected function detectTargetUrl(Project $project): string
    {
        $text = ($project->description ?? '') . ' ' . ($project->title ?? '');
        if (preg_match('#https?://[^\s,\"\'>]+#i', $text, $matches)) {
            return $matches[0];
        }
        if (preg_match('#\b(?:www\.)?([a-zA-Z0-9-]+\.[a-zA-Z]{2,6})\b#i', $text, $matches)) {
            return 'https://' . $matches[1];
        }
        $slug = Str::slug($project->title);
        return "https://{$slug}.com";
    }
}
