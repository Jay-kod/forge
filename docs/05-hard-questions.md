# FORGE — Hard Questions

**Document version:** 1.0  
**Date:** 2026-09-01  
**Status:** DRAFT — Awaiting review  
**Depends on:** [03-architecture.md](file:///c:/xampp/htdocs/1/f/docs/03-architecture.md), [04-architecture-essentials.md](file:///c:/xampp/htdocs/1/f/docs/04-architecture-essentials.md)

---

## Purpose

Before building, actively attack the design. Every question here must have a concrete answer that feeds back into Architecture Essentials.

---

## 1. AI Failures

### Q: What happens when an AI provider is completely down?

**Answer:** The AIOrchestrator routes to a fallback provider. Provider health is checked via `isAvailable()` before routing. If all providers are down, the operation is queued with exponential backoff (max 3 retries over 15 minutes). User sees: "This operation is temporarily delayed. We'll notify you when it completes." Reserved credits are held during retry; released after final failure.

**Architecture impact:** AIOrchestrator must maintain a provider priority list per workload class. Health check results are cached in Redis (TTL: 60s).

### Q: What happens if an AI operation fails halfway through generation?

**Answer:** Partial output is saved if it passes structural validation. User sees partial result with a "Generation incomplete — retry?" option. Credits are consumed proportionally: if 60% of output was usable, ~60% of credits are consumed, remainder refunded.

**Architecture impact:** AI operations must produce checkpointed output where possible. CreditService needs a `partialConsume(reservation, percentage)` method.

### Q: What happens when AI output is invalid or incoherent?

**Answer:** AI output is validated against expected JSON schema or document structure before storage. Invalid output triggers one automatic retry with a modified prompt. If retry also fails, user sees: "We couldn't generate a reliable result for this section. This may happen with unusual or very broad topics." No credits consumed for fully invalid output.

**Architecture impact:** Every AI operation type defines a validation schema. AIOrchestrator wraps execution in a validate-retry loop.

### Q: How do we handle AI hallucination?

**Answer:** FORGE's design inherently mitigates this:
1. Research Engine provides real sources — AI reasons *over* evidence, not from nothing
2. Evidence classification (Verified → Assumption) makes confidence visible
3. Source links are verifiable — users can check claims
4. For competitor data, FORGE cross-references multiple sources before stating facts

**Architecture impact:** PRD and architecture generation prompts must include instructions to separate evidence-based claims from inferences. Output format includes `confidence_level` per major assertion.

---

## 2. Research Failures

### Q: What happens when research sources conflict?

**Answer:** Conflicting information is presented as conflicting with both sources cited. The Evidence system assigns `confidence: 'conflicting'` and shows both positions. FORGE does not silently pick one.

Example display: "Pricing data conflicts — Source A reports $29/mo, Source B reports $49/mo. Both sources are cited below."

### Q: What happens if web research returns no results?

**Answer:** Sections that rely on external data show: "Limited public information available for this topic." The evidence confidence drops to `assumption` or `unknown`. The workflow continues but flags reduced confidence. Credits are consumed at a reduced rate (50%) since the operation executed but found limited data.

### Q: How are stale sources handled?

**Answer:** Every research source has a `retrieved_at` timestamp. Sources older than 90 days display a "Research may be outdated" indicator. Users can trigger a research refresh (costs additional credits). The system never silently uses old data as if it were current.

---

## 3. Credit Edge Cases

### Q: What happens when credits run out mid-workflow?

**Answer:** The current stage completes (credits were already reserved). The next stage that requires credits shows: "You need X credits to continue. [Purchase credits] [Upgrade plan]". The workflow pauses but does not lose progress. Users can resume after acquiring credits.

### Q: What happens if two requests try to consume the final credits simultaneously?

**Answer:** Credit reservation uses `SELECT ... FOR UPDATE` with database-level row locking. The first transaction to acquire the lock reserves the credits. The second transaction sees insufficient balance and returns an "Insufficient credits" error. This is tested with a concurrent credit consumption test.

### Q: What happens if an AI operation fails after credits are reserved?

**Answer:** Credits are in `reservation` status. On failure, the reservation is released back to balance. On timeout (5 minutes with no confirmation or release), a cleanup job automatically releases orphaned reservations. All transitions are logged in `credit_transactions`.

---

## 4. Billing Edge Cases

### Q: Duplicate webhook arrives from Stripe?

**Answer:** The `billing_events` table has a UNIQUE constraint on `stripe_event_id`. Duplicate webhooks are detected and ignored (return 200 OK without processing). All webhook handlers are idempotent.

### Q: Payment succeeds but webhook arrives late?

**Answer:** The system continues operating based on current subscription state. When the webhook arrives (possibly hours later), it updates the billing record. If the user's subscription had been temporarily marked `past_due`, the webhook corrects it to `active`. No credit grants are missed because they're tied to the `invoice.paid` event.

### Q: Payment fails?

**Answer:** Subscription enters `past_due`. User is notified via email and in-app banner. Grace period: 7 days. During grace period, user retains current entitlements. After grace period, entitlements downgrade to Free level. Data is preserved. User can reactivate by updating payment method.

### Q: Upgrade/downgrade during active AI workload?

**Answer:** Active operations complete with the entitlements that were in effect when the operation started. Entitlement changes take effect for the *next* operation. This prevents mid-operation confusion. Upgrade credit bonuses are prorated and applied immediately.

---

## 5. GitHub Integration Edge Cases (Future — Design Now)

### Q: GitHub token expires during operation?

**Answer:** Operations that require GitHub access check token validity before starting. If token expires mid-operation, the operation pauses and prompts the user to re-authenticate. Partial results are saved.

### Q: User revokes GitHub permissions after repository is connected?

**Answer:** On next access attempt, the system detects revoked permissions. Repository connection is marked as `disconnected`. Repository analysis data is preserved but marked as "connection lost — reconnect to refresh." User is prompted to re-authorize.

### Q: What if the repository is deleted on GitHub?

**Answer:** API calls return 404. FORGE marks the repository connection as `not_found`. Existing analysis data is preserved with a "Repository no longer accessible" notice.

### Q: Concurrent writes to the same repository?

**Answer:** FORGE creates branches, never writes to main/master directly. Commits include unique identifiers. If a push fails due to conflicts, user is notified with options to resolve.

---

## 6. Security Edge Cases

### Q: Can User A see User B's project?

**Answer:** No. Every project query includes `WHERE user_id = auth()->id()`. Project policies enforce ownership. API routes and Inertia controllers both check ownership before returning data. This is covered by automated tests.

### Q: Can client-side requests bypass entitlements?

**Answer:** No. Entitlement checks happen server-side in middleware and service methods. The client receives entitlement state for UI display (show/hide features), but the server independently validates every operation. A user who manipulates the client to show "Pro" features will be blocked server-side.

### Q: Can OAuth be abused?

**Answer:** 
- CSRF protection on OAuth initiation
- State parameter validation on OAuth callback
- Rate limiting on auth endpoints (5 attempts per minute per IP)
- Account linking verifies email ownership
- One user per provider ID (prevents duplicate accounts)

### Q: Can uploaded files contain malicious content?

**Answer (future — file uploads not in MVP):**
- File type validation (whitelist: .md, .json, .txt, .pdf, .zip)
- File size limits (10MB per file, 50MB per project)
- Files stored outside web root
- Virus scanning for uploaded archives
- Files served through authenticated, signed URLs

---

## 7. Scale Questions

### Q: What happens at 1,000 users?

**Answer:** Single server with current architecture handles this comfortably. MySQL, Redis, and queue workers run on the same machine. Estimated database size: <1GB. AI API costs are the primary scaling concern, not infrastructure.

### Q: What happens at 10,000 users?

**Answer:** May need:
- Dedicated database server (separate from application)
- Increased queue workers (especially for AI operations)
- Redis on dedicated instance
- Consider read replica if query load is high
- CDN for static assets

### Q: What happens at 100,000 users?

**Answer:** Requires:
- Horizontal application scaling (load balancer + multiple app servers)
- Database read replicas
- Dedicated queue infrastructure
- AI request queuing and rate management per provider
- Full CDN
- Monitoring and alerting infrastructure

### Q: Which infrastructure is unnecessary too early?

**Answer:** Do NOT add before justified:
- Kubernetes / container orchestration
- Microservices architecture
- Event sourcing
- GraphQL
- Full-text search engine (Meilisearch/Elasticsearch)
- Multi-region deployment
- Real-time WebSocket infrastructure (polling works for V1)

---

## 8. Product Edge Cases

### Q: What if users don't understand the workflow?

**Answer:** 
1. The initial prompt ("What are you trying to achieve?") is natural language, not form-based
2. Automatic mode handles everything without user needing to understand stages
3. Page-by-page mode explains each stage before asking for review
4. Contextual help explains why each stage matters
5. Non-technical users receive simpler explanations (based on User Understanding Engine classification)

### Q: What if research overwhelms the user?

**Answer:** Research results are layered:
- Level 1: Summary paragraph with key insight
- Level 2: Detailed findings with evidence indicators ("Sources · 5")
- Level 3: Full source list with URLs and confidence

Users see Level 1 by default and can drill down. The UI does not dump raw research.

### Q: What if FORGE's recommendations are wrong?

**Answer:** 
1. Every recommendation shows its confidence level and evidence basis
2. Users can reject recommendations with optional feedback
3. Rejected recommendation patterns feed into the learning system (anonymized)
4. The system explicitly says "This is a recommendation, not a guarantee" where appropriate
5. Users can regenerate any stage for a fresh analysis

### Q: How do we communicate uncertainty?

**Answer:** Through the Evidence & Confidence system:
- Visual confidence indicators (not buried in text)
- "Sources · N" count visible on major conclusions
- Explicit "Low confidence" / "Limited data" warnings
- Never present uncertain conclusions with the same visual weight as verified ones

---

## 9. Decisions Fed Back to Architecture Essentials

| Decision | Rule | Source |
|---|---|---|
| Credit reservation model | Reserve → Execute → Confirm/Release | Credit Edge Cases |
| Orphaned reservation cleanup | Background job releases after 5 min timeout | Credit Edge Cases |
| Row-level locking for credits | `SELECT ... FOR UPDATE` on credit_accounts | Concurrent Credit Consumption |
| Webhook idempotency | UNIQUE constraint on stripe_event_id | Billing Edge Cases |
| Grace period for payment failure | 7 days at current entitlements | Payment Failure |
| AI output validation | Schema validation + one automatic retry | AI Failures |
| Conflicting evidence display | Show both positions, label as 'conflicting' | Research Failures |
| Layered research display | 3 levels of detail, summary default | Product Edge Cases |
| Scale infrastructure decisions | Defer until justified by load metrics | Scale Questions |

---

*Every answer in this document that reveals an architectural requirement has been cross-referenced to Architecture Essentials for implementation.*
