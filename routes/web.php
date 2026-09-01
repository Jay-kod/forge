<?php

declare(strict_types=1);

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ProjectController;
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

    // Workflow Stages & Decisions
    Route::post('/projects/{project}/stages/{stage}/advance', [WorkflowController::class, 'advance'])->name('workflow.advance');
    Route::post('/projects/{project}/stages/{stage}/approve', [WorkflowController::class, 'approve'])->name('workflow.approve');
    Route::post('/projects/{project}/stages/{stage}/decide', [WorkflowController::class, 'decide'])->name('workflow.decide');

    // Exports
    Route::get('/projects/{project}/export/package', [ExportController::class, 'downloadPackage'])->name('export.package');
    Route::get('/projects/{project}/export/pdf', [ExportController::class, 'downloadPdf'])->name('export.pdf');
});
