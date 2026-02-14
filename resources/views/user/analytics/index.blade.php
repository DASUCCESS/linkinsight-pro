@extends('user.layout')

@section('page_title', 'Analytics')
@section('page_subtitle', 'Detailed view of your LinkedIn profile metrics and posts.')

@section('content')
    @php
        $status = $summary['status'] ?? 'empty';

        $profile   = $summary['profile'] ?? [];
        $times     = $summary['timeseries'] ?? [];
        $postsMeta = $summary['posts_overview'] ?? [];
        $filter    = $summary['filter'] ?? [];

        $creatorAudience = $summary['creator_audience'] ?? null;
        $audMetricDate   = data_get($creatorAudience, 'snapshot_date');
        $audMetrics      = data_get($creatorAudience, 'metrics', []);

        $demographics    = $summary['audience_demographics'] ?? [];
        $demoDate        = $demographics['snapshot_date'] ?? null;
        $followersCount  = $demographics['followers_count'] ?? null;
        $demoCats        = $demographics['demographics'] ?? [];

        $connectionsSample = $summary['connections_sample'] ?? [];
        $connectionsList   = is_array($connectionsSample) ? $connectionsSample : [];
        $connectionsCount  = is_array($connectionsList) ? count($connectionsList) : 0;
    @endphp

    @if($status === 'empty')
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6">
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-50 mb-2">
                No LinkedIn profile connected
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-2">
                Once you connect and sync your profile, detailed analytics will appear here.
            </p>
        </div>
    @else
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                <div class="flex items-center gap-3">
                    @if(!empty($profile['profile_image_url']))
                        <img src="{{ $profile['profile_image_url'] }}"
                             alt="{{ $profile['name'] ?? 'LinkedIn' }}"
                             class="h-11 w-11 rounded-full object-cover border border-slate-200 dark:border-slate-700">
                    @else
                        <div class="h-11 w-11 rounded-full bg-gradient-to-br from-indigo-500 to-sky-500 flex items-center justify-center text-sm font-semibold text-white shadow">
                            {{ strtoupper(substr($profile['name'] ?? 'LI', 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <div class="text-sm font-semibold text-slate-800 dark:text-slate-50">
                            {{ $profile['name'] ?? 'LinkedIn profile' }}
                        </div>
                        @if(!empty($profile['headline']))
                            <div class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $profile['headline'] }}
                            </div>
                        @endif
                        @if(!empty($profile['public_url']))
                            <a href="{{ $profile['public_url'] }}" target="_blank"
                               class="inline-flex items-center mt-1 text-[11px] text-slate-500 dark:text-slate-400 hover:text-indigo-500 cursor-pointer">
                                View on LinkedIn
                            </a>
                        @endif
                    </div>
                </div>

                <form method="get" class="flex flex-wrap items-center gap-2 text-xs">
                    <input type="hidden" name="profile_id" value="{{ request('profile_id') }}">
                    <input type="date"
                           name="from"
                           value="{{ request('from', $filter['from'] ?? '') }}"
                           class="border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 bg-white dark:bg-slate-900 text-xs text-slate-700 dark:text-slate-200">
                    <input type="date"
                           name="to"
                           value="{{ request('to', $filter['to'] ?? '') }}"
                           class="border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 bg-white dark:bg-slate-900 text-xs text-slate-700 dark:text-slate-200">
                    <button type="submit"
                            class="px-3 py-1.5 rounded-full text-xs font-semibold shadow-md cursor-pointer bg-slate-900 text-slate-50 border border-slate-700 hover:scale-[var(--hover-scale)] transition">
                        Apply range
                    </button>
                </form>
            </div>

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

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
                <div class="rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-4 shadow">
                    <div class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-1">
                        Total posts
                    </div>
                    <div class="text-xl font-semibold text-slate-900 dark:text-slate-50">
                        {{ number_format($postsMeta['total_posts'] ?? 0) }}
                    </div>
                </div>
                <div class="rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-4 shadow">
                    <div class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-1">
                        Impressions (range)
                    </div>
                    <div class="text-xl font-semibold text-slate-900 dark:text-slate-50">
                        {{ number_format($postsMeta['impressions_30d'] ?? 0) }}
                    </div>
                </div>
                <div class="rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-4 shadow">
                    <div class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-1">
                        Engagements (range)
                    </div>
                    <div class="text-xl font-semibold text-slate-900 dark:text-slate-50">
                        {{ number_format($postsMeta['engagements_30d'] ?? 0) }}
                    </div>
                </div>
                <div class="rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-4 shadow">
                    <div class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-1">
                        Avg engagement rate
                    </div>
                    <div class="text-xl font-semibold text-slate-900 dark:text-slate-50">
                        {{ number_format($postsMeta['avg_engagement_rate_30d'] ?? 0, 2) }}%
                    </div>
                </div>
            </div>
        </div>

        {{-- Creator audience analytics --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-50">
                        Creator audience analytics
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Latest audience metrics captured from LinkedIn.
                    </p>
                </div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400">
                    {{ $audMetricDate ? 'Snapshot date: ' . $audMetricDate : 'No snapshot date' }}
                </div>
            </div>

            @if(empty($audMetrics))
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    No audience metrics available yet. Sync Creator audience analytics to populate this section.
                </p>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                    @foreach($audMetrics as $metric)
                        @php
                            $v = $metric['value'] ?? null;
                        @endphp
                        <div class="rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-4 shadow">
                            <div class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-1">
                                {{ $metric['label'] ?? 'Metric' }}
                            </div>
                            <div class="text-xl font-semibold text-slate-900 dark:text-slate-50">
                                @if(is_numeric($v))
                                    {{ number_format((float) $v, 0) }}
                                @elseif(is_array($v) || is_object($v))
                                    {{ \Illuminate\Support\Str::limit(json_encode($v), 40) }}
                                @else
                                    {{ \Illuminate\Support\Str::limit((string) $v, 40) }}
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Followers demographics --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-50">
                        Followers demographics
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Breakdown of your followers by category.
                    </p>
                </div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400">
                    {{ $demoDate ? 'Snapshot date: ' . $demoDate : 'No snapshot date' }}
                </div>
            </div>

            @if(empty($demoCats))
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    No demographics available yet. Sync Followers demographics to populate this section.
                </p>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    @foreach($demoCats as $cat => $items)
                        @php
                            $title = ucwords(str_replace('_', ' ', (string) $cat));
                            $items = is_array($items) ? $items : [];
                        @endphp
                        <div class="rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-4">
                            <div class="flex items-center justify-between mb-2">
                                <div class="text-sm font-semibold text-slate-800 dark:text-slate-50">
                                    {{ $title }}
                                </div>
                                @if(!empty($followersCount))
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400">
                                        Followers: {{ number_format($followersCount) }}
                                    </div>
                                @endif
                            </div>

                            <div class="space-y-1 text-xs">
                                @foreach(array_slice($items, 0, 10) as $it)
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-700 dark:text-slate-200 line-clamp-1">
                                            {{ $it['label'] ?? 'Unknown' }}
                                        </span>
                                        <span class="text-slate-500 dark:text-slate-400">
                                            {{ number_format((float) ($it['percent'] ?? 0), 1) }}%
                                        </span>
                                    </div>
                                @endforeach
                            </div>

                            @if(count($items) > 10)
                                <p class="mt-2 text-[11px] text-slate-500 dark:text-slate-400">
                                    Showing top 10 items.
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Connections sample --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-50">
                        Connections
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Synced connections directory (sample).
                    </p>
                </div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400">
                    Total in sample: {{ number_format($connectionsCount ?? 0) }}
                </div>
            </div>

            @if(empty($connectionsList))
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    No connections available yet. Sync Connections to populate this section.
                </p>
            @else
                <div class="overflow-x-auto text-xs">
                    <table class="min-w-full">
                        <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="px-3 py-2 text-left font-medium">Name</th>
                            <th class="px-3 py-2 text-left font-medium">Headline</th>
                            <th class="px-3 py-2 text-left font-medium">Location</th>
                            <th class="px-3 py-2 text-right font-medium">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @foreach(array_slice($connectionsList, 0, 50) as $c)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/70 transition">
                                <td class="px-3 py-2 text-slate-800 dark:text-slate-50">
                                    {{ $c['full_name'] ?? $c['name'] ?? 'Unknown' }}
                                </td>
                                <td class="px-3 py-2 text-slate-600 dark:text-slate-300">
                                    {{ \Illuminate\Support\Str::limit($c['headline'] ?? '', 60) }}
                                </td>
                                <td class="px-3 py-2 text-slate-600 dark:text-slate-300">
                                    {{ $c['location'] ?? 'N/A' }}
                                </td>
                                <td class="px-3 py-2 text-right">
                                    @if(!empty($c['profile_url']))
                                        <a href="{{ $c['profile_url'] }}" target="_blank"
                                           class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 cursor-pointer hover:scale-[var(--hover-scale)] transition">
                                            View
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                @if(count($connectionsList) > 50)
                    <p class="mt-3 text-[11px] text-slate-500 dark:text-slate-400">
                        Showing first 50 connections.
                    </p>
                @endif
            @endif
        </div>

        {{-- Posts performance list --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                <div>
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-50">
                        Posts performance
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Search and filter all posts synced so far.
                    </p>
                </div>
                <form method="get" class="flex flex-wrap items-center gap-2 text-xs">
                    <input type="hidden" name="profile_id" value="{{ request('profile_id') }}">
                    <input type="hidden" name="from" value="{{ request('from', $filter['from'] ?? '') }}">
                    <input type="hidden" name="to" value="{{ request('to', $filter['to'] ?? '') }}">
                    <input type="text"
                           name="q"
                           value="{{ request('q') }}"
                           placeholder="Search content or URL..."
                           class="border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 bg-white dark:bg-slate-900 text-xs text-slate-700 dark:text-slate-200">
                    <select name="type"
                            class="border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 bg-white dark:bg-slate-900 text-xs text-slate-700 dark:text-slate-200">
                        <option value="">All types</option>
                        <option value="post" @selected(request('type') === 'post')>Post</option>
                        <option value="article" @selected(request('type') === 'article')>Article</option>
                        <option value="repost" @selected(request('type') === 'repost')>Repost</option>
                    </select>
                    <button type="submit"
                            class="px-3 py-1.5 rounded-full text-xs font-semibold shadow-md cursor-pointer bg-slate-900 text-slate-50 border border-slate-700 hover:scale-[var(--hover-scale)] transition">
                        Filter
                    </button>
                </form>
            </div>

            @if(!$postsPaginated || $postsPaginated->isEmpty())
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    No posts found for the selected filters.
                </p>
            @else
                <div class="overflow-x-auto text-xs">
                    <table class="min-w-full">
                        <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="px-3 py-2 text-left font-medium">Post</th>
                            <th class="px-3 py-2 text-left font-medium">Type</th>
                            <th class="px-3 py-2 text-left font-medium">Published</th>
                            <th class="px-3 py-2 text-right font-medium">Impressions</th>
                            <th class="px-3 py-2 text-right font-medium">Reactions</th>
                            <th class="px-3 py-2 text-right font-medium">Comments</th>
                            <th class="px-3 py-2 text-right font-medium">Reposts</th>
                            <th class="px-3 py-2 text-right font-medium">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @foreach($postsPaginated as $post)
                            @php
                                $latestMetric = $post->latestMetric ?? $post->metrics?->sortByDesc('metric_date')->first();
                            @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/70 transition">
                                <td class="px-3 py-2 max-w-xs">
                                    <div class="flex flex-col">
                                        <span class="text-slate-800 dark:text-slate-50 line-clamp-2">
                                            {{ $post->content_excerpt ?: 'No content preview' }}
                                        </span>
                                        @if($post->permalink)
                                            <a href="{{ $post->permalink }}" target="_blank"
                                               class="text-[11px] text-indigo-500 hover:text-indigo-400 cursor-pointer mt-1">
                                                Open on LinkedIn
                                            </a>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-slate-600 dark:text-slate-300">
                                    {{ ucfirst($post->post_type ?? 'post') }}
                                </td>
                                <td class="px-3 py-2 text-slate-600 dark:text-slate-300">
                                    {{ optional($post->posted_at)->format('Y-m-d H:i') ?? 'N/A' }}
                                </td>
                                <td class="px-3 py-2 text-right text-slate-700 dark:text-slate-200">
                                    {{ number_format($latestMetric->impressions ?? 0) }}
                                </td>
                                <td class="px-3 py-2 text-right text-slate-700 dark:text-slate-200">
                                    {{ number_format($latestMetric->reactions ?? 0) }}
                                </td>
                                <td class="px-3 py-2 text-right text-slate-700 dark:text-slate-200">
                                    {{ number_format($latestMetric->comments ?? 0) }}
                                </td>
                                <td class="px-3 py-2 text-right text-slate-700 dark:text-slate-200">
                                    {{ number_format($latestMetric->reposts ?? 0) }}
                                </td>
                                <td class="px-3 py-2 text-right">
                                    @if($post->permalink)
                                        <a href="{{ $post->permalink }}" target="_blank"
                                           class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 cursor-pointer hover:scale-[var(--hover-scale)] transition">
                                            View
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $postsPaginated->links() }}
                </div>
            @endif
        </div>
    @endif
@endsection
