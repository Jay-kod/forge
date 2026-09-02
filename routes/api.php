<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\ProjectApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| FORGE Public Intelligence API Routes (v1)
|--------------------------------------------------------------------------
|
| Authenticated via Bearer token (hashed API key) using AuthenticateApiKey.
| Rate limited to 60 requests/minute per client.
|
*/

Route::prefix('v1')->middleware([\App\Http\Middleware\AuthenticateApiKey::class, 'throttle:60,1'])->group(function () {
    Route::get('/projects', [ProjectApiController::class, 'index'])->name('api.v1.projects.index');
    Route::post('/projects', [ProjectApiController::class, 'store'])->name('api.v1.projects.store');
    Route::get('/projects/{project}', [ProjectApiController::class, 'show'])->name('api.v1.projects.show');
    Route::get('/projects/{project}/opportunities', [ProjectApiController::class, 'opportunities'])->name('api.v1.projects.opportunities');
    Route::get('/projects/{project}/graph', [ProjectApiController::class, 'graph'])->name('api.v1.projects.graph');
});
