<?php

use App\Http\Controllers\Api\LinkedinSyncController;
use App\Http\Controllers\Api\LinkedinDashboardController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/linkedin/sync/profile', [LinkedinSyncController::class, 'syncProfile']);
    Route::post('/linkedin/sync/posts', [LinkedinSyncController::class, 'syncPosts']);

    Route::get('/linkedin/dashboard/summary', [LinkedinDashboardController::class, 'summary']);
});
