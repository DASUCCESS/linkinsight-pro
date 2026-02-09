@extends('user.layout')

@section('page_title', 'Dashboard')
@section('page_subtitle', 'Quick overview of your LinkedIn performance and sync activity.')

@section('content')
    @php
        $status = $summary['status'] ?? 'empty';
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
                        class="px-4 py-2 rounded-full text-sm font-semibold shadow-xl cursor-pointer
                               bg-gradient-to-r from-indigo-500 to-sky-500 text-white
                               transition transform duration-200 hover:scale-[var(--hover-scale)]">
                    Connect LinkedIn
                </button>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    This button will later open the extension / connection flow.
                </p>
            </div>
        </div>
    @else
        @php
            $profile  = $summary['profile'] ?? [];
            $times    = $summary['timeseries'] ?? [];
            $posts    = $summary['recent_posts'] ?? [];
        @endphp

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
            {{-- Left: profile + KPIs + charts placeholder --}}
            <div class="xl:col-span-2 space-y-6">
                {{-- Profile overview and KPIs --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6">
                    <div class="flex items-center justify-between mb-4">
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
                        <button type="button"
                                class="px-3 py-1.5 rounded-full text-xs font-semibold shadow-md cursor-pointer
                                       bg-slate-900 text-slate-50 border border-slate-700
                                       transition transform duration-200 hover:scale-[var(--hover-scale)]">
                            Sync now
                        </button>
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
                                Chart area. We will connect Chart.js here.
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

                {{-- Recent posts --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-50">
                            Recent posts performance
                        </h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">
                            Based on your latest synced content.
                        </p>
                    </div>

                    @if(empty($posts) || count($posts) === 0)
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            No posts synced yet. After you sync your posts, their performance will appear here.
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

            {{-- Right: recent sync jobs --}}
            <div class="space-y-6">
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6">
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-50 mb-3">
                        Recent sync activity
                    </h3>

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
                                        <div class="flex items-center gap-2">
                                            <span class="text-[11px] text-slate-500 dark:text-slate-400">
                                                {{ ucfirst($job->type) }} · {{ ucfirst($job->source) }}
                                            </span>
                                        </div>
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
                                            {{ \Illuminate\Support\Str::limit($job->error_message, 90) }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-5">
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-50 mb-2">
                        Smart recommendations
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">
                        This block will later show AI-based posting frequency, best times, and content recommendations.
                    </p>
                    <ul class="text-xs text-slate-500 dark:text-slate-400 space-y-1 list-disc list-inside">
                        <li>We will use your last 30 days of metrics.</li>
                        <li>Suggestions will be refreshed after each sync.</li>
                        <li>This prepares the ground for the “AI Insights & Advanced Analytics” addon.</li>
                    </ul>
                </div>
            </div>
        </div>
    @endif
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.Chart) {
        return;
    }

    const labels = @json($times['dates'] ?? []);
    const connectionsData = @json($times['connections'] ?? []);
    const followersData = @json($times['followers'] ?? []);
    const viewsData = @json($times['profile_views'] ?? []);
    const searchData = @json($times['search_appearances'] ?? []);

    if (!labels.length) {
        return;
    }

    const rootStyle = getComputedStyle(document.documentElement);
    const colorPrimary = rootStyle.getPropertyValue('--color-primary').trim() || '#4f46e5';
    const colorSecondary = rootStyle.getPropertyValue('--color-secondary').trim() || '#0ea5e9';
    const colorAccent = rootStyle.getPropertyValue('--color-accent').trim() || '#f97316';

    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: 'index',
            intersect: false
        },
        plugins: {
            legend: {
                labels: {
                    color: rootStyle.getPropertyValue('--color-text-secondary').trim() || '#6b7280',
                    font: { size: 11 }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.dataset.label || '';
                        const value = context.parsed.y ?? 0;
                        return label + ': ' + value.toLocaleString();
                    }
                }
            }
        },
        scales: {
            x: {
                ticks: {
                    color: rootStyle.getPropertyValue('--color-text-secondary').trim() || '#6b7280',
                    maxRotation: 0,
                    autoSkip: true
                },
                grid: {
                    display: false
                }
            },
            y: {
                ticks: {
                    color: rootStyle.getPropertyValue('--color-text-secondary').trim() || '#6b7280'
                },
                grid: {
                    color: 'rgba(148, 163, 184, 0.25)'
                },
                beginAtZero: true
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
                        data: connectionsData,
                        borderColor: colorPrimary,
                        backgroundColor: colorPrimary + '33',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3,
                        pointRadius: 0
                    },
                    {
                        label: 'Followers',
                        data: followersData,
                        borderColor: colorSecondary,
                        backgroundColor: colorSecondary + '33',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3,
                        pointRadius: 0
                    }
                ]
            },
            options: commonOptions
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
                        data: viewsData,
                        borderColor: colorAccent,
                        backgroundColor: colorAccent + '33',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3,
                        pointRadius: 0
                    },
                    {
                        label: 'Search appearances',
                        data: searchData,
                        borderColor: colorSecondary,
                        backgroundColor: colorSecondary + '33',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3,
                        pointRadius: 0
                    }
                ]
            },
            options: commonOptions
        });
    }
});
</script>
@endpush
