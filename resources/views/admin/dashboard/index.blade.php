@extends('admin.layout')

@section('page_title', 'Dashboard')
@section('page_subtitle', 'High-level overview of usage and metrics.')

@section('content')
    {{-- Top stat cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        {{-- Today\'s Money --}}
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-4 flex items-center justify-between cursor-pointer transition transform hover:scale-[var(--hover-scale)]">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Today&apos;s Money</p>
                <p class="mt-2 text-xl font-semibold text-slate-800">
                    ${{ number_format($stats['today_money']) }}
                </p>
                <p class="mt-1 text-[11px] text-emerald-500 font-medium">
                    +55% <span class="text-slate-400">than last week</span>
                </p>
            </div>
            <div class="h-11 w-11 rounded-2xl flex items-center justify-center bg-gradient-to-br from-amber-400 to-amber-500 text-white shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .79-3 2s1.343 2 3 2 3 .79 3 2-1.343 2-3 2m0-8V6m0 10v2" />
                </svg>
            </div>
        </div>

        {{-- Today\'s Users --}}
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-4 flex items-center justify-between cursor-pointer transition transform hover:scale-[var(--hover-scale)]">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Today&apos;s Users</p>
                <p class="mt-2 text-xl font-semibold text-slate-800">
                    {{ number_format($stats['today_users']) }}
                </p>
                <p class="mt-1 text-[11px] text-emerald-500 font-medium">
                    +3% <span class="text-slate-400">than last month</span>
                </p>
            </div>
            <div class="h-11 w-11 rounded-2xl flex items-center justify-center bg-gradient-to-br from-sky-500 to-sky-600 text-white shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20v-2a3 3 0 00-3-3H6.5A3.5 3.5 0 003 18.5V20" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 11a4 4 0 10-8 0 4 4 0 008 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 8v6" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M22 11h-6" />
                </svg>
            </div>
        </div>

        {{-- New Clients --}}
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-4 flex items-center justify-between cursor-pointer transition transform hover:scale-[var(--hover-scale)]">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">New Clients</p>
                <p class="mt-2 text-xl font-semibold text-slate-800">
                    {{ number_format($stats['new_clients']) }}
                </p>
                <p class="mt-1 text-[11px] text-emerald-500 font-medium">
                    +13% <span class="text-slate-400">than yesterday</span>
                </p>
            </div>
            <div class="h-11 w-11 rounded-2xl flex items-center justify-center bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2" />
                    <circle cx="9" cy="9" r="3" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 8v6" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M22 11h-6" />
                </svg>
            </div>
        </div>

        {{-- Sales --}}
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-4 flex items-center justify-between cursor-pointer transition transform hover:scale-[var(--hover-scale)]">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Sales</p>
                <p class="mt-2 text-xl font-semibold text-slate-800">
                    ${{ number_format($stats['sales']) }}
                </p>
                <p class="mt-1 text-[11px] text-emerald-500 font-medium">
                    +5% <span class="text-slate-400">than yesterday</span>
                </p>
            </div>
            <div class="h-11 w-11 rounded-2xl flex items-center justify-center bg-gradient-to-br from-indigo-500 to-indigo-600 text-white shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 14l3.5-3.5L14 14l5-5" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9h-4V5" />
                </svg>
            </div>
        </div>
    </div>

    {{-- Middle charts row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        {{-- Website Views (bar) --}}
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-4 flex flex-col">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Website Views</p>
                    <p class="text-xs text-slate-400 mt-0.5">Last campaign performance</p>
                </div>
                <span class="px-2 py-1 rounded-full text-[10px] bg-amber-500 text-white font-semibold shadow">
                    +5%
                </span>
            </div>
            <div class="flex-1 flex items-end gap-2 mt-2">
                @foreach([35,55,40,65,45,70,52] as $height)
                    <div class="flex-1 flex flex-col justify-end">
                        <div class="w-full rounded-xl bg-amber-100 overflow-hidden">
                            <div class="w-full rounded-xl bg-amber-400" style="height: {{ $height }}%;"></div>
                        </div>
                    </div>
                @endforeach
            </div>
            <p class="mt-3 text-[11px] text-slate-400">Campaign ended 2 days ago.</p>
        </div>

        {{-- Daily Sales (line) --}}
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-4 flex flex-col">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Daily Sales</p>
                    <p class="text-xs text-slate-400 mt-0.5">LinkedIn impressions in the last 12 months</p>
                </div>
                <span class="px-2 py-1 rounded-full text-[10px] bg-emerald-500 text-white font-semibold shadow">
                    +15%
                </span>
            </div>
            <div class="flex-1 mt-2">
                <div class="relative h-40">
                    <div class="absolute inset-0 border border-dashed border-slate-200 rounded-xl"></div>
                    <svg viewBox="0 0 100 40" class="absolute inset-2 w-[calc(100%-16px)] h-[calc(100%-16px)]">
                        <polyline
                            fill="none"
                            stroke="#22c55e"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            points="0,30 10,26 20,24 30,20 40,18 50,16 60,21 70,17 80,14 90,12 100,8" />
                        <circle cx="100" cy="8" r="2.5" fill="#22c55e" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 text-[11px] text-slate-400">Updated 4 minutes ago.</p>
        </div>

        {{-- Completed Tasks (dark card) --}}
        <div class="bg-slate-900 rounded-2xl shadow-xl border border-slate-800 p-4 flex flex-col text-slate-100">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="text-xs font-semibold text-slate-300 uppercase">Completed Tasks</p>
                    <p class="text-xs text-slate-400 mt-0.5">Campaign performance</p>
                </div>
                <span class="px-2 py-1 rounded-full text-[10px] bg-emerald-500 text-white font-semibold shadow">
                    +12%
                </span>
            </div>
            <div class="flex-1 mt-2">
                <div class="relative h-40">
                    <div class="absolute inset-0 border border-slate-700 rounded-xl"></div>
                    <svg viewBox="0 0 100 40" class="absolute inset-2 w-[calc(100%-16px)] h-[calc(100%-16px)]">
                        <polyline
                            fill="none"
                            stroke="#38bdf8"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            points="0,10 10,12 20,15 30,14 40,18 50,20 60,24 70,26 80,25 90,28 100,30" />
                        <circle cx="100" cy="30" r="2.5" fill="#38bdf8" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 text-[11px] text-slate-400">Just updated.</p>
        </div>
    </div>

    {{-- Bottom row: Projects and Orders --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        {{-- Projects --}}
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-4">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Projects</p>
                    <p class="text-xs text-slate-400 mt-0.5">30 done this month.</p>
                </div>
            </div>

            <div class="space-y-3 text-xs">
                @foreach([
                    ['title' => 'Material Kit Dashboard', 'budget' => '$14,000', 'progress' => 60],
                    ['title' => 'LinkedIn Profile Scanner', 'budget' => '$3,500', 'progress' => 80],
                    ['title' => 'Content Analytics Engine', 'budget' => '$22,000', 'progress' => 35],
                    ['title' => 'Audience Insights Module', 'budget' => '$7,800', 'progress' => 90],
                ] as $project)
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex-1">
                            <p class="font-medium text-slate-700">{{ $project['title'] }}</p>
                            <p class="text-[11px] text-slate-400">{{ $project['budget'] }} budget</p>
                        </div>
                        <div class="w-40">
                            <div class="w-full h-1.5 bg-slate-200 rounded-full overflow-hidden">
                                <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-sky-500" style="width: {{ $project['progress'] }}%;"></div>
                            </div>
                            <p class="mt-1 text-[11px] text-slate-500 text-right">{{ $project['progress'] }}%</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Orders overview / Activity --}}
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-4">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Activity Overview</p>
                    <p class="text-xs text-slate-400 mt-0.5">Recent system events</p>
                </div>
                <span class="text-[11px] text-slate-400">24 entries this month</span>
            </div>

            <div class="space-y-3 text-xs">
                @foreach([
                    ['color' => 'emerald', 'label' => 'Design changes deployed', 'time' => '2 hours ago'],
                    ['color' => 'sky', 'label' => 'New LinkedIn account connected', 'time' => '5 hours ago'],
                    ['color' => 'amber', 'label' => 'New subscription activated', 'time' => '1 day ago'],
                    ['color' => 'rose', 'label' => 'License validation warning', 'time' => '2 days ago'],
                    ['color' => 'indigo', 'label' => 'AI Insights addon installed', 'time' => '3 days ago'],
                ] as $item)
                    @php
                        $colorMap = [
                            'emerald' => 'bg-emerald-500',
                            'sky' => 'bg-sky-500',
                            'amber' => 'bg-amber-500',
                            'rose' => 'bg-rose-500',
                            'indigo' => 'bg-indigo-500',
                        ];
                    @endphp
                    <div class="flex items-center gap-3">
                        <span class="h-2 w-2 rounded-full {{ $colorMap[$item['color']] ?? 'bg-slate-400' }}"></span>
                        <div class="flex-1">
                            <p class="text-slate-700">{{ $item['label'] }}</p>
                            <p class="text-[11px] text-slate-400">{{ $item['time'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
