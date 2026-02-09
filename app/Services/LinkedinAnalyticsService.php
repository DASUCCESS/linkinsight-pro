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
    public function getSummaryForUser(User $user, ?int $profileId = null): array
    {
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

        $today = Carbon::today();
        $from  = $today->copy()->subDays(29);

        $metrics = LinkedinProfileMetric::where('linkedin_profile_id', $profile->id)
            ->whereBetween('metric_date', [$from, $today])
            ->orderBy('metric_date')
            ->get();

        $latest = $metrics->last();

        $connectionsStart = optional($metrics->first())->connections_count ?? 0;
        $connectionsEnd   = optional($latest)->connections_count ?? 0;

        $followersStart   = optional($metrics->first())->followers_count ?? 0;
        $followersEnd     = optional($latest)->followers_count ?? 0;

        $viewsTotal = $metrics->sum('profile_views');
        $searchTotal = $metrics->sum('search_appearances');

        $postsQuery = LinkedinPost::where('linkedin_profile_id', $profile->id);

        $recentPosts = $postsQuery
            ->orderByDesc('posted_at')
            ->take(5)
            ->get();

        $postMetrics = LinkedinPostMetric::whereIn('linkedin_post_id', $recentPosts->pluck('id'))
            ->whereBetween('metric_date', [$from, $today])
            ->get()
            ->groupBy('linkedin_post_id');

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
            ],
            'timeseries' => [
                'dates'             => $metrics->pluck('metric_date')->map->toDateString(),
                'profile_views'     => $metrics->pluck('profile_views'),
                'search_appearances'=> $metrics->pluck('search_appearances'),
                'connections'       => $metrics->pluck('connections_count'),
                'followers'         => $metrics->pluck('followers_count'),
            ],
            'recent_posts' => $recentPosts->map(function (LinkedinPost $post) use ($postMetrics) {
                $metrics = $postMetrics->get($post->id)?->sortByDesc('metric_date')->first();

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
}
