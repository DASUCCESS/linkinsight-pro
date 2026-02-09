<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\LinkedinSyncJob;
use App\Services\LinkedinAnalyticsService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected LinkedinAnalyticsService $analyticsService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $summary = $this->analyticsService->getSummaryForUser($user, null);

        $syncJobs = LinkedinSyncJob::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('user.dashboard.index', compact('summary', 'syncJobs'));
    }
}
