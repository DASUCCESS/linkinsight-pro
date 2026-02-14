<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LinkedinConnection;
use App\Models\LinkedinPost;
use App\Models\LinkedinPostMetric;
use App\Models\LinkedinProfile;
use App\Models\LinkedinProfileMetric;
use App\Models\LinkedinAudienceDemographic;
use App\Models\LinkedinCreatorAudienceMetric;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LinkedinInsightsController extends Controller
{
    public function audienceInsights(Request $request)
    {
        $user = $request->user();

        $profileId = $request->query('profile_id');
        $to = $request->query('to');
        $from = $request->query('from');

        $profile = LinkedinProfile::forUser($user->id)
            ->when($profileId, fn($q) => $q->where('id', $profileId))
            ->orderByDesc('is_primary')
            ->first();

        if (!$profile) {
            return response()->json(['status' => 'empty', 'message' => 'No profile found.'], 404);
        }

        $toDt = $to ? Carbon::parse($to)->endOfDay() : Carbon::today()->endOfDay();
        $fromDt = $from ? Carbon::parse($from)->startOfDay() : $toDt->copy()->subDays(29)->startOfDay();

        $creatorAudience = LinkedinCreatorAudienceMetric::where('linkedin_profile_id', $profile->id)
            ->whereBetween('metric_date', [$fromDt->toDateString(), $toDt->toDateString()])
            ->orderBy('metric_date')
            ->get();

        $demographicsLatest = LinkedinAudienceDemographic::where('linkedin_profile_id', $profile->id)
            ->orderByDesc('snapshot_date')
            ->first();

        return response()->json([
            'status' => 'ok',
            'filter' => ['from' => $fromDt->toDateString(), 'to' => $toDt->toDateString()],
            'profile' => ['id' => $profile->id, 'name' => $profile->name, 'public_url' => $profile->public_url],
            'creator_audience_timeseries' => [
                'dates' => $creatorAudience->pluck('metric_date')->map(fn($d) => $d instanceof Carbon ? $d->toDateString() : (string)$d)->values(),
                'metrics' => $creatorAudience->map(fn($row) => $row->metrics ?? [])->values(),
            ],
            'demographics_latest' => [
                'snapshot_date' => optional($demographicsLatest?->snapshot_date)->toDateString(),
                'followers_count' => $demographicsLatest->followers_count ?? 0,
                'demographics' => $demographicsLatest->demographics ?? null,
            ],
        ]);
    }

    public function recommendations(Request $request)
    {
        $user = $request->user();
        $profileId = $request->query('profile_id');

        $profile = LinkedinProfile::forUser($user->id)
            ->when($profileId, fn($q) => $q->where('id', $profileId))
            ->orderByDesc('is_primary')
            ->first();

        if (!$profile) {
            return response()->json(['status' => 'empty', 'message' => 'No profile found.'], 404);
        }

        $to = Carbon::today()->endOfDay();
        $from = $to->copy()->subDays(29)->startOfDay();

        $postMetrics = LinkedinPostMetric::whereIn('linkedin_post_id', function ($q) use ($profile) {
                $q->select('id')->from((new LinkedinPost())->getTable())->where('linkedin_profile_id', $profile->id);
            })
            ->whereBetween('metric_date', [$from->toDateString(), $to->toDateString()])
            ->get();

        $totalImpressions = (int) $postMetrics->sum('impressions');
        $totalEngagements = (int) ($postMetrics->sum('reactions') + $postMetrics->sum('comments') + $postMetrics->sum('reposts'));
        $avgEngRate = $postMetrics->count() ? round((float)$postMetrics->avg('engagement_rate'), 2) : 0;

        // Simple heuristic recommendations (non-AI, safe default)
        $posts = LinkedinPost::where('linkedin_profile_id', $profile->id)
            ->orderByDesc('posted_at')
            ->take(60)
            ->get();

        $bestHours = $posts
            ->filter(fn($p) => $p->posted_at)
            ->groupBy(fn($p) => (int) $p->posted_at->format('H'))
            ->map(fn($items) => $items->count())
            ->sortDesc()
            ->take(3)
            ->keys()
            ->values();

        $typeCounts = $posts->groupBy('post_type')->map(fn($i) => $i->count())->sortDesc();

        return response()->json([
            'status' => 'ok',
            'profile' => ['id' => $profile->id, 'name' => $profile->name],
            'summary_30d' => [
                'impressions' => $totalImpressions,
                'engagements' => $totalEngagements,
                'avg_engagement_rate' => $avgEngRate,
            ],
            'recommendations' => [
                [
                    'key' => 'best_post_hours',
                    'title' => 'Best posting hours',
                    'data' => $bestHours,
                    'note' => 'Based on your most recent posts timestamps.',
                ],
                [
                    'key' => 'content_mix',
                    'title' => 'Content mix',
                    'data' => $typeCounts,
                    'note' => 'Your recent posting distribution by type.',
                ],
                [
                    'key' => 'next_actions',
                    'title' => 'Next actions',
                    'data' => [
                        'Post consistently 3 to 5 times weekly for 4 weeks.',
                        'Repeat formats that produced higher engagement rate.',
                        'Add a clear call-to-action on your top-performing topics.',
                    ],
                ],
            ],
        ]);
    }

    public function competitorBenchmark(Request $request)
    {
        $user = $request->user();
        $profileId = (int) $request->query('profile_id');
        $competitorId = (int) $request->query('competitor_id');

        $own = LinkedinProfile::forUser($user->id)->where('id', $profileId)->first();
        $competitor = LinkedinProfile::forUser($user->id)->where('id', $competitorId)->first();

        if (!$own || !$competitor) {
            return response()->json(['status' => 'error', 'message' => 'Profiles not found.'], 404);
        }

        $to = Carbon::today()->endOfDay();
        $from = $to->copy()->subDays(29)->startOfDay();

        $ownMetrics = LinkedinProfileMetric::where('linkedin_profile_id', $own->id)
            ->whereBetween('metric_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('metric_date')
            ->get();

        $compMetrics = LinkedinProfileMetric::where('linkedin_profile_id', $competitor->id)
            ->whereBetween('metric_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('metric_date')
            ->get();

        $ownStart = $ownMetrics->first();
        $ownEnd = $ownMetrics->last();
        $compStart = $compMetrics->first();
        $compEnd = $compMetrics->last();

        return response()->json([
            'status' => 'ok',
            'range' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
            'own' => [
                'profile' => ['id' => $own->id, 'name' => $own->name],
                'followers_change' => (int)(($ownEnd->followers_count ?? 0) - ($ownStart->followers_count ?? 0)),
                'connections_change' => (int)(($ownEnd->connections_count ?? 0) - ($ownStart->connections_count ?? 0)),
                'views_total' => (int) $ownMetrics->sum('profile_views'),
                'search_total' => (int) $ownMetrics->sum('search_appearances'),
            ],
            'competitor' => [
                'profile' => ['id' => $competitor->id, 'name' => $competitor->name],
                'followers_change' => (int)(($compEnd->followers_count ?? 0) - ($compStart->followers_count ?? 0)),
                'connections_change' => (int)(($compEnd->connections_count ?? 0) - ($compStart->connections_count ?? 0)),
                'views_total' => (int) $compMetrics->sum('profile_views'),
                'search_total' => (int) $compMetrics->sum('search_appearances'),
            ],
        ]);
    }

    public function connectionsDirectory(Request $request)
    {
        $user = $request->user();
        $profileId = (int) $request->query('profile_id');
        $q = $request->query('q');

        $profile = LinkedinProfile::forUser($user->id)
            ->when($profileId, fn($qq) => $qq->where('id', $profileId))
            ->orderByDesc('is_primary')
            ->first();

        if (!$profile) {
            return response()->json(['status' => 'empty', 'message' => 'No profile found.'], 404);
        }

        $query = LinkedinConnection::where('linkedin_profile_id', $profile->id);

        if ($q) {
            $query->where(function ($w) use ($q) {
                $w->where('full_name', 'like', "%{$q}%")
                    ->orWhere('headline', 'like', "%{$q}%")
                    ->orWhere('location', 'like', "%{$q}%")
                    ->orWhere('industry', 'like', "%{$q}%")
                    ->orWhere('profile_url', 'like', "%{$q}%")
                    ->orWhere('public_identifier', 'like', "%{$q}%");
            });
        }

        $items = $query->orderBy('full_name')->paginate(20)->withQueryString();

        return response()->json([
            'status' => 'ok',
            'profile' => ['id' => $profile->id, 'name' => $profile->name],
            'connections' => $items,
        ]);
    }
}
