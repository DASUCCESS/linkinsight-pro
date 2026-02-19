@extends('user.layout')

@section('page_title', 'Dashboard')
@section('page_subtitle', 'Quick overview of your LinkedIn performance, audience and sync activity.')

@section('content')
    @php
        use Illuminate\Support\Str;
        use Illuminate\Support\Carbon;
        use Illuminate\Support\Facades\Route;

        $status              = $summary['status'] ?? 'empty';

        $profile             = $summary['profile'] ?? [];
        $times               = $summary['timeseries'] ?? [];
        $posts               = $summary['recent_posts'] ?? [];
        $audienceInsights    = $summary['audience_insights'] ?? null;
        $audienceDemographics= $summary['audience_demographics'] ?? null;
        $creatorAudience     = $summary['creator_audience'] ?? null;
        $connectionsSample   = $summary['connections_sample'] ?? [];

        $hasAudienceData = !empty($audienceInsights) || !empty($audienceDemographics) || !empty($creatorAudience);

        $insightDate = data_get($audienceInsights, 'snapshot_date');
        $demoDate    = data_get($audienceDemographics, 'snapshot_date');
        $creatorDate = data_get($creatorAudience, 'snapshot_date');

        $demoFollowersCount = data_get($audienceDemographics, 'followers_count');

        // Fix "Unknown" display (profile)
        $rawName = trim((string) data_get($profile, 'name', ''));
        $displayName = ($rawName !== '' && Str::lower($rawName) !== 'unknown') ? $rawName : 'LinkedIn profile';
        $displayImg  = data_get($profile, 'profile_image_url');

        $extensionId  = config('linkinsight.extension_id');
        $extensionUrl = config('linkinsight.extension_popup_url');
        $storeUrl     = config('linkinsight.chrome_store_url');
    @endphp

    @if($status === 'empty')
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6 max-w-3xl">
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-50 mb-2">
                Connect your LinkedIn profile
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
                You have not connected any LinkedIn profile yet. Install the browser extension or use the API
                to send your profile and post metrics, and we will start showing analytics here.
            </p>

            <div class="flex flex-wrap items-center gap-3">
                <button type="button"
                        id="btnConnectLinkedin"
                        class="px-4 py-2 rounded-full text-sm font-semibold shadow-xl cursor-pointer
                               bg-gradient-to-r from-indigo-500 to-sky-500 text-white
                               transition transform duration-200 hover:scale-[var(--hover-scale)]">
                    Connect LinkedIn
                </button>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Opens the Chrome extension if installed.
                </p>
            </div>
        </div>
    @else
        {{-- Quick navigation --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-5 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-50">
                        Quick navigation
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Open full pages for detailed synced data.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2 text-xs">
                    @if(Route::has('user.linkedin.audience_insights.index'))
                        <a href="{{ route('user.linkedin.audience_insights.index') }}"
                           class="px-3 py-1.5 rounded-full font-semibold shadow-md cursor-pointer border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-100 hover:scale-[var(--hover-scale)] transition">
                            Audience Insights
                        </a>
                    @endif

                    @if(Route::has('user.linkedin.demographics.index'))
                        <a href="{{ route('user.linkedin.demographics.index') }}"
                           class="px-3 py-1.5 rounded-full font-semibold shadow-md cursor-pointer border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-100 hover:scale-[var(--hover-scale)] transition">
                            Demographics
                        </a>
                    @endif

                    @if(Route::has('user.linkedin.creator_metrics.index'))
                        <a href="{{ route('user.linkedin.creator_metrics.index') }}"
                           class="px-3 py-1.5 rounded-full font-semibold shadow-md cursor-pointer border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-100 hover:scale-[var(--hover-scale)] transition">
                            Creator Metrics
                        </a>
                    @endif

                    @if(Route::has('user.linkedin.connections.index'))
                        <a href="{{ route('user.linkedin.connections.index') }}"
                           class="px-3 py-1.5 rounded-full font-semibold shadow-md cursor-pointer border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-100 hover:scale-[var(--hover-scale)] transition">
                            Connections
                        </a>
                    @endif

                    @if(Route::has('user.linkedin.sync_jobs.index'))
                        <a href="{{ route('user.linkedin.sync_jobs.index') }}"
                           class="px-3 py-1.5 rounded-full font-semibold shadow-md cursor-pointer bg-slate-900 text-slate-50 border border-slate-700 hover:scale-[var(--hover-scale)] transition">
                            Sync Jobs
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
            {{-- Left: Performance --}}
            <div class="xl:col-span-2 space-y-6">
                {{-- Profile summary --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                        <div class="flex items-center gap-3">
                            @if(!empty($displayImg))
                                <img src="{{ $displayImg }}"
                                     alt="{{ $displayName }}"
                                     class="h-11 w-11 rounded-full object-cover border border-slate-200 dark:border-slate-700">
                            @else
                                <div class="h-11 w-11 rounded-full bg-gradient-to-br from-indigo-500 to-sky-500 flex items-center justify-center text-sm font-semibold text-white shadow">
                                    {{ strtoupper(substr($displayName, 0, 2)) }}
                                </div>
                            @endif

                            <div>
                                <div class="text-sm font-semibold text-slate-800 dark:text-slate-50">
                                    {{ $displayName }}
                                </div>
                                @if(!empty($profile['headline']) && Str::lower(trim((string)$profile['headline'])) !== 'unknown')
                                    <div class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ $profile['headline'] }}
                                    </div>
                                @endif
                                @if(!empty($profile['public_url']))
                                    <a href="{{ $profile['public_url'] }}" target="_blank"
                                       class="inline-flex items-center mt-1 text-[11px] text-slate-500 dark:text-slate-400 hover:text-indigo-500 cursor-pointer">
                                        {{ $displayName }} on LinkedIn
                                    </a>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button"
                                    id="btnSyncNow"
                                    class="px-3 py-1.5 rounded-full text-xs font-semibold shadow-md cursor-pointer
                                           bg-slate-900 text-slate-50 border border-slate-700
                                           transition transform duration-200 hover:scale-[var(--hover-scale)]">
                                Sync now
                            </button>

                            <button type="button"
                                    id="btnOpenExtension"
                                    class="px-3 py-1.5 rounded-full text-xs font-semibold shadow-md cursor-pointer
                                           border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900
                                           text-slate-700 dark:text-slate-100
                                           transition transform duration-200 hover:scale-[var(--hover-scale)]">
                                Open extension
                            </button>

                            @if(Route::has('user.linkedin.analytics.index'))
                                <a href="{{ route('user.linkedin.analytics.index') }}"
                                   class="px-3 py-1.5 rounded-full text-xs font-semibold shadow-md cursor-pointer
                                          border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900
                                          text-slate-700 dark:text-slate-100
                                          transition transform duration-200 hover:scale-[var(--hover-scale)]">
                                    Open analytics
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Metric cards --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-4 shadow">
                            <div class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-1">
                                Connections
                            </div>
                            <div class="flex items-end justify-between">
                                <div class="text-xl font-semibold text-slate-900 dark:text-slate-50">
                                    {{ number_format($profile['connections'] ?? 0) }}
                                </div>
                                <div class="text-[11px] {{ ($profile['connections_change'] ?? 0) >= 0 ? 'text-emerald-500' : 'text-rose-500' }}">
                                    {{ ($profile['connections_change'] ?? 0) >= 0 ? '+' : '' }}{{ $profile['connections_change'] ?? 0 }}
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-4 shadow">
                            <div class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-1">
                                Followers
                            </div>
                            <div class="flex items-end justify-between">
                                <div class="text-xl font-semibold text-slate-900 dark:text-slate-50">
                                    {{ number_format($profile['followers'] ?? 0) }}
                                </div>
                                <div class="text-[11px] {{ ($profile['followers_change'] ?? 0) >= 0 ? 'text-emerald-500' : 'text-rose-500' }}">
                                    {{ ($profile['followers_change'] ?? 0) >= 0 ? '+' : '' }}{{ $profile['followers_change'] ?? 0 }}
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-4 shadow">
                            <div class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-1">
                                Profile views (range)
                            </div>
                            <div class="text-xl font-semibold text-slate-900 dark:text-slate-50">
                                {{ number_format($profile['views_total'] ?? 0) }}
                            </div>
                        </div>

                        <div class="rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-4 shadow">
                            <div class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-1">
                                Search appearances (range)
                            </div>
                            <div class="text-xl font-semibold text-slate-900 dark:text-slate-50">
                                {{ number_format($profile['search_total'] ?? 0) }}
                            </div>
                        </div>
                    </div>

                    {{-- Charts --}}
                    <div class="mt-6">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-50">
                                Trends last 30 days
                            </h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                Based on profile metrics.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 p-4">
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-2">
                                    Connections vs followers
                                </p>
                                <div class="h-56">
                                    <canvas id="connectionsFollowersChart"></canvas>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 p-4">
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-2">
                                    Profile views vs search appearances
                                </p>
                                <div class="h-56">
                                    <canvas id="viewsSearchChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Recent posts performance --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-50">
                            Recent posts performance
                        </h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">
                            Latest synced content snapshot.
                        </p>
                    </div>

                    @if(empty($posts) || count($posts) === 0)
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            No posts synced yet. After you sync your posts, their performance will appear here.
                        </p>
                    @else
                        <div class="space-y-3 text-xs">
                            @foreach($posts as $post)
                                @php
                                    $postedAt = !empty($post['posted_at']) ? Carbon::parse($post['posted_at']) : null;
                                @endphp
                                <div class="rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-3 hover:shadow-lg transition transform hover:scale-[var(--hover-scale)] cursor-pointer">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <div class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                            {{ ucfirst($post['post_type'] ?? 'post') }}
                                        </div>
                                        <div class="text-[11px] text-slate-400 dark:text-slate-500">
                                            {{ $postedAt ? $postedAt->diffForHumans() : 'n/a' }}
                                        </div>
                                    </div>

                                    <div class="text-[13px] text-slate-800 dark:text-slate-100 line-clamp-2 mb-2">
                                        {{ $post['content'] ?? 'No content preview' }}
                                    </div>

                                    <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                                        <div class="flex items-center gap-3">
                                            <span>{{ number_format($post['impressions'] ?? 0) }} impressions</span>
                                            <span>{{ number_format($post['reactions'] ?? 0) }} reactions</span>
                                            <span>{{ number_format($post['comments'] ?? 0) }} comments</span>
                                        </div>
                                        @if(!empty($post['permalink']))
                                            <a href="{{ $post['permalink'] }}" target="_blank" class="underline cursor-pointer">
                                                View
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if(Route::has('user.linkedin.analytics.index'))
                            <div class="mt-4">
                                <a href="{{ route('user.linkedin.analytics.index') }}"
                                   class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold shadow-md cursor-pointer
                                          bg-slate-900 text-slate-50 border border-slate-700 hover:scale-[var(--hover-scale)] transition">
                                    View all posts and analytics
                                </a>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Right: Audience + Sync + Connections --}}
            <div class="space-y-6">
                {{-- Audience overview --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-50">
                            Audience overview
                        </h3>

                        <div class="flex items-center gap-2 text-[11px]">
                            @if(Route::has('user.linkedin.audience_insights.index'))
                                <a href="{{ route('user.linkedin.audience_insights.index') }}"
                                   class="px-2.5 py-1 rounded-full border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 cursor-pointer hover:scale-[var(--hover-scale)] transition">
                                    Details
                                </a>
                            @endif
                        </div>
                    </div>

                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">
                        Snapshot of who engages with your profile and content.
                    </p>

                    @if(!$hasAudienceData)
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Once you sync audience analytics, your top segments and creator metrics will appear here.
                        </p>
                    @else
                        <div class="space-y-2 text-[11px] text-slate-500 dark:text-slate-400">
                            @if($demoFollowersCount)
                                <div class="flex items-center justify-between">
                                    <span>Followers tracked</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-50">
                                        {{ number_format($demoFollowersCount) }}
                                    </span>
                                </div>
                            @endif

                            <div class="flex items-center justify-between">
                                <span>Latest snapshot</span>
                                <span class="font-medium text-slate-700 dark:text-slate-200">
                                    {{ $demoDate ?? $insightDate ?? $creatorDate ?? 'n/a' }}
                                </span>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Recent sync activity --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-50">
                            Recent sync activity
                        </h3>

                        @if(Route::has('user.linkedin.sync_jobs.index'))
                            <a href="{{ route('user.linkedin.sync_jobs.index') }}"
                               class="px-2.5 py-1 rounded-full text-[11px] border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 cursor-pointer hover:scale-[var(--hover-scale)] transition">
                                View all
                            </a>
                        @endif
                    </div>

                    @if($syncJobs->isEmpty())
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            No sync activity recorded yet.
                        </p>
                    @else
                        <div class="space-y-3 text-xs">
                            @foreach($syncJobs as $job)
                                @php
                                    $statusColor = match($job->status) {
                                        'success' => 'bg-emerald-500/10 text-emerald-500 border-emerald-500/30',
                                        'failed'  => 'bg-rose-500/10 text-rose-500 border-rose-500/30',
                                        'running' => 'bg-amber-500/10 text-amber-500 border-amber-500/30',
                                        default   => 'bg-slate-500/10 text-slate-500 border-slate-500/30',
                                    };
                                @endphp
                                <div class="rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-3">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-[11px] text-slate-500 dark:text-slate-400">
                                            {{ ucfirst($job->type) }} · {{ ucfirst($job->source) }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] border {{ $statusColor }}">
                                            {{ ucfirst($job->status) }}
                                        </span>
                                    </div>

                                    <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                                        <span>
                                            Started: {{ optional($job->started_at)->diffForHumans() ?? $job->created_at->diffForHumans() }}
                                        </span>
                                        @if($job->finished_at)
                                            <span>
                                                Finished: {{ $job->finished_at->diffForHumans() }}
                                            </span>
                                        @endif
                                    </div>

                                    @if($job->status === 'failed' && $job->error_message)
                                        <div class="mt-1 text-[11px] text-rose-400">
                                            {{ Str::limit($job->error_message, 90) }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-3">
                            {{ $syncJobs->links() }}
                        </div>
                    @endif
                </div>

                {{-- Latest connections (sample) --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-5">
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-50">
                                Latest connections
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Sample from your most recent connections sync.
                            </p>
                        </div>

                        @if(Route::has('user.linkedin.connections.index'))
                            <a href="{{ route('user.linkedin.connections.index') }}"
                               class="px-2.5 py-1 rounded-full text-[11px] border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 cursor-pointer hover:scale-[var(--hover-scale)] transition">
                                View all
                            </a>
                        @endif
                    </div>

                    @if(empty($connectionsSample))
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Once you sync connections, a quick list of recent connections will appear here.
                        </p>
                    @else
                        <div class="space-y-3 text-xs">
                            @foreach($connectionsSample as $connection)
                                @php
                                    $cnRaw = trim((string) ($connection['full_name'] ?? ''));
                                    $connName = ($cnRaw !== '' && Str::lower($cnRaw) !== 'unknown') ? $cnRaw : 'Connection';
                                @endphp
                                <div class="flex items-start justify-between rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-3">
                                    <div class="flex items-start gap-3">
                                        @if(!empty($connection['profile_image_url']))
                                            <img src="{{ $connection['profile_image_url'] }}"
                                                 alt="{{ $connName }}"
                                                 class="h-8 w-8 rounded-full object-cover border border-slate-200 dark:border-slate-700">
                                        @else
                                            <div class="h-8 w-8 rounded-full bg-gradient-to-br from-indigo-500 to-sky-500 flex items-center justify-center text-[10px] font-semibold text-white shadow">
                                                {{ strtoupper(substr($connName, 0, 2)) }}
                                            </div>
                                        @endif

                                        <div>
                                            <div class="text-[13px] font-semibold text-slate-800 dark:text-slate-50">
                                                {{ $connName }}
                                            </div>

                                            @if(!empty($connection['headline']) && Str::lower(trim((string)$connection['headline'])) !== 'unknown')
                                                <div class="text-[11px] text-slate-500 dark:text-slate-400">
                                                    {{ Str::limit($connection['headline'], 70) }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex flex-col items-end gap-1">
                                        @if(!empty($connection['profile_url']))
                                            <a href="{{ $connection['profile_url'] }}" target="_blank"
                                               class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 cursor-pointer hover:scale-[var(--hover-scale)] transition">
                                                View profile
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Smart recommendations placeholder --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-5">
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-50 mb-2">
                        Smart recommendations
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">
                        This block will later show AI-based posting frequency, best times and content recommendations powered by your recent metrics and audience data.
                    </p>
                    <ul class="text-xs text-slate-500 dark:text-slate-400 space-y-1 list-disc list-inside">
                        <li>Uses your last 30 days of profile and posts performance.</li>
                        <li>Audience segments and creator metrics will refine suggestions.</li>
                        <li>Suggestions will refresh after each new sync.</li>
                    </ul>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
@if(($summary['status'] ?? 'empty') === 'ok' || ($summary['status'] ?? 'empty') === 'empty')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const EXT_ID   = @json($extensionId);
    const EXT_URL  = @json($extensionUrl);
    const STORE_URL= @json($storeUrl);

    function promptInstall() {
        const ok = confirm('Chrome extension is not installed. Do you want to install it now?');
        if (ok && STORE_URL) window.open(STORE_URL, '_blank', 'noopener,noreferrer');
    }

    async function canOpenExtension() {
        if (!EXT_ID || !EXT_URL) return false;
        try {
            const res = await fetch(EXT_URL, { method: 'GET' });
            return !!res;
        } catch (e) {
            return false;
        }
    }

    async function openExtension(action) {
        if (!EXT_URL) return promptInstall();

        const ok = await canOpenExtension();
        if (!ok) return promptInstall();

        const url = action ? (EXT_URL + '?action=' + encodeURIComponent(action)) : EXT_URL;
        window.open(url, '_blank', 'noopener,noreferrer');
    }

    const btnConnect = document.getElementById('btnConnectLinkedin');
    if (btnConnect) btnConnect.addEventListener('click', function () { openExtension('connect'); });

    const btnSync = document.getElementById('btnSyncNow');
    if (btnSync) btnSync.addEventListener('click', function () { openExtension('sync'); });

    const btnOpen = document.getElementById('btnOpenExtension');
    if (btnOpen) btnOpen.addEventListener('click', function () { openExtension(''); });
});
</script>
@endif

@if(($summary['status'] ?? 'empty') === 'ok')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.Chart) return;

    const labels          = @json($times['dates'] ?? []);
    const connectionsData = @json($times['connections'] ?? []);
    const followersData   = @json($times['followers'] ?? []);
    const viewsData       = @json($times['profile_views'] ?? []);
    const searchData      = @json($times['search_appearances'] ?? []);

    if (!labels.length) return;

    function toNumArray(arr, len) {
        const out = Array.isArray(arr) ? arr.map(v => Number(v) || 0) : [];
        if (len && out.length !== len) {
            const filled = new Array(len).fill(0);
            for (let i = 0; i < Math.min(out.length, len); i++) filled[i] = out[i];
            return filled;
        }
        return out;
    }

    function bounds(arr, padPct = 0.12) {
        const a = arr.filter(v => Number.isFinite(v));
        if (!a.length) return { min: 0, max: 1 };

        let min = Math.min(...a);
        let max = Math.max(...a);

        if (min === max) {
            const pad = Math.max(1, Math.round(min * padPct));
            return { min: Math.max(0, min - pad), max: max + pad };
        }

        const range = max - min;
        const pad = range * padPct;

        return {
            min: Math.max(0, min - pad),
            max: max + pad
        };
    }

    const conn = toNumArray(connectionsData, labels.length);
    const foll = toNumArray(followersData, labels.length);
    const views = toNumArray(viewsData, labels.length);
    const search = toNumArray(searchData, labels.length);

    // Key fix: Do NOT use deltas. Use real numbers, but zoom the Y scale to show growth clearly.
    const bConn = bounds(conn, 0.15);
    const bFoll = bounds(foll, 0.15);
    const bViews = bounds(views, 0.20);
    const bSearch = bounds(search, 0.20);

    const rootStyle      = getComputedStyle(document.documentElement);
    const colorPrimary   = rootStyle.getPropertyValue('--color-primary').trim()   || '#4f46e5';
    const colorSecondary = rootStyle.getPropertyValue('--color-secondary').trim() || '#0ea5e9';
    const colorAccent    = rootStyle.getPropertyValue('--color-accent').trim()    || '#f97316';
    const textColor      = rootStyle.getPropertyValue('--color-text-secondary').trim() || '#6b7280';

    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { labels: { color: textColor, font: { size: 11 } } },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.dataset.label || '';
                        const value = Number(context.parsed.y ?? 0);
                        return label + ': ' + value.toLocaleString();
                    }
                }
            }
        },
        scales: {
            x: {
                ticks: { color: textColor, maxRotation: 0, autoSkip: true },
                grid: { display: false }
            },
            y: {
                ticks: { color: textColor },
                grid: { color: 'rgba(148,163,184,0.25)' },
                beginAtZero: false
            }
        }
    };

    const ctx1 = document.getElementById('connectionsFollowersChart');
    if (ctx1) {
        new Chart(ctx1.getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Connections',
                        data: conn,
                        borderColor: colorPrimary,
                        backgroundColor: 'rgba(79,70,229,0.12)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.45,
                        pointRadius: 2,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Followers',
                        data: foll,
                        borderColor: colorSecondary,
                        backgroundColor: 'rgba(14,165,233,0.10)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.45,
                        pointRadius: 2,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                ...commonOptions,
                scales: {
                    ...commonOptions.scales,
                    y: {
                        ...commonOptions.scales.y,
                        suggestedMin: bConn.min,
                        suggestedMax: bConn.max
                    },
                    y1: {
                        position: 'right',
                        ticks: { color: textColor },
                        grid: { drawOnChartArea: false },
                        beginAtZero: false,
                        suggestedMin: bFoll.min,
                        suggestedMax: bFoll.max
                    }
                }
            }
        });
    }

    const ctx2 = document.getElementById('viewsSearchChart');
    if (ctx2) {
        new Chart(ctx2.getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Profile views',
                        data: views,
                        borderColor: colorAccent,
                        backgroundColor: 'rgba(249,115,22,0.10)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.45,
                        pointRadius: 2,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Search appearances',
                        data: search,
                        borderColor: colorSecondary,
                        backgroundColor: 'rgba(14,165,233,0.10)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.45,
                        pointRadius: 2,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                ...commonOptions,
                scales: {
                    ...commonOptions.scales,
                    y: {
                        ...commonOptions.scales.y,
                        suggestedMin: bViews.min,
                        suggestedMax: bViews.max
                    },
                    y1: {
                        position: 'right',
                        ticks: { color: textColor },
                        grid: { drawOnChartArea: false },
                        beginAtZero: false,
                        suggestedMin: bSearch.min,
                        suggestedMax: bSearch.max
                    }
                }
            }
        });
    }
});
</script>
@endif
@endpush
