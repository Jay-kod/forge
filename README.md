# FORGE

**Framework for Opportunity, Research, Growth & Execution**

> Discover what's possible.

---

## What is FORGE?

FORGE is an intelligence platform where a person arrives with a situation — an idea, a business, a codebase, a growth objective, or uncertainty about what to do next — and leaves with clarity, evidence, a recommended direction, and the right actionable output.

FORGE is not an AI chatbot. It combines user-provided information, internet research, source evidence, competitor intelligence, market data, geographic context, and AI reasoning to produce evidence-backed recommendations and development-ready outputs.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 12.x (PHP 8.4+) |
| Frontend | Vue 3.5+ (Composition API + TypeScript) |
| Bridge | Inertia.js |
| Styling | Tailwind CSS 4.x |
| Database | MySQL 8.4+ |
| Cache/Queue | Redis 7.x |
| Build | Vite 6.x |
| Local Dev | Docker (Laravel Sail) |
| Testing | PHPUnit, Vitest, Playwright |

---

## Local Development Setup

### Prerequisites

- Docker Desktop
- Git

### Installation

```bash
# Clone the repository
git clone <repository-url> forge
cd forge

# Copy environment configuration
cp .env.example .env

# Start Docker containers
./vendor/bin/sail up -d

# Install PHP dependencies
./vendor/bin/sail composer install

# Install Node dependencies
./vendor/bin/sail npm install

# Generate application key
./vendor/bin/sail artisan key:generate

# Run database migrations
./vendor/bin/sail artisan migrate

# Seed development data
./vendor/bin/sail artisan db:seed

# Start the frontend dev server
./vendor/bin/sail npm run dev
```

### Access

- Application: `http://localhost`
- Mailpit: `http://localhost:8025`

### Environment Variables

See `.env.example` for all required configuration. Key variables:

| Variable | Purpose |
|---|---|
| `GOOGLE_CLIENT_ID/SECRET` | Google OAuth |
| `GITHUB_CLIENT_ID/SECRET` | GitHub OAuth |
| `ANTHROPIC_API_KEY` | AI provider (Anthropic) |
| `OPENAI_API_KEY` | AI provider (OpenAI) |
| `GOOGLE_AI_API_KEY` | AI provider (Google) |
| `STRIPE_KEY/SECRET` | Payment processing |
| `STRIPE_WEBHOOK_SECRET` | Webhook verification |

---

## Project Structure

```
forge/
├── app/
│   ├── Modules/                  # Domain modules
│   │   ├── Identity/             # Authentication, users
│   │   ├── Projects/             # Project management, classification
│   │   ├── Context/              # User & project context
│   │   ├── Geography/            # Location, markets
│   │   ├── Discovery/            # Competitor & idea discovery
│   │   ├── Research/             # Web research, sources
│   │   ├── Evidence/             # Evidence, confidence system
│   │   ├── Opportunity/          # Opportunity detection
│   │   ├── Strategy/             # Strategy generation
│   │   ├── Product/              # PRD, architecture, workflow
│   │   ├── Blueprint/            # Dev package generation
│   │   ├── AI/                   # AI provider abstraction
│   │   ├── Credits/              # Credit system
│   │   ├── Billing/              # Plans, subscriptions, Stripe
│   │   ├── Export/               # PDF, package export
│   │   ├── Admin/                # Administration
│   │   ├── Notifications/        # Notification system
│   │   └── Consent/              # Privacy consent
│   ├── Http/Controllers/         # Thin controllers
│   └── Providers/                # Service providers
├── resources/
│   ├── js/                       # Vue components and pages
│   └── css/                      # Design system tokens
├── database/
│   ├── migrations/               # Database schema
│   └── seeders/                  # Development seed data
├── docs/                         # Project documentation
├── tests/                        # Test suites
├── AGENTS.md                     # AI agent instructions
├── CLAUDE.md                     # Claude-specific instructions
└── README.md                     # This file
```

---

## Running Tests

```bash
# All PHP tests
./vendor/bin/sail artisan test

# Specific test suite
./vendor/bin/sail artisan test --testsuite=Unit
./vendor/bin/sail artisan test --testsuite=Feature

# Frontend tests
./vendor/bin/sail npm run test

# E2E tests
./vendor/bin/sail npm run test:e2e
```

---

## Documentation

All project documentation is in the [`docs/`](file:///c:/xampp/htdocs/1/f/docs) directory:

| Document | Description |
|---|---|
| [00-discovery.md](file:///c:/xampp/htdocs/1/f/docs/00-discovery.md) | Market research and competitive analysis |
| [01-prd.md](file:///c:/xampp/htdocs/1/f/docs/01-prd.md) | Product requirements document |
| [02-saas-business-model.md](file:///c:/xampp/htdocs/1/f/docs/02-saas-business-model.md) | Plans, credits, billing |
| [03-architecture.md](file:///c:/xampp/htdocs/1/f/docs/03-architecture.md) | Technical architecture |
| [04-architecture-essentials.md](file:///c:/xampp/htdocs/1/f/docs/04-architecture-essentials.md) | Architectural rules (single source of truth) |
| [05-hard-questions.md](file:///c:/xampp/htdocs/1/f/docs/05-hard-questions.md) | Edge cases and solutions |
| [06-development-workflow.md](file:///c:/xampp/htdocs/1/f/docs/06-development-workflow.md) | How to build features |
| [07-testing-strategy.md](file:///c:/xampp/htdocs/1/f/docs/07-testing-strategy.md) | Testing approach |
| [08-security.md](file:///c:/xampp/htdocs/1/f/docs/08-security.md) | Security requirements |
| [09-git-github.md](file:///c:/xampp/htdocs/1/f/docs/09-git-github.md) | Git workflow |
| [10-ai-system.md](file:///c:/xampp/htdocs/1/f/docs/10-ai-system.md) | AI provider architecture |
| [11-privacy-data-learning.md](file:///c:/xampp/htdocs/1/f/docs/11-privacy-data-learning.md) | Privacy and learning system |
| [12-ui-design-system.md](file:///c:/xampp/htdocs/1/f/docs/12-ui-design-system.md) | Design system |
| [13-product-roadmap.md](file:///c:/xampp/htdocs/1/f/docs/13-product-roadmap.md) | Implementation phases |

---

## Contributing

1. Read `AGENTS.md` and `docs/04-architecture-essentials.md` before writing code
2. Follow the module architecture — every feature belongs to a module
3. Write tests for authorization, validation, and edge cases
4. Never commit secrets or environment files
5. Use meaningful commit messages following the convention in `docs/09-git-github.md`

---

## License

Proprietary. All rights reserved.
