# FORGE — Product Roadmap

**Document version:** 1.0  
**Date:** 2026-09-01  
**Status:** DRAFT  

---

## Phase 1 — Foundation (MVP)

**Target:** 8–12 weeks  
**Goal:** Working intelligence loop for new product ideas

### Core Infrastructure
- [ ] Laravel project scaffold with module architecture
- [ ] Docker development environment (Sail)
- [ ] Database migrations for all MVP tables
- [ ] Authentication (Google + GitHub OAuth)
- [ ] Session management
- [ ] Basic routing and Inertia setup

### AI System
- [ ] AI provider abstraction layer
- [ ] Anthropic provider implementation
- [ ] OpenAI provider implementation
- [ ] Workload router (LIGHT/STANDARD/DEEP)
- [ ] AI output validation
- [ ] Provider fallback logic

### Credit System
- [ ] Credit account model
- [ ] Credit reservation / consumption / refund
- [ ] Concurrent access safety (row-level locking)
- [ ] Credit balance display
- [ ] Cost estimation before operations

### Billing System
- [ ] Plan and entitlement models
- [ ] Entitlement service (capability checks)
- [ ] Stripe integration (checkout, webhooks)
- [ ] Subscription lifecycle (create, cancel, upgrade, downgrade)
- [ ] Webhook idempotency
- [ ] Free plan provisioning on signup

### Project System
- [ ] Project creation ("What are you trying to achieve?")
- [ ] Situation classification engine
- [ ] Project context model
- [ ] User understanding engine (basic)
- [ ] Project dashboard (list, view, archive)
- [ ] Project ownership authorization

### Intelligence Loop
- [ ] Workflow engine (stage management)
- [ ] Dual mode (Automatic + Page-by-Page)
- [ ] Stage: Understanding
- [ ] Stage: Existence & Opportunity Discovery
- [ ] Stage: Competitor Analysis
- [ ] Stage: Market Research
- [ ] Stage: Challenge & Recommendation
- [ ] Stage: PRD Generation
- [ ] Stage: Architecture Generation
- [ ] Stage: Development Package Generation

### Research System
- [ ] Web research integration
- [ ] Source collection with metadata
- [ ] Source reliability classification
- [ ] Evidence model with confidence levels
- [ ] Evidence-source linking

### Export System
- [ ] PDF generation (basic)
- [ ] AI development package download (ZIP)
- [ ] Master prompt copy-to-clipboard

### UI
- [ ] Design system implementation (Tailwind config + tokens)
- [ ] Light mode + Dark mode
- [ ] Application shell (sidebar, navigation)
- [ ] Authentication pages
- [ ] Dashboard
- [ ] Project creation flow
- [ ] Workflow stage views
- [ ] Document viewer
- [ ] Responsive design

### Admin
- [ ] Basic admin panel (users, plans, credit overview)

### Testing
- [ ] Authorization tests
- [ ] Entitlement tests
- [ ] Credit concurrency tests
- [ ] Webhook idempotency tests
- [ ] AI orchestration tests (with mocks)
- [ ] Core workflow tests

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
- [ ] Repository authorization (separate from login)
- [ ] Repository read access
- [ ] Repository structure analysis
- [ ] Architecture detection
- [ ] Dependency analysis
- [ ] Technical debt identification
- [ ] Security concern detection

### Code-Connected Recommendations
- [ ] Link repository findings to Opportunity Graph
- [ ] Improvement roadmap generation
- [ ] Architecture review output

### GitHub Export
- [ ] Create repository
- [ ] Create branch
- [ ] Commit generated package
- [ ] Push with confirmation

### New Classification Support
- [ ] SOFTWARE_OPTIMIZATION workflow
- [ ] TECHNICAL_AUDIT workflow
- [ ] SOFTWARE_REBUILD workflow

---

## Phase 4 — Continuous Intelligence

**Target:** 4–6 weeks after Phase 3  
**Goal:** Living workspaces with ongoing value

### Opportunity Graph
- [ ] Visual graph implementation
- [ ] Interactive node exploration
- [ ] Evidence and source connections
- [ ] Impact and difficulty indicators

### Continuous Monitoring
- [ ] Competitor change detection
- [ ] Market signal monitoring
- [ ] Opportunity alerting
- [ ] Research auto-refresh

### Notifications
- [ ] In-app notification system
- [ ] Email notifications (configurable)
- [ ] Notification preferences

### Project Evolution
- [ ] Version comparison
- [ ] Decision history timeline
- [ ] Re-run any workflow stage
- [ ] Track approved vs. current state

---

## Phase 5 — Teams & Enterprise

**Target:** 6–8 weeks after Phase 4  
**Goal:** Multi-user workspaces and enterprise features

### Organizations
- [ ] Organization model
- [ ] Team member management
- [ ] Shared project workspaces
- [ ] Shared credit pools

### Enterprise
- [ ] SSO integration
- [ ] Dedicated tenant isolation
- [ ] Custom data retention
- [ ] Audit log export
- [ ] Enterprise billing

### API
- [ ] Public API for intelligence engines
- [ ] API key management
- [ ] API rate limiting
- [ ] API documentation

### BYOK
- [ ] User-provided API key storage (encrypted)
- [ ] BYOK routing in AI orchestrator
- [ ] Credit adjustment for BYOK operations

---

## Success Milestones

| Milestone | Criteria |
|---|---|
| **Alpha** | Phase 1 complete; 10 test users; core workflow functional |
| **Beta** | Phases 1–2 complete; 100 users; billing active |
| **Public Launch** | Phases 1–3 complete; 500+ users; positive retention signals |
| **Growth** | Phase 4 complete; 2,000+ users; referral system active |
| **Scale** | Phase 5 complete; enterprise customers; API revenue |

---

*Roadmap timelines are estimates. Phases may be adjusted based on user feedback, technical discoveries, and business priorities.*
