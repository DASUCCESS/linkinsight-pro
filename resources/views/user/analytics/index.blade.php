@extends('user.layout')

@section('page_title', 'Analytics')
@section('page_subtitle', 'Detailed view of your LinkedIn profile metrics and posts.')

@section('content')
    @php
        $status = $summary['status'] ?? 'empty';
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
        @php
            $profile   = $summary['profile'] ?? [];
            $times     = $summary['timeseries'] ?? [];
            $postsMeta = $summary['posts_overview'] ?? [];
            $filter    = $summary['filter'] ?? [];
        @endphp

        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                <div class="flex items-center gap-3">
                    @if(!empty($profile['profile_image_url']))
                        <img src="{{ $profile['profile_image_url'] }}"
                             alt="{{ $profile['name'] }}"
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
