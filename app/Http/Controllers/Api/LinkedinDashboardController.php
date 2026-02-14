<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LinkedinAnalyticsService;
use Illuminate\Http\Request;

class LinkedinDashboardController extends Controller
{
    public function __construct(
        protected LinkedinAnalyticsService $analyticsService
    ) {}

    public function summary(Request $request)
    {
        $user      = $request->user();
        $profileId = $request->query('profile_id');

        $summary = $this->analyticsService->getSummaryForUser($user, $profileId);

        return response()->json($summary);
    }
}
