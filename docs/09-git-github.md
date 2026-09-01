# FORGE — Git & GitHub Strategy

**Document version:** 1.0  
**Date:** 2026-09-01  
**Status:** DRAFT  

---

## 1. Repository Setup

### 1.1 Git Initialization

```bash
git init
git add .
git commit -m "Initial project scaffold"
```

### 1.2 .gitignore

```
# Environment
.env
.env.backup
.env.production

# Dependencies
/vendor/
/node_modules/

# Build output
/public/build/
/public/hot

# Laravel
/storage/*.key
/storage/logs/
/storage/framework/cache/data/
/storage/framework/sessions/
/storage/framework/views/
/bootstrap/cache/

# IDE
/.idea/
/.vscode/
*.swp
*.swo

# OS
.DS_Store
Thumbs.db

# Testing
.phpunit.result.cache
/coverage/

# Docker
/docker-compose.override.yml
```

---

## 2. Commit Strategy

### 2.1 Commit Message Format

```
<type>(<scope>): <description>

[optional body]
```

**Types:**
- `feat` — new feature
- `fix` — bug fix
- `refactor` — code restructuring without behavior change
- `docs` — documentation changes
- `test` — adding or updating tests
- `chore` — maintenance, dependencies, configuration
- `style` — formatting, no logic change
- `perf` — performance improvement

**Examples:**
```
feat(projects): add project classification service
fix(credits): prevent race condition in concurrent reservation
docs(architecture): update AI provider routing diagram
test(billing): add webhook idempotency test
chore(deps): update laravel/framework to 12.1
```

### 2.2 Commit Frequency

- Commit after each logical milestone
- Never create one massive commit for an entire feature
- A feature spanning multiple files should have multiple commits if they represent distinct logical changes

---

## 3. Branch Strategy

### 3.1 Pre-Launch (Solo/Small Team)

```
main ←── feature/auth-oauth
     ←── feature/project-creation
     ←── feature/credit-system
```

Direct merge to `main` is acceptable during early development.

### 3.2 Post-Launch

```
main (production) ←── develop ←── feature/{name}
                                   hotfix/{name}
```

---

## 4. What Never Gets Committed

- `.env` files (any environment)
- API keys, secrets, credentials
- OAuth client secrets
- Stripe keys
- Private certificates
- Database dumps
- User data exports
- IDE-specific configuration (personal)

---

## 5. GitHub Repository Settings (When Created)

- Private repository (until public launch decision)
- Branch protection on `main` (post-launch)
- Require PR reviews (post-launch, if team)
- `.gitignore` as defined above
- `README.md` with setup instructions

---

## 6. GitHub Integration Architecture (FORGE Feature — Future)

### 6.1 Permission Model

GitHub Sign-In and GitHub Repository Integration are **separate capabilities**:

| Capability | Scope | Purpose |
|---|---|---|
| **Sign In** | `user:email`, `read:user` | Authentication only |
| **Repository Read** | `repo` (read) | Analyze repository structure, code, dependencies |
| **Repository Write** | `repo` (write) | Create repository, push generated package |

### 6.2 Safety Rules

- **Never delete** a repository
- **Never force push**
- **Never merge** branches
- **Never overwrite** existing significant content
- **Always create branches** for generated content
- **Always show** what will be created before writing
- **Always require** explicit confirmation before write operations

### 6.3 Token Management

- GitHub OAuth tokens encrypted at rest
- Tokens refreshed when close to expiry
- Revoked tokens detected and handled gracefully
- Token scope is the minimum required for the operation

---

*GitHub integration is a future feature. The architecture is documented now to ensure design-time compatibility.*
