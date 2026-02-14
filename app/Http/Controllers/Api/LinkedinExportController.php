<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LinkedinPost;
use App\Models\LinkedinProfile;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LinkedinExportController extends Controller
{
    public function exportPostsCsv(Request $request): StreamedResponse
    {
        $user = $request->user();
        $profileId = (int) $request->query('profile_id');
        $from = $request->query('from');
        $to = $request->query('to');

        $profile = LinkedinProfile::forUser($user->id)
            ->when($profileId, fn($q) => $q->where('id', $profileId))
            ->orderByDesc('is_primary')
            ->firstOrFail();

        $toDt = $to ? Carbon::parse($to)->endOfDay() : Carbon::today()->endOfDay();
        $fromDt = $from ? Carbon::parse($from)->startOfDay() : $toDt->copy()->subDays(29)->startOfDay();

        $posts = LinkedinPost::where('linkedin_profile_id', $profile->id)
            ->whereBetween('posted_at', [$fromDt->toDateTimeString(), $toDt->toDateTimeString()])
            ->with('latestMetric')
            ->orderByDesc('posted_at')
            ->get();

        $filename = 'linkedin-posts-' . $profile->id . '-' . $fromDt->toDateString() . '-to-' . $toDt->toDateString() . '.csv';

        return response()->streamDownload(function () use ($posts) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'posted_at','post_type','permalink','content_excerpt',
                'impressions','unique_impressions','clicks','reactions','comments','reposts','saves',
                'video_views','follows_from_post','profile_visits_from_post','engagement_rate'
            ]);

            foreach ($posts as $post) {
                $m = $post->latestMetric;
                fputcsv($out, [
                    optional($post->posted_at)->toDateTimeString(),
                    $post->post_type,
                    $post->permalink,
                    $post->content_excerpt,
                    (int)($m->impressions ?? 0),
                    (int)($m->unique_impressions ?? 0),
                    (int)($m->clicks ?? 0),
                    (int)($m->reactions ?? 0),
                    (int)($m->comments ?? 0),
                    (int)($m->reposts ?? 0),
                    (int)($m->saves ?? 0),
                    (int)($m->video_views ?? 0),
                    (int)($m->follows_from_post ?? 0),
                    (int)($m->profile_visits_from_post ?? 0),
                    (float)($m->engagement_rate ?? 0),
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
