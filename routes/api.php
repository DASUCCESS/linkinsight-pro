<?php

use App\Http\Controllers\Api\LinkedinSyncController;
use App\Http\Controllers\Api\LinkedinDashboardController;
use Illuminate\Support\Facades\Route;

// Extension + API clients
Route::post('/linkedin/sync/profile', [LinkedinSyncController::class, 'syncProfile']);
Route::post('/linkedin/sync/posts', [LinkedinSyncController::class, 'syncPosts']);

// Dashboard summary still protected by Sanctum
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/linkedin/dashboard/summary', [LinkedinDashboardController::class, 'summary']);
});
