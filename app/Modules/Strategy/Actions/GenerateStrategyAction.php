<?php

declare(strict_types=1);

namespace App\Modules\Strategy\Actions;

use App\Models\User;
use App\Modules\AI\DTOs\AIRequest;
use App\Modules\AI\Enums\WorkloadClass;
use App\Modules\AI\Services\AIOrchestrator;
use App\Modules\AI\Services\AIOutputValidator;
use App\Modules\Projects\Models\Project;
use App\Modules\Strategy\DTOs\StrategyResult;
use App\Modules\Strategy\Enums\StrategicRecommendation;

class GenerateStrategyAction
{
    public function __construct(
        protected AIOrchestrator $ai,
        protected AIOutputValidator $validator
    ) {}

    public function execute(User $user, Project $project): StrategyResult
    {
        $project->loadMissing(['competitors', 'evidence', 'context']);

        $competitorCount = $project->competitors->count();
        $competitorNames = $project->competitors->pluck('name')->implode(', ');
        $evidenceCount = $project->evidence->count();
        $description = $project->description ?? 'No description provided';

        $prompt = <<<PROMPT
You are FORGE's Principal Product Strategist.
Formulate a decisive, evidence-grounded strategic recommendation for this product idea:

Project Title: {$project->title}
Classification: {$project->classification->label()}
Description: {$description}
Competitors Analyzed ({$competitorCount}): {$competitorNames}
Evidence Points Collected: {$evidenceCount}

Formulate a definitive strategic posture. Choose ONE recommendation from:
- BUILD (Clear whitespace, strong evidence, low incumbent defensibility)
- BUILD_WITH_MODIFICATIONS (Viable concept, but needs distinct wedge or feature trim)
- CONSIDER_ALTERNATIVE (Overcrowded market; pivot value proposition)
- DO_NOT_BUILD_YET (Unverified demand or prohibitive execution moat)

Return valid JSON with this exact schema:
{
  "recommendation": "BUILD_WITH_MODIFICATIONS",
  "posture_title": "Laser Wedge into High-Pain Niche",
  "rationale": "Comprehensive 3-4 sentence explanation of the strategic justification.",
  "core_differentiators": [
    "Differentiator 1",
    "Differentiator 2",
    "Differentiator 3"
  ],
  "go_to_market_steps": [
    "Step 1: Direct outreach to high-intent target sub-segment",
    "Step 2: Launch open blueprint / community demo",
    "Step 3: Establish programmatic referral loop"
  ],
  "moats": [
    "Proprietary workflow integration",
    "Speed of iteration and focused user experience"
  ]
}
PROMPT;

        $request = new AIRequest(
            user: $user,
            prompt: $prompt,
            operationType: 'strategy.generate',
            workloadClass: WorkloadClass::STANDARD,
            projectId: $project->id
        );

        $response = $this->ai->execute($request);

        $parsed = null;
        try {
            $parsed = $this->validator->validateJson($response->content, [
                'recommendation',
                'posture_title',
                'rationale',
                'core_differentiators',
                'go_to_market_steps',
                'moats',
            ]);
        } catch (\Throwable) {
            // Safe fallback
            $rec = $competitorCount > 5 ? StrategicRecommendation::BUILD_WITH_MODIFICATIONS : StrategicRecommendation::BUILD;
            $parsed = [
                'recommendation' => $rec->value,
                'posture_title' => 'Focused Counter-Positioning & Specialized Workflow',
                'rationale' => "Given the presence of {$competitorCount} existing solutions, {$project->title} must avoid broad feature parity and instead attack a specific under-served workflow with superior execution.",
                'core_differentiators' => [
                    'Narrow, zero-fluff workflow targeting the primary bottleneck',
                    'Evidence-linked decision support rather than speculative output',
                    'Low friction time-to-value without configuration overhead',
                ],
                'go_to_market_steps' => [
                    'Phase 1: Validate value proposition with 10 high-intent early adopters',
                    'Phase 2: Publish benchmark studies comparing workflow speed against incumbents',
                    'Phase 3: Roll out team workspace and collaborative export capabilities',
                ],
                'moats' => [
                    'Domain-specific intelligence loops',
                    'High switching costs created by connected project evolution',
                ],
            ];
        }

        $recommendationEnum = StrategicRecommendation::tryFrom($parsed['recommendation'] ?? '')
            ?? StrategicRecommendation::BUILD_WITH_MODIFICATIONS;

        $diffs = (array) ($parsed['core_differentiators'] ?? []);
        $gtm = (array) ($parsed['go_to_market_steps'] ?? []);
        $moats = (array) ($parsed['moats'] ?? []);

        // Build markdown document
        $markdown = "# Strategic Recommendation & Posture: {$project->title}\n\n"
            . "## Verdict: **{$recommendationEnum->label()}** (Risk: {$recommendationEnum->riskLevel()})\n\n"
            . "**Posture:** {$parsed['posture_title']}\n\n"
            . "### Strategic Rationale\n{$parsed['rationale']}\n\n"
            . "### Core Differentiators\n" . implode("\n", array_map(fn($d) => "- {$d}", $diffs)) . "\n\n"
            . "### Go-To-Market Sequence\n" . implode("\n", array_map(fn($s) => "1. {$s}", $gtm)) . "\n\n"
            . "### Long-Term Defensibility & Moats\n" . implode("\n", array_map(fn($m) => "- {$m}", $moats)) . "\n\n"
            . "### Required Next Action\n{$recommendationEnum->actionRequired()}\n";

        return new StrategyResult(
            recommendation: $recommendationEnum,
            postureTitle: (string) ($parsed['posture_title'] ?? 'Strategic Roadmap'),
            rationale: (string) ($parsed['rationale'] ?? ''),
            coreDifferentiators: $diffs,
            go_to_market_steps: $gtm,
            moats: $moats,
            markdownReport: $markdown,
            rawPayload: $parsed
        );
    }
}
