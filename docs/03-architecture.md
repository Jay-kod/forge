# FORGE — Architecture

**Document version:** 1.0  
**Date:** 2026-09-01  
**Status:** DRAFT — Awaiting review  
**Depends on:** [01-prd.md](file:///c:/xampp/htdocs/1/f/docs/01-prd.md), [02-saas-business-model.md](file:///c:/xampp/htdocs/1/f/docs/02-saas-business-model.md)

---

## 1. Technology Stack

| Layer | Technology | Rationale |
|---|---|---|
| **Backend** | Laravel 12.x (PHP 8.4+) | Full-featured framework with mature ecosystem; queues, events, policies, migrations, testing built in; strong community; suitable for SaaS |
| **Frontend** | Vue 3.5+ with Composition API | Reactive, component-based; pairs natively with Laravel via Inertia.js; TypeScript support |
| **Bridge** | Inertia.js | Eliminates API-first overhead for V1; server-driven routing with SPA-like experience; reduces development surface |
| **Styling** | Tailwind CSS 4.x | Utility-first; design token support via CSS custom properties; responsive by default |
| **Database** | MySQL 8.4+ | Relational integrity for billing, credits, entitlements; JSON column support for flexible context storage; proven at scale |
| **Cache/Queue** | Redis 7.x | Queue driver, session driver, cache layer; only used where justified (queues, rate limiting, cache) |
| **Search** | MySQL Full-Text (V1) → Meilisearch (V2) | Avoid premature infrastructure; MySQL handles V1 search; upgrade path exists |
| **Build** | Vite 6.x | Fast HMR, native ESM, Laravel integration via laravel-vite-plugin |
| **Local Dev** | Docker (Laravel Sail) | Consistent environment; MySQL, Redis, Mailpit included |
| **Testing** | PHPUnit (backend), Vitest (frontend), Playwright (E2E) | Comprehensive test coverage across all layers |
| **AI** | Multi-provider abstraction (see Section 7) | Anthropic Claude, OpenAI GPT, Google Gemini — routed by workload |

---

## 2. High-Level Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                        BROWSER (Vue 3 + Inertia)                │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐          │
│  │  Auth    │ │ Projects │ │ Workflow │ │ Dashboard│          │
│  │  Pages   │ │  Pages   │ │  Pages   │ │  Pages   │          │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘          │
└─────────────────────────────────────────────────────────────────┘
                              │ Inertia
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                     LARAVEL APPLICATION                         │
│                                                                 │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │                   HTTP / Controller Layer                  │  │
│  │  (Thin controllers — delegate to services/actions)        │  │
│  └──────────────────────────────────────────────────────────┘  │
│                              │                                  │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │                    Domain Modules                         │  │
│  │                                                           │  │
│  │  ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐       │  │
│  │  │Identity │ │Projects │ │Research │ │   AI    │       │  │
│  │  └─────────┘ └─────────┘ └─────────┘ └─────────┘       │  │
│  │  ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐       │  │
│  │  │Context  │ │Discovery│ │Evidence │ │Opportun.│       │  │
│  │  └─────────┘ └─────────┘ └─────────┘ └─────────┘       │  │
│  │  ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐       │  │
│  │  │Product  │ │Architec.│ │Blueprint│ │ Credits │       │  │
│  │  └─────────┘ └─────────┘ └─────────┘ └─────────┘       │  │
│  │  ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐       │  │
│  │  │Billing  │ │ Export  │ │  Admin  │ │Geography│       │  │
│  │  └─────────┘ └─────────┘ └─────────┘ └─────────┘       │  │
│  └──────────────────────────────────────────────────────────┘  │
│                              │                                  │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │                Infrastructure Layer                       │  │
│  │  Queue │ Events │ Cache │ Storage │ Mail │ Logging       │  │
│  └──────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
                              │
                    ┌─────────┼─────────┐
                    ▼         ▼         ▼
              ┌─────────┐ ┌─────┐ ┌──────────┐
              │  MySQL  │ │Redis│ │AI Providers│
              │  8.4+   │ │ 7.x │ │(Abstracted)│
              └─────────┘ └─────┘ └──────────┘
```

---

## 3. Module Architecture

### 3.1 Module Organization

Laravel's default structure is extended with domain-organized modules. Each module contains its own models, services, actions, events, policies, and requests.

```
app/
├── Modules/
│   ├── Identity/
│   │   ├── Models/          (User, Identity, SocialAccount)
│   │   ├── Actions/         (CreateUser, LinkSocialAccount)
│   │   ├── Services/        (AuthenticationService)
│   │   ├── Policies/
│   │   ├── Requests/
│   │   ├── Events/
│   │   └── Tests/
│   │
│   ├── Projects/
│   │   ├── Models/          (Project, ProjectVersion, Goal)
│   │   ├── Actions/         (CreateProject, ClassifyProject, ArchiveProject)
│   │   ├── Services/        (ProjectService, ClassificationService)
│   │   ├── Enums/           (ProjectType, ProjectStatus, WorkflowMode)
│   │   ├── Policies/
│   │   ├── Requests/
│   │   ├── Events/
│   │   └── Tests/
│   │
│   ├── Context/
│   │   ├── Models/          (ProjectContext, UserContext)
│   │   ├── Actions/         (BuildContext, UpdateContext)
│   │   ├── Services/        (ContextEngine)
│   │   ├── Enums/           (KnowledgeClassification)
│   │   └── Tests/
│   │
│   ├── Geography/
│   │   ├── Models/          (Location, Market)
│   │   ├── Services/        (GeographyService, MarketService)
│   │   └── Tests/
│   │
│   ├── Discovery/
│   │   ├── Models/          (Discovery, Competitor)
│   │   ├── Actions/         (RunDiscovery, AnalyzeCompetitors)
│   │   ├── Services/        (DiscoveryService, CompetitorAnalysisService)
│   │   └── Tests/
│   │
│   ├── Research/
│   │   ├── Models/          (ResearchSource, ResearchFinding)
│   │   ├── Actions/         (ConductResearch, ValidateSources)
│   │   ├── Services/        (ResearchEngine, SourceValidationService)
│   │   ├── Enums/           (SourceType, SourceReliability)
│   │   └── Tests/
│   │
│   ├── Evidence/
│   │   ├── Models/          (Evidence, EvidenceLink)
│   │   ├── Services/        (EvidenceService, ConfidenceCalculator)
│   │   ├── Enums/           (ConfidenceLevel)
│   │   └── Tests/
│   │
│   ├── Opportunity/
│   │   ├── Models/          (Opportunity, OpportunityLink, Recommendation)
│   │   ├── Actions/         (IdentifyOpportunities, RankOpportunities)
│   │   ├── Services/        (OpportunityEngine, RecommendationService)
│   │   └── Tests/
│   │
│   ├── Strategy/
│   │   ├── Actions/         (GenerateStrategy, ChallengeAssumptions)
│   │   ├── Services/        (StrategyEngine, CreativeStrategyService)
│   │   └── Tests/
│   │
│   ├── Product/
│   │   ├── Models/          (ProductDocument, Workflow, WorkflowStage, Decision)
│   │   ├── Actions/         (GeneratePRD, GenerateArchitecture)
│   │   ├── Services/        (PRDService, ArchitectureService, WorkflowService)
│   │   ├── Enums/           (WorkflowStageType, StageStatus, DecisionStatus)
│   │   └── Tests/
│   │
│   ├── Blueprint/
│   │   ├── Actions/         (GenerateBlueprint, GenerateDevPackage, GenerateMasterPrompt)
│   │   ├── Services/        (BlueprintService, PackageAssembler)
│   │   └── Tests/
│   │
│   ├── AI/
│   │   ├── Contracts/       (AIProvider, AIResponse, AIModel)
│   │   ├── Providers/       (AnthropicProvider, OpenAIProvider, GeminiProvider)
│   │   ├── Services/        (AIOrchestrator, WorkloadRouter, PromptBuilder)
│   │   ├── Enums/           (WorkloadClass, ProviderType)
│   │   ├── DTOs/            (AIRequest, AIResult, WorkloadEstimate)
│   │   └── Tests/
│   │
│   ├── Credits/
│   │   ├── Models/          (CreditAccount, CreditTransaction)
│   │   ├── Actions/         (ReserveCredits, ConsumeCredits, RefundCredits, GrantCredits)
│   │   ├── Services/        (CreditService, CreditEstimator)
│   │   ├── Enums/           (TransactionType, TransactionStatus)
│   │   └── Tests/
│   │
│   ├── Billing/
│   │   ├── Models/          (Subscription, Plan, Entitlement, BillingEvent)
│   │   ├── Actions/         (ProcessWebhook, ChangePlan, HandlePaymentFailure)
│   │   ├── Services/        (BillingService, EntitlementService, SubscriptionService)
│   │   ├── Webhooks/        (StripeWebhookHandler)
│   │   └── Tests/
│   │
│   ├── Export/
│   │   ├── Actions/         (ExportPDF, ExportPackage, CopyMasterPrompt)
│   │   ├── Services/        (PDFService, PackageExportService)
│   │   └── Tests/
│   │
│   ├── Admin/
│   │   ├── Services/        (AdminService)
│   │   ├── Policies/
│   │   └── Tests/
│   │
│   ├── Notifications/
│   │   ├── Models/          (Notification)
│   │   ├── Services/        (NotificationService)
│   │   └── Tests/
│   │
│   └── Consent/
│       ├── Models/          (ConsentRecord)
│       ├── Actions/         (RecordConsent, RevokeConsent)
│       ├── Services/        (ConsentService)
│       └── Tests/
│
├── Http/
│   └── Controllers/         (Thin — organized by module)
│       ├── Auth/
│       ├── Projects/
│       ├── Workflow/
│       ├── Export/
│       ├── Billing/
│       └── Admin/
│
└── Providers/
```

### 3.2 Module Communication Rules

1. Modules communicate through **service contracts** (interfaces), not direct model access across modules
2. A module may depend on another module's contract, but not its internals
3. **No circular dependencies** — dependency graph must be acyclic
4. Cross-module data is passed via **DTOs** or **events**, not by sharing Eloquent models directly
5. Exception: shared base models (User) may be referenced by multiple modules through their contracts

### 3.3 Dependency Graph (Simplified)

```
Identity ← (everything depends on identity)
    │
    ├── Projects ← Context ← Discovery
    │                  │         │
    │                  ├── Research ← Evidence
    │                  │              │
    │                  ├── Opportunity ← Strategy
    │                  │
    │                  └── Geography
    │
    ├── Product ← Blueprint ← Export
    │
    ├── AI (standalone — consumed by Research, Discovery, Product, Strategy, Blueprint)
    │
    ├── Credits ← Billing
    │
    ├── Consent (standalone)
    │
    ├── Notifications (standalone — consumed by many)
    │
    └── Admin (reads from many, writes to configuration)
```

---

## 4. Database Design

### 4.1 Core Tables

#### Identity & Users

```sql
users
    id                  BIGINT UNSIGNED PK
    name                VARCHAR(255)
    email               VARCHAR(255) UNIQUE
    email_verified_at   TIMESTAMP NULL
    avatar_url          VARCHAR(500) NULL
    role                ENUM('user', 'admin') DEFAULT 'user'
    technical_level     ENUM('non_developer', 'vibe_coder', 'developer', 'senior_developer') NULL
    created_at          TIMESTAMP
    updated_at          TIMESTAMP

social_accounts
    id                  BIGINT UNSIGNED PK
    user_id             BIGINT UNSIGNED FK → users
    provider            ENUM('google', 'github')
    provider_id         VARCHAR(255)
    provider_token      TEXT (encrypted)
    provider_refresh    TEXT (encrypted) NULL
    created_at          TIMESTAMP
    updated_at          TIMESTAMP
    UNIQUE(provider, provider_id)
```

#### Projects

```sql
projects
    id                  BIGINT UNSIGNED PK
    user_id             BIGINT UNSIGNED FK → users
    title               VARCHAR(255)
    description         TEXT NULL
    classification      VARCHAR(50)          -- ProjectType enum
    status              VARCHAR(30)          -- active, archived, completed
    workflow_mode       ENUM('automatic', 'page_by_page')
    current_stage       VARCHAR(50) NULL     -- current workflow stage
    created_at          TIMESTAMP
    updated_at          TIMESTAMP

project_versions
    id                  BIGINT UNSIGNED PK
    project_id          BIGINT UNSIGNED FK → projects
    version             INT UNSIGNED
    snapshot            JSON                 -- full project state at this version
    created_by          VARCHAR(50)          -- 'user', 'system', 'regeneration'
    note                TEXT NULL
    created_at          TIMESTAMP
```

#### Context

```sql
project_contexts
    id                  BIGINT UNSIGNED PK
    project_id          BIGINT UNSIGNED FK → projects
    user_input          TEXT                 -- original user input
    classification      VARCHAR(50)
    classification_confidence DECIMAL(3,2)
    user_understanding  JSON                 -- technical_level, business_maturity, etc.
    business_context    JSON NULL
    product_context     JSON NULL
    geographic_context  JSON NULL
    existing_system     JSON NULL
    goals               JSON NULL
    updated_at          TIMESTAMP

context_knowledge
    id                  BIGINT UNSIGNED PK
    project_context_id  BIGINT UNSIGNED FK → project_contexts
    field               VARCHAR(100)
    value               TEXT
    classification      ENUM('confirmed', 'inferred', 'assumed', 'unknown', 'conflicting')
    source              VARCHAR(100)         -- 'user_input', 'research', 'inference'
    created_at          TIMESTAMP
```

#### Research & Evidence

```sql
research_sessions
    id                  BIGINT UNSIGNED PK
    project_id          BIGINT UNSIGNED FK → projects
    type                VARCHAR(50)          -- 'market', 'competitor', 'technology', etc.
    status              VARCHAR(30)          -- 'pending', 'running', 'completed', 'failed'
    started_at          TIMESTAMP NULL
    completed_at        TIMESTAMP NULL
    credits_consumed    INT UNSIGNED DEFAULT 0
    created_at          TIMESTAMP

research_sources
    id                  BIGINT UNSIGNED PK
    research_session_id BIGINT UNSIGNED FK → research_sessions
    url                 VARCHAR(2000)
    title               VARCHAR(500)
    source_type         ENUM('official', 'government', 'research', 'documentation', 'publication', 'industry', 'community', 'weak')
    publication_date    DATE NULL
    retrieved_at        TIMESTAMP
    content_summary     TEXT NULL
    reliability_score   DECIMAL(3,2) NULL

evidence
    id                  BIGINT UNSIGNED PK
    project_id          BIGINT UNSIGNED FK → projects
    claim               TEXT
    confidence          ENUM('verified', 'strongly_supported', 'probable', 'inferred', 'assumption', 'unknown', 'conflicting')
    confidence_score    DECIMAL(3,2) NULL    -- 0.00 to 1.00
    category            VARCHAR(50)          -- 'market', 'competitor', 'technology', 'user', etc.
    created_at          TIMESTAMP
    updated_at          TIMESTAMP

evidence_source_links
    id                  BIGINT UNSIGNED PK
    evidence_id         BIGINT UNSIGNED FK → evidence
    research_source_id  BIGINT UNSIGNED FK → research_sources
    relevance           TEXT NULL             -- what this source supports about the claim
```

#### Discovery & Competitors

```sql
discoveries
    id                  BIGINT UNSIGNED PK
    project_id          BIGINT UNSIGNED FK → projects
    verdict             ENUM('build_as_proposed', 'build_with_modifications', 'consider_alternative', 'do_not_build_yet')
    summary             TEXT
    rationale           TEXT
    created_at          TIMESTAMP
    updated_at          TIMESTAMP

competitors
    id                  BIGINT UNSIGNED PK
    project_id          BIGINT UNSIGNED FK → projects
    name                VARCHAR(255)
    url                 VARCHAR(2000) NULL
    description         TEXT NULL
    category            ENUM('direct', 'indirect', 'adjacent')
    strengths           JSON NULL
    weaknesses          JSON NULL
    pricing             JSON NULL
    target_market       VARCHAR(255) NULL
    differentiation     TEXT NULL
    source_ids          JSON NULL            -- references to research_sources
    created_at          TIMESTAMP
    updated_at          TIMESTAMP
```

#### Opportunities & Recommendations

```sql
opportunities
    id                  BIGINT UNSIGNED PK
    project_id          BIGINT UNSIGNED FK → projects
    title               VARCHAR(255)
    description         TEXT
    category            VARCHAR(50)          -- 'product', 'market', 'revenue', 'technical', etc.
    impact              ENUM('low', 'medium', 'high', 'critical')
    difficulty          ENUM('low', 'medium', 'high', 'extreme')
    confidence          ENUM('verified', 'strongly_supported', 'probable', 'inferred', 'assumption')
    confidence_score    DECIMAL(3,2) NULL
    status              ENUM('identified', 'recommended', 'accepted', 'rejected', 'implemented')
    created_at          TIMESTAMP
    updated_at          TIMESTAMP

recommendations
    id                  BIGINT UNSIGNED PK
    opportunity_id      BIGINT UNSIGNED FK → opportunities
    project_id          BIGINT UNSIGNED FK → projects
    title               VARCHAR(255)
    description         TEXT
    why_it_matters      TEXT
    why_now             TEXT NULL
    potential_impact    TEXT
    difficulty          VARCHAR(30)
    dependencies        JSON NULL
    evidence_ids        JSON NULL            -- references to evidence records
    suggested_action    TEXT
    status              ENUM('pending', 'accepted', 'rejected', 'modified')
    user_response       TEXT NULL
    created_at          TIMESTAMP
    updated_at          TIMESTAMP
```

#### Workflow

```sql
workflows
    id                  BIGINT UNSIGNED PK
    project_id          BIGINT UNSIGNED FK → projects
    mode                ENUM('automatic', 'page_by_page')
    status              ENUM('active', 'completed', 'paused', 'abandoned')
    created_at          TIMESTAMP
    updated_at          TIMESTAMP

workflow_stages
    id                  BIGINT UNSIGNED PK
    workflow_id         BIGINT UNSIGNED FK → workflows
    stage_type          VARCHAR(50)          -- 'understanding', 'discovery', 'research', etc.
    order               INT UNSIGNED
    status              ENUM('pending', 'active', 'completed', 'skipped', 'failed')
    content             JSON NULL            -- stage output
    approved_at         TIMESTAMP NULL
    approved_by         BIGINT UNSIGNED NULL FK → users
    version             INT UNSIGNED DEFAULT 1
    started_at          TIMESTAMP NULL
    completed_at        TIMESTAMP NULL
    created_at          TIMESTAMP
    updated_at          TIMESTAMP

decisions
    id                  BIGINT UNSIGNED PK
    workflow_stage_id   BIGINT UNSIGNED FK → workflow_stages
    project_id          BIGINT UNSIGNED FK → projects
    question            TEXT
    options             JSON NULL
    selected_option     TEXT NULL
    rationale           TEXT NULL
    status              ENUM('pending', 'decided', 'revised')
    decided_at          TIMESTAMP NULL
    created_at          TIMESTAMP
```

#### Product Documents

```sql
product_documents
    id                  BIGINT UNSIGNED PK
    project_id          BIGINT UNSIGNED FK → projects
    type                VARCHAR(50)          -- 'prd', 'architecture', 'agents_md', 'claude_md', etc.
    title               VARCHAR(255)
    content             LONGTEXT
    version             INT UNSIGNED DEFAULT 1
    status              ENUM('draft', 'approved', 'superseded')
    evidence_ids        JSON NULL
    created_at          TIMESTAMP
    updated_at          TIMESTAMP
```

#### Credits & Billing

```sql
credit_accounts
    id                  BIGINT UNSIGNED PK
    user_id             BIGINT UNSIGNED FK → users UNIQUE
    balance             INT UNSIGNED DEFAULT 0
    lifetime_granted    INT UNSIGNED DEFAULT 0
    lifetime_consumed   INT UNSIGNED DEFAULT 0
    updated_at          TIMESTAMP

credit_transactions
    id                  BIGINT UNSIGNED PK
    credit_account_id   BIGINT UNSIGNED FK → credit_accounts
    type                ENUM('grant', 'consumption', 'reservation', 'release', 'refund', 'expiry', 'purchase')
    amount              INT                  -- positive for grants, negative for consumption
    balance_after       INT UNSIGNED
    reference_type      VARCHAR(50) NULL     -- 'subscription_renewal', 'ai_operation', 'purchase', etc.
    reference_id        VARCHAR(100) NULL
    description         VARCHAR(255) NULL
    project_id          BIGINT UNSIGNED NULL FK → projects
    created_at          TIMESTAMP

plans
    id                  BIGINT UNSIGNED PK
    slug                VARCHAR(50) UNIQUE   -- 'free', 'pro', 'business', 'enterprise'
    name                VARCHAR(100)
    price_monthly       DECIMAL(8,2) NULL
    price_annual        DECIMAL(8,2) NULL
    credits_monthly     INT UNSIGNED
    stripe_price_id_monthly VARCHAR(255) NULL
    stripe_price_id_annual  VARCHAR(255) NULL
    is_active           BOOLEAN DEFAULT TRUE
    features            JSON                 -- human-readable feature list
    created_at          TIMESTAMP
    updated_at          TIMESTAMP

entitlements
    id                  BIGINT UNSIGNED PK
    plan_id             BIGINT UNSIGNED FK → plans
    capability          VARCHAR(100)         -- 'research.deep', 'export.pdf.clean', etc.
    value               VARCHAR(100)         -- 'true', 'false', 'unlimited', 'limit:5', etc.
    created_at          TIMESTAMP

subscriptions
    id                  BIGINT UNSIGNED PK
    user_id             BIGINT UNSIGNED FK → users
    plan_id             BIGINT UNSIGNED FK → plans
    status              ENUM('trialing', 'active', 'past_due', 'canceled', 'expired')
    stripe_subscription_id VARCHAR(255) NULL
    stripe_customer_id  VARCHAR(255) NULL
    current_period_start TIMESTAMP NULL
    current_period_end  TIMESTAMP NULL
    canceled_at         TIMESTAMP NULL
    ends_at             TIMESTAMP NULL
    created_at          TIMESTAMP
    updated_at          TIMESTAMP

billing_events
    id                  BIGINT UNSIGNED PK
    user_id             BIGINT UNSIGNED FK → users
    event_type          VARCHAR(100)
    stripe_event_id     VARCHAR(255) UNIQUE  -- idempotency key
    payload             JSON
    processed_at        TIMESTAMP NULL
    created_at          TIMESTAMP
```

#### Consent & Audit

```sql
consent_records
    id                  BIGINT UNSIGNED PK
    user_id             BIGINT UNSIGNED FK → users
    consent_type        ENUM('analytics', 'product_improvement', 'ai_improvement', 'marketing')
    granted             BOOLEAN
    version             VARCHAR(20)          -- consent version (e.g., '1.0', '1.1')
    ip_address          VARCHAR(45) NULL
    granted_at          TIMESTAMP
    revoked_at          TIMESTAMP NULL
    created_at          TIMESTAMP

audit_logs
    id                  BIGINT UNSIGNED PK
    user_id             BIGINT UNSIGNED NULL FK → users
    action              VARCHAR(100)
    entity_type         VARCHAR(100) NULL
    entity_id           BIGINT UNSIGNED NULL
    details             JSON NULL
    ip_address          VARCHAR(45) NULL
    created_at          TIMESTAMP
```

### 4.2 Entity Relationship Summary

```
User ─┬─ SocialAccounts
      ├─ Projects ─┬─ ProjectVersions
      │            ├─ ProjectContext ── ContextKnowledge
      │            ├─ ResearchSessions ── ResearchSources
      │            ├─ Evidence ── EvidenceSourceLinks
      │            ├─ Discoveries
      │            ├─ Competitors
      │            ├─ Opportunities ── Recommendations
      │            ├─ Workflows ── WorkflowStages ── Decisions
      │            └─ ProductDocuments
      ├─ CreditAccount ── CreditTransactions
      ├─ Subscription (→ Plan → Entitlements)
      ├─ ConsentRecords
      └─ AuditLogs

Plan ── Entitlements
BillingEvents (→ User)
```

---

## 5. Authentication Architecture

### 5.1 OAuth Flow

```
User clicks "Sign in with Google/GitHub"
    → Redirect to provider OAuth consent screen
    → Provider redirects back with authorization code
    → Laravel exchanges code for access token (server-side)
    → Laravel fetches user profile from provider
    → Match or create User + SocialAccount
    → Create authenticated session
    → Redirect to dashboard
```

### 5.2 Implementation

Use Laravel Socialite for OAuth integration.

- **Google:** Email + name + avatar
- **GitHub:** Email + name + avatar + GitHub user ID (used later for repository integration)

### 5.3 Session Management

- Server-side sessions (database or Redis driver)
- CSRF protection on all state-changing requests
- Session expiry: 2 hours idle, 24 hours absolute
- Remember-me: 30-day persistent token (optional)

---

## 6. Authorization Architecture

### 6.1 Ownership

Every project belongs to a user. Authorization checks:

```php
// Policy pattern
class ProjectPolicy
{
    public function view(User $user, Project $project): bool
    {
        return $user->id === $project->user_id;
    }
}
```

### 6.2 Entitlement Checks

```php
// Middleware or inline check
class EntitlementService
{
    public function can(User $user, string $capability): bool
    {
        $entitlements = $this->resolveForUser($user);
        return $entitlements->allows($capability);
    }
    
    public function limit(User $user, string $capability): int|null
    {
        $entitlements = $this->resolveForUser($user);
        return $entitlements->getLimit($capability);
    }
}
```

---

## 7. AI Provider Architecture

### 7.1 Abstraction Layer

```php
interface AIProvider
{
    public function complete(AIRequest $request): AIResponse;
    public function stream(AIRequest $request): Generator;
    public function estimateCost(AIRequest $request): CostEstimate;
    public function isAvailable(): bool;
}

class AIOrchestrator
{
    public function execute(AIRequest $request): AIResponse
    {
        $provider = $this->router->selectProvider($request);
        $credits = $this->estimator->estimate($request);
        
        // Reserve credits
        $reservation = $this->credits->reserve($request->user, $credits);
        
        try {
            $response = $provider->complete($request);
            $this->credits->confirm($reservation);
            return $response;
        } catch (AIProviderException $e) {
            $this->credits->release($reservation);
            throw $e;
        }
    }
}
```

### 7.2 Workload Routing

| Workload Class | Preferred Provider | Fallback | Rationale |
|---|---|---|---|
| LIGHT | Fast/cheap model (e.g., Claude Haiku, GPT-4o-mini) | Any available | Speed and cost |
| STANDARD | Mid-tier model (e.g., Claude Sonnet, GPT-4o) | Upgrade to strong model | Balance of quality and cost |
| DEEP | Strong reasoning model (e.g., Claude Opus, GPT-4-turbo) | Alternative strong model | Quality matters most |
| EXTREME | Strong reasoning with extended context | Chunked processing | Large context, deep analysis |

### 7.3 Failure Handling

1. **Provider unavailable:** Fallback to alternative provider
2. **Rate limited:** Queue and retry with backoff
3. **Partial output:** Save partial result, refund proportional credits, offer retry
4. **Invalid output:** Validate structure, retry once, report failure if still invalid
5. **Timeout:** Cancel, refund credits, notify user

---

## 8. Queue Architecture

### 8.1 Queue Usage

AI operations, research, and heavy processing run on queues to avoid blocking HTTP requests.

| Queue | Purpose | Workers |
|---|---|---|
| `default` | General background jobs | 2 |
| `ai` | AI provider operations | 3 |
| `research` | Web research operations | 2 |
| `export` | PDF/package generation | 1 |
| `billing` | Webhook processing, credit operations | 1 |

### 8.2 Job Pattern

```
User clicks "Run Research"
    → Controller dispatches ResearchJob to 'research' queue
    → Returns immediately with "Research in progress" status
    → ResearchJob executes (may take 30-120 seconds)
    → Job broadcasts progress via WebSocket/polling
    → Job completes → stores results → notifies user
```

### 8.3 Progress Communication

V1: Polling endpoint (`GET /projects/{id}/workflow/status`)  
V2: WebSocket via Laravel Reverb for real-time updates

---

## 9. Security Architecture

See [08-security.md](file:///c:/xampp/htdocs/1/f/docs/08-security.md) for comprehensive security documentation.

Key architectural decisions:
- All authorization is server-side
- OAuth tokens encrypted at rest
- Input validation on every request (FormRequest classes)
- Rate limiting on AI operations and authentication
- Strict ownership checks on all project operations
- Webhook signature verification
- CORS configured for application domain only
- CSP headers configured

---

## 10. Scalability Considerations

### 10.1 What to Build Now

- Indexed database queries
- Queue-based AI operations
- Redis caching for entitlements and frequently-accessed data
- Efficient Eloquent queries (eager loading, select specific columns)

### 10.2 What to Defer

- Read replicas (not needed until ~10K users)
- Horizontal scaling (single server handles V1 load)
- CDN for static assets (defer until traffic justifies)
- Full-text search engine (MySQL handles V1)
- Event sourcing (standard CRUD is appropriate for V1)

---

*Architecture decisions will be refined during the Hard Questions review and Architecture Essentials document.*
