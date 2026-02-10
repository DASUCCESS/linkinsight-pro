<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
    /**
     * Resolve user and source.
     * - If Authorization: Bearer <extension_api_token> is present, authenticate via extension token.
     * - Otherwise fall back to normal authenticated user (Sanctum/session).
     */
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

    /**
     * Unified profile sync entry point.
     * - If "metrics" is present in the payload, use rich sync logic.
     * - Otherwise use the simple Chrome extension payload logic.
     */
    public function syncProfile(Request $request)
    {
        [$user, $source] = $this->resolveUserAndSource($request);

        if ($request->has('metrics')) {
            return $this->syncProfileWithMetrics($request, $user, $source);
        }

        return $this->syncProfileSimple($request, $user, $source);
    }

    /**
     * Rich profile sync (metrics + optional audience).
     */
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
                $profile = LinkedinProfile::firstOrNew([
                    'user_id'    => $user->id,
                    'public_url' => $data['public_url'],
                ]);

                $profile->fill([
                    'linkedin_id'       => $data['linkedin_id'] ?? $profile->linkedin_id,
                    'name'              => $data['name'],
                    'headline'          => $data['headline'] ?? null,
                    'profile_image_url' => $data['profile_image_url'] ?? null,
                    'location'          => $data['location'] ?? null,
                    'industry'          => $data['industry'] ?? null,
                    'connections_count' => $data['connections_count'],
                    'followers_count'   => $data['followers_count'],
                    'profile_type'      => $data['profile_type'] ?? ($profile->profile_type ?: 'own'),
                    'last_synced_at'    => now(),
                    'sync_status'       => 'ok',
                    'sync_error'        => null,
                ]);

                if (
                    !$profile->exists &&
                    !LinkedinProfile::forUser($user->id)->owned()->where('is_primary', true)->exists()
                ) {
                    $profile->is_primary = true;
                }

                $profile->save();

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

    /**
     * Simple profile sync used by the Chrome extension.
     * Accepts lightweight payload and maps it into the same models.
     * Accepts BOTH:
     * - connections/followers
     * - connections_count/followers_count
     */
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

            $profile = LinkedinProfile::firstOrNew([
                'user_id'    => $user->id,
                'public_url' => $data['public_url'],
            ]);

            $profile->fill([
                'linkedin_id'       => $data['linkedin_id'] ?? $profile->linkedin_id,
                'name'              => $data['name'] ?? $profile->name ?? 'LinkedIn profile',
                'headline'          => $data['headline'] ?? $profile->headline,
                'profile_image_url' => $data['profile_image_url'] ?? $profile->profile_image_url,
                'location'          => $data['location'] ?? $profile->location,
                'industry'          => $data['industry'] ?? $profile->industry,
                'connections_count' => $connectionsCount,
                'followers_count'   => $followersCount,
                'profile_type'      => $profile->profile_type ?: 'own',
                'last_synced_at'    => now(),
                'sync_status'       => 'ok',
                'sync_error'        => null,
            ]);

            if (
                !$profile->exists &&
                !LinkedinProfile::forUser($user->id)->owned()->where('is_primary', true)->exists()
            ) {
                $profile->is_primary = true;
            }

            $profile->save();

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

    /**
     * Unified posts sync entry point.
     * - If posts.*.metrics is present, use rich posts logic.
     * - Otherwise use the simple Chrome extension posts payload.
     */
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

    /**
     * Rich posts sync (per post metrics).
     */
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
            'posts.*.metrics.clicks'             => ['nullable', 'integer', 'min:0'],
            'posts.*.metrics.reactions'          => ['nullable', 'integer', 'min:0'],
            'posts.*.metrics.comments'           => ['nullable', 'integer', 'min:0'],
            'posts.*.metrics.reposts'            => ['nullable', 'integer', 'min:0'],
            'posts.*.metrics.saves'              => ['nullable', 'integer', 'min:0'],
            'posts.*.metrics.engagement_rate'    => ['nullable', 'numeric', 'min:0'],
        ]);

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
            $profile = LinkedinProfile::firstOrCreate(
                [
                    'user_id'    => $user->id,
                    'public_url' => $data['public_url'],
                ],
                [
                    'name'         => 'Unknown',
                    'profile_type' => 'own',
                    'sync_status'  => 'ok',
                ]
            );

            DB::transaction(function () use ($profile, $data) {
                foreach ($data['posts'] as $p) {
                    $post = LinkedinPost::updateOrCreate(
                        [
                            'linkedin_profile_id' => $profile->id,
                            'linkedin_post_id'    => $p['linkedin_post_id'],
                        ],
                        [
                            'linkedin_profile_id' => $profile->id,
                            'permalink'           => $p['permalink'] ?? null,
                            'posted_at'           => Carbon::parse($p['posted_at'])->toDateTimeString(),
                            'post_type'           => $p['post_type'] ?? 'post',
                            'is_reshare'          => $p['is_reshare'] ?? false,
                            'is_sponsored'        => $p['is_sponsored'] ?? false,
                            'content_excerpt'     => $p['content_excerpt'] ?? null,
                        ]
                    );

                    $m = $p['metrics'];

                    LinkedinPostMetric::updateOrCreate(
                        [
                            'linkedin_post_id' => $post->id,
                            'metric_date'      => Carbon::parse($m['metric_date'])->toDateString(),
                        ],
                        [
                            'impressions'     => $m['impressions'],
                            'clicks'          => $m['clicks'] ?? 0,
                            'reactions'       => $m['reactions'] ?? 0,
                            'comments'        => $m['comments'] ?? 0,
                            'reposts'         => $m['reposts'] ?? 0,
                            'saves'           => $m['saves'] ?? 0,
                            'engagement_rate' => $m['engagement_rate'] ?? 0,
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

    /**
     * Simple posts sync used by the Chrome extension.
     * Supports extra fields (activity_category, media_type, reposts, target_permalink).
     */
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

                    $postedAt = $this->parseLinkedInDate($postData['posted_at_human'] ?? null)
                        ?? Carbon::today();

                    $postType = $postData['post_type'] ?? 'post';

                    $post = LinkedinPost::updateOrCreate(
                        [
                            'linkedin_profile_id' => $profile->id,
                            'linkedin_post_id'    => $postData['external_id'],
                        ],
                        [
                            'linkedin_profile_id' => $profile->id,
                            'permalink'           => $postData['permalink'] ?? null,
                            'target_permalink'    => $postData['target_permalink'] ?? null,
                            'posted_at'           => $postedAt->toDateTimeString(),
                            'post_type'           => $postType,
                            'activity_category'   => $category,
                            'media_type'          => $postData['media_type'] ?? null,
                            'is_reshare'          => false,
                            'is_sponsored'        => false,
                            'content_excerpt'     => isset($postData['content'])
                                ? Str::limit($postData['content'], 400)
                                : null,
                        ]
                    );

                    LinkedinPostMetric::updateOrCreate(
                        [
                            'linkedin_post_id' => $post->id,
                            'metric_date'      => $today,
                        ],
                        [
                            'impressions'     => $impressions,
                            'clicks'          => 0,
                            'reactions'       => $reactions,
                            'comments'        => $comments,
                            'reposts'         => $reposts,
                            'saves'           => 0,
                            'engagement_rate' => 0,
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

    /**
     * Normalizes numbers like:
     * - "1,234" => 1234
     * - "500+ connections" => 500
     * - "1.2k" => 1200
     * - "3m" => 3000000
     */
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

        // Remove plus anywhere after a number (handles "500+ connections")
        $str = preg_replace('/(\d)\+/', '$1', $str);

        // Exact compact forms: 1.2k / 3m / 4b
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

        // If string contains words, extract first numeric token + optional suffix.
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

    /**
     * Parses LinkedIn time strings like:
     * - "just now"
     * - "4h", "1d", "2w", "3mo", "1y"
     * Also supports full dates (Carbon::parse).
     */
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
