<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\LinkedinAnalyticsService;
use Illuminate\Http\Request;

class LinkedinAudienceController extends Controller
{
    public function __construct(
        protected LinkedinAnalyticsService $analyticsService
    ) {
    }

    public function demographics(Request $request)
    {
        $user = $request->user();

        $profileId = $request->query('profile_id') ? (int) $request->query('profile_id') : null;
        $from      = $request->query('from');
        $to        = $request->query('to');

        $data = $this->analyticsService->getAudienceDemographicsForUser($user, $profileId, $from, $to);

        return view('user.linkedin.audience.demographics', [
            'data' => $data,
        ]);
    }

    public function creatorMetrics(Request $request)
    {
        $user = $request->user();

        $profileId = $request->query('profile_id') ? (int) $request->query('profile_id') : null;
        $from      = $request->query('from');
        $to        = $request->query('to');

        $data = $this->analyticsService->getCreatorAudienceMetricsForUser($user, $profileId, $from, $to);

        $latestMetrics = $data['latest']?->metrics ?? [];
        $data['latest_metrics_list'] = $this->analyticsService->normalizeCreatorMetrics($latestMetrics);

        return view('user.linkedin.audience.creator-metrics', [
            'data' => $data,
        ]);
    }
}
