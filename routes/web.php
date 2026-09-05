<?php

declare(strict_types=1);

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\GitHubController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ResearchController;
use App\Http\Controllers\WorkflowController;
use Illuminate\Support\Facades\Route;

// Health Check (Public, for Kubernetes / Docker / load-balancer probes)
Route::get('/healthz', HealthCheckController::class)->name('healthz');

// Public / Guest Routes
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('projects.index')
        : redirect()->route('login');
});

Route::middleware(['guest', 'throttle:auth'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/auth/{provider}/redirect', [AuthController::class, 'redirect'])->name('auth.redirect');
    Route::get('/auth/{provider}/callback', [AuthController::class, 'callback'])->name('auth.callback');
    Route::post('/demo-login', [AuthController::class, 'demoLogin'])->name('auth.demo');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Public pricing
Route::get('/pricing', [BillingController::class, 'pricing'])->name('pricing');

// Webhook endpoint (excluded from CSRF in bootstrap/app.php if needed)
Route::post('/webhook/stripe', [BillingController::class, 'webhook'])->name('webhook.stripe');

// Authenticated Application Routes
Route::middleware(['auth'])->group(function () {
    // Projects
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::post('/projects/{project}/archive', [ProjectController::class, 'archive'])->name('projects.archive');
    Route::post('/projects/{project}/research/refresh', [ResearchController::class, 'refresh'])->name('research.refresh')->middleware('throttle:ai');

    // Workflow Stages & Decisions
    Route::get('/projects/{project}/workflow/status', [WorkflowController::class, 'status'])->name('workflow.status');
    Route::post('/projects/{project}/stages/{stage}/advance', [WorkflowController::class, 'advance'])->name('workflow.advance')->middleware('throttle:ai');
    Route::post('/projects/{project}/stages/{stage}/approve', [WorkflowController::class, 'approve'])->name('workflow.approve');
    Route::post('/projects/{project}/stages/{stage}/decide', [WorkflowController::class, 'decide'])->name('workflow.decide');
    Route::post('/projects/{project}/stages/{stage}/rerun', [\App\Http\Controllers\ProjectVersionController::class, 'rerun'])->name('workflow.rerun')->middleware('throttle:ai');

    // Project Versions & Decision Timeline
    Route::get('/projects/{project}/versions', [\App\Http\Controllers\ProjectVersionController::class, 'index'])->name('projects.versions.index');
    Route::get('/projects/{project}/versions/{v1}/diff/{v2}', [\App\Http\Controllers\ProjectVersionController::class, 'diff'])->name('projects.versions.diff');
    Route::get('/projects/{project}/timeline', [\App\Http\Controllers\ProjectVersionController::class, 'timeline'])->name('projects.timeline');
    Route::get('/projects/{project}/graph', [\App\Http\Controllers\OpportunityGraphController::class, 'show'])->name('projects.graph');
    Route::post('/projects/{project}/feedback', [\App\Http\Controllers\LearningFeedbackController::class, 'store'])->name('projects.feedback');

    // In-App Alerts & Notifications
    Route::get('/alerts', [\App\Http\Controllers\AlertController::class, 'index'])->name('alerts.index');
    Route::post('/alerts/{alert}/read', [\App\Http\Controllers\AlertController::class, 'markRead'])->name('alerts.read');
    Route::post('/alerts/read-all', [\App\Http\Controllers\AlertController::class, 'markAllRead'])->name('alerts.read_all');

    // Organizations, Team Workspaces & Membership
    Route::get('/organizations', [\App\Http\Controllers\OrganizationController::class, 'index'])->name('organizations.index');
    Route::post('/organizations', [\App\Http\Controllers\OrganizationController::class, 'store'])->name('organizations.store');
    Route::get('/organizations/{organization}', [\App\Http\Controllers\OrganizationController::class, 'show'])->name('organizations.show');
    Route::post('/organizations/{organization}/invite', [\App\Http\Controllers\OrganizationController::class, 'invite'])->name('organizations.invite');
    Route::post('/organizations/invitations/{token}/accept', [\App\Http\Controllers\OrganizationController::class, 'acceptInvite'])->name('organizations.invitations.accept');
    Route::delete('/organizations/{organization}/members/{user}', [\App\Http\Controllers\OrganizationController::class, 'removeMember'])->name('organizations.members.remove');
    Route::patch('/organizations/{organization}/members/{user}/role', [\App\Http\Controllers\OrganizationController::class, 'updateRole'])->name('organizations.members.update-role');
    Route::get('/organizations/{organization}/audit-logs', [\App\Http\Controllers\AuditLogController::class, 'index'])->name('organizations.audit-logs.index');
    Route::get('/organizations/{organization}/audit-logs/export', [\App\Http\Controllers\AuditLogController::class, 'export'])->name('organizations.audit-logs.export');

    // API Key Management
    Route::get('/settings/api-keys', [\App\Http\Controllers\ApiKeyManagementController::class, 'index'])->name('api-keys.index');
    Route::post('/settings/api-keys', [\App\Http\Controllers\ApiKeyManagementController::class, 'store'])->name('api-keys.store');
    Route::delete('/settings/api-keys/{apiKey}', [\App\Http\Controllers\ApiKeyManagementController::class, 'destroy'])->name('api-keys.destroy');

    // Bring Your Own Key (BYOK) Credentials
    Route::get('/settings/byok', [\App\Http\Controllers\ByokController::class, 'index'])->name('byok.index');
    Route::post('/settings/byok', [\App\Http\Controllers\ByokController::class, 'store'])->name('byok.store');
    Route::delete('/settings/byok/{provider}', [\App\Http\Controllers\ByokController::class, 'destroy'])->name('byok.destroy');

    // Privacy & Consent Governance
    Route::get('/settings/privacy', [\App\Http\Controllers\PrivacyController::class, 'index'])->name('privacy.index');
    Route::post('/settings/privacy/consent', [\App\Http\Controllers\PrivacyController::class, 'updateConsent'])->name('privacy.consent');
    Route::get('/settings/privacy/export-data', [\App\Http\Controllers\PrivacyController::class, 'exportData'])->name('privacy.export-data');
    Route::delete('/settings/privacy/account', [\App\Http\Controllers\PrivacyController::class, 'destroyAccount'])->name('privacy.account.destroy');

    // Billing & Subscriptions
    Route::post('/billing/checkout', [BillingController::class, 'checkout'])->name('billing.checkout');
    Route::get('/billing/portal', [BillingController::class, 'portal'])->name('billing.portal');

    // Exports & Signed Temporary Download Links
    Route::post('/projects/{project}/export/signed-url', [ExportController::class, 'generateSignedUrl'])->name('export.signed-url');
    Route::get('/projects/{project}/export/package', [ExportController::class, 'downloadPackage'])->name('export.package')->middleware('throttle:export');
    Route::get('/projects/{project}/export/pdf', [ExportController::class, 'downloadPdf'])->name('export.pdf')->middleware('throttle:export');
    Route::get('/projects/{project}/export/growth-plan', [ExportController::class, 'downloadGrowthPlanPdf'])->name('export.growth-plan')->middleware('throttle:export');
    Route::get('/projects/{project}/export/package/signed', [ExportController::class, 'downloadPackage'])->name('export.package.signed')->middleware('signed');
    Route::get('/projects/{project}/export/pdf/signed', [ExportController::class, 'downloadPdf'])->name('export.pdf.signed')->middleware('signed');
    Route::get('/projects/{project}/export/growth-plan/signed', [ExportController::class, 'downloadGrowthPlanPdf'])->name('export.growth-plan.signed')->middleware('signed');

    // GitHub Repository Integration
    Route::get('/integrations/github/connect', [GitHubController::class, 'connect'])->name('github.connect');
    Route::get('/integrations/github/callback', [GitHubController::class, 'callback'])->name('github.callback');
    Route::post('/integrations/github/disconnect', [GitHubController::class, 'disconnect'])->name('github.disconnect');
    Route::get('/integrations/github/repositories', [GitHubController::class, 'repositories'])->name('github.repositories');
    Route::post('/projects/{project}/github/scan', [GitHubController::class, 'scan'])->name('projects.github.scan');
    Route::post('/projects/{project}/github/export', [GitHubController::class, 'export'])->name('projects.github.export');

    // Admin Panel (Admin role only)
    Route::middleware(['admin'])->prefix('admin')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::post('/users/{user}/credits', [AdminController::class, 'grantCredits'])->name('admin.users.credits');
        Route::post('/users/{user}/role', [AdminController::class, 'updateRole'])->name('admin.users.role');
        Route::get('/api-keys', [\App\Http\Controllers\AdminApiKeyController::class, 'index'])->name('admin.api-keys.index');
        Route::post('/api-keys/test', [\App\Http\Controllers\AdminApiKeyController::class, 'testConnection'])->name('admin.api-keys.test');
    });
});
