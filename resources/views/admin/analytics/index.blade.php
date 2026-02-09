@extends('admin.layout')

@section('page_title', 'LinkedIn analytics')
@section('page_subtitle', 'Overview of your LinkedIn profile performance.')

@section('content')
    @php
        $status = $summary['status'] ?? 'empty';
    @endphp

    @if($status === 'empty')
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6">
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-50 mb-2">
                No LinkedIn profile connected
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
                Connect a LinkedIn profile using the browser extension or API to start seeing analytics here.
            </p>
        </div>
    @else
        @php
            $profile  = $summary['profile'] ?? [];
            $times    = $summary['timeseries'] ?? [];
            $posts    = $summary['recent_posts'] ?? [];
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            @if(!empty($profile['profile_image_url']))
                                <img src="{{ $profile['profile_image_url'] }}"
                                     alt="{{ $profile['name'] }}"
                                     class="h-10 w-10 rounded-full object-cover border border-slate-200 dark:border-slate-700">
                            @else
                                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-500 to-sky-500 flex items-center justify-center text-xs font-semibold text-white shadow">
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
                            </div>
                        </div>
                        @if(!empty($profile['public_url']))
                            <a href="{{ $profile['public_url'] }}"
                               target="_blank"
                               class="inline-flex items-center px-3 py-1.5 rounded-full text-[11px] font-semibold cursor-pointer border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-100 shadow-sm transition transform hover:scale-[var(--hover-scale)]">
                                View on LinkedIn
                            </a>
                        @endif
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
                                Profile views 30d
                            </div>
                            <div class="text-xl font-semibold text-slate-900 dark:text-slate-50">
                                {{ number_format($profile['views_total'] ?? 0) }}
                            </div>
                        </div>

                        <div class="rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-4 shadow">
                            <div class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-1">
                                Search appearances 30d
                            </div>
                            <div class="text-xl font-semibold text-slate-900 dark:text-slate-50">
                                {{ number_format($profile['search_total'] ?? 0) }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-50">
                                Trends last 30 days
                            </h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                Charts placeholder. We will plug a chart library here.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div class="rounded-2xl border border-dashed border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-900/40 p-4 flex items-center justify-center text-xs text-slate-500 dark:text-slate-400">
                                Connections and followers chart
                            </div>
                            <div class="rounded-2xl border border-dashed border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-900/40 p-4 flex items-center justify-center text-xs text-slate-500 dark:text-slate-400">
                                Profile views and search appearances chart
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6 h-full flex flex-col">
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-50 mb-3">
                        Recent posts
                    </h3>

                    @if(empty($posts) || count($posts) === 0)
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            No posts synced yet. When you sync your content from LinkedIn, top posts will appear here.
                        </p>
                    @else
                        <div class="space-y-3 text-xs">
                            @foreach($posts as $post)
                                <div class="rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-3 hover:shadow-lg transition transform hover:scale-[var(--hover-scale)] cursor-pointer">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <div class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                            {{ ucfirst($post['post_type'] ?? 'post') }}
                                        </div>
                                        <div class="text-[11px] text-slate-400 dark:text-slate-500">
                                            {{ \Illuminate\Support\Carbon::parse($post['posted_at'])->diffForHumans() }}
                                        </div>
                                    </div>
                                    @if(!empty($post['content']))
                                        <div class="text-[13px] text-slate-800 dark:text-slate-100 line-clamp-2 mb-2">
                                            {{ $post['content'] }}
                                        </div>
                                    @endif
                                    <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                                        <div class="flex items-center gap-3">
                                            <span>{{ number_format($post['impressions'] ?? 0) }} impressions</span>
                                            <span>{{ number_format($post['reactions'] ?? 0) }} reactions</span>
                                            <span>{{ number_format($post['comments'] ?? 0) }} comments</span>
                                        </div>
                                        @if(!empty($post['permalink']))
                                            <a href="{{ $post['permalink'] }}" target="_blank" class="underline">
                                                View
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
@endsection
