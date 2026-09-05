<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Services;

use App\Models\User;
use App\Modules\Blueprint\Actions\GenerateMasterPromptAction;
use App\Modules\Blueprint\Contracts\BlueprintServiceInterface;
use App\Modules\Blueprint\DTOs\DevPackage;
use App\Modules\Blueprint\DTOs\PackageFile;
use App\Modules\Projects\Models\Project;

class BlueprintService implements BlueprintServiceInterface
{
    public function __construct(
        protected GenerateMasterPromptAction $masterPromptAction,
        protected PackageAssembler $packageAssembler
    ) {}

    public function generatePackage(User $user, Project $project): DevPackage
    {
        $project->loadMissing(['documents', 'evidence', 'competitors', 'context']);

        $prdDoc = $project->documents->firstWhere('type', 'prd')?->content
            ?? "# Product Requirements Document: {$project->title}\n\n## Overview\n{$project->description}\n\n## Classification\n{$project->classification->label()}";

        $archDoc = $project->documents->firstWhere('type', 'architecture')?->content
            ?? "# System Architecture: {$project->title}\n\n## Stack\n- Modular Architecture\n- Server-side validation\n- Responsive client";

        $strategyDoc = $project->documents->firstWhere('type', 'strategy')?->content
            ?? "# Strategic Recommendation: {$project->title}\n\nFocus on rapid value validation and defensible differentiation.";

        $competitorsList = $project->competitors->pluck('name')->implode(', ') ?: 'General industry solutions';
        $masterPrompt = $this->masterPromptAction->execute($project);

        $agentsMd = <<<AGENTS
# Agent Operating Instructions — {$project->title}

> **Read this file first.** Then read PRD and Architecture documents in order.

---

## Identity & Mission
You are building **{$project->title}**, a high-leverage application classified as **{$project->classification->label()}**.
Primary objective: {$project->description}

---

## Core Operating Rules

### 1. Specification Fidelity
- Never invent business logic that contradicts `docs/01-prd.md`.
- Evidence and verified requirements take precedence over LLM assumptions.

### 2. Architecture & Modularity
- Implement domain features within modular boundaries (`app/Modules/{Domain}/`).
- Controllers must remain thin (validate -> delegate to Action/Service -> return response).
- Pass cross-domain data using typed DTOs or events; avoid cross-module database joins.

### 3. Server-Side Security & Invariants
- Enforce strict server-side authorization on every endpoint (`user_id = auth()->id()`).
- All state-changing mutations require database transactions with appropriate locking.
- Input validation is mandatory on every request via FormRequest classes.

### 4. Code Standards & Testing
- Use strict typing throughout (`declare(strict_types=1)`).
- Write automated tests for both the happy path and critical edge cases (auth failures, concurrency, bad input).
AGENTS;

        $claudeMd = <<<CLAUDE
# Claude Code Guidelines — {$project->title}

## Build & Test Commands
- Backend tests: `php artisan test`
- Single test: `php artisan test --filter={TestName}`
- Type check / lint: `composer test`
- Frontend build: `npm run build`
- Frontend dev server: `npm run dev`

## Code Standards
- Strict typing on all PHP classes.
- Thin controllers delegating to single-purpose Actions.
- Component-based frontend with clear props/emits interfaces.
- Test both light and dark mode for UI components.
CLAUDE;

        $architectureEssentials = <<<ARCH
# Architecture Essentials — {$project->title}

## 1. Stack Decisions
- **Backend:** Modular Laravel 12.x / PHP 8.4+
- **Frontend:** Vue 3 Composition API with Inertia.js
- **Database:** Relational with strict foreign keys and JSON context fields
- **Caching & Queues:** Redis for rate limiting and background processing

## 2. Cross-Cutting Concerns
- **Authentication:** Server-side session & token validation.
- **Authorization:** Laravel Policies gating model access.
- **Error Handling:** Graceful error envelopes and clear user feedback.
ARCH;

        $hardQuestions = <<<HARD
# Pre-Mortem & Hard Questions — {$project->title}

## Key Risks & Mitigations
1. **Competitive Pressure:** Known competitors ({$competitorsList}) have brand recognition.
   - *Mitigation:* Focus aggressively on the core differentiator; avoid broad feature parity.
2. **Data Integrity:** Concurrent mutations could cause race conditions.
   - *Mitigation:* Use row-level locking (`SELECT ... FOR UPDATE`) in transactions.
3. **User Churn:** Failure to demonstrate immediate ROI in first session.
   - *Mitigation:* Zero-friction onboarding and instant intelligence previews.
HARD;

        $testingStrategy = <<<TEST
# Testing Strategy — {$project->title}

## Testing Layers
1. **Unit Tests:** Fast, isolated tests for pure domain logic, Actions, and DTOs.
2. **Feature Tests:** HTTP tests asserting endpoint status, authorization gates, and database mutations.
3. **Concurrency Tests:** Verify database row locking and atomic credit/resource operations under load.
TEST;

        $readme = <<<README
# {$project->title}

Generated by **FORGE** — Framework for Opportunity, Research, Growth & Execution.

## Classification
**{$project->classification->label()}**

## Description
{$project->description}

## Included Specification Documents
- `AGENTS.md` — Autonomous AI coding agent operating manual
- `CLAUDE.md` — Claude Code / Cursor shortcut & build cheat-sheet
- `MASTER-PROMPT.md` — Copy-to-clipboard master prompt for zero-shot LLM onboarding
- `docs/01-prd.md` — Product Requirements Document
- `docs/03-architecture.md` — Technical System Architecture
- `docs/04-architecture-essentials.md` — Stack invariants and architecture rules
- `docs/05-hard-questions.md` — Risk pre-mortem and edge-case solutions
- `docs/07-testing-strategy.md` — Test suite guidelines and coverage expectations
README;

        $files = [
            new PackageFile('README.md', $readme, 'Project overview and package manifest'),
            new PackageFile('AGENTS.md', $agentsMd, 'Agent instructions for AI pair programming'),
            new PackageFile('CLAUDE.md', $claudeMd, 'Claude Code and CLI instructions'),
            new PackageFile('MASTER-PROMPT.md', $masterPrompt, 'Complete master prompt for LLMs'),
            new PackageFile('docs/01-prd.md', $prdDoc, 'Evidence-linked PRD'),
            new PackageFile('docs/03-architecture.md', $archDoc, 'System Architecture specification'),
            new PackageFile('docs/04-architecture-essentials.md', $architectureEssentials, 'Architecture rules & stack invariants'),
            new PackageFile('docs/05-hard-questions.md', $hardQuestions, 'Risk pre-mortem and defensive strategies'),
            new PackageFile('docs/07-testing-strategy.md', $testingStrategy, 'Testing strategy and test boundaries'),
        ];

        return new DevPackage(
            projectName: $project->title,
            version: '1.0.0',
            files: $files,
            masterPrompt: $masterPrompt
        );
    }

    public function generateMasterPrompt(Project $project): string
    {
        return $this->masterPromptAction->execute($project);
    }

    public function assembleZip(DevPackage $package): string
    {
        return $this->packageAssembler->assemble($package);
    }
}
