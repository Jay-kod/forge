# FORGE — Agent Operating Instructions

> **Read this file first.** Then read the documents it references in order.

---

## Identity

You are working on **FORGE** — Framework for Opportunity, Research, Growth & Execution.

FORGE is an intelligent product, business, growth, research, and engineering platform. It is NOT a chatbot, a PRD generator, or a landing-page project.

---

## Required Reading (In Order)

Before implementing any feature, read:

1. [`docs/04-architecture-essentials.md`](file:///c:/xampp/htdocs/1/f/docs/04-architecture-essentials.md) — **Every architectural rule in one place**
2. [`docs/01-prd.md`](file:///c:/xampp/htdocs/1/f/docs/01-prd.md) — What FORGE does and for whom
3. [`docs/03-architecture.md`](file:///c:/xampp/htdocs/1/f/docs/03-architecture.md) — Technical stack, module structure, database design
4. [`docs/06-development-workflow.md`](file:///c:/xampp/htdocs/1/f/docs/06-development-workflow.md) — How to implement features

---

## Core Rules

### Architecture

- **Module-based architecture** — every domain lives under `app/Modules/{ModuleName}/`
- **Thin controllers** — controllers validate input and delegate to Actions or Services
- **No circular dependencies** — module dependency graph is acyclic
- **Cross-module communication** via service contracts (interfaces), not direct model access

### Authorization

- **All authorization is server-side** — never trust the client
- **Every project query scoped to `user_id = auth()->id()`**
- **Entitlement checks use capability strings** — never `if (plan === 'pro')`
- **Use Laravel Policies** on all model access

### Credits

- **Reserve → Execute → Confirm/Release** — atomic credit operations
- **Show cost before expensive operations** — user sees estimated credits
- **Refund on failure** — if AI operation fails, release reserved credits
- **Row-level locking** on credit_accounts for concurrency safety

### AI

- **AI reasons over evidence** — not from nothing
- **Classify confidence** — every significant claim: Verified / Strongly Supported / Probable / Inferred / Assumption / Unknown / Conflicting
- **Never fabricate citations** — only cite URLs actually retrieved
- **Validate output structure** — schema validation before storage
- **Multi-provider abstraction** — route workloads, don't hard-code providers

### Data

- **Never destroy user data on downgrade** — read-only, not deleted
- **Never silently overwrite approved decisions** — version everything
- **Webhook processing is idempotent** — deduplicate by event ID
- **Billing state comes from server-side events only**

### Frontend

- **Design tokens in CSS custom properties** — no hard-coded values
- **Component-based** — reusable, single-responsibility components
- **Both dark and light mode** — independently designed
- **Mobile-first responsive design**
- **Accessible** — semantic HTML, keyboard nav, focus states, ARIA

### Security

- **Never commit secrets** — `.env`, API keys, tokens
- **Input validation on every request** — FormRequest classes
- **Rate limiting** on auth, AI, and export routes
- **CSRF protection** on all state-changing requests

---

## Development Workflow

For every implementation task:

1. Read the relevant requirement
2. Read Architecture Essentials
3. Inspect current code in the affected module
4. Plan the smallest correct change
5. Implement
6. Test (happy path + failure cases)
7. Review security and authorization
8. Review UI (both modes, mobile)
9. Commit with meaningful message

---

## Key Reference Documents

| Document | Purpose |
|---|---|
| [`docs/00-discovery.md`](file:///c:/xampp/htdocs/1/f/docs/00-discovery.md) | Market research and competitive positioning |
| [`docs/01-prd.md`](file:///c:/xampp/htdocs/1/f/docs/01-prd.md) | Product requirements, personas, workflows |
| [`docs/02-saas-business-model.md`](file:///c:/xampp/htdocs/1/f/docs/02-saas-business-model.md) | Plans, credits, entitlements, billing |
| [`docs/03-architecture.md`](file:///c:/xampp/htdocs/1/f/docs/03-architecture.md) | Stack, modules, database, AI providers |
| [`docs/04-architecture-essentials.md`](file:///c:/xampp/htdocs/1/f/docs/04-architecture-essentials.md) | All architectural rules (single source of truth) |
| [`docs/05-hard-questions.md`](file:///c:/xampp/htdocs/1/f/docs/05-hard-questions.md) | Edge cases and their solutions |
| [`docs/06-development-workflow.md`](file:///c:/xampp/htdocs/1/f/docs/06-development-workflow.md) | Implementation protocol and code standards |
| [`docs/07-testing-strategy.md`](file:///c:/xampp/htdocs/1/f/docs/07-testing-strategy.md) | Testing layers, patterns, organization |
| [`docs/08-security.md`](file:///c:/xampp/htdocs/1/f/docs/08-security.md) | Security requirements and checklist |
| [`docs/09-git-github.md`](file:///c:/xampp/htdocs/1/f/docs/09-git-github.md) | Git workflow, commit strategy, GitHub integration |
| [`docs/10-ai-system.md`](file:///c:/xampp/htdocs/1/f/docs/10-ai-system.md) | AI provider architecture, prompts, research |
| [`docs/11-privacy-data-learning.md`](file:///c:/xampp/htdocs/1/f/docs/11-privacy-data-learning.md) | Privacy, consent, learning system |
| [`docs/12-ui-design-system.md`](file:///c:/xampp/htdocs/1/f/docs/12-ui-design-system.md) | Colors, typography, components, accessibility |
| [`docs/13-product-roadmap.md`](file:///c:/xampp/htdocs/1/f/docs/13-product-roadmap.md) | Phased implementation plan |

---

## Before Making Changes

Ask yourself:

1. **Which module does this belong to?** If you're not sure, check the module map in architecture.md.
2. **Am I crossing module boundaries?** If yes, use service contracts.
3. **Does this need authorization?** Almost certainly yes. Add the check.
4. **Does this consume credits?** If AI is involved, add credit reservation.
5. **Am I contradicting an approved decision?** If yes, flag the conflict explicitly.
6. **Have I tested the failure case?** Not just the happy path.
