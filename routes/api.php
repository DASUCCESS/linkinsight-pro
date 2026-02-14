<?php

use App\Http\Controllers\Api\LinkedinSyncController;
use App\Http\Controllers\Api\LinkedinDashboardController;
use App\Http\Controllers\Api\LinkedinInsightsController;
use App\Http\Controllers\Api\LinkedinExportController;
use Illuminate\Support\Facades\Route;

// Extension + API clients (token-based or sanctum)
Route::post('/linkedin/sync/profile', [LinkedinSyncController::class, 'syncProfile']);
Route::post('/linkedin/sync/posts', [LinkedinSyncController::class, 'syncPosts']);

// New extension-ready endpoints
Route::post('/linkedin/sync/audience-demographics', [LinkedinSyncController::class, 'syncAudienceDemographics']);
Route::post('/linkedin/sync/creator-audience', [LinkedinSyncController::class, 'syncCreatorAudience']);
Route::post('/linkedin/sync/connections', [LinkedinSyncController::class, 'syncConnections']);

// Dashboard APIs (Sanctum)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/linkedin/dashboard/summary', [LinkedinDashboardController::class, 'summary']);

    Route::get('/linkedin/insights/audience', [LinkedinInsightsController::class, 'audienceInsights']);
    Route::get('/linkedin/insights/recommendations', [LinkedinInsightsController::class, 'recommendations']);
    Route::get('/linkedin/insights/benchmark', [LinkedinInsightsController::class, 'competitorBenchmark']);
    Route::get('/linkedin/connections/directory', [LinkedinInsightsController::class, 'connectionsDirectory']);

    Route::get('/linkedin/export/posts.csv', [LinkedinExportController::class, 'exportPostsCsv']);
});
