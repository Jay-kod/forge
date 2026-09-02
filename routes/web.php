<?php

declare(strict_types=1);

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\GitHubController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ResearchController;
use App\Http\Controllers\WorkflowController;
use Illuminate\Support\Facades\Route;

// Public / Guest Routes
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('projects.index')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
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
    Route::post('/projects/{project}/research/refresh', [ResearchController::class, 'refresh'])->name('research.refresh');

    // Workflow Stages & Decisions
    Route::post('/projects/{project}/stages/{stage}/advance', [WorkflowController::class, 'advance'])->name('workflow.advance');
    Route::post('/projects/{project}/stages/{stage}/approve', [WorkflowController::class, 'approve'])->name('workflow.approve');
    Route::post('/projects/{project}/stages/{stage}/decide', [WorkflowController::class, 'decide'])->name('workflow.decide');
    Route::post('/projects/{project}/stages/{stage}/rerun', [\App\Http\Controllers\ProjectVersionController::class, 'rerun'])->name('workflow.rerun');

    // Project Versions & Decision Timeline
    Route::get('/projects/{project}/versions', [\App\Http\Controllers\ProjectVersionController::class, 'index'])->name('projects.versions.index');
    Route::get('/projects/{project}/versions/{v1}/diff/{v2}', [\App\Http\Controllers\ProjectVersionController::class, 'diff'])->name('projects.versions.diff');
    Route::get('/projects/{project}/timeline', [\App\Http\Controllers\ProjectVersionController::class, 'timeline'])->name('projects.timeline');
    Route::get('/projects/{project}/graph', [\App\Http\Controllers\OpportunityGraphController::class, 'show'])->name('projects.graph');

    // In-App Alerts & Notifications
    Route::get('/alerts', [\App\Http\Controllers\AlertController::class, 'index'])->name('alerts.index');
    Route::post('/alerts/{alert}/read', [\App\Http\Controllers\AlertController::class, 'markRead'])->name('alerts.read');
    Route::post('/alerts/read-all', [\App\Http\Controllers\AlertController::class, 'markAllRead'])->name('alerts.read_all');

    // Billing & Subscriptions
    Route::post('/billing/checkout', [BillingController::class, 'checkout'])->name('billing.checkout');
    Route::get('/billing/portal', [BillingController::class, 'portal'])->name('billing.portal');

    // Exports
    Route::get('/projects/{project}/export/package', [ExportController::class, 'downloadPackage'])->name('export.package');
    Route::get('/projects/{project}/export/pdf', [ExportController::class, 'downloadPdf'])->name('export.pdf');
    Route::get('/projects/{project}/export/growth-plan', [ExportController::class, 'downloadGrowthPlanPdf'])->name('export.growth-plan');

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
    });
});
