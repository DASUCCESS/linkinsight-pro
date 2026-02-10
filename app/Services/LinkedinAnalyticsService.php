<?php

namespace App\Services;

use App\Models\LinkedinProfile;
use App\Models\LinkedinProfileMetric;
use App\Models\LinkedinPost;
use App\Models\LinkedinPostMetric;
use App\Models\LinkedinSyncJob;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LinkedinAnalyticsService
{
    /**
     * Per-user summary for a single profile (or primary profile).
     * Supports optional date range; defaults to last 30 days.
     */
    public function getSummaryForUser(
        User $user,
        ?int $profileId = null,
        ?Carbon $from = null,
        ?Carbon $to = null
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

        $to = $to ?: Carbon::today();
        $from = $from ?: $to->copy()->subDays(29);

        $profileMetrics = LinkedinProfileMetric::where('linkedin_profile_id', $profile->id)
            ->whereBetween('metric_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('metric_date')
            ->get();

        $latestProfileMetric = $profileMetrics->last();

        $connectionsStart = optional($profileMetrics->first())->connections_count ?? 0;
        $connectionsEnd   = optional($latestProfileMetric)->connections_count ?? $profile->connections_count ?? 0;

        $followersStart   = optional($profileMetrics->first())->followers_count ?? 0;
        $followersEnd     = optional($latestProfileMetric)->followers_count ?? $profile->followers_count ?? 0;

        $viewsTotal  = $profileMetrics->sum('profile_views');
        $searchTotal = $profileMetrics->sum('search_appearances');

        [$postsOverview, $recentPosts] = $this->buildPostsOverviewAndRecent($profile, $from, $to);

        return [
            'status'  => 'ok',
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
                'last_synced_at'     => optional($profile->last_synced_at)?->toDateTimeString(),
                'total_posts'        => $postsOverview['total_posts'] ?? 0,
                'last_post_at'       => $postsOverview['last_post_at'] ?? null,
            ],
            'posts_overview' => $postsOverview,
            'timeseries' => [
                'dates'              => $profileMetrics->pluck('metric_date')->map->toDateString(),
                'profile_views'      => $profileMetrics->pluck('profile_views'),
                'search_appearances' => $profileMetrics->pluck('search_appearances'),
                'connections'        => $profileMetrics->pluck('connections_count'),
                'followers'          => $profileMetrics->pluck('followers_count'),
            ],
            'recent_posts' => $recentPosts,
            'range' => [
                'from' => $from->toDateString(),
                'to'   => $to->toDateString(),
            ],
        ];
    }

    /**
     * Global admin overview across all users.
     * Default range is last 30 days.
     */
    public function getAdminOverview(?Carbon $from = null, ?Carbon $to = null): array
    {
        $to = $to ?: Carbon::today();
        $from = $from ?: $to->copy()->subDays(29);

        $totalUsers    = User::count();
        $totalProfiles = LinkedinProfile::count();
        $totalPosts    = LinkedinPost::count();
        $totalJobs     = LinkedinSyncJob::count();

        $newUsers30 = User::where('created_at', '>=', $from->copy()->startOfDay())->count();
        $jobsLast7  = LinkedinSyncJob::where('created_at', '>=', $to->copy()->subDays(6)->startOfDay())->count();

        $activeProfiles30 = LinkedinProfile::whereNotNull('last_synced_at')
            ->where('last_synced_at', '>=', $from->copy()->startOfDay())
            ->count();

        $postMetrics = LinkedinPostMetric::whereBetween('metric_date', [$from->toDateString(), $to->toDateString()])
            ->get();

        $impressions30 = $postMetrics->sum('impressions');
        $engagements30 = $postMetrics->sum(function (LinkedinPostMetric $m) {
            return ($m->reactions ?? 0)
                + ($m->comments ?? 0)
                + ($m->reposts ?? 0)
                + ($m->saves ?? 0);
        });

        $postsWithMetrics30 = $postMetrics->groupBy('linkedin_post_id')->count();

        $dailyPostsTimeseries = $postMetrics
            ->groupBy(fn(LinkedinPostMetric $m) => $m->metric_date->toDateString())
            ->sortKeys()
            ->map(function (Collection $group, string $date) {
                $impressions = $group->sum('impressions');
                $engagements = $group->sum(function (LinkedinPostMetric $m) {
                    return ($m->reactions ?? 0)
                        + ($m->comments ?? 0)
                        + ($m->reposts ?? 0)
                        + ($m->saves ?? 0);
                });

                return [
                    'date'         => $date,
                    'impressions'  => $impressions,
                    'engagements'  => $engagements,
                    'posts_count'  => $group->groupBy('linkedin_post_id')->count(),
                ];
            })
            ->values()
            ->all();

        $topProfiles = LinkedinProfile::with('user')
            ->orderByDesc('followers_count')
            ->limit(10)
            ->get()
            ->map(function (LinkedinProfile $profile) {
                return [
                    'id'              => $profile->id,
                    'user_name'       => optional($profile->user)->name,
                    'profile_name'    => $profile->name,
                    'public_url'      => $profile->public_url,
                    'followers_count' => $profile->followers_count,
                    'connections'     => $profile->connections_count,
                    'last_synced_at'  => optional($profile->last_synced_at)?->diffForHumans(),
                ];
            })
            ->values()
            ->all();

        return [
            'totals' => [
                'users'             => $totalUsers,
                'profiles'          => $totalProfiles,
                'posts'             => $totalPosts,
                'sync_jobs'         => $totalJobs,
                'new_users_30d'     => $newUsers30,
                'active_profiles_30d' => $activeProfiles30,
            ],
            'posts_kpis' => [
                'impressions_30d'      => $impressions30,
                'engagements_30d'      => $engagements30,
                'posts_with_metrics_30d' => $postsWithMetrics30,
            ],
            'posts_timeseries' => $dailyPostsTimeseries,
            'top_profiles'     => $topProfiles,
            'range' => [
                'from' => $from->toDateString(),
                'to'   => $to->toDateString(),
            ],
        ];
    }

    /**
     * Internal helper for posts overview and recent posts.
     */
    protected function buildPostsOverviewAndRecent(
        LinkedinProfile $profile,
        Carbon $from,
        Carbon $to
    ): array {
        $postIds = LinkedinPost::where('linkedin_profile_id', $profile->id)
            ->pluck('id');

        if ($postIds->isEmpty()) {
            return [
                [
                    'total_posts'              => 0,
                    'impressions_30d'          => 0,
                    'engagements_30d'          => 0,
                    'avg_impressions_per_post' => 0,
                    'last_post_at'             => null,
                ],
                [],
            ];
        }

        $metrics = LinkedinPostMetric::whereIn('linkedin_post_id', $postIds)
            ->whereBetween('metric_date', [$from->toDateString(), $to->toDateString()])
            ->get();

        $impressionsTotal = $metrics->sum('impressions');

        $engagementsTotal = $metrics->sum(function (LinkedinPostMetric $m) {
            return ($m->reactions ?? 0)
                + ($m->comments ?? 0)
                + ($m->reposts ?? 0)
                + ($m->saves ?? 0);
        });

        $postsWithMetrics = $metrics->groupBy('linkedin_post_id')->count();

        $avgImpressions = $postsWithMetrics > 0
            ? (int) round($impressionsTotal / $postsWithMetrics)
            : 0;

        $lastPost = LinkedinPost::where('linkedin_profile_id', $profile->id)
            ->orderByDesc('posted_at')
            ->first();

        $overview = [
            'total_posts'              => LinkedinPost::where('linkedin_profile_id', $profile->id)->count(),
            'impressions_30d'          => $impressionsTotal,
            'engagements_30d'          => $engagementsTotal,
            'avg_impressions_per_post' => $avgImpressions,
            'last_post_at'             => optional($lastPost?->posted_at)?->toDateTimeString(),
        ];

        if ($metrics->isEmpty()) {
            return [$overview, []];
        }

        $latestMetricPerPost = $metrics
            ->sortByDesc('metric_date')
            ->groupBy('linkedin_post_id')
            ->map(function (Collection $rows) {
                return $rows->first();
            });

        $topPostIds = $latestMetricPerPost
            ->sortByDesc('impressions')
            ->take(5)
            ->keys();

        $posts = LinkedinPost::whereIn('id', $topPostIds)
            ->get()
            ->keyBy('id');

        $recentPosts = [];
        foreach ($topPostIds as $postId) {
            $post = $posts->get($postId);
            $metric = $latestMetricPerPost->get($postId);

            if (!$post || !$metric) {
                continue;
            }

            $recentPosts[] = [
                'id'              => $post->id,
                'linkedin_id'     => $post->linkedin_post_id,
                'permalink'       => $post->permalink,
                'posted_at'       => optional($post->posted_at)?->toDateTimeString(),
                'post_type'       => $post->post_type,
                'content'         => $post->content_excerpt,
                'impressions'     => $metric->impressions ?? 0,
                'reactions'       => $metric->reactions ?? 0,
                'comments'        => $metric->comments ?? 0,
                'reposts'         => $metric->reposts ?? 0,
                'engagement_rate' => $metric->engagement_rate ?? 0,
            ];
        }

        return [$overview, array_values($recentPosts)];
    }
}
