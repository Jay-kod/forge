# FORGE — Security

**Document version:** 1.0  
**Date:** 2026-09-01  
**Status:** DRAFT  

---

## 1. Security Principles

1. **Defense in depth** — multiple layers of protection
2. **Least privilege** — users and services access only what they need
3. **Server-side authority** — the server is the single source of truth for authorization
4. **Secure by default** — features ship locked down, not open

---

## 2. Authentication Security

| Concern | Mitigation |
|---|---|
| OAuth state injection | CSRF token validated on OAuth callback; `state` parameter verified |
| Session hijacking | Secure, HttpOnly, SameSite=Lax cookies; session regeneration on login |
| Session fixation | `session()->regenerate()` after successful authentication |
| Brute force | Rate limiting: 5 login attempts per minute per IP |
| Account enumeration | Generic error messages on auth failure |
| Token storage | OAuth tokens encrypted at rest using Laravel's `Crypt` facade |

---

## 3. Authorization Security

| Concern | Mitigation |
|---|---|
| Horizontal privilege escalation | Every query scoped to `user_id = auth()->id()` |
| Vertical privilege escalation | Entitlement checks are server-side; client cannot determine own plan |
| Missing authorization | Laravel Policies on all models; middleware on all routes |
| Admin access | Admin routes behind `role:admin` middleware + IP restriction in production |

---

## 4. Data Protection

| Data Type | Protection |
|---|---|
| Passwords | N/A — OAuth only, no passwords stored |
| OAuth tokens | AES-256-CBC encryption via Laravel Crypt |
| AI API keys | Environment variables only; never in database or logs |
| Stripe webhook secret | Environment variable; signature verification on every webhook |
| User project data | Isolated by user_id; never exposed cross-user |
| Credit card info | Never touches FORGE servers — handled entirely by Stripe |
| Session data | Server-side storage (Redis); session ID in secure cookie |

---

## 5. Input Validation

- **Every request** uses Laravel FormRequest with explicit validation rules
- **No raw user input** is passed to AI prompts without sanitization
- **File uploads** (future): whitelist extensions, size limits, stored outside web root
- **JSON inputs**: validated against expected structure
- **URL inputs**: validated format, no local/internal URLs (SSRF prevention)

---

## 6. API & Request Security

| Concern | Mitigation |
|---|---|
| CSRF | Laravel's `@csrf` on all forms; verified middleware on all state-changing routes |
| XSS | Vue's default escaping; no `v-html` with user content; CSP headers |
| SQL injection | Eloquent ORM parameterized queries; no raw queries with user input |
| SSRF | URL validation; no fetching of local/internal addresses |
| Mass assignment | `$fillable` defined on all models; no `$guarded = []` |
| Rate limiting | Applied to: auth routes, AI operations, webhook endpoint, export operations |
| CORS | Configured for application domain only |

---

## 7. Infrastructure Security

| Concern | Mitigation |
|---|---|
| Secrets in source control | `.env` in `.gitignore`; CI secrets via environment variables |
| Debug in production | `APP_DEBUG=false` in production; error details never exposed |
| SQL errors | Custom error pages; no stack traces in production responses |
| Log exposure | Log files not accessible via web; sensitive data not logged |
| Dependency vulnerabilities | Regular `composer audit` and `npm audit` |

---

## 8. Webhook Security

```php
// Stripe webhook verification pattern
public function handleWebhook(Request $request): Response
{
    $signature = $request->header('Stripe-Signature');
    
    try {
        $event = Webhook::constructEvent(
            $request->getContent(),
            $signature,
            config('services.stripe.webhook_secret')
        );
    } catch (SignatureVerificationException $e) {
        return response('Invalid signature', 400);
    }
    
    // Check idempotency
    if (BillingEvent::where('stripe_event_id', $event->id)->exists()) {
        return response('Already processed', 200);
    }
    
    // Process event
    $this->processEvent($event);
    
    return response('OK', 200);
}
```

---

## 9. AI Security

| Concern | Mitigation |
|---|---|
| Prompt injection | User input is sanitized and placed in structured prompt templates, not concatenated |
| Data leakage to AI providers | No user PII in system prompts; project data sent only for processing |
| AI key exposure | Keys in environment variables; never in client-side code or logs |
| Cost attacks | Rate limiting + credit system prevents abuse; max credits per operation enforced |

---

## 10. Audit Logging

Log security-relevant events:

| Event | Logged Data |
|---|---|
| Login success/failure | User ID, provider, IP, timestamp |
| Project access | User ID, project ID, action, timestamp |
| Entitlement check failure | User ID, capability, timestamp |
| Billing event | Event type, user ID, amount (no card details) |
| Credit operation | Type, amount, user ID, project ID |
| Admin action | Admin ID, action, target, timestamp |
| OAuth token refresh | User ID, provider, timestamp |

**Never log:** OAuth tokens, API keys, full AI prompts with sensitive data, payment card numbers.

---

## 11. Security Checklist (Pre-Launch)

- [ ] All routes require authentication except public pages
- [ ] Every project query is scoped to authenticated user
- [ ] Entitlement checks are server-side
- [ ] OAuth state parameter is validated
- [ ] Sessions regenerated on login
- [ ] Rate limiting on auth, AI, and export routes
- [ ] Webhook signatures verified
- [ ] CSP headers configured
- [ ] CORS restricted to application domain
- [ ] `APP_DEBUG=false` in production
- [ ] No secrets in source control
- [ ] `composer audit` and `npm audit` pass
- [ ] Error pages do not expose stack traces
- [ ] Audit logging is active

---

*Security review should be repeated before every major release.*
