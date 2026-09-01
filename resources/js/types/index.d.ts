export interface User {
    id: number;
    name: string;
    email: string;
    avatar_url: string | null;
    role: 'user' | 'admin';
    technical_level: 'non_developer' | 'vibe_coder' | 'developer' | 'senior_developer' | null;
    created_at?: string;
}

export interface SharedProps {
    auth: {
        user: User | null;
    };
    credits: {
        balance: number;
    };
    flash: {
        success: string | null;
        error: string | null;
        info: string | null;
        warning: string | null;
    };
    appName: string;
    [key: string]: any;
}

export type ProjectClassification =
    | 'NEW_PRODUCT'
    | 'EXISTING_PRODUCT'
    | 'BUSINESS_GROWTH'
    | 'DIGITAL_TRANSFORMATION'
    | 'PROCESS_AUTOMATION'
    | 'WEBSITE_IMPROVEMENT'
    | 'SOFTWARE_REBUILD'
    | 'SOFTWARE_OPTIMIZATION'
    | 'MARKET_VALIDATION'
    | 'BUSINESS_VALIDATION'
    | 'TECHNICAL_AUDIT'
    | 'MARKET_EXPANSION'
    | 'STRATEGIC_PLANNING'
    | 'UNDEFINED';

export type ConfidenceLevel =
    | 'verified'
    | 'strongly_supported'
    | 'probable'
    | 'inferred'
    | 'assumption'
    | 'unknown'
    | 'conflicting';

export type WorkloadClass = 'LIGHT' | 'STANDARD' | 'DEEP' | 'EXTREME';

export type WorkflowMode = 'automatic' | 'page_by_page';

export type WorkflowStageType =
    | 'understanding'
    | 'discovery'
    | 'research'
    | 'competitors'
    | 'challenge'
    | 'strategy'
    | 'prd'
    | 'architecture'
    | 'package'
    | 'export';

export interface Project {
    id: number;
    user_id: number;
    title: string;
    description: string | null;
    classification: ProjectClassification;
    status: 'active' | 'archived' | 'completed';
    workflow_mode: WorkflowMode;
    current_stage: WorkflowStageType | null;
    created_at: string;
    updated_at: string;
}

export interface WorkflowStage {
    id: number;
    workflow_id: number;
    stage_type: WorkflowStageType;
    order: number;
    status: 'pending' | 'active' | 'completed' | 'skipped' | 'failed';
    content: any | null;
    approved_at: string | null;
    version: number;
}

export interface ResearchSource {
    id: number;
    url: string;
    title: string;
    source_type: 'official' | 'government' | 'research' | 'documentation' | 'publication' | 'industry' | 'community' | 'weak';
    publication_date: string | null;
    retrieved_at: string;
    content_summary: string | null;
    reliability_score: number | null;
}

export interface Evidence {
    id: number;
    claim: string;
    confidence: ConfidenceLevel;
    confidence_score: number | null;
    category: string;
    sources?: ResearchSource[];
}

export interface Competitor {
    id: number;
    name: string;
    url: string | null;
    description: string | null;
    category: 'direct' | 'indirect' | 'adjacent';
    strengths: string[] | null;
    weaknesses: string[] | null;
    pricing: any | null;
    target_market: string | null;
    differentiation: string | null;
}

export interface Opportunity {
    id: number;
    title: string;
    description: string;
    category: string;
    impact: 'low' | 'medium' | 'high' | 'critical';
    difficulty: 'low' | 'medium' | 'high' | 'extreme';
    confidence: ConfidenceLevel;
    confidence_score: number | null;
    status: 'identified' | 'recommended' | 'accepted' | 'rejected' | 'implemented';
}

export interface Recommendation {
    id: number;
    opportunity_id?: number;
    title: string;
    description: string;
    why_it_matters: string;
    why_now: string | null;
    potential_impact: string;
    difficulty: string;
    suggested_action: string;
    status: 'pending' | 'accepted' | 'rejected' | 'modified';
}
