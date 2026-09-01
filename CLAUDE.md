# FORGE — Claude Code Instructions

> This file is for Claude Code (claude.ai/code, Claude in terminal, Cursor with Claude). Read AGENTS.md first, then return here for Claude-specific guidance.

---

## How to Work on FORGE

1. **Always read `AGENTS.md` first** — it contains the authoritative rules
2. **Read `docs/04-architecture-essentials.md`** before any implementation
3. **Check current code** before writing new code — avoid duplication
4. **Follow the module architecture** — files go in `app/Modules/{ModuleName}/`
5. **Never create god files** — break things into Actions, Services, and focused components

---

## Module Map

When asked to work on a feature, identify the correct module:

| Feature Area | Module | Key Files |
|---|---|---|
| Login, OAuth, users | `Identity` | Models/User, Actions/CreateUser |
| Projects, classification | `Projects` | Models/Project, Services/ClassificationService |
| User & project context | `Context` | Services/ContextEngine |
| Location, markets | `Geography` | Services/GeographyService |
| Competitor & idea research | `Discovery` | Services/DiscoveryService |
| Web research, sources | `Research` | Services/ResearchEngine |
| Evidence, confidence | `Evidence` | Services/EvidenceService |
| Opportunities | `Opportunity` | Services/OpportunityEngine |
| Strategy generation | `Strategy` | Services/StrategyEngine |
| PRD, architecture, workflow | `Product` | Services/PRDService, WorkflowService |
| Dev package generation | `Blueprint` | Services/BlueprintService |
| AI provider abstraction | `AI` | Services/AIOrchestrator, Providers/* |
| Credit accounts, transactions | `Credits` | Services/CreditService |
| Plans, subscriptions, Stripe | `Billing` | Services/BillingService, EntitlementService |
| PDF, ZIP, master prompt | `Export` | Services/PDFService, PackageExportService |
| Admin panel | `Admin` | Services/AdminService |
| User notifications | `Notifications` | Services/NotificationService |
| Privacy consent | `Consent` | Services/ConsentService |

---

## Key Patterns to Follow

### Action Pattern (Single-Purpose Operations)
```php
class CreateProjectAction
{
    public function execute(User $user, CreateProjectData $data): Project
    {
        // Validate entitlements
        // Create project
        // Initialize context
        // Dispatch events
        // Return result
    }
}
```

### Entitlement Check Pattern
```php
// CORRECT
if (!$this->entitlements->can($user, 'research.deep')) {
    abort(402, 'Upgrade required');
}

// WRONG — never do this
if ($user->plan === 'free') {
    abort(402);
}
```

### Credit Pattern
```php
$reservation = $this->credits->reserve($user, $estimatedCost);
try {
    $result = $this->ai->execute($request);
    $this->credits->confirm($reservation);
    return $result;
} catch (Exception $e) {
    $this->credits->release($reservation);
    throw $e;
}
```

---

## File Naming Conventions

| Type | Convention | Example |
|---|---|---|
| Model | Singular, PascalCase | `Project.php` |
| Action | VerbNounAction | `CreateProjectAction.php` |
| Service | NounService | `CreditService.php` |
| Policy | NounPolicy | `ProjectPolicy.php` |
| Request | VerbNounRequest | `CreateProjectRequest.php` |
| Enum | PascalCase | `ProjectType.php` |
| Event | PastTenseEvent | `ProjectCreatedEvent.php` |
| Vue Component | PascalCase | `ProjectCard.vue` |
| Vue Page | PascalCase | `ProjectDashboard.vue` |
| Test | NounTest | `CreditServiceTest.php` |

---

## What Not to Do

- Don't create a single massive controller for all project operations
- Don't skip authorization checks ("I'll add them later")
- Don't hard-code plan names in business logic
- Don't put AI provider-specific code outside the AI module
- Don't bypass the credit system for AI operations
- Don't commit .env or any secrets
- Don't create migrations that modify existing migrations
- Don't use `$guarded = []` on models
- Don't skip input validation
- Don't use `v-html` with user-provided content

---

## Testing Requirements

Every PR-worthy change should include tests for:
1. **Happy path** — feature works as intended
2. **Authorization** — unauthorized access is blocked
3. **Validation** — invalid input is rejected
4. **Edge cases** — empty states, concurrent access, failure scenarios
