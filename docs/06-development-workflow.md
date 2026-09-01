# FORGE — Development Workflow

**Document version:** 1.0  
**Date:** 2026-09-01  
**Status:** DRAFT  

---

## 1. Development Workflow Protocol

For every implementation task, follow this sequence:

1. **Read** the relevant requirement from PRD or task description
2. **Read** Architecture Essentials for applicable rules
3. **Inspect** current code in the affected module(s)
4. **Identify** which module(s) will be affected
5. **Plan** the smallest correct change
6. **Implement** following module architecture rules
7. **Test** the happy path
8. **Test** failure cases and edge cases
9. **Review** security and authorization implications
10. **Review** UI behavior (both light and dark mode)
11. **Review** the diff — is it minimal and focused?
12. **Update** documentation if behavior changed
13. **Commit** with a meaningful message
14. **Push** when authorized
15. **Verify** repository state

---

## 2. Module Development Pattern

### 2.1 New Feature in Existing Module

```
1. Define the Action or Service method
2. Create FormRequest for input validation
3. Update Policy if authorization changes
4. Create/update Model if data changes
5. Create migration if schema changes
6. Create Controller method (thin)
7. Create/update Vue page or component
8. Write tests (unit for action/service, feature for HTTP)
9. Verify module boundaries are respected
```

### 2.2 New Module

```
1. Create module directory under app/Modules/{ModuleName}/
2. Create Models/, Actions/, Services/, Policies/, Requests/, Events/, Enums/, Tests/
3. Define service contract (interface) if other modules will depend on this
4. Register service provider bindings
5. Create routes
6. Create migrations
7. Create controller(s) in Http/Controllers/{ModuleName}/
8. Implement core logic
9. Write tests
10. Document in Architecture Essentials if it introduces new patterns
```

---

## 3. Code Standards

### 3.1 PHP / Laravel

- PHP 8.4+ features (readonly properties, enums, match expressions, named arguments)
- Strict typing: `declare(strict_types=1)` in all files
- PSR-12 coding standard
- Actions pattern for single-purpose operations
- Service classes for complex multi-step operations
- FormRequest for all input validation
- Eloquent relationships defined explicitly
- No raw queries unless performance-critical and documented

### 3.2 Vue / TypeScript

- Composition API with `<script setup lang="ts">`
- TypeScript for all component logic
- Props defined with TypeScript interfaces
- Emits defined explicitly
- Composables for reusable logic
- Components in PascalCase
- CSS scoped to component or using design tokens

### 3.3 Database

- Migrations are incremental (never modify existing migrations after they've run)
- Foreign keys enforced
- Indexes on frequently queried columns
- Enum columns for fixed-set values
- JSON columns for flexible structured data
- Timestamps on all tables

---

## 4. Branch Strategy

### 4.1 V1 (Pre-Launch)

Simple trunk-based development:
- `main` — stable, deployable
- `feature/{name}` — feature branches from main
- Merge via pull request (or direct merge during solo development)

### 4.2 Post-Launch

- `main` — production
- `develop` — integration branch
- `feature/{name}` — feature work
- `hotfix/{name}` — critical production fixes

---

## 5. Environment Configuration

```
.env.example contains all required variables with safe defaults
.env is NEVER committed (in .gitignore)

Required environment variables:
    APP_NAME=FORGE
    APP_ENV=local|staging|production
    APP_KEY=
    APP_URL=
    
    DB_CONNECTION=mysql
    DB_HOST=
    DB_PORT=3306
    DB_DATABASE=forge
    DB_USERNAME=
    DB_PASSWORD=
    
    REDIS_HOST=
    REDIS_PORT=6379
    
    STRIPE_KEY=
    STRIPE_SECRET=
    STRIPE_WEBHOOK_SECRET=
    
    GOOGLE_CLIENT_ID=
    GOOGLE_CLIENT_SECRET=
    GOOGLE_REDIRECT_URI=
    
    GITHUB_CLIENT_ID=
    GITHUB_CLIENT_SECRET=
    GITHUB_REDIRECT_URI=
    
    ANTHROPIC_API_KEY=
    OPENAI_API_KEY=
    GOOGLE_AI_API_KEY=
    
    QUEUE_CONNECTION=redis
    SESSION_DRIVER=redis
    CACHE_STORE=redis
```

---

## 6. Local Development Setup

```bash
# Clone
git clone <repository-url> forge
cd forge

# Start Docker environment
./vendor/bin/sail up -d

# Install dependencies
./vendor/bin/sail composer install
./vendor/bin/sail npm install

# Configure
cp .env.example .env
./vendor/bin/sail artisan key:generate

# Database
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed

# Build frontend
./vendor/bin/sail npm run dev

# Access
http://localhost
```

---

*Development workflow will be refined as the team and project mature.*
