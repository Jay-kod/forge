# FORGE — SaaS & Business Model

**Document version:** 1.0  
**Date:** 2026-09-01  
**Status:** DRAFT — Awaiting review  
**Depends on:** [00-discovery.md](file:///c:/xampp/htdocs/1/f/docs/00-discovery.md), [01-prd.md](file:///c:/xampp/htdocs/1/f/docs/01-prd.md)

---

## 1. Business Model Overview

FORGE operates on a **Plans + Credits** hybrid model.

- **Plans** control feature access and define monthly credit allocations
- **Credits** control consumption of expensive AI operations
- Revenue comes from plan subscriptions and optional credit top-ups

---

## 2. Plan Tiers

### 2.1 Tier Definitions

| Attribute | Free | Pro | Business | Enterprise |
|---|---|---|---|---|
| **Price** | $0/mo | $39/mo | $99/mo | Custom |
| **Billing** | — | Monthly/Annual | Monthly/Annual | Annual contract |
| **Projects** | 1 active | Unlimited | Unlimited | Unlimited |
| **Credits/month** | 25 | 200 | 500 | Configurable |
| **Research depth** | Basic (limited sources) | Full (60+ sources) | Full + priority | Full + dedicated |
| **PRD generation** | Basic template | Full evidence-linked | Full + team review | Full + compliance |
| **Architecture** | Overview only | Complete | Complete + audit | Complete + enterprise patterns |
| **Dev package** | Master prompt only | Full package | Full + team config | Full + custom templates |
| **PDF export** | Watermarked | Clean | Clean + branded | Custom branding |
| **GitHub export** | — | Future | Future | Future |
| **Team members** | — | — | Up to 5 seats | Configurable |
| **API access** | — | — | Future | Future |
| **Support** | Community | Email | Priority | Dedicated |
| **Data isolation** | Shared | Shared | Shared | Dedicated tenant |

### 2.2 Annual Discount

Annual billing: 2 months free (effectively ~17% discount).

- Pro Annual: $390/year ($32.50/mo effective)
- Business Annual: $990/year ($82.50/mo effective)

---

## 3. Entitlement Architecture

### 3.1 Capability-Based Access Control

Never use `if (plan === "pro")` in application logic. Use capability/entitlement checks.

```
Entitlement: research.deep
    Free: false
    Pro: true
    Business: true
    Enterprise: true

Entitlement: project.create
    Free: limit(1)
    Pro: unlimited
    Business: unlimited
    Enterprise: unlimited

Entitlement: export.pdf.clean
    Free: false
    Pro: true
    Business: true
    Enterprise: true
```

### 3.2 Entitlement Registry (MVP)

| Entitlement Key | Free | Pro | Business | Enterprise |
|---|---|---|---|---|
| `project.create` | limit:1 | unlimited | unlimited | unlimited |
| `project.archive` | true | true | true | true |
| `research.basic` | true | true | true | true |
| `research.deep` | false | true | true | true |
| `research.priority` | false | false | true | true |
| `prd.generate` | true | true | true | true |
| `prd.evidence_linked` | false | true | true | true |
| `architecture.generate` | basic | full | full | full |
| `package.generate` | master_prompt_only | full | full | full |
| `export.pdf` | watermarked | clean | clean | branded |
| `export.package` | false | true | true | true |
| `export.github` | false | future | future | future |
| `workflow.automatic` | false | true | true | true |
| `workflow.page_by_page` | true | true | true | true |
| `credits.monthly` | 25 | 200 | 500 | configurable |
| `credits.purchase` | false | true | true | true |
| `credits.rollover` | false | false | false | true |
| `team.seats` | 0 | 0 | 5 | configurable |
| `admin.access` | false | false | false | true |
| `api.access` | false | false | future | future |

### 3.3 Entitlement Check Pattern

```
// Pseudocode — application pattern
function canPerform(user, capability) {
    entitlements = resolveEntitlements(user.subscription)
    return entitlements.check(capability)
}

// Usage
if (!canPerform(user, 'research.deep')) {
    return upgradePrompt('research.deep')
}
```

---

## 4. Credit System

### 4.1 Credit Architecture

Credits are a consumable resource tracked per user (and per organization for Business/Enterprise).

| Property | Design |
|---|---|
| **Unit** | 1 credit = 1 unit of AI consumption |
| **Allocation** | Monthly grant based on plan |
| **Accumulation** | Credits do NOT roll over (except Enterprise) |
| **Expiry** | Unused credits expire at billing cycle reset |
| **Purchase** | Pro and above can purchase additional credit packs |
| **Refund** | If an AI operation fails before producing output, credits are refunded |
| **Concurrency** | Credit reservation model — reserve before operation, confirm or refund after |

### 4.2 Credit Pricing (Preliminary)

| Credit Pack | Price | Per-Credit Cost |
|---|---|---|
| 50 credits | $5 | $0.10 |
| 200 credits | $18 | $0.09 |
| 500 credits | $40 | $0.08 |

### 4.3 Workload Credit Costs

| Operation | Workload Class | Credits |
|---|---|---|
| Situation classification | LIGHT | 1 |
| Basic question answering | LIGHT | 1 |
| Short summary/rewrite | LIGHT | 1 |
| Basic research | STANDARD | 5 |
| PRD generation | STANDARD | 10 |
| Architecture generation | STANDARD | 15 |
| Competitor analysis | DEEP | 15 |
| Deep market research | DEEP | 20 |
| Full development package | DEEP | 35 |
| Business strategy generation | DEEP | 20 |
| Repository analysis (future) | EXTREME | 30 |
| Full business/product audit (future) | EXTREME | 50 |

### 4.4 Credit Transaction Model

```
1. User initiates operation
2. System checks: credit_balance >= operation_cost
3. System RESERVES credits (deducted from available balance)
4. AI operation executes
5a. SUCCESS → reservation becomes confirmed consumption
5b. FAILURE → reservation is released (credits refunded)
5c. PARTIAL → credits consumed proportional to completed work
```

**Concurrency safety:** Use database-level locking or atomic operations to prevent double-spending when multiple operations overlap against the same balance.

### 4.5 Before Expensive Operations

Display estimated cost before execution:

```
Deep Market Analysis
Estimated: 20 credits
Your balance: 145 credits

[Proceed] [Cancel]
```

---

## 5. Subscription Lifecycle

### 5.1 State Machine

```
NONE → TRIALING → ACTIVE → PAST_DUE → CANCELED → EXPIRED
                     ↑                      ↓
                     └── REACTIVATED ←──────┘
```

### 5.2 Lifecycle Events

| Event | Action |
|---|---|
| **Signup** | Create user, set plan = Free, grant initial credits |
| **Subscribe (Pro/Business)** | Create subscription, update entitlements, grant credits |
| **Payment success** | Confirm billing period, reset/grant credits |
| **Payment failure** | Set PAST_DUE, send notification, maintain access for grace period |
| **Grace period expires** | Downgrade to Free entitlements, preserve data |
| **Cancel** | Set end date, maintain access until period end |
| **Period end (canceled)** | Downgrade to Free entitlements, preserve data |
| **Reactivate** | Restore subscription, restore entitlements, grant credits |
| **Upgrade** | Immediate entitlement upgrade, pro-rate billing, adjust credits |
| **Downgrade** | Schedule for end of current period, preserve current access until then |

### 5.3 Downgrade Rules

When downgrading to a lower plan:

1. **Projects:** Projects exceeding the new plan limit become read-only (not deleted)
2. **Data:** All project data is preserved
3. **Credits:** Current credits are consumed normally; new allocation takes effect at next cycle
4. **Features:** Restricted features become inaccessible; existing outputs remain viewable

**CRITICAL:** Never destroy user data on downgrade.

---

## 6. Billing Integration

### 6.1 Payment Provider

Stripe (recommended) or Paddle for international tax handling.

### 6.2 Webhook Processing

| Requirement | Implementation |
|---|---|
| **Idempotency** | Every webhook event processed at most once per event ID |
| **Source verification** | Verify webhook signature before processing |
| **Server-side truth** | Subscription state is determined only by billing events, never by client |
| **Retry handling** | Support Stripe's retry behavior without creating duplicate state |
| **Logging** | All billing events are logged with full payload (sensitive fields redacted) |

### 6.3 Webhook Events to Handle

| Event | Action |
|---|---|
| `checkout.session.completed` | Create/activate subscription |
| `invoice.paid` | Confirm billing period, grant credits |
| `invoice.payment_failed` | Set PAST_DUE, notify user |
| `customer.subscription.updated` | Update plan/entitlements |
| `customer.subscription.deleted` | Handle cancellation |

---

## 7. Billing Records

Maintain internal billing records independent of payment provider:

- **Subscriptions:** Plan, status, period, provider reference
- **Billing events:** Type, amount, currency, status, provider event ID
- **Credit transactions:** Grant, consumption, refund, expiry
- **Usage records:** Operation type, credits consumed, timestamp, project

---

## 8. Future Extensions

### 8.1 BYOK (Bring Your Own Key)

Not in V1. Architecture should accommodate:
- Users providing their own AI provider API keys
- Credits may be waived or reduced for BYOK operations
- Security: keys must be encrypted at rest, never logged, never shared

### 8.2 Organizations & Teams

Business plan introduces basic team features. Architecture should support:
- Organization entity owning multiple users
- Shared credit pool
- Shared projects
- Role-based access within organization

### 8.3 API Access

Future API for programmatic access to FORGE's intelligence engines. Usage would consume credits.

---

*Pricing, credit costs, and entitlements are preliminary. They must be validated against actual AI operation costs during development and calibrated before launch.*
