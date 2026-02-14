<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LinkedinAudienceDemographic;
use App\Models\LinkedinConnection;
use App\Models\LinkedinCreatorAudienceMetric;
use App\Models\LinkedinAudienceInsight;
use App\Models\LinkedinPost;
use App\Models\LinkedinPostMetric;
use App\Models\LinkedinProfile;
use App\Models\LinkedinProfileMetric;
use App\Models\LinkedinSyncJob;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LinkedinSyncController extends Controller
{
    protected function resolveUserAndSource(Request $request): array
    {
        $authHeader = $request->header('Authorization');

        if ($authHeader && Str::startsWith($authHeader, 'Bearer ')) {
            $token = trim(Str::after($authHeader, 'Bearer '));

            $user = User::where('extension_api_token', $token)->first();

            if (!$user) {
                abort(401, 'Invalid extension token.');
            }

            return [$user, 'extension'];
        }

        $user = $request->user();

        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        return [$user, 'api'];
    }

    public function syncProfile(Request $request)
    {
        [$user, $source] = $this->resolveUserAndSource($request);

        if ($request->has('metrics')) {
            return $this->syncProfileWithMetrics($request, $user, $source);
        }

        return $this->syncProfileSimple($request, $user, $source);
    }

    protected function syncProfileWithMetrics(Request $request, User $user, string $source)
    {
        $data = $request->validate([
            'linkedin_id'          => ['nullable', 'string', 'max:191'],
            'public_url'           => ['required', 'url'],
            'name'                 => ['required', 'string', 'max:191'],
            'headline'             => ['nullable', 'string', 'max:255'],
            'profile_image_url'    => ['nullable', 'url'],
            'location'             => ['nullable', 'string', 'max:191'],
            'industry'             => ['nullable', 'string', 'max:191'],
            'connections_count'    => ['required', 'integer', 'min:0'],
            'followers_count'      => ['required', 'integer', 'min:0'],
            'profile_type'         => ['nullable', 'in:own,competitor,peer'],

            'metrics'                       => ['required', 'array'],
            'metrics.metric_date'           => ['required', 'date'],
            'metrics.profile_views'         => ['required', 'integer', 'min:0'],
            'metrics.search_appearances'    => ['required', 'integer', 'min:0'],
            'metrics.posts_count'           => ['nullable', 'integer', 'min:0'],
            'metrics.impressions_7d'        => ['nullable', 'integer', 'min:0'],
            'metrics.engagements_7d'        => ['nullable', 'integer', 'min:0'],

            'audience'                      => ['nullable', 'array'],
            'audience.snapshot_date'        => ['nullable', 'date'],
            'audience.top_job_titles'       => ['nullable', 'array'],
            'audience.top_industries'       => ['nullable', 'array'],
            'audience.top_locations'        => ['nullable', 'array'],
            'audience.engagement_sources'   => ['nullable', 'array'],
        ]);

        $data['public_url'] = $this->normalizePublicUrl($data['public_url']);

        $job = LinkedinSyncJob::create([
            'user_id'    => $user->id,
            'source'     => $source,
            'type'       => 'profile',
            'status'     => 'running',
            'payload'    => ['public_url' => $data['public_url']],
            'started_at' => now(),
        ]);

        $profile = null;

        try {
            DB::transaction(function () use (&$profile, $data, $user) {
                $payloadForProfile = [
                    'linkedin_id'       => $data['linkedin_id'] ?? null,
                    'public_url'        => $data['public_url'],
                    'name'              => $data['name'],
                    'headline'          => $data['headline'] ?? null,
                    'profile_image_url' => $data['profile_image_url'] ?? null,
                    'location'          => $data['location'] ?? null,
                    'industry'          => $data['industry'] ?? null,
                    'connections_count' => $data['connections_count'],
                    'followers_count'   => $data['followers_count'],
                    'profile_type'      => $data['profile_type'] ?? 'own',
                    'last_synced_at'    => now(),
                    'sync_status'       => 'ok',
                    'sync_error'        => null,
                ];

                $profile = LinkedinProfile::upsertFromPayload($user, $payloadForProfile);

                $m = $data['metrics'];

                LinkedinProfileMetric::updateOrCreate(
                    [
                        'linkedin_profile_id' => $profile->id,
                        'metric_date'         => Carbon::parse($m['metric_date'])->toDateString(),
                    ],
                    [
                        'connections_count'  => $data['connections_count'],
                        'followers_count'    => $data['followers_count'],
                        'profile_views'      => $m['profile_views'],
                        'search_appearances' => $m['search_appearances'],
                        'posts_count'        => $m['posts_count'] ?? 0,
                        'impressions_7d'     => $m['impressions_7d'] ?? 0,
                        'engagements_7d'     => $m['engagements_7d'] ?? 0,
                    ]
                );

                if (!empty($data['audience'])) {
                    $a = $data['audience'];
                    $snapshotDate = $a['snapshot_date'] ?? $m['metric_date'];

                    LinkedinAudienceInsight::updateOrCreate(
                        [
                            'linkedin_profile_id' => $profile->id,
                            'snapshot_date'       => Carbon::parse($snapshotDate)->toDateString(),
                        ],
                        [
                            'top_job_titles'     => $a['top_job_titles'] ?? null,
                            'top_industries'     => $a['top_industries'] ?? null,
                            'top_locations'      => $a['top_locations'] ?? null,
                            'engagement_sources' => $a['engagement_sources'] ?? null,
                        ]
                    );
                }
            });

            $job->status       = 'success';
            $job->items_count  = 1;
            $job->finished_at  = now();
            $job->save();

            return response()->json([
                'status'     => 'ok',
                'profile_id' => $profile->id,
            ]);
        } catch (\Throwable $e) {
            if ($profile) {
                $profile->update([
                    'sync_status' => 'error',
                    'sync_error'  => $e->getMessage(),
                ]);
            }

            $job->status        = 'failed';
            $job->error_message = $e->getMessage();
            $job->finished_at   = now();
            $job->save();

            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to sync profile.',
            ], 500);
        }
    }

    protected function syncProfileSimple(Request $request, User $user, string $source)
    {
        $data = $request->validate([
            'linkedin_id'       => ['nullable', 'string', 'max:191'],
            'public_url'        => ['required', 'url'],
            'name'              => ['nullable', 'string', 'max:191'],
            'headline'          => ['nullable', 'string', 'max:255'],
            'profile_image_url' => ['nullable', 'url'],
            'location'          => ['nullable', 'string', 'max:191'],
            'industry'          => ['nullable', 'string', 'max:191'],

            'connections'       => ['nullable'],
            'followers'         => ['nullable'],
            'connections_count' => ['nullable'],
            'followers_count'   => ['nullable'],

            'profile_views'      => ['nullable'],
            'search_appearances' => ['nullable'],
        ]);

        $data['public_url'] = $this->normalizePublicUrl($data['public_url']);

        $job = LinkedinSyncJob::create([
            'user_id'    => $user->id,
            'source'     => $source,
            'type'       => 'profile',
            'status'     => 'running',
            'payload'    => ['public_url' => $data['public_url']],
            'started_at' => now(),
        ]);

        $profile = null;

        try {
            $connectionsCount = $this->normalizeNumber(
                $data['connections_count'] ?? ($data['connections'] ?? null)
            ) ?? 0;

            $followersCount = $this->normalizeNumber(
                $data['followers_count'] ?? ($data['followers'] ?? null)
            ) ?? 0;

            $payloadForProfile = [
                'linkedin_id'       => $data['linkedin_id'] ?? null,
                'public_url'        => $data['public_url'],
                'name'              => $data['name'] ?? null,
                'headline'          => $data['headline'] ?? null,
                'profile_image_url' => $data['profile_image_url'] ?? null,
                'location'          => $data['location'] ?? null,
                'industry'          => $data['industry'] ?? null,
                'connections_count' => $connectionsCount,
                'followers_count'   => $followersCount,
                'profile_type'      => 'own',
                'last_synced_at'    => now(),
                'sync_status'       => 'ok',
                'sync_error'        => null,
            ];

            $profile = LinkedinProfile::upsertFromPayload($user, $payloadForProfile);

            $today = Carbon::today()->toDateString();

            LinkedinProfileMetric::updateOrCreate(
                [
                    'linkedin_profile_id' => $profile->id,
                    'metric_date'         => $today,
                ],
                [
                    'connections_count'  => $connectionsCount,
                    'followers_count'    => $followersCount,
                    'profile_views'      => $this->normalizeNumber($data['profile_views'] ?? null) ?? 0,
                    'search_appearances' => $this->normalizeNumber($data['search_appearances'] ?? null) ?? 0,
                    'posts_count'        => 0,
                    'impressions_7d'     => 0,
                    'engagements_7d'     => 0,
                ]
            );

            $job->status       = 'success';
            $job->items_count  = 1;
            $job->finished_at  = now();
            $job->save();

            return response()->json([
                'status'     => 'ok',
                'message'    => 'Profile synced.',
                'profile_id' => $profile->id,
            ]);
        } catch (\Throwable $e) {
            if ($profile) {
                $profile->update([
                    'sync_status' => 'error',
                    'sync_error'  => $e->getMessage(),
                ]);
            }

            $job->status        = 'failed';
            $job->error_message = $e->getMessage();
            $job->finished_at   = now();
            $job->save();

            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to sync profile.',
            ], 500);
        }
    }

    public function syncPosts(Request $request)
    {
        [$user, $source] = $this->resolveUserAndSource($request);

        $postsInput = $request->input('posts');
        $hasMetrics = false;

        if (is_array($postsInput)) {
            foreach ($postsInput as $p) {
                if (isset($p['metrics'])) {
                    $hasMetrics = true;
                    break;
                }
            }
        }

        if ($hasMetrics) {
            return $this->syncPostsWithMetrics($request, $user, $source);
        }

        return $this->syncPostsSimple($request, $user, $source);
    }

    protected function normalizeUrl(?string $url): ?string
    {
        if (!$url) return null;

        $u = trim($url);
        $u = preg_replace('/[?#].*$/', '', $u);
        $u = preg_replace('#^http://#i', 'https://', $u);

        $u = str_ireplace('https://linkedin.com', 'https://www.linkedin.com', $u);
        $u = str_ireplace('https://m.linkedin.com', 'https://www.linkedin.com', $u);
        $u = str_ireplace('https://mobile.linkedin.com', 'https://www.linkedin.com', $u);

        $u = rtrim($u, '/');

        return $u ?: null;
    }

    protected function normalizePublicUrl(string $publicUrl): string
    {
        return $this->normalizeUrl($publicUrl) ?? $publicUrl;
    }

    protected function permalinkFirstKey(array $data): ?string
    {
        $permalink = $this->normalizeUrl($data['permalink'] ?? null);
        if ($permalink) return 'permalink:' . $permalink;

        $target = $this->normalizeUrl($data['target_permalink'] ?? null);
        if ($target) return 'target:' . $target;

        $id = $data['linkedin_post_id'] ?? ($data['external_id'] ?? null);
        if ($id) return 'id:' . trim((string) $id);

        return null;
    }

    protected function upsertPostPermalinkFirst(int $profileId, array $postData): LinkedinPost
    {
        $permalink = $this->normalizeUrl($postData['permalink'] ?? null);
        $target    = $this->normalizeUrl($postData['target_permalink'] ?? null);

        $query = LinkedinPost::where('linkedin_profile_id', $profileId);

        $existing = null;

        if ($permalink) {
            $existing = (clone $query)->where('permalink', $permalink)->first();
        }

        if (!$existing && $target) {
            $existing = (clone $query)->where('target_permalink', $target)->first();
        }

        if (!$existing) {
            $id = $postData['linkedin_post_id'] ?? ($postData['external_id'] ?? null);
            if ($id) {
                $existing = (clone $query)->where('linkedin_post_id', (string) $id)->first();
            }
        }

        $postedAt = $postData['posted_at'] ?? null;
        if (!$postedAt && !empty($postData['posted_at_human'])) {
            $postedAt = $this->parseLinkedInDate($postData['posted_at_human'])?->toDateTimeString();
        }

        $payload = [
            'linkedin_profile_id' => $profileId,
            'linkedin_post_id'    => (string) (
                $postData['linkedin_post_id']
                ?? ($postData['external_id'] ?? ($existing?->linkedin_post_id ?? ''))
            ),
            'permalink'         => $permalink ?? ($existing?->permalink ?? null),
            'target_permalink'  => $target ?? ($existing?->target_permalink ?? null),
            'posted_at'         => $postedAt
                ? Carbon::parse($postedAt)->toDateTimeString()
                : (($existing?->posted_at) ? Carbon::parse($existing->posted_at)->toDateTimeString() : Carbon::now()->toDateTimeString()),
            'post_type'         => $postData['post_type'] ?? ($existing?->post_type ?? 'post'),
            'activity_category' => $postData['activity_category'] ?? ($existing?->activity_category ?? null),
            'media_type'        => $postData['media_type'] ?? ($existing?->media_type ?? null),
            'is_reshare'        => (bool) ($postData['is_reshare'] ?? ($existing?->is_reshare ?? false)),
            'is_sponsored'      => (bool) ($postData['is_sponsored'] ?? ($existing?->is_sponsored ?? false)),
            'content_excerpt'   => $postData['content_excerpt']
                ?? (isset($postData['content'])
                    ? Str::limit($postData['content'], 400)
                    : ($existing?->content_excerpt ?? null)),
        ];

        if ($existing) {
            $existing->fill($payload);
            $existing->save();
            return $existing;
        }

        return LinkedinPost::create($payload);
    }

    protected function sha256($value): string
    {
        return hash(
            'sha256',
            is_string($value) ? $value : json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );
    }

    protected function syncPostsWithMetrics(Request $request, User $user, string $source)
    {
        $data = $request->validate([
            'public_url'                 => ['required', 'url'],
            'posts'                      => ['required', 'array', 'min:1'],

            'posts.*.linkedin_post_id'   => ['required', 'string', 'max:191'],
            'posts.*.permalink'          => ['nullable', 'string', 'max:2048'],
            'posts.*.posted_at'          => ['required', 'date'],
            'posts.*.post_type'          => ['nullable', 'string', 'max:50'],
            'posts.*.is_reshare'         => ['nullable', 'boolean'],
            'posts.*.is_sponsored'       => ['nullable', 'boolean'],
            'posts.*.content_excerpt'    => ['nullable', 'string'],

            'posts.*.metrics'                    => ['required', 'array'],
            'posts.*.metrics.metric_date'        => ['required', 'date'],
            'posts.*.metrics.impressions'        => ['required', 'integer', 'min:0'],
            'posts.*.metrics.unique_impressions' => ['nullable', 'integer', 'min:0'],
            'posts.*.metrics.clicks'             => ['nullable', 'integer', 'min:0'],
            'posts.*.metrics.reactions'          => ['nullable', 'integer', 'min:0'],
            'posts.*.metrics.comments'           => ['nullable', 'integer', 'min:0'],
            'posts.*.metrics.reposts'            => ['nullable', 'integer', 'min:0'],
            'posts.*.metrics.saves'              => ['nullable', 'integer', 'min:0'],
            'posts.*.metrics.video_views'        => ['nullable', 'integer', 'min:0'],
            'posts.*.metrics.follows_from_post'  => ['nullable', 'integer', 'min:0'],
            'posts.*.metrics.profile_visits_from_post' => ['nullable', 'integer', 'min:0'],
            'posts.*.metrics.engagement_rate'    => ['nullable', 'numeric', 'min:0'],
        ]);

        $data['public_url'] = $this->normalizePublicUrl($data['public_url']);

        $job = LinkedinSyncJob::create([
            'user_id'    => $user->id,
            'source'     => $source,
            'type'       => 'posts',
            'status'     => 'running',
            'payload'    => [
                'public_url' => $data['public_url'],
                'count'      => count($data['posts']),
            ],
            'started_at' => now(),
        ]);

        $profile = null;

        try {
            $profile = LinkedinProfile::upsertFromPayload($user, [
                'public_url'     => $data['public_url'],
                'name'           => 'Unknown',
                'profile_type'   => 'own',
                'last_synced_at' => now(),
                'sync_status'    => 'ok',
                'sync_error'     => null,
            ]);

            DB::transaction(function () use ($profile, $data) {
                foreach ($data['posts'] as $p) {
                    $post = $this->upsertPostPermalinkFirst($profile->id, [
                        'linkedin_post_id' => $p['linkedin_post_id'],
                        'permalink'        => $p['permalink'] ?? null,
                        'posted_at'        => $p['posted_at'],
                        'post_type'        => $p['post_type'] ?? 'post',
                        'is_reshare'       => $p['is_reshare'] ?? false,
                        'is_sponsored'     => $p['is_sponsored'] ?? false,
                        'content_excerpt'  => $p['content_excerpt'] ?? null,
                    ]);

                    $m = $p['metrics'];
                    $metricDate = Carbon::parse($m['metric_date'])->toDateString();

                    LinkedinPostMetric::updateOrCreate(
                        [
                            'linkedin_post_id' => $post->id,
                            'metric_date'      => $metricDate,
                        ],
                        [
                            'impressions'                  => (int) $m['impressions'],
                            'unique_impressions'           => (int) ($m['unique_impressions'] ?? 0),
                            'clicks'                      => (int) ($m['clicks'] ?? 0),
                            'reactions'                   => (int) ($m['reactions'] ?? 0),
                            'comments'                    => (int) ($m['comments'] ?? 0),
                            'reposts'                     => (int) ($m['reposts'] ?? 0),
                            'saves'                       => (int) ($m['saves'] ?? 0),
                            'video_views'                 => (int) ($m['video_views'] ?? 0),
                            'follows_from_post'           => (int) ($m['follows_from_post'] ?? 0),
                            'profile_visits_from_post'    => (int) ($m['profile_visits_from_post'] ?? 0),
                            'engagement_rate'             => (float) ($m['engagement_rate'] ?? 0),
                        ]
                    );
                }

                $profile->last_synced_at = now();
                $profile->sync_status    = 'ok';
                $profile->sync_error     = null;
                $profile->save();
            });

            $job->status       = 'success';
            $job->items_count  = count($data['posts']);
            $job->finished_at  = now();
            $job->save();

            return response()->json([
                'status'       => 'ok',
                'profile_id'   => $profile->id,
                'posts_synced' => count($data['posts']),
            ]);
        } catch (\Throwable $e) {
            if ($profile) {
                $profile->update([
                    'sync_status' => 'error',
                    'sync_error'  => $e->getMessage(),
                ]);
            }

            $job->status        = 'failed';
            $job->error_message = $e->getMessage();
            $job->finished_at   = now();
            $job->save();

            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to sync posts.',
            ], 500);
        }
    }

    protected function syncPostsSimple(Request $request, User $user, string $source)
    {
        $validated = $request->validate([
            'activity_category'        => ['nullable', 'string', 'max:50'],
            'public_url'               => ['nullable', 'url'],
            'posts'                    => ['required', 'array', 'min:1'],

            'posts.*.external_id'      => ['required', 'string', 'max:191'],
            'posts.*.post_type'        => ['nullable', 'string', 'max:50'],
            'posts.*.media_type'       => ['nullable', 'string', 'max:30'],
            'posts.*.content'          => ['nullable', 'string'],
            'posts.*.posted_at_human'  => ['nullable', 'string'],
            'posts.*.impressions'      => ['nullable'],
            'posts.*.reactions'        => ['nullable'],
            'posts.*.comments'         => ['nullable'],
            'posts.*.reposts'          => ['nullable'],
            'posts.*.permalink'        => ['nullable', 'string', 'max:2048'],
            'posts.*.target_permalink' => ['nullable', 'string', 'max:2048'],
        ]);

        $job = LinkedinSyncJob::create([
            'user_id'    => $user->id,
            'source'     => $source,
            'type'       => 'posts',
            'status'     => 'running',
            'payload'    => [
                'count'             => count($validated['posts']),
                'activity_category' => $validated['activity_category'] ?? null,
            ],
            'started_at' => now(),
        ]);

        try {
            $profile = LinkedinProfile::forUser($user->id)
                ->orderByDesc('is_primary')
                ->orderBy('id')
                ->first();

            if (!$profile) {
                $job->status        = 'failed';
                $job->error_message = 'No LinkedIn profile found for this user. Sync profile first.';
                $job->finished_at   = now();
                $job->save();

                return response()->json([
                    'message' => 'No LinkedIn profile found for this user. Sync profile first.',
                ], 422);
            }

            $count = 0;
            $today = Carbon::today()->toDateString();
            $category = $validated['activity_category'] ?? null;

            DB::transaction(function () use ($validated, $profile, $today, $category, &$count) {
                foreach ($validated['posts'] as $postData) {
                    $impressions = $this->normalizeNumber($postData['impressions'] ?? null) ?? 0;
                    $reactions   = $this->normalizeNumber($postData['reactions'] ?? null) ?? 0;
                    $comments    = $this->normalizeNumber($postData['comments'] ?? null) ?? 0;
                    $reposts     = $this->normalizeNumber($postData['reposts'] ?? null) ?? 0;

                    $postedAt = $this->parseLinkedInDate($postData['posted_at_human'] ?? null) ?? Carbon::today();

                    $post = $this->upsertPostPermalinkFirst($profile->id, [
                        'external_id'       => $postData['external_id'],
                        'permalink'         => $postData['permalink'] ?? null,
                        'target_permalink'  => $postData['target_permalink'] ?? null,
                        'posted_at'         => $postedAt->toDateTimeString(),
                        'post_type'         => $postData['post_type'] ?? 'post',
                        'activity_category' => $category,
                        'media_type'        => $postData['media_type'] ?? null,
                        'content'           => $postData['content'] ?? null,
                    ]);

                    LinkedinPostMetric::updateOrCreate(
                        [
                            'linkedin_post_id' => $post->id,
                            'metric_date'      => $today,
                        ],
                        [
                            'impressions'                => (int) $impressions,
                            'unique_impressions'         => 0,
                            'clicks'                    => 0,
                            'reactions'                 => (int) $reactions,
                            'comments'                  => (int) $comments,
                            'reposts'                   => (int) $reposts,
                            'saves'                     => 0,
                            'video_views'               => 0,
                            'follows_from_post'         => 0,
                            'profile_visits_from_post'  => 0,
                            'engagement_rate'           => 0,
                        ]
                    );

                    $count++;
                }
            });

            $job->status       = 'success';
            $job->items_count  = $count;
            $job->finished_at  = now();
            $job->save();

            return response()->json([
                'message' => 'Activity synced.',
                'synced'  => $count,
            ]);
        } catch (\Throwable $e) {
            $job->status        = 'failed';
            $job->error_message = $e->getMessage();
            $job->finished_at   = now();
            $job->save();

            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to sync activity.',
            ], 500);
        }
    }

    public function syncAudienceDemographics(Request $request)
    {
        [$user, $source] = $this->resolveUserAndSource($request);

        $data = $request->validate([
            'public_url'      => ['required', 'url'],
            'snapshot_date'   => ['required', 'date'],
            'followers_count' => ['nullable', 'integer', 'min:0'],
            'demographics'    => ['nullable', 'array'],
        ]);

        $data['public_url'] = $this->normalizePublicUrl($data['public_url']);

        $job = LinkedinSyncJob::create([
            'user_id'    => $user->id,
            'source'     => $source,
            'type'       => 'audience_demographics',
            'status'     => 'running',
            'payload'    => [
                'public_url'    => $data['public_url'],
                'snapshot_date' => $data['snapshot_date'],
            ],
            'started_at' => now(),
        ]);

        $profile = null;

        try {
            $profile = LinkedinProfile::upsertFromPayload($user, [
                'public_url'     => $data['public_url'],
                'name'           => 'Unknown',
                'profile_type'   => 'own',
                'last_synced_at' => now(),
                'sync_status'    => 'ok',
                'sync_error'     => null,
            ]);

            $snapshot = Carbon::parse($data['snapshot_date'])->toDateString();
            $demo     = $data['demographics'] ?? null;

            LinkedinAudienceDemographic::updateOrCreate(
                [
                    'linkedin_profile_id' => $profile->id,
                    'snapshot_date'       => $snapshot,
                ],
                [
                    'demographics'   => $demo,
                    'followers_count'=> (int) ($data['followers_count'] ?? 0),
                    'source_hash'    => $demo ? $this->sha256($demo) : null,
                ]
            );

            $profile->last_synced_at = now();
            $profile->save();

            $job->status      = 'success';
            $job->items_count = 1;
            $job->finished_at = now();
            $job->save();

            return response()->json([
                'status'     => 'ok',
                'profile_id' => $profile->id,
            ]);
        } catch (\Throwable $e) {
            if ($profile) {
                $profile->update([
                    'sync_status' => 'error',
                    'sync_error'  => $e->getMessage(),
                ]);
            }

            $job->status        = 'failed';
            $job->error_message = $e->getMessage();
            $job->finished_at   = now();
            $job->save();

            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to sync demographics.',
            ], 500);
        }
    }

    public function syncCreatorAudience(Request $request)
    {
        [$user, $source] = $this->resolveUserAndSource($request);

        $data = $request->validate([
            'public_url'           => ['required', 'url'],
            'metrics'              => ['required', 'array'],
            'metrics.metric_date'  => ['required', 'date'],
            'metrics.data'         => ['nullable', 'array'],
        ]);

        $data['public_url'] = $this->normalizePublicUrl($data['public_url']);

        $job = LinkedinSyncJob::create([
            'user_id'    => $user->id,
            'source'     => $source,
            'type'       => 'creator_audience',
            'status'     => 'running',
            'payload'    => [
                'public_url'  => $data['public_url'],
                'metric_date' => $data['metrics']['metric_date'],
            ],
            'started_at' => now(),
        ]);

        $profile = null;

        try {
            $profile = LinkedinProfile::upsertFromPayload($user, [
                'public_url'     => $data['public_url'],
                'name'           => 'Unknown',
                'profile_type'   => 'own',
                'last_synced_at' => now(),
                'sync_status'    => 'ok',
                'sync_error'     => null,
            ]);

            $metricDate = Carbon::parse($data['metrics']['metric_date'])->toDateString();
            $payload    = $data['metrics']['data'] ?? null;

            LinkedinCreatorAudienceMetric::updateOrCreate(
                [
                    'linkedin_profile_id' => $profile->id,
                    'metric_date'         => $metricDate,
                ],
                [
                    'metrics'     => $payload,
                    'source_hash' => $payload ? $this->sha256($payload) : null,
                ]
            );

            $profile->last_synced_at = now();
            $profile->save();

            $job->status      = 'success';
            $job->items_count = 1;
            $job->finished_at = now();
            $job->save();

            return response()->json([
                'status'     => 'ok',
                'profile_id' => $profile->id,
            ]);
        } catch (\Throwable $e) {
            if ($profile) {
                $profile->update([
                    'sync_status' => 'error',
                    'sync_error'  => $e->getMessage(),
                ]);
            }

            $job->status        = 'failed';
            $job->error_message = $e->getMessage();
            $job->finished_at   = now();
            $job->save();

            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to sync creator audience metrics.',
            ], 500);
        }
    }

    public function syncConnections(Request $request)
    {
        [$user, $source] = $this->resolveUserAndSource($request);

        $data = $request->validate([
            'public_url'                => ['required', 'url'],
            'connections'              => ['required', 'array', 'min:1'],

            'connections.*.linkedin_connection_id' => ['nullable', 'string', 'max:191'],
            'connections.*.public_identifier'      => ['nullable', 'string', 'max:191'],
            'connections.*.profile_url'            => ['nullable', 'string', 'max:2048'],
            'connections.*.full_name'              => ['nullable', 'string', 'max:191'],
            'connections.*.headline'               => ['nullable', 'string', 'max:255'],
            'connections.*.location'               => ['nullable', 'string', 'max:191'],
            'connections.*.industry'               => ['nullable', 'string', 'max:191'],
            'connections.*.profile_image_url'      => ['nullable', 'string', 'max:2048'],
            'connections.*.degree'                 => ['nullable', 'integer', 'min:1', 'max:3'],
            'connections.*.mutual_connections_count' => ['nullable', 'integer', 'min:0'],
            'connections.*.connected_at'           => ['nullable', 'date'],
            'connections.*.last_seen_at'           => ['nullable', 'date'],
        ]);

        $data['public_url'] = $this->normalizePublicUrl($data['public_url']);

        $job = LinkedinSyncJob::create([
            'user_id'    => $user->id,
            'source'     => $source,
            'type'       => 'connections',
            'status'     => 'running',
            'payload'    => [
                'public_url' => $data['public_url'],
                'count'      => count($data['connections']),
            ],
            'started_at' => now(),
        ]);

        $profile = null;

        try {
            $profile = LinkedinProfile::upsertFromPayload($user, [
                'public_url'     => $data['public_url'],
                'name'           => 'Unknown',
                'profile_type'   => 'own',
                'last_synced_at' => now(),
                'sync_status'    => 'ok',
                'sync_error'     => null,
            ]);

            $count = 0;

            DB::transaction(function () use ($profile, $data, &$count) {
                foreach ($data['connections'] as $c) {
                    $profileUrl = $this->normalizeUrl($c['profile_url'] ?? null);

                    if (!empty($c['public_identifier'])) {
                        $dedupe = 'handle:' . trim((string) $c['public_identifier']);
                    } elseif (!empty($c['linkedin_connection_id'])) {
                        $dedupe = 'id:' . trim((string) $c['linkedin_connection_id']);
                    } elseif ($profileUrl) {
                        $dedupe = 'url:' . $profileUrl;
                    } else {
                        $dedupe = 'hash:' . $this->sha256([
                            'n' => $c['full_name'] ?? null,
                            'h' => $c['headline'] ?? null,
                            'l' => $c['location'] ?? null,
                        ]);
                    }

                    $payload = [
                        'linkedin_profile_id'      => $profile->id,
                        'linkedin_connection_id'   => $c['linkedin_connection_id'] ?? null,
                        'public_identifier'        => $c['public_identifier'] ?? null,
                        'profile_url'              => $profileUrl,
                        'full_name'                => $c['full_name'] ?? null,
                        'headline'                 => $c['headline'] ?? null,
                        'location'                 => $c['location'] ?? null,
                        'industry'                 => $c['industry'] ?? null,
                        'profile_image_url'        => $c['profile_image_url'] ?? null,
                        'degree'                   => $c['degree'] ?? null,
                        'mutual_connections_count' => (int) ($c['mutual_connections_count'] ?? 0),
                        'connected_at'             => !empty($c['connected_at'])
                            ? Carbon::parse($c['connected_at'])->toDateTimeString()
                            : null,
                        'last_seen_at'             => !empty($c['last_seen_at'])
                            ? Carbon::parse($c['last_seen_at'])->toDateTimeString()
                            : null,
                        'dedupe_key'               => $dedupe,
                        'source_hash'              => $this->sha256([
                            'linkedin_connection_id'   => $c['linkedin_connection_id'] ?? null,
                            'public_identifier'        => $c['public_identifier'] ?? null,
                            'profile_url'              => $profileUrl,
                            'full_name'                => $c['full_name'] ?? null,
                            'headline'                 => $c['headline'] ?? null,
                            'location'                 => $c['location'] ?? null,
                            'industry'                 => $c['industry'] ?? null,
                            'degree'                   => $c['degree'] ?? null,
                            'mutual_connections_count' => $c['mutual_connections_count'] ?? 0,
                        ]),
                    ];

                    LinkedinConnection::updateOrCreate(
                        [
                            'linkedin_profile_id' => $profile->id,
                            'dedupe_key'          => $dedupe,
                        ],
                        $payload
                    );

                    $count++;
                }

                $profile->last_synced_at = now();
                $profile->save();
            });

            $job->status      = 'success';
            $job->items_count = $count;
            $job->finished_at = now();
            $job->save();

            return response()->json([
                'status'             => 'ok',
                'profile_id'         => $profile->id,
                'connections_synced' => $count,
            ]);
        } catch (\Throwable $e) {
            if ($profile) {
                $profile->update([
                    'sync_status' => 'error',
                    'sync_error'  => $e->getMessage(),
                ]);
            }

            $job->status        = 'failed';
            $job->error_message = $e->getMessage();
            $job->finished_at   = now();
            $job->save();

            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to sync connections.',
            ], 500);
        }
    }

    protected function normalizeNumber($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        $str = trim((string) $value);
        $str = str_replace(',', '', $str);
        $str = preg_replace('/(\d)\+/', '$1', $str);

        if (preg_match('/^(\d+(?:\.\d+)?)([kmb])$/i', $str, $matches)) {
            $num = (float) $matches[1];
            $suffix = strtolower($matches[2]);
            $multiplier = match ($suffix) {
                'k' => 1000,
                'm' => 1000000,
                'b' => 1000000000,
                default => 1,
            };

            return (int) round($num * $multiplier);
        }

        if (!is_numeric($str)) {
            if (preg_match('/(\d[\d\.]*)(\s*)([kmb])?/i', $str, $m)) {
                $num = (float) $m[1];
                $suffix = strtolower($m[3] ?? '');
                $multiplier = match ($suffix) {
                    'k' => 1000,
                    'm' => 1000000,
                    'b' => 1000000000,
                    default => 1,
                };

                return (int) round($num * $multiplier);
            }

            return null;
        }

        return (int) $str;
    }

    protected function parseLinkedInDate(?string $text): ?Carbon
    {
        if (!$text) {
            return null;
        }

        $t = trim(mb_strtolower($text));

        if ($t === 'just now') {
            return Carbon::now();
        }

        if (preg_match('/^(\d+)\s*(m|h|d|w|mo|y)$/', $t, $m)) {
            $n = (int) $m[1];
            $u = $m[2];

            return match ($u) {
                'm'  => Carbon::now()->subMinutes($n),
                'h'  => Carbon::now()->subHours($n),
                'd'  => Carbon::now()->subDays($n),
                'w'  => Carbon::now()->subWeeks($n),
                'mo' => Carbon::now()->subMonths($n),
                'y'  => Carbon::now()->subYears($n),
                default => null,
            };
        }

        try {
            return Carbon::parse($text);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
