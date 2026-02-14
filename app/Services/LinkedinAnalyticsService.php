<?php

namespace App\Services;

use App\Models\LinkedinAudienceDemographic;
use App\Models\LinkedinAudienceInsight;
use App\Models\LinkedinConnection;
use App\Models\LinkedinCreatorAudienceMetric;
use App\Models\LinkedinProfile;
use App\Models\LinkedinProfileMetric;
use App\Models\LinkedinPost;
use App\Models\LinkedinPostMetric;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LinkedinAnalyticsService
{
    /**
     * Return all LinkedIn profiles for the user (latest first).
     */
    public function userProfiles(User $user): Collection
    {
        return LinkedinProfile::query()
            ->forUser($user->id)
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Pick a default profile that actually has data, otherwise first profile.
     * Priority: primary -> profile metrics -> posts -> demographics/insights/creator -> first profile.
     */
    public function resolveDefaultProfileId(User $user, ?int $profileId = null): ?int
    {
        $profiles = $this->userProfiles($user);

        if ($profiles->isEmpty()) {
            return null;
        }

        $profileIds = $profiles->pluck('id');

        if ($profileId && $profileIds->contains($profileId)) {
            return (int) $profileId;
        }

        $primary = $profiles->firstWhere('is_primary', true);
        if ($primary) {
            return (int) $primary->id;
        }

        $metricRow = LinkedinProfileMetric::query()
            ->whereIn('linkedin_profile_id', $profileIds)
            ->orderByDesc('metric_date')
            ->orderByDesc('id')
            ->first();

        if ($metricRow?->linkedin_profile_id) {
            return (int) $metricRow->linkedin_profile_id;
        }

        $postRow = LinkedinPost::query()
            ->whereIn('linkedin_profile_id', $profileIds)
            ->orderByDesc('posted_at')
            ->orderByDesc('id')
            ->first();

        if ($postRow?->linkedin_profile_id) {
            return (int) $postRow->linkedin_profile_id;
        }

        $row = LinkedinAudienceDemographic::query()
            ->whereIn('linkedin_profile_id', $profileIds)
            ->orderByDesc('snapshot_date')
            ->orderByDesc('id')
            ->first();

        if ($row?->linkedin_profile_id) {
            return (int) $row->linkedin_profile_id;
        }

        $row = LinkedinAudienceInsight::query()
            ->whereIn('linkedin_profile_id', $profileIds)
            ->orderByDesc('snapshot_date')
            ->orderByDesc('id')
            ->first();

        if ($row?->linkedin_profile_id) {
            return (int) $row->linkedin_profile_id;
        }

        $row = LinkedinCreatorAudienceMetric::query()
            ->whereIn('linkedin_profile_id', $profileIds)
            ->orderByDesc('metric_date')
            ->orderByDesc('id')
            ->first();

        if ($row?->linkedin_profile_id) {
            return (int) $row->linkedin_profile_id;
        }

        return (int) $profiles->first()->id;
    }

    /**
     * Resolve a specific profile for a user. If none provided, choose the best default.
     */
    protected function resolveUserProfile(User $user, ?int $profileId = null): ?LinkedinProfile
    {
        $activeProfileId = $this->resolveDefaultProfileId($user, $profileId);

        if (!$activeProfileId) {
            return null;
        }

        return LinkedinProfile::query()
            ->forUser($user->id)
            ->where('id', $activeProfileId)
            ->first();
    }

    /**
     * Parse date range (inclusive) with a default lookback window.
     */
    protected function parseRange(?string $fromDate, ?string $toDate, int $defaultDays = 29): array
    {
        $to = $toDate ? Carbon::parse($toDate)->endOfDay() : Carbon::today()->endOfDay();
        $from = $fromDate ? Carbon::parse($fromDate)->startOfDay() : $to->copy()->subDays($defaultDays)->startOfDay();

        return [$from, $to];
    }

    /**
     * Main per-user analytics summary used on Dashboard and Analytics pages.
     * Now returns profiles list and active_profile_id for switching.
     */
    public function getSummaryForUser(
        User $user,
        ?int $profileId = null,
        ?string $fromDate = null,
        ?string $toDate = null
    ): array {
        $profiles = $this->userProfiles($user);

        if ($profiles->isEmpty()) {
            return [
                'status'            => 'empty',
                'message'           => 'No LinkedIn profile connected yet.',
                'profiles'          => collect(),
                'active_profile_id' => null,
            ];
        }

        $activeProfileId = $this->resolveDefaultProfileId($user, $profileId);
        $profile = $profiles->firstWhere('id', $activeProfileId) ?? $profiles->first();
        $activeProfileId = $profile->id;

        [$from, $to] = $this->parseRange($fromDate, $toDate, 29);

        $metrics = LinkedinProfileMetric::query()
            ->where('linkedin_profile_id', $profile->id)
            ->whereBetween('metric_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('metric_date')
            ->get();

        $latest = $metrics->last();

        $connectionsStart = optional($metrics->first())->connections_count ?? 0;
        $connectionsEnd   = optional($latest)->connections_count ?? 0;

        $followersStart = optional($metrics->first())->followers_count ?? 0;
        $followersEnd   = optional($latest)->followers_count ?? 0;

        $viewsTotal  = $metrics->sum('profile_views');
        $searchTotal = $metrics->sum('search_appearances');

        $recentPosts = LinkedinPost::query()
            ->where('linkedin_profile_id', $profile->id)
            ->orderByDesc('posted_at')
            ->take(10)
            ->get();

        $recentPostMetrics = LinkedinPostMetric::query()
            ->whereIn('linkedin_post_id', $recentPosts->pluck('id'))
            ->orderBy('metric_date')
            ->get()
            ->groupBy('linkedin_post_id');

        $postMetricsRange = LinkedinPostMetric::query()
            ->whereIn('linkedin_post_id', function ($q) use ($profile) {
                $q->select('id')
                    ->from((new LinkedinPost())->getTable())
                    ->where('linkedin_profile_id', $profile->id);
            })
            ->whereBetween('metric_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('metric_date')
            ->get();

        $totalPosts    = LinkedinPost::query()->where('linkedin_profile_id', $profile->id)->count();
        $impressions30 = $postMetricsRange->sum('impressions');
        $reactions30   = $postMetricsRange->sum('reactions');
        $comments30    = $postMetricsRange->sum('comments');
        $reposts30     = $postMetricsRange->sum('reposts');
        $engagements30 = $reactions30 + $comments30 + $reposts30;

        $avgEngagement30 = $postMetricsRange->count() > 0
            ? round((float) $postMetricsRange->avg('engagement_rate'), 2)
            : 0;

        $postTimeseriesGrouped = $postMetricsRange->groupBy(function (LinkedinPostMetric $m) {
            return $m->metric_date instanceof Carbon
                ? $m->metric_date->toDateString()
                : (string) $m->metric_date;
        });

        $postDates        = [];
        $postImpressions  = [];
        $postEngagements  = [];

        foreach ($postTimeseriesGrouped as $date => $items) {
            $postDates[]       = $date;
            $postImpressions[] = $items->sum('impressions');
            $postEngagements[] = $items->sum(function (LinkedinPostMetric $m) {
                return ($m->reactions ?? 0) + ($m->comments ?? 0) + ($m->reposts ?? 0);
            });
        }

        $latestInsight = LinkedinAudienceInsight::query()
            ->where('linkedin_profile_id', $profile->id)
            ->orderByDesc('snapshot_date')
            ->orderByDesc('id')
            ->first();

        $latestDemo = LinkedinAudienceDemographic::query()
            ->where('linkedin_profile_id', $profile->id)
            ->orderByDesc('snapshot_date')
            ->orderByDesc('id')
            ->first();

        $latestCreator = LinkedinCreatorAudienceMetric::query()
            ->where('linkedin_profile_id', $profile->id)
            ->orderByDesc('metric_date')
            ->orderByDesc('id')
            ->first();

        $connectionsSample = LinkedinConnection::query()
            ->where('linkedin_profile_id', $profile->id)
            ->orderByDesc('connected_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $audienceInsightsData = null;
        if ($latestInsight) {
            $audienceInsightsData = [
                'snapshot_date'      => optional($latestInsight->snapshot_date)->toDateString(),
                'top_job_titles'     => array_slice($this->normalizeTopList($latestInsight->top_job_titles ?? []), 0, 5),
                'top_industries'     => array_slice($this->normalizeTopList($latestInsight->top_industries ?? []), 0, 5),
                'top_locations'      => array_slice($this->normalizeTopList($latestInsight->top_locations ?? []), 0, 5),
                'engagement_sources' => array_slice($this->normalizeTopList($latestInsight->engagement_sources ?? []), 0, 5),
            ];
        }

        $audienceDemographicsData = null;
        if ($latestDemo) {
            $audienceDemographicsData = [
                'snapshot_date'   => optional($latestDemo->snapshot_date)->toDateString(),
                'followers_count' => $latestDemo->followers_count,
                'demographics'    => $this->normalizeDemographics($latestDemo->demographics ?? [], 10),
            ];
        }

        $creatorAudienceData = null;
        if ($latestCreator) {
            $metricsArray = is_array($latestCreator->metrics) ? $latestCreator->metrics : [];

            $creatorAudienceData = [
                'snapshot_date' => optional($latestCreator->metric_date)->toDateString(),
                'metrics'       => array_slice($this->normalizeCreatorMetrics($metricsArray), 0, 8),
            ];
        }

        $connectionsSampleData = $connectionsSample->map(function (LinkedinConnection $connection) {
            return [
                'full_name'                => $connection->full_name,
                'headline'                 => $connection->headline,
                'location'                 => $connection->location,
                'industry'                 => $connection->industry,
                'profile_url'              => $connection->profile_url,
                'profile_image_url'        => $connection->profile_image_url,
                'degree'                   => $connection->degree,
                'mutual_connections_count' => $connection->mutual_connections_count,
                'connected_at'             => optional($connection->connected_at)->toDateTimeString(),
            ];
        })->values();

        return [
            'status'            => 'ok',
            'filter'            => [
                'from' => $from->toDateString(),
                'to'   => $to->toDateString(),
            ],
            'profiles'          => $profiles,
            'active_profile_id' => $activeProfileId,
            'profile'           => [
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
                })->values(),
                'profile_views'      => $metrics->pluck('profile_views')->values(),
                'search_appearances' => $metrics->pluck('search_appearances')->values(),
                'connections'        => $metrics->pluck('connections_count')->values(),
                'followers'          => $metrics->pluck('followers_count')->values(),
            ],
            'posts_overview' => [
                'total_posts'             => $totalPosts,
                'impressions_30d'         => $impressions30,
                'engagements_30d'         => $engagements30,
                'reactions_30d'           => $reactions30,
                'comments_30d'            => $comments30,
                'reposts_30d'             => $reposts30,
                'avg_engagement_rate_30d' => $avgEngagement30,
            ],
            'post_timeseries' => [
                'dates'       => $postDates,
                'impressions' => $postImpressions,
                'engagements' => $postEngagements,
            ],
            'recent_posts'         => $recentPosts->map(function (LinkedinPost $post) use ($recentPostMetrics) {
                $m = $recentPostMetrics->get($post->id)?->sortByDesc('metric_date')->first();

                return [
                    'id'              => $post->id,
                    'linkedin_id'     => $post->linkedin_post_id,
                    'permalink'       => $post->permalink,
                    'posted_at'       => $post->posted_at?->toDateTimeString(),
                    'post_type'       => $post->post_type,
                    'content'         => $post->content_excerpt,
                    'impressions'     => $m->impressions ?? 0,
                    'reactions'       => $m->reactions ?? 0,
                    'comments'        => $m->comments ?? 0,
                    'reposts'         => $m->reposts ?? 0,
                    'engagement_rate' => $m->engagement_rate ?? 0,
                ];
            })->values(),
            'audience_insights'     => $audienceInsightsData,
            'audience_demographics' => $audienceDemographicsData,
            'creator_audience'      => $creatorAudienceData,
            'connections_sample'    => $connectionsSampleData,
        ];
    }

    /**
     * System-wide aggregate summary (admin use).
     */
    public function getSystemSummary(?string $fromDate = null, ?string $toDate = null): array
    {
        $profilesCount = LinkedinProfile::count();

        if ($profilesCount === 0) {
            return [
                'status'  => 'empty',
                'message' => 'No LinkedIn profiles have been synced yet.',
            ];
        }

        [$from, $to] = $this->parseRange($fromDate, $toDate, 29);

        $profileMetrics = LinkedinProfileMetric::query()
            ->whereBetween('metric_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('metric_date')
            ->get();

        $connectionsEnd = $profileMetrics
            ->groupBy('linkedin_profile_id')
            ->sum(function (Collection $items) {
                $last = $items->last();
                return $last->connections_count ?? 0;
            });

        $followersEnd = $profileMetrics
            ->groupBy('linkedin_profile_id')
            ->sum(function (Collection $items) {
                $last = $items->last();
                return $last->followers_count ?? 0;
            });

        $viewsTotal  = $profileMetrics->sum('profile_views');
        $searchTotal = $profileMetrics->sum('search_appearances');

        $postMetrics = LinkedinPostMetric::query()
            ->whereBetween('metric_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('metric_date')
            ->get();

        $totalPosts    = LinkedinPost::count();
        $impressions30 = $postMetrics->sum('impressions');
        $reactions30   = $postMetrics->sum('reactions');
        $comments30    = $postMetrics->sum('comments');
        $reposts30     = $postMetrics->sum('reposts');
        $engagements30 = $reactions30 + $comments30 + $reposts30;

        $avgEngagement30 = $postMetrics->count() > 0
            ? round((float) $postMetrics->avg('engagement_rate'), 2)
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
            $dates[]             = $date;
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
                'dates'       => $postDates,
                'impressions' => $postImpressions,
                'engagements' => $postEngagements,
            ],
        ];
    }

    /**
     * Audience insights snapshots for a user/profile.
     */
    public function getAudienceInsightsForUser(
        User $user,
        ?int $profileId = null,
        ?string $from = null,
        ?string $to = null
    ): array {
        $profiles = $this->userProfiles($user);

        if ($profiles->isEmpty()) {
            return [
                'status'            => 'empty',
                'message'           => 'No LinkedIn profile connected yet.',
                'profiles'          => collect(),
                'active_profile_id' => null,
                'profile'           => null,
                'latest'            => null,
                'history'           => null,
                'filter'            => ['from' => $from, 'to' => $to],
            ];
        }

        $activeProfileId = $this->resolveDefaultProfileId($user, $profileId);
        $activeProfile   = $profiles->firstWhere('id', $activeProfileId) ?? $profiles->first();
        $activeProfileId = $activeProfile->id;

        $q = LinkedinAudienceInsight::query()
            ->where('linkedin_profile_id', $activeProfileId);

        if ($from) {
            $q->whereDate('snapshot_date', '>=', $from);
        }

        if ($to) {
            $q->whereDate('snapshot_date', '<=', $to);
        }

        $latestModel = (clone $q)->orderByDesc('snapshot_date')->orderByDesc('id')->first();
        $history     = (clone $q)->orderByDesc('snapshot_date')->orderByDesc('id')->paginate(25)->withQueryString();

        $latest = null;
        if ($latestModel) {
            $latest = [
                'snapshot_date'      => optional($latestModel->snapshot_date)->toDateString(),
                'top_job_titles'     => $this->normalizeTopList($latestModel->top_job_titles ?? []),
                'top_industries'     => $this->normalizeTopList($latestModel->top_industries ?? []),
                'top_locations'      => $this->normalizeTopList($latestModel->top_locations ?? []),
                'engagement_sources' => $this->normalizeTopList($latestModel->engagement_sources ?? []),
            ];
        }

        return [
            'status'            => 'ok',
            'profiles'          => $profiles,
            'active_profile_id' => $activeProfileId,
            'profile'           => $activeProfile->toArray(),
            'latest'            => $latest,
            'history'           => $history,
            'filter'            => ['from' => $from, 'to' => $to],
        ];
    }

    /**
     * Followers demographics snapshots for a user/profile.
     */
    public function getAudienceDemographicsForUser(
        User $user,
        ?int $profileId = null,
        ?string $from = null,
        ?string $to = null
    ): array {
        $profiles = $this->userProfiles($user);

        if ($profiles->isEmpty()) {
            return [
                'status'            => 'empty',
                'message'           => 'No LinkedIn profile connected yet.',
                'profiles'          => collect(),
                'active_profile_id' => null,
                'profile'           => null,
                'latest'            => null,
                'history'           => null,
                'filter'            => ['from' => $from, 'to' => $to],
            ];
        }

        $activeProfileId = $this->resolveDefaultProfileId($user, $profileId);
        $activeProfile   = $profiles->firstWhere('id', $activeProfileId) ?? $profiles->first();
        $activeProfileId = $activeProfile->id;

        $q = LinkedinAudienceDemographic::query()
            ->where('linkedin_profile_id', $activeProfileId);

        if ($from) {
            $q->whereDate('snapshot_date', '>=', $from);
        }

        if ($to) {
            $q->whereDate('snapshot_date', '<=', $to);
        }

        $latestModel = (clone $q)->orderByDesc('snapshot_date')->orderByDesc('id')->first();
        $history     = (clone $q)->orderByDesc('snapshot_date')->orderByDesc('id')->paginate(25)->withQueryString();

        $latest = null;
        if ($latestModel) {
            $latest = [
                'snapshot_date'   => optional($latestModel->snapshot_date)->toDateString(),
                'followers_count' => $latestModel->followers_count,
                'demographics'    => $this->normalizeDemographics($latestModel->demographics ?? []),
            ];
        }

        return [
            'status'            => 'ok',
            'profiles'          => $profiles,
            'active_profile_id' => $activeProfileId,
            'profile'           => $activeProfile->toArray(),
            'latest'            => $latest,
            'history'           => $history,
            'filter'            => ['from' => $from, 'to' => $to],
        ];
    }

    /**
     * Creator audience metrics snapshots for a user/profile.
     */
    public function getCreatorAudienceMetricsForUser(
        User $user,
        ?int $profileId = null,
        ?string $from = null,
        ?string $to = null
    ): array {
        $profiles = $this->userProfiles($user);

        if ($profiles->isEmpty()) {
            return [
                'status'            => 'empty',
                'message'           => 'No LinkedIn profile connected yet.',
                'profiles'          => collect(),
                'active_profile_id' => null,
                'profile'           => null,
                'latest'            => null,
                'history'           => null,
                'filter'            => ['from' => $from, 'to' => $to],
            ];
        }

        $activeProfileId = $this->resolveDefaultProfileId($user, $profileId);
        $activeProfile   = $profiles->firstWhere('id', $activeProfileId) ?? $profiles->first();
        $activeProfileId = $activeProfile->id;

        $q = LinkedinCreatorAudienceMetric::query()
            ->where('linkedin_profile_id', $activeProfileId);

        if ($from) {
            $q->whereDate('metric_date', '>=', $from);
        }

        if ($to) {
            $q->whereDate('metric_date', '<=', $to);
        }

        $latestModel = (clone $q)->orderByDesc('metric_date')->orderByDesc('id')->first();
        $history     = (clone $q)->orderByDesc('metric_date')->orderByDesc('id')->paginate(25)->withQueryString();

        $latest = null;
        if ($latestModel) {
            $metricsArray = is_array($latestModel->metrics) ? $latestModel->metrics : [];

            $latest = [
                'metric_date' => optional($latestModel->metric_date)->toDateString(),
                'metrics'     => $this->normalizeCreatorMetrics($metricsArray),
            ];
        }

        return [
            'status'            => 'ok',
            'profiles'          => $profiles,
            'active_profile_id' => $activeProfileId,
            'profile'           => $activeProfile->toArray(),
            'latest'            => $latest,
            'history'           => $history,
            'filter'            => ['from' => $from, 'to' => $to],
        ];
    }

    /**
     * Connections directory for a user/profile.
     */
    public function getConnectionsForUser(
        User $user,
        ?int $profileId = null,
        ?string $q = null,
        ?string $location = null,
        ?string $industry = null,
        ?string $degree = null,
        ?string $fromDate = null,
        ?string $toDate = null
    ): array {
        $profile = $this->resolveUserProfile($user, $profileId);

        if (!$profile) {
            return [
                'status'  => 'empty',
                'message' => 'No LinkedIn profile connected yet.',
            ];
        }

        $from = $fromDate ? Carbon::parse($fromDate)->startOfDay() : null;
        $to   = $toDate ? Carbon::parse($toDate)->endOfDay() : null;

        $query = LinkedinConnection::query()
            ->where('linkedin_profile_id', $profile->id);

        if ($q) {
            $query->where(function ($qb) use ($q) {
                $qb->where('full_name', 'like', '%' . $q . '%')
                    ->orWhere('headline', 'like', '%' . $q . '%')
                    ->orWhere('public_identifier', 'like', '%' . $q . '%')
                    ->orWhere('profile_url', 'like', '%' . $q . '%');
            });
        }

        if ($location) {
            $query->where('location', 'like', '%' . $location . '%');
        }

        if ($industry) {
            $query->where('industry', 'like', '%' . $industry . '%');
        }

        if ($degree !== null && $degree !== '') {
            $query->where('degree', (int) $degree);
        }

        if ($from && $to) {
            $query->where(function ($qb) use ($from, $to) {
                $qb->whereBetween('connected_at', [$from->toDateTimeString(), $to->toDateTimeString()])
                    ->orWhereNull('connected_at');
            });
        } elseif ($from) {
            $query->where(function ($qb) use ($from) {
                $qb->where('connected_at', '>=', $from->toDateTimeString())
                    ->orWhereNull('connected_at');
            });
        } elseif ($to) {
            $query->where(function ($qb) use ($to) {
                $qb->where('connected_at', '<=', $to->toDateTimeString())
                    ->orWhereNull('connected_at');
            });
        }

        $connections = $query
            ->orderByDesc('connected_at')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return [
            'status' => 'ok',
            'filter' => [
                'from'     => $from ? $from->toDateString() : null,
                'to'       => $to ? $to->toDateString() : null,
                'q'        => $q,
                'location' => $location,
                'industry' => $industry,
                'degree'   => $degree,
            ],
            'profile' => [
                'id'                => $profile->id,
                'name'              => $profile->name,
                'headline'          => $profile->headline,
                'public_url'        => $profile->public_url,
                'profile_image_url' => $profile->profile_image_url,
            ],
            'connections' => $connections,
        ];
    }

    /**
     * Normalize list fields like top_job_titles, top_locations etc.
     */
    protected function normalizeTopList(?array $items): array
    {
        if (!$items) {
            return [];
        }

        $normalized = [];

        foreach ($items as $key => $item) {
            if (is_array($item)) {
                $label = $item['label']
                    ?? $item['name']
                    ?? $item['title']
                    ?? $item['location']
                    ?? $item['industry']
                    ?? (is_string($key) ? $key : 'Item');

                $value = $item['value']
                    ?? $item['count']
                    ?? $item['percentage']
                    ?? $item['percent']
                    ?? null;
            } else {
                $label = is_string($item) ? $item : (string) $key;
                $value = null;
            }

            $normalized[] = [
                'label' => $label,
                'value' => $value,
            ];
        }

        return $normalized;
    }

    /**
     * Normalize demographics structure into:
     * [
     *   'job_title' => [['label' => ..., 'percent' => ...], ...],
     *   'location'  => [...],
     * ]
     */
    protected function normalizeDemographics(?array $categories, int $limitPerCategory = 15): array
    {
        if (!$categories) {
            return [];
        }

        $result = [];

        foreach ($categories as $category => $items) {
            $items = is_array($items) ? $items : [];
            $normalizedItems = [];

            foreach ($items as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $normalizedItems[] = [
                    'label'   => $item['label'] ?? $item['name'] ?? 'Item',
                    'percent' => $item['percent'] ?? $item['percentage'] ?? null,
                ];
            }

            if ($limitPerCategory > 0) {
                $normalizedItems = array_slice($normalizedItems, 0, $limitPerCategory);
            }

            $result[$category] = $normalizedItems;
        }

        return $result;
    }

    /**
     * Convert creator metrics object to a list usable by Blade.
     */
    public function normalizeCreatorMetrics(?array $metrics): array
    {
        $metrics = $metrics ?: [];

        $skipKeys = [
            'charts_raw',
            'chart_line_chart_with_data_points',
            'line_chart_with_data_points',
            'top_demographics_of_followers',
        ];

        $items = [];

        foreach ($metrics as $key => $value) {
            if (in_array($key, $skipKeys, true)) {
                continue;
            }

            $items[] = [
                'key'   => (string) $key,
                'label' => $this->humanizeKey((string) $key),
                'value' => $value,
            ];
        }

        return $items;
    }

    public function humanizeKey(string $key): string
    {
        $key = str_replace(['_', '-'], ' ', $key);
        $key = preg_replace('/\s+/', ' ', $key) ?: $key;

        return ucwords(trim($key));
    }
}
