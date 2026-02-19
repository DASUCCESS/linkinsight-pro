<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\LinkedinAnalyticsService;
use Illuminate\Http\Request;

class LinkedinConnectionsController extends Controller
{
    public function __construct(
        protected LinkedinAnalyticsService $analyticsService
    ) {
    }

    public function index(Request $request)
    {
        $user      = $request->user();
        $profileId = $request->query('profile_id');

        $q    = $request->query('q');
        $from = $request->query('from');
        $to   = $request->query('to');

        $data = $this->analyticsService->getConnectionsForUser(
            user: $user,
            profileId: $profileId ? (int) $profileId : null,
            q: $q,
            fromDate: $from,
            toDate: $to
        );

        return view('user.linkedin.connections.index', compact('data'));
    }
}
