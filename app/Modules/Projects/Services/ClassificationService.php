<?php

declare(strict_types=1);

namespace App\Modules\Projects\Services;

use App\Modules\Projects\Contracts\ClassificationServiceInterface;
use App\Modules\Projects\DTOs\ClassificationResult;
use App\Modules\Projects\Enums\ProjectType;

class ClassificationService implements ClassificationServiceInterface
{
    /**
     * Classify user natural language input into one of FORGE's situation types.
     */
    public function classify(string $userInput): ClassificationResult
    {
        $input = mb_strtolower(trim($userInput));

        // Rule-based classification heuristics
        if (preg_match('/\b(github|repo|codebase|refactor|legacy code|code quality|audit.*code)\b/i', $input)) {
            return new ClassificationResult(
                classification: ProjectType::SOFTWARE_OPTIMIZATION,
                confidence: 0.90,
                reasoning: 'Input mentions GitHub repository or codebase optimization.',
                suggestedStages: ['understanding', 'discovery', 'research', 'challenge', 'strategy', 'package']
            );
        }

        if (preg_match('/\b(expand|new market|lagos|new city|expansion|cross-border)\b/i', $input)) {
            return new ClassificationResult(
                classification: ProjectType::MARKET_EXPANSION,
                confidence: 0.88,
                reasoning: 'Input mentions geographic or market expansion.',
                suggestedStages: ['understanding', 'geographic_research', 'market_comparison', 'expansion_strategy']
            );
        }

        if (preg_match('/\b(website|landing page|conversion|not converting|bounce rate|traffic)\b/i', $input)) {
            return new ClassificationResult(
                classification: ProjectType::WEBSITE_IMPROVEMENT,
                confidence: 0.88,
                reasoning: 'Input focuses on website performance and conversion improvement.',
                suggestedStages: ['understanding', 'website_audit', 'ux_analysis', 'competitor_comparison', 'improvement_plan']
            );
        }

        if (preg_match('/\b(strategic planning|business strategy|strategic roadmap|what should we do next|quarterly strategy|pivot.*business)\b/i', $input)) {
            return new ClassificationResult(
                classification: ProjectType::STRATEGIC_PLANNING,
                confidence: 0.88,
                reasoning: 'Input focuses on executive strategy and strategic planning.',
                suggestedStages: ['understanding', 'situation_analysis', 'research', 'strategy', 'roadmap']
            );
        }

        if (preg_match('/\b(manual process|automate|paperwork|digitize|spreadsheet|streamline.*workflow)\b/i', $input)) {
            return new ClassificationResult(
                classification: ProjectType::PROCESS_AUTOMATION,
                confidence: 0.85,
                reasoning: 'Input describes automating or digitizing manual workflows.',
                suggestedStages: ['understanding', 'discovery', 'research', 'challenge', 'strategy', 'prd']
            );
        }

        if (preg_match('/\b(more customers|grow.*business|increase revenue|sales|marketing|scale.*business|run a.*business)\b/i', $input)) {
            return new ClassificationResult(
                classification: ProjectType::BUSINESS_GROWTH,
                confidence: 0.86,
                reasoning: 'Input focuses on growing an existing business and customer acquisition.',
                suggestedStages: ['understanding', 'business_analysis', 'research', 'growth_plan']
            );
        }

        if (preg_match('/\b(rebuild|rewrite|modernize.*app|modernize.*software|switch stack)\b/i', $input)) {
            return new ClassificationResult(
                classification: ProjectType::SOFTWARE_REBUILD,
                confidence: 0.87,
                reasoning: 'Input indicates a complete software rebuild or modernization.',
                suggestedStages: ['understanding', 'discovery', 'research', 'challenge', 'strategy', 'prd', 'architecture', 'package']
            );
        }

        if (preg_match('/\b(makes? sense|validate|validation|market for|there is a market|is there a market|will (people|anyone|users) pay|idea validation|don\'t know if|whether.*makes sense)\b/i', $input)) {
            return new ClassificationResult(
                classification: ProjectType::MARKET_VALIDATION,
                confidence: 0.89,
                reasoning: 'User is seeking validation for a concept before committing resources.',
                suggestedStages: ['understanding', 'discovery', 'research', 'challenge', 'strategy']
            );
        }

        if (preg_match('/\b(build|app|saas|platform|startup|tool|create.*app|new project|software for)\b/i', $input)) {
            return new ClassificationResult(
                classification: ProjectType::NEW_PRODUCT,
                confidence: 0.85,
                reasoning: 'User is proposing a new software product or SaaS application.',
                suggestedStages: ['understanding', 'discovery', 'research', 'competitors', 'challenge', 'strategy', 'prd', 'architecture', 'package', 'export']
            );
        }

        return new ClassificationResult(
            classification: ProjectType::NEW_PRODUCT,
            confidence: 0.70,
            reasoning: 'Defaulting to general product development flow.',
            suggestedStages: ['understanding', 'discovery', 'research', 'challenge', 'strategy', 'prd', 'architecture', 'package']
        );
    }
}
