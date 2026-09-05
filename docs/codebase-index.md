# FORGE Codebase Index

## Top-level map

| Path | Responsibility |
| --- | --- |
| `app/` | Laravel application code |
| `app/Modules/` | Domain modules and services |
| `app/Http/` | Controllers and middleware |
| `bootstrap/` | Application bootstrapping and providers |
| `config/` | Framework and service configuration |
| `database/` | Migrations, factories, and seeders |
| `resources/js/` | Vue/Inertia frontend |
| `resources/css/` | Frontend styles and design tokens |
| `resources/views/` | Inertia root and PDF Blade views |
| `routes/` | Web, API, and console routes |
| `tests/` | PHPUnit unit and feature tests |
| `docs/` | Product, architecture, security, and workflow documentation |
| `public/` | Laravel entry point and built assets |

## Product ownership

| Area | Primary implementation |
| --- | --- |
| Authentication and OAuth | `app/Http/Controllers/AuthController.php`; `app/Modules/Identity/Services/AuthenticationService.php` |
| Projects and classification | `app/Http/Controllers/ProjectController.php`; `app/Modules/Projects/Actions/CreateProjectAction.php`; `app/Modules/Projects/Services/ClassificationService.php` |
| Project context | `app/Modules/Context/Models/ProjectContext.php` |
| Geography and markets | `app/Modules/Geography/Services/GeographicIntelligenceService.php` |
| Discovery and competitors | `app/Modules/Discovery/Services/DiscoveryService.php`; `CompetitorAnalysisService.php` |
| Research and sources | `app/Modules/Research/Services/ResearchEngine.php`; `WebSearchService.php` |
| Evidence and confidence | `app/Modules/Evidence/Services/EvidenceService.php` |
| Opportunities | `app/Modules/Opportunity/Services/OpportunityRankingService.php`; `OpportunityGraphService.php` |
| Strategy | `app/Modules/Strategy/Services/StrategyEngine.php`; `CreativeStrategyService.php`; `Actions/ChallengeAssumptionsAction.php`; `GenerateStrategyAction.php` |
| Product workflow | `app/Modules/Product/Actions/ExecuteStageAction.php`; `app/Http/Controllers/WorkflowController.php` |
| Blueprint and development package | `app/Modules/Blueprint/Services/BlueprintService.php`; `PackageAssembler.php`; `Actions/GenerateMasterPromptAction.php`; `GenerateDevPackageAction.php` |
| AI providers and orchestration | `app/Modules/AI/Services/AIOrchestrator.php`; `Providers/` (Anthropic, OpenAI, Gemini) |
| Credits | `app/Modules/Credits/Services/CreditService.php`; reserve/release/confirm actions |
| Billing and entitlements | `app/Modules/Billing/Services/EntitlementService.php`; `BillingController.php` |
| Exports | `app/Http/Controllers/ExportController.php`; `app/Modules/Export/Actions/` |
| Admin | `app/Modules/Admin/Services/AdminService.php`; `app/Http/Controllers/AdminController.php`; `app/Http/Middleware/AdminMiddleware.php` |
| Notifications | `app/Modules/Notifications/Services/AlertService.php` |
| Privacy and consent | `app/Http/Controllers/PrivacyController.php`; `app/Modules/Consent/Services/ConsentService.php` |
| Public API | `app/Http/Controllers/Api/V1/ProjectApiController.php`; `app/Modules/API/Services/ApiKeyService.php` |
| Organizations | `app/Http/Controllers/OrganizationController.php`; `app/Modules/Organizations/Services/OrganizationService.php` |
| GitHub integration | `app/Http/Controllers/GitHubController.php`; `app/Modules/GitHub/Services/GitHubClientService.php` |
| Continuous intelligence | `app/Console/Commands/MonitorCompetitorsCommand.php`; `GenerateIntelligenceDigestCommand.php` |

## HTTP and frontend entry points

- Web routes: `routes/web.php`
- API v1 routes: `routes/api.php`
- Console routes: `routes/console.php`
- Inertia bootstrap: `resources/js/app.ts`
- Axios bootstrap: `resources/js/bootstrap.js`
- Main application layout: `resources/js/Layouts/AppLayout.vue`
- Pages: `resources/js/Pages/`
- Reusable components: `resources/js/Components/`
- CSS tokens and global styles: `resources/css/app.css`
- Inertia root view: `resources/views/app.blade.php`
- PDF views: `resources/views/pdf/`

## Persistence

- Migrations: `database/migrations/`
- User factory: `database/factories/UserFactory.php`
- Demo and plan seed data: `database/seeders/DatabaseSeeder.php`
- Shared user model: `app/Models/User.php`

The migrations cover identity, plans and billing, credits, projects and versions, context, geography, research and evidence, discovery and competitors, opportunities, workflows, documents, consent, notifications, referrals, GitHub, organizations, API keys, BYOK, and learning signals.

## Testing and configuration

- PHPUnit configuration: `phpunit.xml`
- Feature tests: `tests/Feature/`
- Unit tests: `tests/Unit/`
- PHP dependencies: `composer.json`
- JavaScript dependencies: `package.json`
- Vite configuration: `vite.config.js`
- Application/provider setup: `bootstrap/app.php`, `bootstrap/providers.php`
- Service bindings and policies: `app/Providers/AppServiceProvider.php`
- Local environment template: `.env.example`

There are no frontend test files or frontend test scripts currently configured.

## Current structural gaps

- Several documented module `Policies`, `Requests`, `Events`, and module-local tests are absent or empty (handled centrally by thin controllers and FormRequests).
- Documentation targets PHP 8.4+ and Vite 6, while the package manifests allow PHP 8.2+ and use Vite 7.
- Strategy and Blueprint modules are fully implemented with comprehensive test coverage.
- Continuous Intelligence console commands are scheduled in `routes/console.php`.
- Google Gemini provider is implemented and registered alongside Anthropic and OpenAI.