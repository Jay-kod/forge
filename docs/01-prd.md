# FORGE — Product Requirements Document (PRD)

**Document version:** 1.0  
**Date:** 2026-09-01  
**Status:** DRAFT — Awaiting review  
**Depends on:** [00-discovery.md](file:///c:/xampp/htdocs/1/f/docs/00-discovery.md)

---

## 1. Problem Statement

People with ideas, businesses, products, codebases, and growth objectives are forced through a fragmented, expensive, and unreliable workflow to make progress.

They currently must:

1. **Guess** whether their idea is viable — or pay for a one-shot validation report that may be LLM-simulated
2. **Manually research** competitors, markets, and opportunities across multiple tools
3. **Hire consultants** or rely on AI chatbots for strategic advice that lacks traceable evidence
4. **Switch between 5–8 disconnected tools** to go from understanding to execution
5. **Start from scratch** every time they revisit their project — no continuity, no living workspace
6. **Accept AI-generated output as fact** because no tool clearly separates evidence from interpretation from recommendation

The result: wasted time, poor decisions, premature building, missed opportunities, and expensive backtracking.

---

## 2. Product Vision

FORGE is an intelligence platform where a person arrives with a situation — an idea, a business, a codebase, a growth objective, or uncertainty — and leaves with clarity, evidence, a recommended direction, and the right actionable output.

**Core promise:** DISCOVER WHAT'S POSSIBLE.

FORGE does not ask "What do you want the AI to do?" It asks "What are you trying to achieve?" and then does the thinking.

---

## 3. Target Personas

### 3.1 Primary Persona — The Technical Founder

**Name archetype:** Adaeze  
**Profile:** Software developer (3–8 years experience) with product ideas, side projects, or a small startup. Technically strong but lacks structured product thinking, market research capability, and strategic methodology.

**Jobs to be done:**
- Validate whether my idea is worth building before I write code
- Understand who my competitors are and what they're actually doing
- Get a structured architecture and PRD that I can hand to Cursor/Claude for implementation
- Stop building things nobody wants

**Pain points:**
- Wastes weekends building products with no market
- Doesn't know how to do proper market research
- Reads competitor landing pages but can't assess their real positioning
- Writes code before writing a spec

**FORGE delivers:** Evidence-backed validation → Competitor analysis → PRD → Architecture → Development-ready package → Continuous opportunity monitoring

---

### 3.2 Secondary Persona — The Non-Technical Founder

**Name archetype:** Marcus  
**Profile:** Business-oriented founder with domain expertise but no development experience. Has ideas and possibly an existing business. Considers himself a "vibe coder" — uses Lovable/Bolt.new to prototype.

**Jobs to be done:**
- Understand whether my idea can work as a business
- Get a clear specification that I can give to a developer or AI tool
- Avoid getting scammed by dev agencies because I don't understand technical requirements
- Find growth opportunities for my existing business

**Pain points:**
- Can't distinguish good architecture from bad architecture
- Pays for development work he can't evaluate
- Doesn't know what he doesn't know
- Overwhelmed by technical choices

**FORGE delivers:** Situation understanding → Market research → Strategy → Simplified PRD → Architecture decisions explained → Master prompt for AI coding tools → Growth recommendations

---

### 3.3 Secondary Persona — The Business Owner

**Name archetype:** Folake  
**Profile:** Owns a running business (5–50 employees). Has a website, possibly has software, possibly has manual processes. Wants to grow, digitize, or improve.

**Jobs to be done:**
- Find new revenue opportunities I'm not seeing
- Understand why my website isn't converting
- Digitize manual processes
- Expand to a new market
- Get a technology roadmap that doesn't require me to become a developer

**Pain points:**
- Existing validation tools are designed for "new ideas," not existing businesses
- Doesn't know if her technology is holding her back
- Gets generic advice that ignores her specific market and geography
- Has no way to continuously monitor competitors and opportunities

**FORGE delivers:** Business analysis → Website audit → Competitor tracking → Geographic intelligence → Digital transformation plan → Growth roadmap → Continuous opportunity alerts

---

### 3.4 Secondary Persona — The Product Manager

**Name archetype:** Daniel  
**Profile:** PM at a mid-size company. Responsible for product strategy, PRDs, and coordinating with engineering. Uses ChatPRD and Notion AI but wants deeper research backing.

**Jobs to be done:**
- Generate PRDs that are backed by actual market evidence, not my assumptions
- Audit our existing product architecture against best practices
- Identify feature opportunities based on competitor gaps
- Create specifications that engineering teams can actually follow

**Pain points:**
- PRD generators accept whatever he writes without challenging assumptions
- No tool connects market research to product specifications
- Competitor analysis is manual and time-consuming
- Architecture decisions are made without proper context

**FORGE delivers:** Research-backed PRDs → Competitor gap analysis → Evidence-linked feature recommendations → Architecture review → Connected Opportunity Graph

---

### 3.5 Tertiary Persona — The Student / Aspiring Entrepreneur

**Name archetype:** Yusuf  
**Profile:** University student or recent graduate with business ideas and limited resources. Wants to learn and validate before investing time and money.

**Jobs to be done:**
- Find out if my idea makes sense before I invest my savings
- Learn what product development actually looks like
- Get a realistic plan I can follow
- Understand market dynamics for my specific geography

**Pain points:**
- Can't afford consultants or expensive tools
- AI chatbots give encouraging but useless advice
- Doesn't know the right questions to ask
- Geographic context (local market, regulations, payment infrastructure) is absent from global tools

**FORGE delivers:** Honest, evidence-based idea assessment → Geographic-aware market research → Realistic action plan → Free tier access → Educational progression through the workflow

---

## 4. User Journeys

### 4.1 Journey A — New Product Idea

```
User arrives → "What are you trying to achieve?"
    → User describes idea naturally
    → FORGE classifies: NEW_PRODUCT
    → FORGE builds initial understanding (silent — no unnecessary questions)
    → FORGE researches: competitors, market, existing solutions
    → FORGE produces Existence & Opportunity Discovery
    → User reviews: "Here's what exists, here's where the gap is"
    → FORGE challenges assumptions if warranted
    → FORGE recommends approach (BUILD / MODIFY / ALTERNATIVE / WAIT)
    → User approves direction
    → FORGE generates PRD (evidence-linked)
    → FORGE generates Architecture
    → FORGE generates Development Package
    → User exports (PDF / AI Package / GitHub)
    → Project becomes living workspace
    → FORGE monitors for opportunities, competitors, changes
```

### 4.2 Journey B — Existing Business Growth

```
User arrives → "What are you trying to achieve?"
    → "I run a laundry business and want more customers"
    → FORGE classifies: BUSINESS_GROWTH
    → FORGE asks minimal targeted questions about business, location, current channels
    → FORGE researches: local competition, market, digital presence
    → FORGE analyzes website (if URL provided)
    → FORGE produces situation assessment + opportunity map
    → FORGE recommends growth actions (ranked by impact / effort)
    → User reviews and selects priorities
    → FORGE produces actionable growth plan
    → Optional: digital transformation roadmap
    → Project becomes living workspace
    → FORGE monitors for new opportunities
```

### 4.3 Journey C — Existing Codebase Improvement

```
User arrives → "What are you trying to achieve?"
    → "I have a GitHub project that needs improvement"
    → FORGE classifies: SOFTWARE_OPTIMIZATION
    → User authorizes GitHub repository access
    → FORGE analyzes repository structure, architecture, dependencies, patterns
    → FORGE produces technical audit with specific findings
    → FORGE connects findings to Opportunity Graph
    → FORGE recommends improvements (ranked by impact)
    → User approves priorities
    → FORGE generates improvement roadmap
    → Optional: architecture revision, refactoring plan
    → Project becomes living workspace
```

### 4.4 Journey D — "I Don't Know What I Need"

```
User arrives → "What are you trying to achieve?"
    → "I have an idea but I don't know whether it makes sense"
    → FORGE classifies: UNDEFINED (initially)
    → FORGE asks clarifying questions (adaptive to user level)
    → FORGE reclassifies as appropriate type
    → FORGE follows the relevant workflow with extra guidance
    → FORGE explains what each stage means and why it matters
```

---

## 5. Product Modes & Internal Classification

When a user describes their situation, FORGE silently classifies it into one of these internal types. Classification determines which workflow stages are relevant.

| Classification | Trigger Examples | Key Workflow Stages |
|---|---|---|
| `NEW_PRODUCT` | "I want to build an app for..." | Understanding → Discovery → Research → Challenge → PRD → Architecture → Package |
| `EXISTING_PRODUCT` | "I have an app that needs..." | Understanding → Product Audit → Research → Opportunities → Improvement Roadmap |
| `BUSINESS_GROWTH` | "I run a business and want..." | Understanding → Business Analysis → Market Research → Growth Plan |
| `DIGITAL_TRANSFORMATION` | "I have manual processes..." | Understanding → Process Analysis → Automation Assessment → Transformation Plan |
| `PROCESS_AUTOMATION` | "I want to automate..." | Understanding → Process Mapping → Technology Assessment → Automation Plan |
| `WEBSITE_IMPROVEMENT` | "My website isn't converting..." | Understanding → Website Audit → UX Analysis → Competitor Comparison → Improvement Plan |
| `SOFTWARE_REBUILD` | "I need to rebuild my app..." | Understanding → Current System Audit → Architecture Review → Rebuild Strategy |
| `SOFTWARE_OPTIMIZATION` | "My codebase needs improvement..." | Understanding → Repository Analysis → Technical Audit → Improvement Roadmap |
| `MARKET_VALIDATION` | "Is there a market for..." | Understanding → Market Research → Competitor Analysis → Demand Evidence → Verdict |
| `BUSINESS_VALIDATION` | "Does this business model work..." | Understanding → Business Model Analysis → Market Research → Financial Assessment |
| `TECHNICAL_AUDIT` | "Audit my architecture..." | Understanding → Repository Analysis → Architecture Review → Recommendations |
| `MARKET_EXPANSION` | "I want to expand to Lagos..." | Understanding → Geographic Research → Market Comparison → Expansion Strategy |
| `STRATEGIC_PLANNING` | "What should we do next..." | Understanding → Situation Analysis → Research → Strategy → Roadmap |
| `UNDEFINED` | "I don't know what I need..." | Guided Discovery → Reclassification → Appropriate Workflow |

---

## 6. Core Workflows (MVP — Phase 1)

### 6.1 The Intelligence Loop

Every FORGE project follows this loop, with stages activated or skipped based on project classification:

```
UNDERSTAND → DISCOVER → RESEARCH → ANALYZE → CHALLENGE → IMAGINE → RECOMMEND → DECIDE → BUILD → OBSERVE → LEARN → IMPROVE
```

### 6.2 MVP Workflow Stages

For Phase 1 (MVP), FORGE supports `NEW_PRODUCT` and `MARKET_VALIDATION` classifications with these stages:

| Stage | Description | User Action |
|---|---|---|
| **1. Understanding** | FORGE builds a model of the user's situation, goals, technical level, and existing resources | User provides initial description; FORGE asks minimal clarifying questions |
| **2. Existence Discovery** | FORGE researches what already exists — competitors, similar products, alternative solutions | User reviews findings |
| **3. Market Research** | FORGE investigates market size, demand signals, user sentiment, trends, and geographic factors | User reviews research with source citations |
| **4. Analysis & Challenge** | FORGE synthesizes findings. If the proposed approach has issues, FORGE explains why and suggests alternatives | User considers FORGE's assessment |
| **5. Strategy** | FORGE recommends an approach: BUILD / BUILD WITH MODIFICATIONS / CONSIDER ALTERNATIVE / DO NOT BUILD YET | User approves direction |
| **6. PRD Generation** | Evidence-backed PRD with problem, personas, user journeys, features, success metrics | User reviews, edits, approves |
| **7. Architecture** | Technical architecture appropriate for the project, with documented decisions | User reviews, approves |
| **8. Development Package** | AGENTS.md, CLAUDE.md, Architecture Essentials, Hard Questions, Testing Strategy, Scaffold, Master Prompt | User downloads or exports |

### 6.3 Dual Mode Execution

**Mode A — Automatic:** User provides initial input. FORGE runs all stages and presents the complete result.

**Mode B — Page-by-Page:** User reviews and approves each stage before FORGE proceeds. Each stage supports: View, Edit, Regenerate, Approve, Version History.

---

## 7. Intelligence Engines (Core Systems)

### 7.1 User Understanding Engine

Builds a model of the user without asking unnecessary questions.

Classifies all user knowledge as:
- **CONFIRMED** — explicitly stated by the user
- **INFERRED** — deduced from context with high confidence
- **ASSUMED** — reasonable default, may be wrong
- **UNKNOWN** — not enough information
- **CONFLICTING** — contradictory signals

**Rule:** Never ask for information that FORGE can reasonably determine, research, retrieve, or infer.

### 7.2 Context Engine

Maintains a unified context model combining:
- User profile (technical level, goals, experience)
- Goal classification
- Business context
- Product context
- Existing system information
- Geographic context
- Market research results
- Competitor data
- Previous decisions
- Active recommendations

This context feeds every downstream engine.

### 7.3 Research Engine

Conducts internet research with source traceability.

Every research conclusion retains:
- Source URL
- Source type (Official / Government / Research / Publication / Community / Weak)
- Claim supported
- Retrieval date
- Publication date (if known)
- Confidence level

### 7.4 Evidence & Confidence System

Every significant conclusion is classified:
- **Verified** — confirmed by multiple reliable sources
- **Strongly supported** — backed by at least one reliable source
- **Probable** — consistent with available evidence
- **Inferred** — reasonable deduction, not directly sourced
- **Assumption** — default position, may not be correct
- **Unknown** — insufficient information
- **Conflicting** — sources disagree

### 7.5 Opportunity Graph

Dynamic, functional visualization of interconnected:
- Goals, Problems, Opportunities
- Markets, Competitors, Customers
- Features, Technology, Risks
- Recommendations, Dependencies, Growth Paths

Clicking any node reveals: why it exists, related evidence, sources, expected impact, difficulty, risks, dependencies, recommended action, and confidence.

### 7.6 Growth & Opportunity Engine

Proactively identifies opportunities without waiting for user requests:
- Product, revenue, and market opportunities
- UX and technical improvements
- Automation and operational opportunities
- Customer retention improvements
- New product directions

Every recommendation includes: opportunity, why it matters, why now, potential impact, difficulty, dependencies, evidence, confidence, and suggested next step.

### 7.7 Creative Strategy Engine

For important problems, evaluates multiple approaches:
- Improve current solution
- Simplify
- Automate
- Combine existing capabilities
- Change business model / target market / positioning
- Build something different
- Don't build software — improve the process

Generates alternatives and evaluates them against evidence.

---

## 8. Output Types

FORGE produces different outputs based on classification:

| Output | When | Format |
|---|---|---|
| **Product Blueprint** | NEW_PRODUCT, SOFTWARE_REBUILD | PRD + Architecture + Dev Package |
| **Business Growth Plan** | BUSINESS_GROWTH, MARKET_EXPANSION | Strategy + Actions + Timeline |
| **Market Research Report** | MARKET_VALIDATION, BUSINESS_VALIDATION | Research + Evidence + Verdict |
| **Technical Audit** | TECHNICAL_AUDIT, SOFTWARE_OPTIMIZATION | Findings + Recommendations + Roadmap |
| **Improvement Roadmap** | EXISTING_PRODUCT, WEBSITE_IMPROVEMENT | Prioritized actions + Evidence |
| **Digital Transformation Plan** | DIGITAL_TRANSFORMATION, PROCESS_AUTOMATION | Assessment + Strategy + Implementation Plan |
| **AI Development Package** | Any software project | PRD.md, Architecture.md, AGENTS.md, CLAUDE.md, etc. |
| **PDF Report** | Any project | Executive summary, findings, recommendations, sources |

---

## 9. MVP Scope

### 9.1 MVP Includes (Phase 1)

- [ ] Authentication (Google, GitHub OAuth)
- [ ] "What are you trying to achieve?" entry experience
- [ ] Automatic situation classification (NEW_PRODUCT, MARKET_VALIDATION initially)
- [ ] User Understanding Engine (basic)
- [ ] Context Engine (basic)
- [ ] Research Engine with source traceability
- [ ] Evidence & Confidence System
- [ ] Existence & Opportunity Discovery
- [ ] Competitor analysis
- [ ] Market research
- [ ] Challenge & recommendation stage
- [ ] PRD generation (evidence-linked)
- [ ] Architecture generation
- [ ] Development package generation
- [ ] Dual-mode workflow (Automatic + Page-by-Page)
- [ ] Project workspace (create, revisit, update)
- [ ] PDF export
- [ ] AI Package download
- [ ] Master Prompt copy
- [ ] Credit system (basic)
- [ ] Plan & entitlement system (Free, Pro)
- [ ] AI workload classification and credit estimation
- [ ] Dark mode + Light mode
- [ ] Responsive design
- [ ] Basic admin panel

### 9.2 MVP Excludes (Future Phases)

- GitHub read/write integration
- Repository analysis
- Website audit engine
- Existing business workflows
- Opportunity Graph visualization
- Growth & Opportunity Engine (proactive)
- Continuous monitoring / notifications
- Team / organization features
- Enterprise features
- Referral system
- API access
- BYOK (Bring Your Own Key)
- Advanced admin features

---

## 10. Success Metrics

### 10.1 Activation

| Metric | Target (3 months post-launch) |
|---|---|
| Signup → First project created | >60% |
| First project → Completed workflow (reached output stage) | >40% |
| Completed workflow → Downloaded/exported output | >70% |

### 10.2 Retention

| Metric | Target (6 months post-launch) |
|---|---|
| Week 1 return rate | >30% |
| Month 1 return rate | >15% |
| Users with 2+ projects | >25% of active users |

### 10.3 Quality

| Metric | Target |
|---|---|
| Research accuracy (spot-checked citations are valid) | >90% |
| User satisfaction with recommendations (thumbs up/down) | >70% positive |
| Workflow completion rate (started vs. finished) | >50% |

### 10.4 Revenue

| Metric | Target (6 months post-launch) |
|---|---|
| Free → Pro conversion | >5% |
| Monthly churn (Pro subscribers) | <8% |
| Average revenue per paying user | >$25/mo |

---

## 11. Monetization

### 11.1 Plan Structure

| Plan | Price | Access |
|---|---|---|
| **Free** | $0 | 1 project, basic research (limited sources), basic PRD, basic architecture, watermarked PDF, 25 credits/month |
| **Pro** | $39/mo | Unlimited projects, deep research, full PRD + architecture, development packages, clean PDF, GitHub export (future), 200 credits/month |
| **Business** | $99/mo | Everything in Pro + team workspace (up to 5), priority AI, 500 credits/month, API access (future) |
| **Enterprise** | Custom | Dedicated infrastructure, SSO, audit logs, unlimited credits, SLA, BYOK (future) |

### 11.2 Credit System

Credits control expensive AI operations. Plans include monthly credit allocations. Additional credits can be purchased.

| Operation | Estimated Credits |
|---|---|
| Situation classification | 1 |
| Basic research | 5 |
| Deep market research | 15–25 |
| Competitor analysis | 10–20 |
| PRD generation | 10 |
| Architecture generation | 15 |
| Full development package | 30–50 |
| Repository analysis (future) | 20–40 |
| Research refresh (future) | 10–15 |

---

## 12. Retention Strategy

1. **Living workspaces:** Projects persist and evolve. Users return to update, re-research, and iterate.
2. **Opportunity alerts (future):** Notify users when meaningful changes occur in their market, competitors, or technology landscape.
3. **Version history:** Compare current strategy/PRD/architecture with previous versions.
4. **Research freshness indicators:** Show when research was last updated, prompt re-research.
5. **New project types:** As FORGE expands to business growth, existing systems, and codebases, existing users have new reasons to use the platform.

---

## 13. Referral Strategy

Reward **meaningful activation**, not just signup:

```
User refers friend
    → Friend signs up
    → Friend creates first project
    → Friend completes first workflow
    → Both receive bonus credits
```

Anti-abuse: rate limiting, duplicate detection, device fingerprinting.

---

## 14. Long-Term Vision

FORGE becomes the intelligence layer between a person's goals and their execution — regardless of whether the execution is software development, business strategy, market expansion, or process improvement.

Year 1: Validated intelligence loop for new products  
Year 2: Existing businesses, codebases, and continuous growth  
Year 3: Team/enterprise platform with organizational intelligence  
Year 4: API-first platform — other tools integrate FORGE's intelligence

---

*This PRD will be refined after architecture decisions are made. All scope and pricing are subject to revision based on technical feasibility and business model design.*
