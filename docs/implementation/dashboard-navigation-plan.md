# FORGE — Dashboard, Navigation & Role-Aware Workspace Implementation Plan

**Document:** `docs/implementation/dashboard-navigation-plan.md`  
**Date:** 2026-09-05  
**Author:** AI Architecture & Engineering  
**Depends on:** `docs/04-architecture-essentials.md`, `docs/01-prd.md`, `docs/03-architecture.md`, `docs/12-ui-design-system.md`  
**Status:** Approved for Implementation  

---

## 1. Current Architecture Findings

1. **Backend Modular Architecture:**
   - The application is organized cleanly under `app/Modules/` with 22 specialized domains (AI, API, Admin, Billing, Blueprint, Consent, Context, ContinuousIntelligence, Credits, Discovery, Evidence, Export, Geography, GitHub, Identity, Notifications, Opportunity, Organizations, Product, Projects, Research, Strategy).
   - Core entity models already exist: `Project`, `ProjectVersion`, `Workflow`, `WorkflowStage`, `Opportunity`, `Recommendation`, `ResearchSession`, `ResearchSource`, `WebsiteAnalysis`, `GitHubConnection`, `RepositoryAudit`, `CreditAccount`, `CreditTransaction`, `Plan`, `Subscription`, `Entitlement`, `Alert`, `AuditLog`, `ByokCredential`, `ApiKey`, `ConsentRecord`, and `LearningSignal`.
   - Domain logic is decoupled via service contracts (e.g. `EntitlementServiceInterface`, `OpportunityGraphService`, `ByokService`, `CompetitorDriftMonitor`). Controllers are thin and delegate to Actions and Services.

2. **Frontend Inertia & Vue 3 Setup:**
   - Built with Vue 3.5+ (Composition API), Inertia.js 2.x, Tailwind CSS 4.x.
   - `AppLayout.vue` wraps all authenticated pages, with `Sidebar.vue` handling left navigation.
   - Global props are provided via `HandleInertiaRequests.php` (`auth.user`, `credits.balance`, `flash`, `appName`).

3. **Current Limitations & Gaps:**
   - **Global Navigation (Level 1):** Routes like `/dashboard`, `/discover`, `/opportunities`, `/research`, `/growth`, `/github`, `/notifications`, `/exports`, `/usage`, `/billing`, `/settings`, and `/help` either point to partial pages or are not yet exposed as dedicated, first-class routes in `routes/web.php`. Currently `/` redirects directly to `/projects`.
   - **Project Navigation (Level 2):** When viewing `/projects/{project}`, the sidebar does not automatically switch into the focused Project Workspace context (Overview, Understanding, Discovery, Research, Competitors, Strategy, Opportunity Graph, Recommendations, PRD, Architecture, Hard Questions, Testing, GitHub, Exports, Activity).
   - **Admin Navigation (Level 3):** When an administrator enters `/admin/*`, they still see the standard user navigation with a small "Admin Operations" subsection, instead of an exclusive operational command center covering Overview, Revenue, Intelligence, Platform, Security, and System.
   - **Role & Plan Awareness:** Navigation links are statically displayed without visual entitlement indicators (e.g., indicating Pro-only capabilities, live connection status, or role boundaries).

---

## 2. What Already Exists

- **Controllers:**
  - `ProjectController` (`index`, `create`, `store`, `show`, `archive`)
  - `WorkflowController` (`status`, `advance`, `approve`, `decide`)
  - `ProjectVersionController` (`index`, `diff`, `timeline`, `rerun`)
  - `OpportunityGraphController` (`show`)
  - `AlertController` (`index`, `markRead`, `markAllRead`)
  - `BillingController` (`pricing`, `checkout`, `portal`, `webhook`)
  - `ExportController` (`generateSignedUrl`, `downloadPackage`, `downloadPdf`, `downloadGrowthPlanPdf`)
  - `GitHubController` (`connect`, `callback`, `disconnect`, `repositories`, `scan`, `export`)
  - `AdminController` (`dashboard`, `grantCredits`, `updateRole`)
  - `AdminApiKeyController` (`index`, `testConnection`)
  - `OrganizationController`, `AuditLogController`, `PrivacyController`, `ByokController`, `ApiKeyManagementController`
- **Components:**
  - `OpportunityGraph.vue` (interactive SVG topology graph for goals, problems, tech, risks)
  - `NotificationCenter.vue` (top header alert dropdown)
  - `DecisionTimeline.vue` (historical version runs and approval states)
  - `VersionComparisonModal.vue` (diffing versions)
  - `GitHubConnectModal.vue`, `GitHubExportModal.vue`, `RepositoryAuditCard.vue`
  - `CompetitorMatrix.vue`, `EvidenceRegistry.vue`, `WebsiteAuditCard.vue`, `DiscoveryVerdictCard.vue`, `WorkflowProgressBar.vue`
- **Design Tokens:**
  - Semantic CSS custom properties (`--bg-surface-*`, `--color-text-*`, `--color-border-*`, etc.) in `resources/css/app.css` supporting dark and light modes.

---

## 3. What Can Be Reused

1. `OpportunityGraphService.php` to feed real nodes and edges into both the global and project-level Opportunity Graph.
2. Existing controllers and actions (`GitHubController`, `AlertController`, `ExportController`, `BillingController`, `OrganizationController`).
3. Existing Vue components (`OpportunityGraph.vue`, `DecisionTimeline.vue`, `RepositoryAuditCard.vue`, `EvidenceRegistry.vue`, `CompetitorMatrix.vue`).
4. `EntitlementService` to enforce server-side capabilities and feed client-side plan-aware states.

---

## 4. What Needs to Be Added

1. **New Controllers:**
   - `DashboardController`: Aggregates active projects, cross-project opportunities, recent intelligence activity, credit balance, and plan summary.
   - `DiscoverController`: Handles the `/discover` intelligence launchpad ("What are you trying to achieve?").
   - `OpportunityController`: Serves global `/opportunities` cross-project catalog.
   - `ResearchCatalogController`: Serves global `/research` hub with sources, sessions, and website audits.
   - `GrowthController`: Serves global `/growth` proactive intelligence hub adapted to user's technical profile.
   - `UsageController`: Serves `/usage` showing credit ledger, transaction history, and workload breakdown.
   - `SettingsController`: Serves consolidated `/settings` hub (Profile, Appearance, Connected Accounts, Privacy, API Keys).
   - `HelpController`: Serves `/help` resource and support center.
2. **Dedicated Vue Views:**
   - `Pages/Dashboard.vue`
   - `Pages/Discover.vue`
   - `Pages/Opportunities/Index.vue`
   - `Pages/Research/Index.vue`
   - `Pages/Growth/Index.vue`
   - `Pages/GitHub/Index.vue`
   - `Pages/Exports/Index.vue`
   - `Pages/Usage/Index.vue`
   - `Pages/Settings/Index.vue`
   - `Pages/Help/Index.vue`
   - Dedicated Admin operational views under `Pages/Admin/`
3. **Tri-Level Contextual Navigation System:**
   - Update `Sidebar.vue` to dynamically switch between:
     - **Level 1 (Global Navigation):** Overview, Discover, Projects, Opportunities, Research, Growth, GitHub | Notifications, Exports | Usage, Billing, Settings, Help.
     - **Level 2 (Project Workspace Navigation):** When active route is within a project, switch sidebar to: Project Header / Back, Overview, Understanding, Discovery, Research, Competitors, Strategy, Opportunity Graph, Recommendations, PRD, Architecture, Hard Questions, Testing, GitHub, Exports, Activity.
     - **Level 3 (Admin Command Center):** When an admin is in `/admin/*`, switch sidebar to: OVERVIEW, REVENUE, INTELLIGENCE, PLATFORM, SECURITY, SYSTEM.

---

## 5. Backend Changes

1. **Route Definitions (`routes/web.php`):**
   - Route `/` to redirect to `/dashboard` when authenticated.
   - Register `/dashboard`, `/discover`, `/opportunities`, `/research`, `/growth`, `/github`, `/notifications`, `/exports`, `/usage`, `/billing`, `/settings`, `/help`.
   - Register expanded admin operational endpoints.
2. **Shared Inertia Props (`HandleInertiaRequests.php`):**
   - Share user's active plan information (`plan_name`, `capabilities`) and active project context when present.
3. **Thin Controllers:**
   - Build controllers ensuring zero business logic in controllers, querying through scoped user relations (`auth()->user()->projects()`, `whereHas('project', fn($q) => $q->where('user_id', auth()->id()))`).

---

## 6. Frontend Changes

1. **Sidebar Modernization (`resources/js/Components/Sidebar.vue`):**
   - Refactor into clean sub-navigation modes: Global, Project Workspace, Admin.
   - Support desktop collapsible rail (20rem expanded to 5rem collapsed) and mobile slide-out drawer with single top hamburger trigger.
   - Restrained, anti-AI styling (no gratuitous purple/blue gradients, no excessive glow).
2. **Dashboard Hierarchy (`Pages/Dashboard.vue`):**
   - Intentional priority layout:
     - Header: Context greeting + quick status + "Discover What's Possible" launchpad.
     - KPI Metric Grid: Active Projects, Discovered Opportunities, Evidence Sources, Available Credits.
     - Dual-column workspace: Recent Projects & Immediate Action Items vs Opportunity & Growth Radar.
3. **Discover Launchpad (`Pages/Discover.vue`):**
   - Prominent prompt input: "What are you trying to achieve?"
   - Guided quick-starters (New Business Idea, Optimize Website, Expand into New Market, Audit GitHub Repository, Rebuild Legacy App).
   - Maps directly to project initiation with automated classification.
4. **Project Workspace Switching:**
   - In `Projects/Show.vue` and `Sidebar.vue`, deep links allow jump-scrolling or tab-switching directly to specific project sections (e.g. `#graph`, `#competitors`, `#prd`, `#github`, `#export`).

---

## 7. Database Changes

No destructive migrations required. All required tables exist (`projects`, `opportunities`, `recommendations`, `evidence`, `research_sources`, `credit_accounts`, `credit_transactions`, `plans`, `subscriptions`, `alerts`, `github_connections`, `repository_audits`, `audit_logs`).

---

## 8. Routing Changes

| Route | Name | Middleware | Controller Action | Description |
|---|---|---|---|---|
| `/` | `home` | web | Closure | Redirect to `/dashboard` or `/login` |
| `/dashboard` | `dashboard` | `auth` | `DashboardController@index` | Main user intelligence dashboard |
| `/discover` | `discover` | `auth` | `DiscoverController@index` | Intelligent discovery entrypoint |
| `/opportunities` | `opportunities.index` | `auth` | `OpportunityController@index` | Cross-project opportunities |
| `/research` | `research.index` | `auth` | `ResearchCatalogController@index` | Cross-project research & sources |
| `/growth` | `growth.index` | `auth` | `GrowthController@index` | Proactive growth & recommendations |
| `/github` | `github.index` | `auth` | `GitHubController@index` | GitHub accounts & repo health |
| `/notifications` | `notifications.index` | `auth` | `AlertController@index` | In-app alerts & notifications |
| `/exports` | `exports.index` | `auth` | `ExportController@index` | Artifacts & exported packages |
| `/usage` | `usage.index` | `auth` | `UsageController@index` | Credit usage & workloads |
| `/billing` | `billing.index` | `auth` | `BillingController@pricing` | Plans & billing portal |
| `/settings` | `settings.index` | `auth` | `SettingsController@index` | Settings hub |
| `/help` | `help.index` | `auth` | `HelpController@index` | Documentation & help |
| `/admin/*` | `admin.*` | `auth, admin` | `AdminController` | Administrative command center |

---

## 9. Authorization Changes

1. **Owner-scoping on all queries:**
   - Every project-linked entity (opportunity, research source, audit, export) must verify `project.user_id === auth()->id()`.
2. **Admin-only routes:**
   - Strict `AdminMiddleware` protection on `/admin/*` checking `$user->role === UserRole::ADMIN`.
3. **Plan-aware capability checks:**
   - Frontend exposes badge indicators ("Available on Pro", "Connect GitHub") without hiding actionable context.
   - Backend enforces capability validation before execution.

---

## 10. Component Changes

- **Update `Sidebar.vue`:** Introduce tri-mode navigation (Global, Project, Admin) with responsive drawer & collapse memory.
- **Update `AppLayout.vue`:** Dynamic view titles, header breadcrumbs, sticky top navigation, credits counter, theme toggle.
- **Update `Projects/Show.vue`:** Integrate section anchors matching Level 2 project navigation items.
- **Create Page Components:** `Dashboard.vue`, `Discover.vue`, `Opportunities/Index.vue`, `Research/Index.vue`, `Growth/Index.vue`, `GitHub/Index.vue`, `Exports/Index.vue`, `Usage/Index.vue`, `Settings/Index.vue`, `Help/Index.vue`.

---

## 11. Responsive Behavior

- **Desktop (>= 1024px):** Fixed collapsible rail sidebar (256px expanded / 80px collapsed).
- **Tablet (768px - 1023px):** Compact sidebar or toggleable drawer.
- **Mobile (< 768px):** Off-canvas slide-out navigation with backdrop overlay, controlled by the single top header hamburger button. Touch targets >= 44px.

---

## 12. Dark/Light Mode Behavior

- All components consume semantic CSS variables:
  - Light mode: `--bg-surface-primary` (`#f8fafc`), `--bg-surface-secondary` (`#ffffff`), `--color-text-primary` (`#0f172a`), border `#e2e8f0`.
  - Dark mode: `--bg-surface-primary` (`#0b0f19`), `--bg-surface-secondary` (`#111827`), `--color-text-primary` (`#f9fafb`), border `#1f2937`.
- Verified contrast ratios >= 4.5:1 for standard text.

---

## 13. Testing Plan

1. **Feature Tests:**
   - `DashboardTest`: Authenticated user can view dashboard with populated project and credit data; guest is redirected.
   - `NavigationAccessTest`: Non-admin cannot access `/admin`; admin can access all admin views.
   - `OpportunityAccessTest`: User only sees their own project opportunities.
   - `DiscoverTest`: Prompt submission correctly initiates project creation.
2. **Browser / Frontend Verification:**
   - Verify mobile drawer opens and closes without layout clipping.
   - Verify sidebar collapses and expands cleanly.
   - Verify project workspace switches navigation context.

---

## 14. Migration Requirements

None needed. Schema is intact.

---

## 15. Risks & Mitigations

- **Risk:** Navigation complexity causing cognitive overload.
  - **Mitigation:** Strict 3-level separation; project-specific pages are never shown in global navigation.
- **Risk:** N+1 queries when loading dashboard metrics.
  - **Mitigation:** Eager load relations with `withCount()` and limit recent items to 5.
- **Risk:** Frontend/backend entitlement mismatch.
  - **Mitigation:** Always use server-side `EntitlementService` and share capability array in Inertia props.

---

## 16. Dependency Order

1. **Phase 1:** Backend Controllers & Route Registration.
2. **Phase 2:** Navigation Component Overhaul (`Sidebar.vue` & `AppLayout.vue`).
3. **Phase 3:** Core Global Views (`Dashboard.vue`, `Discover.vue`, `Projects/Index.vue`).
4. **Phase 4:** Domain Intelligence Views (`Opportunities`, `Research`, `Growth`, `GitHub`, `Exports`).
5. **Phase 5:** Account & Governance Views (`Usage`, `Settings`, `Help`).
6. **Phase 6:** Project Contextual Workspace Sub-Navigation in `Projects/Show.vue`.
7. **Phase 7:** Admin Command Center Expansion.
8. **Phase 8:** Asset Compilation, Docker Sync & Testing.

---

## 17. Implementation Phases & Definition of Done

### Phase 1: Navigation Architecture & Route Layer
- Register all 13 Level 1 routes and admin operational routes in `routes/web.php`.
- Create corresponding thin controllers.

### Phase 2: Sidebar & Layout Overhaul
- Implement tri-level navigation logic in `Sidebar.vue` (Global, Project, Admin).
- Maintain single top hamburger toggle and collapse state in `localStorage`.

### Phase 3: Dashboard & Discovery Implementation
- Implement `Dashboard.vue` with real KPIs, recent projects, opportunities, and quick actions.
- Implement `Discover.vue` with natural-language intent input.

### Phase 4: Opportunities, Research, Growth, GitHub & Exports
- Implement `Opportunities/Index.vue` with multi-category filtering, impact/difficulty badges, and real data.
- Implement `Research/Index.vue` with source citations and freshness indicators.
- Implement `Growth/Index.vue` with tailored technical vs business recommendations.
- Implement `GitHub/Index.vue` separating login, read access, and write exports.
- Implement `Exports/Index.vue` listing previous blueprints, packages, and PDF downloads.

### Phase 5: Usage, Settings & Help
- Implement `Usage/Index.vue` with credit balance, transaction history, and workload breakdown.
- Implement `Settings/Index.vue` tabbed hub.
- Implement `Help/Index.vue` with guides and FAQs.

### Phase 6: Project Sub-Navigation
- Link `Projects/Show.vue` stages and sections to project workspace sub-navigation.

### Phase 7: Admin Console Expansion
- Implement Admin navigation model and operational dashboard tabs.

### Phase 8: Verification & QA
- Build assets via Vite in Docker.
- Run PHPUnit tests.
- Perform visual QA across viewport sizes and dark/light themes.

---

## 18. Definition of Done

1. All 13 Level 1 global routes are reachable and render real data.
2. Opening a project switches navigation to the Level 2 Project Workspace.
3. Accessing `/admin/*` displays the Level 3 Admin Command Center for admins and denies non-admins.
4. No fake placeholder data where models exist.
5. Fully responsive on mobile, tablet, and desktop with zero horizontal scroll or layout bugs.
6. Clean light and dark modes adhering to FORGE anti-AI design guidelines.
7. Automated tests pass and assets build with 0 errors.
