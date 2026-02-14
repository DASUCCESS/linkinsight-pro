<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\LinkedinPost;
use App\Services\LinkedinAnalyticsService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(
        protected LinkedinAnalyticsService $analyticsService
    ) {
    }

    public function index(Request $request)
    {
        $user      = $request->user();
        $profileId = $request->query('profile_id');
        $from      = $request->query('from');
        $to        = $request->query('to');
        $search    = $request->query('q');
        $type      = $request->query('type');

        $summary = $this->analyticsService->getSummaryForUser($user, $profileId ? (int) $profileId : null, $from, $to);

        $postsPaginated = null;

        if (($summary['status'] ?? 'empty') === 'ok') {
            $profile = $summary['profile'] ?? null;

            if ($profile) {
                $postsQuery = LinkedinPost::query()
                    ->where('linkedin_profile_id', $profile['id']);

                if ($search) {
                    $postsQuery->where(function ($q) use ($search) {
                        $q->where('content_excerpt', 'like', '%' . $search . '%')
                            ->orWhere('permalink', 'like', '%' . $search . '%');
                    });
                }

                if ($type) {
                    $postsQuery->where('post_type', $type);
                }

                $postsPaginated = $postsQuery
                    ->with('latestMetric')
                    ->orderByDesc('posted_at')
                    ->paginate(10)
                    ->withQueryString();
            }
        }

        return view('user.analytics.index', compact('summary', 'postsPaginated'));
    }
}
