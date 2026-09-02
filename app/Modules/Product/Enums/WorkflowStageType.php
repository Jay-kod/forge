<?php

declare(strict_types=1);

namespace App\Modules\Product\Enums;

enum WorkflowStageType: string
{
    // Phase 1 (Core)
    case UNDERSTANDING = 'understanding';
    case DISCOVERY = 'discovery';
    case RESEARCH = 'research';
    case COMPETITORS = 'competitors';
    case CHALLENGE = 'challenge';
    case STRATEGY = 'strategy';
    case PRD = 'prd';
    case ARCHITECTURE = 'architecture';
    case PACKAGE = 'package';
    case EXPORT = 'export';

    // Phase 2 (Business Growth & Website Improvement)
    case BUSINESS_ANALYSIS = 'business_analysis';
    case GROWTH_PLAN = 'growth_plan';
    case WEBSITE_AUDIT = 'website_audit';
    case UX_ANALYSIS = 'ux_analysis';
    case COMPETITOR_COMPARISON = 'competitor_comparison';
    case IMPROVEMENT_PLAN = 'improvement_plan';

    // Phase 2 (Market Expansion & Strategic Planning)
    case GEOGRAPHIC_RESEARCH = 'geographic_research';
    case MARKET_COMPARISON = 'market_comparison';
    case EXPANSION_STRATEGY = 'expansion_strategy';
    case SITUATION_ANALYSIS = 'situation_analysis';
    case ROADMAP = 'roadmap';

    public function label(): string
    {
        return match ($this) {
            self::UNDERSTANDING => 'Understanding & Context',
            self::DISCOVERY => 'Existence Discovery',
            self::RESEARCH => 'Real-World Market Research',
            self::COMPETITORS => 'Competitive Landscape',
            self::CHALLENGE => 'Analysis & Challenge',
            self::STRATEGY => 'Strategic Recommendation',
            self::PRD => 'Evidence-Linked PRD',
            self::ARCHITECTURE => 'System Architecture',
            self::PACKAGE => 'AI Development Package',
            self::EXPORT => 'Blueprint & Export',

            // Phase 2
            self::BUSINESS_ANALYSIS => 'Business Model & Unit Economics',
            self::GROWTH_PLAN => 'Comprehensive Growth Plan',
            self::WEBSITE_AUDIT => 'Website Performance & Technical Audit',
            self::UX_ANALYSIS => 'AI UX & Conversion Analysis',
            self::COMPETITOR_COMPARISON => 'Head-to-Head Competitor Benchmark',
            self::IMPROVEMENT_PLAN => 'Optimization & Improvement Roadmap',
            self::GEOGRAPHIC_RESEARCH => 'Geographic & Market Intelligence',
            self::MARKET_COMPARISON => 'Regional Market Comparison',
            self::EXPANSION_STRATEGY => 'Geographic Expansion Strategy',
            self::SITUATION_ANALYSIS => 'Strategic Situation Analysis',
            self::ROADMAP => 'Executive Execution Roadmap',
        };
    }
}
