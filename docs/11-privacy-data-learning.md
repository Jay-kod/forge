# FORGE — Privacy, Data & Learning System

**Document version:** 1.0  
**Date:** 2026-09-01  
**Status:** DRAFT  

---

## 1. Privacy Principles

1. **Explicit consent** — never collect or process data beyond what the user agreed to
2. **Minimum necessary** — collect only what's needed for the requested functionality
3. **Transparency** — users can see what data is stored and how it's used
4. **User control** — users can export, delete, or restrict their data
5. **Isolation** — one customer's data is never exposed to another

---

## 2. Consent Model

### 2.1 Consent Types

| Consent Type | Purpose | Default | Required for Service |
|---|---|---|---|
| **Analytics** | Track feature usage, page views, flow completion | Opt-in | No |
| **Product improvement** | Use anonymized usage patterns to improve FORGE | Opt-in | No |
| **AI/Model improvement** | Use anonymized behavioral signals to improve AI quality | Opt-in | No |
| **Marketing** | Receive product updates, tips, announcements | Opt-in | No |

### 2.2 Consent Properties

Every consent record must be:
- **Explicit** — user actively grants consent (no pre-checked boxes)
- **Versioned** — consent text has a version number
- **Timestamped** — when consent was granted
- **Auditable** — full history preserved
- **Revocable** — user can revoke at any time (where legally and technically feasible)

### 2.3 Consent Storage

```sql
consent_records
    id
    user_id
    consent_type        -- 'analytics', 'product_improvement', 'ai_improvement', 'marketing'
    granted             -- true/false
    version             -- consent version (e.g., '1.0')
    ip_address
    granted_at
    revoked_at          -- NULL if still active
    created_at
```

---

## 3. Data Classification

| Data Category | Examples | Storage | Retention | Sharing |
|---|---|---|---|---|
| **Identity** | Name, email, avatar | Database (encrypted email) | Account lifetime | Never shared |
| **Authentication** | OAuth tokens, session IDs | Database (encrypted) / Redis | Token lifetime | Never shared |
| **Project data** | PRDs, architecture, research findings | Database | Account lifetime (or until deleted) | Never shared cross-user |
| **Research results** | Market data, competitor info, sources | Database | Project lifetime | Public data; sources are attributed |
| **Billing** | Subscription status, credit balance | Database | Legal retention period | Stripe only |
| **Payment details** | Card numbers, bank info | Never stored (Stripe handles) | N/A | Stripe only |
| **Usage data** | Feature usage, flow completion | Database (anonymized) | 24 months | Aggregated only, if consented |
| **AI operation data** | Token counts, latency, model used | Database | 12 months | Never shared |
| **Learning signals** | Accepted/rejected recommendations | Database (anonymized) | Indefinite (aggregated) | Never shared raw |

---

## 4. Learning System

### 4.1 What FORGE Learns

FORGE improves by learning from **aggregated, anonymized behavioral patterns**, not from individual user data.

| Signal | What It Tells Us | Privacy Level |
|---|---|---|
| Recommendation acceptance rate by category | Which recommendation types are most useful | Fully anonymized aggregate |
| Recommendation rejection rate by category | Which recommendation types need improvement | Fully anonymized aggregate |
| Common user edits to generated PRDs | Where AI output consistently needs correction | Anonymized edit patterns |
| Workflow abandonment points | Where users lose interest or get confused | Fully anonymized aggregate |
| Classification accuracy (user corrections) | Whether situation classification is working | Anonymized correction data |
| Research quality feedback | Whether research is accurate and useful | Anonymized thumbs up/down |
| Stage regeneration frequency | Which stages produce unsatisfactory output | Fully anonymized aggregate |

### 4.2 Learning Boundaries

**FORGE WILL:**
- Learn that "marketplace" recommendations are frequently rejected → improve marketplace-related advice
- Learn that users often edit the "target persona" section of generated PRDs → improve persona generation
- Learn that users in a specific market segment have different needs → adapt recommendations

**FORGE WILL NOT:**
- Use User A's private project data to improve User B's experience directly
- Store identifiable project content as training data
- Send user project content to AI providers for model training
- Expose one customer's competitive research to another customer
- Make learning signals reversible to individual users

### 4.3 Distinction

```
PRIVATE PROJECT DATA                    AGGREGATED LEARNING SIGNALS
─────────────────────                   ──────────────────────────
"Adaeze's laundry app PRD"             "78% of users accept competitor analysis"
"Marcus's competitor list"              "Users edit persona section 45% of the time"
"Folake's revenue numbers"             "Market expansion recommendations have 
                                        highest acceptance in BUSINESS_GROWTH"
```

---

## 5. Data Access & Rights

### 5.1 User Rights

| Right | Implementation |
|---|---|
| **Access** | Users can view all their stored data through account settings |
| **Export** | Users can export all project data as JSON/ZIP |
| **Deletion** | Users can delete individual projects or their entire account |
| **Correction** | Users can edit their profile and project data |
| **Restriction** | Users can revoke consent for specific data processing |
| **Portability** | Data export in standard formats (JSON, Markdown) |

### 5.2 Account Deletion

When a user requests account deletion:
1. All projects and project data are permanently deleted
2. All research results for the user are deleted
3. All credit and billing records are anonymized (retain for accounting, remove PII)
4. OAuth tokens are revoked and deleted
5. Consent records are retained (anonymized) for legal compliance
6. Aggregated learning signals already generated are not affected (they're not reversible to the user)

---

## 6. Third-Party Data Sharing

### 6.1 AI Providers

| Provider | Data Sent | Terms |
|---|---|---|
| Anthropic | Project context needed for AI operation | Subject to Anthropic's API data usage policy |
| OpenAI | Project context needed for AI operation | Subject to OpenAI's API data usage policy |
| Google | Project context needed for AI operation | Subject to Google's API data usage policy |

**Requirement:** Use API providers' business/enterprise tiers that do NOT use customer data for model training. Verify data usage terms before adding new providers.

### 6.2 Stripe

Only billing-related data (email, subscription status, payment events). No project content.

### 6.3 Analytics (If Consented)

Anonymized usage analytics only, if user has consented to analytics tracking.

---

## 7. Enterprise Privacy (Future)

For enterprise customers:
- **Dedicated tenant isolation** — separate database or schema
- **Data residency** — option to specify geographic data storage
- **No learning signal contribution** — enterprise data excluded from aggregated learning
- **Custom data retention policies**
- **Audit logging** with export capability
- **SSO integration** — no FORGE-managed credentials

---

## 8. Legal Requirements (Placeholders)

The following legal documents must be created with appropriate legal counsel before launch:

- [ ] Privacy Policy
- [ ] Terms of Service
- [ ] Cookie Policy (if applicable)
- [ ] Data Processing Agreement (for enterprise)
- [ ] Subprocessor List

**Important:** This technical document does not constitute legal advice. All privacy and data handling practices must be reviewed by qualified legal counsel before public launch.

---

*Privacy and learning architecture will be refined based on legal review and regulatory requirements applicable to the deployment jurisdiction.*
