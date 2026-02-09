<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LinkedinAnalyticsService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(
        protected LinkedinAnalyticsService $analyticsService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $profileId = $request->query('profile_id');

        $summary = $this->analyticsService->getSummaryForUser($user, $profileId);

        return view('admin.analytics.index', compact('summary'));
    }
}
