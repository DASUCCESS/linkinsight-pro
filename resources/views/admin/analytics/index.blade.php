@extends('admin.layout')

@section('page_title', 'LinkedIn analytics')
@section('page_subtitle', 'Platform-wide LinkedIn performance across all users.')

@section('content')
    @php
        $status = $summary['status'] ?? 'empty';
    @endphp

    @if($status === 'empty')
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6">
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-50 mb-2">
                No LinkedIn data yet
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
                Once users connect their LinkedIn profiles and sync data, platform-wide analytics will appear here.
            </p>
        </div>
    @else
        @php
            $global = $summary['global'] ?? [];
            $times  = $summary['timeseries'] ?? [];
            $posts  = $summary['post_timeseries'] ?? [];
            $filter = $summary['filter'] ?? [];
        @endphp

        <div class="mb-4 flex items-center justify-between gap-4">
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Date range:
                    <span class="font-medium text-slate-700 dark:text-slate-200">
                        {{ $filter['from'] ?? '' }} – {{ $filter['to'] ?? '' }}
                    </span>
                </p>
            </div>
            <form method="get" class="flex items-center gap-2 text-xs">
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
                    Apply
                </button>
            </form>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-6">
            <div class="rounded-2xl bg-white dark:bg-slate-900 shadow-xl border border-slate-200 dark:border-slate-800 p-4">
                <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-1">
                    Total users
                </p>
                <p class="text-xl font-semibold text-slate-900 dark:text-slate-50">
                    {{ number_format($global['profiles_count'] ?? 0) }}
                </p>
                <p class="text-[11px] text-slate-400 mt-1">
                    LinkedIn profiles across the platform.
                </p>
            </div>
            <div class="rounded-2xl bg-white dark:bg-slate-900 shadow-xl border border-slate-200 dark:border-slate-800 p-4">
                <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-1">
                    Total posts
                </p>
                <p class="text-xl font-semibold text-slate-900 dark:text-slate-50">
                    {{ number_format($global['total_posts'] ?? 0) }}
                </p>
                <p class="text-[11px] text-slate-400 mt-1">
                    All synced posts from all users.
                </p>
            </div>
            <div class="rounded-2xl bg-white dark:bg-slate-900 shadow-xl border border-slate-200 dark:border-slate-800 p-4">
                <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-1">
                    Profile views 30d
                </p>
                <p class="text-xl font-semibold text-slate-900 dark:text-slate-50">
                    {{ number_format($global['views_total'] ?? 0) }}
                </p>
                <p class="text-[11px] text-slate-400 mt-1">
                    Sum of profile views in the date window.
                </p>
            </div>
            <div class="rounded-2xl bg-white dark:bg-slate-900 shadow-xl border border-slate-200 dark:border-slate-800 p-4">
                <p class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-1">
                    Impressions 30d
                </p>
                <p class="text-xl font-semibold text-slate-900 dark:text-slate-50">
                    {{ number_format($global['impressions_30d'] ?? 0) }}
                </p>
                <p class="text-[11px] text-slate-400 mt-1">
                    Combined post impressions in the date window.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-50">
                        Audience size and discovery
                    </h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                        Connections, followers, profile views, search appearances.
                    </p>
                </div>
                <div class="h-64">
                    <canvas id="adminConnectionsViewsChart"></canvas>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-50">
                        Post impressions and engagements
                    </h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                        Sum across all synced posts.
                    </p>
                </div>
                <div class="h-64">
                    <canvas id="adminPostPerformanceChart"></canvas>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
@if(($summary['status'] ?? 'empty') === 'ok')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (!window.Chart) return;

            const rootStyle = getComputedStyle(document.documentElement);
            const colorPrimary   = rootStyle.getPropertyValue('--color-primary').trim() || '#4f46e5';
            const colorSecondary = rootStyle.getPropertyValue('--color-secondary').trim() || '#0ea5e9';
            const colorAccent    = rootStyle.getPropertyValue('--color-accent').trim() || '#f97316';
            const textColor      = rootStyle.getPropertyValue('--color-text-secondary').trim() || '#6b7280';

            const labels      = @json($summary['timeseries']['dates'] ?? []);
            const connections = @json($summary['timeseries']['connections'] ?? []);
            const followers   = @json($summary['timeseries']['followers'] ?? []);
            const views       = @json($summary['timeseries']['profile_views'] ?? []);
            const search      = @json($summary['timeseries']['search_appearances'] ?? []);

            const postLabels       = @json($summary['post_timeseries']['dates'] ?? []);
            const postImpressions  = @json($summary['post_timeseries']['impressions'] ?? []);
            const postEngagements  = @json($summary['post_timeseries']['engagements'] ?? []);

            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        labels: {
                            color: textColor,
                            font: { size: 11 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                const label = context.dataset.label || '';
                                const value = context.parsed.y ?? 0;
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
                        beginAtZero: true
                    }
                }
            };

            const ctx1 = document.getElementById('adminConnectionsViewsChart');
            if (ctx1 && labels.length) {
                new Chart(ctx1.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Connections',
                                data: connections,
                                borderColor: colorPrimary,
                                backgroundColor: colorPrimary + '33',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.3,
                                pointRadius: 0
                            },
                            {
                                label: 'Followers',
                                data: followers,
                                borderColor: colorSecondary,
                                backgroundColor: colorSecondary + '33',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.3,
                                pointRadius: 0
                            },
                            {
                                label: 'Profile views',
                                data: views,
                                borderColor: colorAccent,
                                backgroundColor: colorAccent + '26',
                                borderWidth: 1.5,
                                fill: false,
                                tension: 0.3,
                                pointRadius: 0
                            },
                            {
                                label: 'Search appearances',
                                data: search,
                                borderColor: '#0f766e',
                                backgroundColor: '#0f766e33',
                                borderWidth: 1.5,
                                fill: false,
                                tension: 0.3,
                                pointRadius: 0
                            }
                        ]
                    },
                    options: commonOptions
                });
            }

            const ctx2 = document.getElementById('adminPostPerformanceChart');
            if (ctx2 && postLabels.length) {
                new Chart(ctx2.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: postLabels,
                        datasets: [
                            {
                                label: 'Impressions',
                                data: postImpressions,
                                borderColor: colorPrimary,
                                backgroundColor: colorPrimary + '33',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.3,
                                pointRadius: 0
                            },
                            {
                                label: 'Engagements',
                                data: postEngagements,
                                borderColor: colorAccent,
                                backgroundColor: colorAccent + '33',
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
@endif
@endpush
