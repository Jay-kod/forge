# FORGE — Architecture Essentials

**Document version:** 1.0  
**Date:** 2026-09-01  
**Status:** DRAFT — Living document, updated as decisions are made  
**Depends on:** [03-architecture.md](file:///c:/xampp/htdocs/1/f/docs/03-architecture.md)

---

## Purpose

This document captures every architectural decision, constraint, and rule that any developer (human or AI) must follow when working on FORGE. It is the single source of truth for "how we build things here."

---

## 1. Stack Decisions

| Decision | Choice | Rationale | Decided |
|---|---|---|---|
| Backend framework | Laravel 12.x | Mature SaaS ecosystem, queues, events, policies, testing | 2026-09-01 |
| Frontend framework | Vue 3.5+ (Composition API) | Pairs with Inertia.js, reactive, TypeScript support | 2026-09-01 |
| Server-client bridge | Inertia.js | Eliminates API-first overhead for V1; server-driven routing | 2026-09-01 |
| Styling | Tailwind CSS 4.x | Design tokens via CSS custom properties, responsive | 2026-09-01 |
| Database | MySQL 8.4+ | Relational integrity for billing/credits, JSON support | 2026-09-01 |
| Cache/Queue backend | Redis 7.x | Queue driver, rate limiting, session/cache — justified uses only | 2026-09-01 |
| Build tooling | Vite 6.x | Fast HMR, native Laravel integration | 2026-09-01 |
| Local development | Docker via Laravel Sail | Consistent environments | 2026-09-01 |
| Testing | PHPUnit + Vitest + Playwright | Backend + frontend unit + E2E | 2026-09-01 |
| Payment provider | Stripe | Mature, webhook-driven, global coverage | 2026-09-01 |
| AI providers | Multi-provider (Anthropic, OpenAI, Google) | Avoid single-provider lock-in | 2026-09-01 |

---

## 2. Module Rules

1. **Every domain has its own module** under `app/Modules/`
2. **Modules contain:** Models, Actions, Services, Policies, Requests, Events, Enums, DTOs, Tests
3. **Controllers are thin** — they validate input, call an Action or Service, return a response
4. **No circular module dependencies** — dependency graph is acyclic
5. **Cross-module communication** uses service contracts (interfaces), not direct model access
6. **Module changes should be isolated** — changing competitor analysis should not require editing billing, auth, or export

---

## 3. Authorization Rules

1. **All authorization is server-side** — never trust the client
2. **Every project access checks ownership** — `user_id === auth()->id()`
3. **Every feature access checks entitlements** — `EntitlementService::can($user, 'capability')`
4. **Never use `if (plan === 'pro')`** — always use capability checks
5. **Policies gate model access** — `$this->authorize('view', $project)`
6. **Admin routes are middleware-protected** — `role:admin`

---

## 4. Credit Rules

1. **Reserve before consuming** — atomic reservation prevents race conditions
2. **Refund on failure** — if AI operation fails before producing useful output, release reserved credits
3. **Show cost before execution** — user sees estimated credits before expensive operations
4. **Credits do not roll over** (except Enterprise) — monthly allocation resets
5. **Credit operations use database transactions** with row-level locking on `credit_accounts`

---

## 5. AI Rules

1. **AI is not the sole source of intelligence** — evidence, context, and structured rules come first
2. **Never present AI speculation as verified fact** — use the Evidence & Confidence classification
3. **Route workloads by complexity** — cheap models for LIGHT, strong models for DEEP/EXTREME
4. **Always have a fallback provider** — if primary provider fails, route to alternative
5. **Validate AI output structure** — parse and validate before storing or displaying
6. **Log all AI operations** — request type, model used, tokens consumed, latency, success/failure
7. **Never log prompt content containing user private data** to third-party services

---

## 6. Research Rules

1. **Every major claim requires a source** — URL, source type, retrieval date
2. **Never fabricate citations** — if research fails, say "insufficient data"
3. **Source reliability is classified** — Official > Government > Research > Publication > Industry > Community > Weak
4. **Stale sources are flagged** — research older than 90 days shows a freshness warning
5. **Conflicting sources are presented as conflicting** — don't silently pick one

---

## 7. Data Rules

1. **Never destroy user data on downgrade** — projects become read-only, not deleted
2. **Never silently overwrite approved decisions** — versioning preserves previous states
3. **Sensitive fields are encrypted** — OAuth tokens, API keys
4. **Billing state comes from server-side events** — never from client assertions
5. **Webhook processing is idempotent** — deduplicate by event ID

---

## 8. Frontend Rules

1. **Components have single responsibilities** — no "god components"
2. **Design tokens live in CSS custom properties** — never hard-code colors, spacing, fonts
3. **Both dark and light mode are independently designed** — dark mode is not inverted light mode
4. **Mobile-first responsive design** — desktop adapts up, not down
5. **Accessibility is required** — semantic HTML, keyboard navigation, focus states, ARIA labels
6. **Loading states are explicit** — every async operation shows appropriate feedback
7. **Error states are designed** — not just console errors

---

## 9. Git Rules

1. **Never commit secrets** — `.env`, API keys, OAuth credentials
2. **Meaningful commit messages** — describe the *what* and *why*
3. **Logical commits** — one commit per logical change, not one giant commit
4. **Never force-push without explicit authorization**
5. **`.gitignore` is maintained** — vendor, node_modules, .env, storage/logs, compiled assets

---

## 10. Definition of Done

A feature is complete only when:

- [ ] Requirement is satisfied
- [ ] Correct module architecture is followed
- [ ] Input validation exists (FormRequest)
- [ ] Authorization is verified (Policy + Entitlement)
- [ ] Error states are handled
- [ ] Edge cases are considered
- [ ] Tests pass (unit + relevant integration)
- [ ] UI is verified (both modes)
- [ ] Mobile layout is verified
- [ ] Accessibility is considered
- [ ] Security review is done
- [ ] Documentation is updated if needed
- [ ] Git diff is reviewed

---

## 11. Open Decisions (Pending)

| Decision | Options | Status |
|---|---|---|
| Real-time communication | Polling vs. Laravel Reverb WebSocket | V1 uses polling; V2 evaluates Reverb |
| PDF generation library | DomPDF vs. Browsershot vs. external service | Research needed |
| File storage driver | Local vs. S3 | Local for V1; S3 when deployment requires |
| Deployment target | VPS vs. Laravel Forge vs. Railway vs. Fly.io | Deferred until pre-launch |
| Primary AI provider for DEEP workloads | Claude Opus vs. GPT-4-turbo | Test during development |

---

*This document is updated whenever an architectural decision is made, revised, or discovered through the Hard Questions process.*
