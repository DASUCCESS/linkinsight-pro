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
    ) {
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $summary = $this->analyticsService->getSummaryForUser($user, null);

        $syncJobs = LinkedinSyncJob::query()
            ->where('user_id', $user->id)
            ->when($request->query('sync_status'), function ($q, $status) {
                $q->where('status', $status);
            })
            ->when($request->query('sync_type'), function ($q, $type) {
                $q->where('type', $type);
            })
            ->orderByDesc('created_at')
            ->paginate(5)
            ->withQueryString();

        return view('user.dashboard.index', compact('summary', 'syncJobs'));
    }
}
