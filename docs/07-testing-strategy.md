# FORGE — Testing Strategy

**Document version:** 1.0  
**Date:** 2026-09-01  
**Status:** DRAFT  

---

## 1. Testing Philosophy

Tests exist to catch regressions, verify business rules, and ensure authorization. Tests should focus on behavior, not implementation details.

---

## 2. Testing Layers

| Layer | Tool | Focus | Coverage Target |
|---|---|---|---|
| **Unit** | PHPUnit | Actions, Services, Enums, DTOs | All business logic |
| **Feature/Integration** | PHPUnit (Laravel) | HTTP routes, controllers, middleware, database interactions | All API endpoints |
| **Frontend Unit** | Vitest | Vue components, composables | Critical UI components |
| **E2E** | Playwright | Complete user flows | Core user journeys |

---

## 3. What to Test

### 3.1 Always Test

- **Authorization** — User A cannot access User B's project
- **Entitlements** — Free user cannot access Pro features
- **Credit operations** — Reservation, consumption, refund, concurrent access
- **Billing webhooks** — Idempotency, state transitions
- **AI orchestration** — Provider fallback, failure handling, credit integration
- **Input validation** — Required fields, type constraints, size limits
- **Classification** — User input correctly maps to project type
- **Workflow transitions** — Stage progression, status changes

### 3.2 Test Edge Cases

- Empty inputs
- Very long inputs
- Special characters / Unicode
- Concurrent credit consumption
- Duplicate webhook events
- Expired sessions
- Rate limit enforcement

### 3.3 Do Not Over-Test

- Eloquent model attribute access (framework-level)
- Basic CRUD that's covered by feature tests
- CSS styling (E2E visual regression is separate)

---

## 4. Test Organization

```
tests/
├── Unit/
│   ├── Modules/
│   │   ├── Identity/
│   │   ├── Projects/
│   │   │   ├── ClassificationServiceTest.php
│   │   │   └── CreateProjectActionTest.php
│   │   ├── Credits/
│   │   │   ├── CreditServiceTest.php
│   │   │   └── ConcurrentCreditTest.php
│   │   ├── AI/
│   │   │   ├── AIOrchestrationTest.php
│   │   │   └── WorkloadRouterTest.php
│   │   ├── Billing/
│   │   │   ├── EntitlementServiceTest.php
│   │   │   └── WebhookIdempotencyTest.php
│   │   └── ...
│   └── Support/
│
├── Feature/
│   ├── Auth/
│   │   ├── OAuthLoginTest.php
│   │   └── SessionManagementTest.php
│   ├── Projects/
│   │   ├── ProjectCreationTest.php
│   │   ├── ProjectAccessTest.php
│   │   └── ProjectWorkflowTest.php
│   ├── Billing/
│   │   ├── SubscriptionTest.php
│   │   └── WebhookProcessingTest.php
│   └── ...
│
├── Frontend/
│   ├── components/
│   └── composables/
│
└── E2E/
    ├── auth.spec.ts
    ├── project-creation.spec.ts
    ├── workflow.spec.ts
    └── billing.spec.ts
```

---

## 5. Testing Patterns

### 5.1 Authorization Test Pattern

```php
public function test_user_cannot_access_other_users_project(): void
{
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $project = Project::factory()->for($owner)->create();

    $this->actingAs($intruder)
         ->get(route('projects.show', $project))
         ->assertForbidden();
}
```

### 5.2 Entitlement Test Pattern

```php
public function test_free_user_cannot_access_deep_research(): void
{
    $user = User::factory()->withPlan('free')->create();

    $this->actingAs($user)
         ->post(route('research.deep', $project))
         ->assertStatus(402); // Payment Required
}
```

### 5.3 Credit Concurrency Test Pattern

```php
public function test_concurrent_credit_consumption_prevents_double_spend(): void
{
    $user = User::factory()->create();
    $account = CreditAccount::factory()->for($user)->create(['balance' => 10]);

    // Simulate two concurrent reservations of 8 credits each
    // Only one should succeed
    $results = collect([8, 8])->map(fn ($amount) => 
        DB::transaction(fn () => $this->creditService->reserve($user, $amount))
    );

    $this->assertCount(1, $results->filter(fn ($r) => $r->success));
    $this->assertCount(1, $results->filter(fn ($r) => !$r->success));
}
```

### 5.4 Webhook Idempotency Test Pattern

```php
public function test_duplicate_webhook_is_ignored(): void
{
    $payload = $this->createStripeWebhookPayload('invoice.paid');
    
    // Process first time
    $this->postJson('/webhook/stripe', $payload)
         ->assertOk();
    
    // Process duplicate
    $this->postJson('/webhook/stripe', $payload)
         ->assertOk();
    
    // Assert only processed once
    $this->assertDatabaseCount('billing_events', 1);
}
```

---

## 6. CI Pipeline (Future)

```
Push → Lint (PHP CS Fixer + ESLint) → Unit Tests → Feature Tests → Build Frontend → E2E Tests → Deploy (if main)
```

---

## 7. Test Data

- Use Laravel Factories for test data generation
- Use Seeders for development environment only
- Never use production data in tests
- AI provider responses are mocked in tests (fakes/stubs)

---

*Testing strategy will evolve as the codebase grows. Focus on high-value tests that catch real bugs.*
