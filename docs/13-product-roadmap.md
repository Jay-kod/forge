# FORGE — Product Roadmap

**Document version:** 1.0  
**Date:** 2026-09-01  
**Status:** DRAFT  

---

## Phase 1 — Foundation (MVP)

**Target:** 8–12 weeks  
**Goal:** Working intelligence loop for new product ideas

### Core Infrastructure
- [x] Laravel project scaffold with module architecture
- [x] Docker development environment (Sail)
- [x] Database migrations for all MVP tables
- [x] Authentication (Google + GitHub OAuth)
- [x] Session management
- [x] Basic routing and Inertia setup

### AI System
- [x] AI provider abstraction layer
- [x] Anthropic provider implementation
- [x] OpenAI provider implementation
- [x] Workload router (LIGHT/STANDARD/DEEP)
- [x] AI output validation
- [x] Provider fallback logic

### Credit System
- [x] Credit account model
- [x] Credit reservation / consumption / refund
- [x] Concurrent access safety (row-level locking)
- [x] Credit balance display
- [x] Cost estimation before operations

### Billing System
- [x] Plan and entitlement models
- [x] Entitlement service (capability checks)
- [x] Stripe integration (checkout, webhooks)
- [x] Subscription lifecycle (create, cancel, upgrade, downgrade)
- [x] Webhook idempotency
- [x] Free plan provisioning on signup

### Project System
- [x] Project creation ("What are you trying to achieve?")
- [x] Situation classification engine
- [x] Project context model
- [x] User understanding engine (basic)
- [x] Project dashboard (list, view, archive)
- [x] Project ownership authorization

### Intelligence Loop
- [x] Workflow engine (stage management)
- [x] Dual mode (Automatic + Page-by-Page)
- [x] Stage: Understanding
- [x] Stage: Existence & Opportunity Discovery
- [x] Stage: Competitor Analysis
- [x] Stage: Market Research
- [x] Stage: Challenge & Recommendation
- [x] Stage: PRD Generation
- [x] Stage: Architecture Generation
- [x] Stage: Development Package Generation

### Research System
- [x] Web research integration
- [x] Source collection with metadata
- [x] Source reliability classification
- [x] Evidence model with confidence levels
- [x] Evidence-source linking

### Export System
- [x] PDF generation (basic)
- [x] AI development package download (ZIP)
- [x] Master prompt copy-to-clipboard

### UI
- [x] Design system implementation (Tailwind config + tokens)
- [x] Light mode + Dark mode
- [x] Application shell (sidebar, navigation)
- [x] Authentication pages
- [x] Dashboard
- [x] Project creation flow
- [x] Workflow stage views
- [x] Document viewer
- [x] Responsive design

### Admin
- [x] Basic admin panel (users, plans, credit overview)

### Testing
- [x] Authorization tests
- [x] Entitlement tests
- [x] Credit concurrency tests
- [x] Webhook idempotency tests
- [x] AI orchestration tests (with mocks)
- [x] Core workflow tests

---

## Phase 2 — Expansion

**Target:** 4–8 weeks after Phase 1  
**Goal:** Support existing businesses and broader classification types

### New Classification Support
- [x] BUSINESS_GROWTH workflow
- [x] WEBSITE_IMPROVEMENT workflow
- [x] MARKET_EXPANSION workflow
- [x] STRATEGIC_PLANNING workflow

### Website Analysis
- [x] URL input and basic website analysis
- [x] UX assessment (AI-driven)
- [x] Competitor comparison

### Geographic Intelligence
- [x] Location model and market model
- [x] Geographic context in research
- [x] Location-aware competitor analysis

### Growth & Opportunity Engine
- [x] Proactive opportunity identification
- [x] Opportunity ranking
- [x] Growth plan generation

### Improved Research
- [x] Research refresh capability
- [x] Research freshness indicators
- [x] Multiple research sessions per project

### PDF Improvement
- [x] Polished PDF with executive summary
- [x] Branded PDF (Business plan)
- [x] Source references section

### Referral System
- [x] Referral code generation
- [x] Referral tracking
- [x] Credit rewards on meaningful activation

---

## Phase 3 — GitHub Intelligence

**Target:** 4–6 weeks after Phase 2  
**Goal:** Repository analysis and code-connected recommendations

### GitHub Integration
- [x] Repository authorization (separate from login)
- [x] Repository read access
- [x] Repository structure analysis
- [x] Architecture detection
- [x] Dependency analysis
- [x] Technical debt identification
- [x] Security concern detection

### Code-Connected Recommendations
- [x] Link repository findings to Opportunity Graph
- [x] Improvement roadmap generation
- [x] Architecture review output

### GitHub Export
- [x] Create repository / branch isolation
- [x] Create branch (enforces zero force-push, zero commit to main)
- [x] Commit generated package (FORGE_BLUEPRINT, docs, roadmaps)
- [x] Push with confirmation and automated Pull Request link

### New Classification Support
- [x] SOFTWARE_OPTIMIZATION workflow
- [x] TECHNICAL_AUDIT workflow
- [x] SOFTWARE_REBUILD workflow

---

## Phase 4 — Continuous Intelligence

**Target:** 4–6 weeks after Phase 3  
**Goal:** Living workspaces with ongoing value

### Opportunity Graph
- [x] Visual graph implementation
- [x] Interactive node exploration
- [x] Evidence and source connections
- [x] Impact and difficulty indicators

### Continuous Monitoring
- [x] Competitor change detection
- [x] Market signal monitoring
- [x] Opportunity alerting
- [x] Research auto-refresh & weekly digest generation

### Notifications
- [x] In-app notification system (Continuous Intelligence badge & dropdown center)
- [x] Unread tracking & deduplication
- [x] Notification preferences & severity filtering

### Project Evolution
- [x] Version comparison (side-by-side specs and metrics delta comparator)
- [x] Decision history timeline (immutable event log of approvals & decisions)
- [x] Re-run any workflow stage (non-destructive auto-snapshot guarantee)
- [x] Track approved vs. current state

---

## Phase 5 — Teams & Enterprise

**Target:** 6–8 weeks after Phase 4  
**Goal:** Multi-user workspaces and enterprise features

### Organizations
- [x] Organization model
- [x] Team member management
- [x] Shared project workspaces
- [x] Shared credit pools

### Enterprise
- [x] SSO integration
- [x] Dedicated tenant isolation
- [x] Custom data retention
- [x] Audit log export
- [x] Enterprise billing

### API
- [x] Public API for intelligence engines
- [x] API key management
- [x] API rate limiting
- [x] API documentation

### BYOK
- [x] User-provided API key storage (encrypted)
- [x] BYOK routing in AI orchestrator
- [x] Credit adjustment for BYOK operations

---

## Phase 6 — Privacy, Data Governance, Portability & Self-Learning Intelligence

**Target:** Post-Phase 5  
**Goal:** Enterprise privacy compliance, GDPR/CCPA data portability, Right to Be Forgotten, and anonymous AI self-learning feedback loops

### Consent & Privacy Governance
- [x] Granular consent records model (`analytics`, `product_improvement`, `ai_improvement`, `marketing`)
- [x] Consent management service with explicit opt-in/opt-out controls
- [x] Immutable consent audit logging with IP tracking and policy versioning

### Data Portability & Rights
- [x] GDPR Article 20 / CCPA machine-readable JSON data archive export
- [x] Right to Be Forgotten account purge engine (GDPR Article 17)
- [x] Financial & ledger PII anonymization preserving accounting integrity

### Self-Learning Intelligence System
- [x] Zero-PII learning signals model (`learning_signals`)
- [x] Strict consent-gated telemetry ingestion
- [x] Deep PII scrubbing pipeline
- [x] Category acceptance metrics and recommendation weight boosters
- [x] Interactive blueprint feedback widgets (thumbs up/down)

---

## Phase 7 — Production Hardening, Launch Readiness & Operational Scale

**Target:** Post-Phase 6  
**Goal:** Enterprise resilience, asynchronous queue processing, security headers, E2E test suites, and container orchestration

### Asynchronous Queue Processing & Real-Time Progress
- [x] Dedicated workload queues (`ai`, `research`, `export`, `billing`, `default`)
- [x] `ExecuteStageJob` with credit failure recovery
- [x] Real-time polling `/projects/{project}/workflow/status`

### Testing Rigor
- [x] Vitest configuration (`vitest.config.ts`) and Vue component tests
- [x] Playwright E2E configuration (`playwright.config.ts`) and user journey specs
- [x] Full product lifecycle integration test (`FullProductJourneyTest.php`)

### Security Hardening
- [x] `SecurityHeadersMiddleware` (CSP, HSTS, X-Frame-Options, X-Content-Type-Options, Referrer-Policy)
- [x] Named rate limiters (`auth`, `ai`, `export`)
- [x] HMAC-signed temporary download URLs (30-min expiration)

### Production Deployment & Container Orchestration
- [x] Multi-stage `Dockerfile.production` (PHP 8.2-FPM Alpine + OPcache + Redis)
- [x] `docker-compose.prod.yml` and production Nginx configuration
- [x] Deep `/healthz` system diagnostic probe endpoint
- [x] Automated deployment scripts (`deploy.sh`, `deploy.ps1`)

---

## Phase 8 — Unified API Keys Management & Launch Cutover

**Target:** Final Launch Phase  
**Goal:** Unified developer & AI provider credentials management, admin integration console, and automated disaster recovery

### Unified API Keys Management
- [x] Unified User API Keys Page (`/settings/api-keys`) integrating Platform REST tokens and BYOK credentials
- [x] Clean tabbed interface with one-time secret reveals and scope controls
- [x] Deprecated `/settings/byok` redirected seamlessly to `/settings/api-keys?tab=byok`

### Admin Integrations & System Keys Dashboard
- [x] Superadmin API Keys Console (`/admin/api-keys`)
- [x] Real-time live connectivity testing and latency benchmarks for Anthropic, OpenAI, Gemini, Stripe, and GitHub
- [x] Masked credentials and environment variable auditing

### Disaster Recovery & Production Readiness
- [x] `.env.production.example` secrets template with production defaults
- [x] Point-in-time database and storage snapshot scripts (`backup.sh`, `backup.ps1`)
- [x] Structured JSON logging channel (`config/logging.php`)

---

## Success Milestones

| Milestone | Criteria |
|---|---|
| **Alpha** | Phase 1 complete; 10 test users; core workflow functional |
| **Beta** | Phases 1–2 complete; 100 users; billing active |
| **Public Launch** | Phases 1–3 complete; 500+ users; positive retention signals |
| **Growth** | Phase 4 complete; 2,000+ users; referral system active |
| **Scale** | Phase 5 complete; enterprise customers; API revenue |
| **Governance & Trust** | Phase 6 complete; GDPR/CCPA portability; self-learning AI engine |
| **Production Hardening** | Phase 7 complete; async workers; E2E tests; CSP; Docker orchestration |
| **Launch Readiness** | Phase 8 complete; unified API keys; admin console; backup automation; live-ready |

---

*Roadmap timelines are estimates. Phases may be adjusted based on user feedback, technical discoveries, and business priorities.*

