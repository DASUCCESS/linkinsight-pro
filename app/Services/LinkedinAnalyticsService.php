<?php

namespace App\Services;

use App\Models\LinkedinProfile;
use App\Models\LinkedinProfileMetric;
use App\Models\LinkedinPost;
use App\Models\LinkedinPostMetric;
use App\Models\User;
use Carbon\Carbon;

class LinkedinAnalyticsService
{
    public function getSummaryForUser(
        User $user,
        ?int $profileId = null,
        ?string $fromDate = null,
        ?string $toDate = null
    ): array {
        $profileQuery = LinkedinProfile::forUser($user->id);

        if ($profileId) {
            $profileQuery->where('id', $profileId);
        } else {
            $profileQuery->orderByDesc('is_primary')->orderBy('id');
        }

        /** @var LinkedinProfile|null $profile */
        $profile = $profileQuery->first();

        if (!$profile) {
            return [
                'status'  => 'empty',
                'message' => 'No LinkedIn profile connected yet.',
            ];
        }

        $to = $toDate ? Carbon::parse($toDate)->endOfDay() : Carbon::today()->endOfDay();
        $from = $fromDate ? Carbon::parse($fromDate)->startOfDay() : $to->copy()->subDays(29)->startOfDay();

        $metrics = LinkedinProfileMetric::where('linkedin_profile_id', $profile->id)
            ->whereBetween('metric_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('metric_date')
            ->get();

        $latest = $metrics->last();

        $connectionsStart = optional($metrics->first())->connections_count ?? 0;
        $connectionsEnd   = optional($latest)->connections_count ?? 0;

        $followersStart   = optional($metrics->first())->followers_count ?? 0;
        $followersEnd     = optional($latest)->followers_count ?? 0;

        $viewsTotal   = $metrics->sum('profile_views');
        $searchTotal  = $metrics->sum('search_appearances');

        $postsQuery = LinkedinPost::where('linkedin_profile_id', $profile->id);

        $recentPosts = $postsQuery
            ->orderByDesc('posted_at')
            ->take(10)
            ->get();

        $recentPostMetrics = LinkedinPostMetric::whereIn('linkedin_post_id', $recentPosts->pluck('id'))
            ->orderBy('metric_date')
            ->get()
            ->groupBy('linkedin_post_id');

        $postMetricsRange = LinkedinPostMetric::whereIn('linkedin_post_id', function ($q) use ($profile) {
                $q->select('id')
                    ->from((new LinkedinPost())->getTable())
                    ->where('linkedin_profile_id', $profile->id);
            })
            ->whereBetween('metric_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('metric_date')
            ->get();

        $totalPosts      = LinkedinPost::where('linkedin_profile_id', $profile->id)->count();
        $impressions30   = $postMetricsRange->sum('impressions');
        $reactions30     = $postMetricsRange->sum('reactions');
        $comments30      = $postMetricsRange->sum('comments');
        $reposts30       = $postMetricsRange->sum('reposts');
        $engagements30   = $reactions30 + $comments30 + $reposts30;
        $avgEngagement30 = $postMetricsRange->count() > 0
            ? round($postMetricsRange->avg('engagement_rate'), 2)
            : 0;

        $postTimeseriesGrouped = $postMetricsRange->groupBy(function (LinkedinPostMetric $m) {
            return $m->metric_date instanceof Carbon
                ? $m->metric_date->toDateString()
                : (string) $m->metric_date;
        });

        $postDates       = [];
        $postImpressions = [];
        $postEngagements = [];

        foreach ($postTimeseriesGrouped as $date => $items) {
            $postDates[]       = $date;
            $postImpressions[] = $items->sum('impressions');
            $postEngagements[] = $items->sum(function (LinkedinPostMetric $m) {
                return ($m->reactions ?? 0) + ($m->comments ?? 0) + ($m->reposts ?? 0);
            });
        }

        return [
            'status' => 'ok',
            'filter' => [
                'from' => $from->toDateString(),
                'to'   => $to->toDateString(),
            ],
            'profile' => [
                'id'                 => $profile->id,
                'name'               => $profile->name,
                'headline'           => $profile->headline,
                'public_url'         => $profile->public_url,
                'profile_image_url'  => $profile->profile_image_url,
                'connections'        => $connectionsEnd,
                'followers'          => $followersEnd,
                'connections_change' => $connectionsEnd - $connectionsStart,
                'followers_change'   => $followersEnd - $followersStart,
                'views_total'        => $viewsTotal,
                'search_total'       => $searchTotal,
            ],
            'timeseries' => [
                'dates'              => $metrics->pluck('metric_date')->map(function ($d) {
                    return $d instanceof Carbon ? $d->toDateString() : (string) $d;
                }),
                'profile_views'      => $metrics->pluck('profile_views'),
                'search_appearances' => $metrics->pluck('search_appearances'),
                'connections'        => $metrics->pluck('connections_count'),
                'followers'          => $metrics->pluck('followers_count'),
            ],
            'posts_overview' => [
                'total_posts'           => $totalPosts,
                'impressions_30d'       => $impressions30,
                'engagements_30d'       => $engagements30,
                'reactions_30d'         => $reactions30,
                'comments_30d'          => $comments30,
                'reposts_30d'           => $reposts30,
                'avg_engagement_rate_30d' => $avgEngagement30,
            ],
            'post_timeseries' => [
                'dates'        => $postDates,
                'impressions'  => $postImpressions,
                'engagements'  => $postEngagements,
            ],
            'recent_posts' => $recentPosts->map(function (LinkedinPost $post) use ($recentPostMetrics) {
                $metrics = $recentPostMetrics->get($post->id)?->sortByDesc('metric_date')->first();

                return [
                    'id'              => $post->id,
                    'linkedin_id'     => $post->linkedin_post_id,
                    'permalink'       => $post->permalink,
                    'posted_at'       => $post->posted_at?->toDateTimeString(),
                    'post_type'       => $post->post_type,
                    'content'         => $post->content_excerpt,
                    'impressions'     => $metrics->impressions ?? 0,
                    'reactions'       => $metrics->reactions ?? 0,
                    'comments'        => $metrics->comments ?? 0,
                    'reposts'         => $metrics->reposts ?? 0,
                    'engagement_rate' => $metrics->engagement_rate ?? 0,
                ];
            })->values(),
        ];
    }

    public function getSystemSummary(
        ?string $fromDate = null,
        ?string $toDate = null
    ): array {
        $profilesCount = LinkedinProfile::count();

        if ($profilesCount === 0) {
            return [
                'status'  => 'empty',
                'message' => 'No LinkedIn profiles have been synced yet.',
            ];
        }

        $to = $toDate ? Carbon::parse($toDate)->endOfDay() : Carbon::today()->endOfDay();
        $from = $fromDate ? Carbon::parse($fromDate)->startOfDay() : $to->copy()->subDays(29)->startOfDay();

        $profileMetrics = LinkedinProfileMetric::whereBetween('metric_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('metric_date')
            ->get();

        $connectionsEnd = $profileMetrics
            ->groupBy('linkedin_profile_id')
            ->sum(function ($items) {
                $last = $items->last();
                return $last->connections_count ?? 0;
            });

        $followersEnd = $profileMetrics
            ->groupBy('linkedin_profile_id')
            ->sum(function ($items) {
                $last = $items->last();
                return $last->followers_count ?? 0;
            });

        $viewsTotal   = $profileMetrics->sum('profile_views');
        $searchTotal  = $profileMetrics->sum('search_appearances');

        $postMetrics = LinkedinPostMetric::whereBetween('metric_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('metric_date')
            ->get();

        $totalPosts      = LinkedinPost::count();
        $impressions30   = $postMetrics->sum('impressions');
        $reactions30     = $postMetrics->sum('reactions');
        $comments30      = $postMetrics->sum('comments');
        $reposts30       = $postMetrics->sum('reposts');
        $engagements30   = $reactions30 + $comments30 + $reposts30;
        $avgEngagement30 = $postMetrics->count() > 0
            ? round($postMetrics->avg('engagement_rate'), 2)
            : 0;

        $profileTimeseriesGrouped = $profileMetrics->groupBy(function (LinkedinProfileMetric $m) {
            return $m->metric_date instanceof Carbon
                ? $m->metric_date->toDateString()
                : (string) $m->metric_date;
        });

        $dates             = [];
        $seriesConnections = [];
        $seriesFollowers   = [];
        $seriesViews       = [];
        $seriesSearch      = [];

        foreach ($profileTimeseriesGrouped as $date => $items) {
            $dates[]           = $date;
            $seriesConnections[] = $items->sum('connections_count');
            $seriesFollowers[]   = $items->sum('followers_count');
            $seriesViews[]       = $items->sum('profile_views');
            $seriesSearch[]      = $items->sum('search_appearances');
        }

        $postTimeseriesGrouped = $postMetrics->groupBy(function (LinkedinPostMetric $m) {
            return $m->metric_date instanceof Carbon
                ? $m->metric_date->toDateString()
                : (string) $m->metric_date;
        });

        $postDates       = [];
        $postImpressions = [];
        $postEngagements = [];

        foreach ($postTimeseriesGrouped as $date => $items) {
            $postDates[]       = $date;
            $postImpressions[] = $items->sum('impressions');
            $postEngagements[] = $items->sum(function (LinkedinPostMetric $m) {
                return ($m->reactions ?? 0) + ($m->comments ?? 0) + ($m->reposts ?? 0);
            });
        }

        return [
            'status' => 'ok',
            'filter' => [
                'from' => $from->toDateString(),
                'to'   => $to->toDateString(),
            ],
            'global' => [
                'profiles_count'          => $profilesCount,
                'total_posts'             => $totalPosts,
                'connections_total'       => $connectionsEnd,
                'followers_total'         => $followersEnd,
                'views_total'             => $viewsTotal,
                'search_total'            => $searchTotal,
                'impressions_30d'         => $impressions30,
                'engagements_30d'         => $engagements30,
                'reactions_30d'           => $reactions30,
                'comments_30d'            => $comments30,
                'reposts_30d'             => $reposts30,
                'avg_engagement_rate_30d' => $avgEngagement30,
            ],
            'timeseries' => [
                'dates'              => $dates,
                'connections'        => $seriesConnections,
                'followers'          => $seriesFollowers,
                'profile_views'      => $seriesViews,
                'search_appearances' => $seriesSearch,
            ],
            'post_timeseries' => [
                'dates'        => $postDates,
                'impressions'  => $postImpressions,
                'engagements'  => $postEngagements,
            ],
        ];
    }
}
