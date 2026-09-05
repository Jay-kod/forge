<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Actions;

use App\Modules\Projects\Models\Project;

class GenerateMasterPromptAction
{
    public function execute(Project $project): string
    {
        $project->loadMissing(['documents', 'evidence', 'competitors']);

        $prd = $project->documents->firstWhere('type', 'prd')?->content ?? $project->description;
        $arch = $project->documents->firstWhere('type', 'architecture')?->content ?? 'Modern modular architecture with server-side authorization and strict type safety.';
        $competitors = $project->competitors->pluck('name')->implode(', ') ?: 'None specified';

        return <<<PROMPT
# FORGE Master Prompt — {$project->title}

You are an expert principal engineer and product architect.
You are tasked with building **{$project->title}** according to rigorous production standards.

---

## 1. Project Context & Objectives
- **Title:** {$project->title}
- **Classification:** {$project->classification->label()}
- **Known Competitors:** {$competitors}

---

## 2. Core Operating Rules (Non-Negotiable)
1. **Spec-First Engineering:** Always read the PRD and Architecture files first before touching code.
2. **Server-Side Authorization:** Never trust the client. Enforce row-level ownership and policy gates on every operation.
3. **Strict Type Safety:** Use strict typing, explicit DTOs, and schema-validated contracts. Avoid loose untyped arrays.
4. **Thin Controllers:** Controllers only validate input and delegate immediately to single-responsibility Actions or Services.
5. **Acyclic Modules:** Maintain strict domain isolation. No circular dependencies between domain modules.
6. **Atomic Transactions:** Wrap critical state changes (credits, billing, inventory) in database transactions with row locks.
7. **Test Every Edge Case:** Implement automated tests for happy paths, failure conditions, and concurrency races.

---

## 3. Product Requirements Document (PRD)
{$prd}

---

## 4. System Architecture Specification
{$arch}

---

## 5. First Implementation Milestone
1. Initialize core domain models and database migrations.
2. Implement backend service contracts and single-purpose Actions.
3. Build authenticated API/Inertia endpoints with FormRequest validation and Policy checks.
4. Construct responsive, accessible frontend views with light and dark mode support.
5. Verify complete test suite coverage before shipping.
PROMPT;
    }
}
