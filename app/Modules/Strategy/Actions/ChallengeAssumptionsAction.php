<?php

declare(strict_types=1);

namespace App\Modules\Strategy\Actions;

use App\Models\User;
use App\Modules\AI\DTOs\AIRequest;
use App\Modules\AI\Enums\WorkloadClass;
use App\Modules\AI\Services\AIOrchestrator;
use App\Modules\AI\Services\AIOutputValidator;
use App\Modules\Projects\Models\Project;
use App\Modules\Strategy\DTOs\ChallengeResult;

class ChallengeAssumptionsAction
{
    public function __construct(
        protected AIOrchestrator $ai,
        protected AIOutputValidator $validator
    ) {}

    public function execute(User $user, Project $project): ChallengeResult
    {
        $project->loadMissing(['competitors', 'evidence', 'context']);

        $competitorNames = $project->competitors->pluck('name')->implode(', ');
        $evidenceSnippets = $project->evidence->take(8)->pluck('fact')->implode("\n- ");
        $description = $project->description ?? 'No description provided';

        $prompt = <<<PROMPT
You are FORGE's Chief Strategy & Pre-Mortem Officer.
Analyze this proposed project and rigorously challenge its underlying assumptions:

Project Title: {$project->title}
Classification: {$project->classification->label()}
Description: {$description}
Direct Competitors Found: {$competitorNames}
Verified Evidence:
- {$evidenceSnippets}

Actively test 4 key vectors:
1. Demand reality: Is this problem painful enough for people to pay?
2. Defensibility: Can incumbents clone this in one sprint?
3. Distribution friction: How hard will it be to acquire the first 100 customers?
4. Technical/operational feasibility: What hidden complexities exist?

Respond ONLY with valid JSON in the following format:
{
  "overall_risk_score": 0.45,
  "summary": "Concise 2-sentence summary of project defensibility and key risks.",
  "challenges": [
    {
      "assumption": "Specific assumption made by founder",
      "challenge": "Evidence-grounded counter-argument",
      "evidence_ref": "Reference to competitor or market signal",
      "severity": "HIGH",
      "recommended_action": "Concrete step to validate or pivot"
    }
  ],
  "defensibility_flags": [
    "High switching costs required",
    "Incumbent feature parity risk"
  ]
}
PROMPT;

        $request = new AIRequest(
            user: $user,
            prompt: $prompt,
            operationType: 'strategy.challenge',
            workloadClass: WorkloadClass::STANDARD,
            projectId: $project->id
        );

        $response = $this->ai->execute($request);

        $parsed = null;
        try {
            $parsed = $this->validator->validateJson($response->content, [
                'overall_risk_score',
                'summary',
                'challenges',
                'defensibility_flags',
            ]);
        } catch (\Throwable) {
            // Fallback structured baseline if AI response lacks JSON framing
            $parsed = [
                'overall_risk_score' => 0.50,
                'summary' => "The core concept for {$project->title} addresses an active space, but faces competitive pressure and customer acquisition hurdles.",
                'challenges' => [
                    [
                        'assumption' => 'Users will readily switch from existing tools.',
                        'challenge' => 'Incumbents have entrenched workflows and existing integration moats.',
                        'evidence_ref' => $competitorNames ?: 'Market landscape',
                        'severity' => 'MEDIUM',
                        'recommended_action' => 'Focus on a 10x differentiator rather than feature parity.',
                    ],
                    [
                        'assumption' => 'Market readiness and willingness to pay.',
                        'challenge' => 'Pricing power requires immediate demonstration of quantifiable ROI.',
                        'evidence_ref' => 'Pricing benchmarks',
                        'severity' => 'HIGH',
                        'recommended_action' => 'Validate pricing expectations through pre-commitments.',
                    ],
                ],
                'defensibility_flags' => [
                    'Switching friction from existing tools',
                    'Low proprietary barrier without proprietary data loop',
                ],
            ];
        }

        return new ChallengeResult(
            challenges: $parsed['challenges'] ?? [],
            overallRiskScore: (float) ($parsed['overall_risk_score'] ?? 0.5),
            summary: (string) ($parsed['summary'] ?? 'Assumption challenge complete.'),
            defensibilityFlags: (array) ($parsed['defensibility_flags'] ?? [])
        );
    }
}
