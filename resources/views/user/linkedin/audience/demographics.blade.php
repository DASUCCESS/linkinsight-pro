@extends('user.layout')

@section('page_title', 'Followers demographics')
@section('page_subtitle', 'Full demographics snapshots synced from your extension.')

@section('content')
@php
    $status           = $data['status'] ?? 'empty';
    $profiles         = $data['profiles'] ?? collect();
    $activeProfileId  = $data['active_profile_id'] ?? null;
    $profileArray     = $data['profile'] ?? [];
    $latest           = $data['latest'] ?? null;
    $history          = $data['history'] ?? null;
    $filter           = $data['filter'] ?? [];

    $from = request('from', $filter['from'] ?? '');
    $to   = request('to', $filter['to'] ?? '');

    $rawName = trim((string)($profileArray['name'] ?? ''));
    $isUnknownName = $rawName === '' || strtolower($rawName) === 'unknown';

    $rawLinkedinId = trim((string)($profileArray['linkedin_id'] ?? ''));
    $linkedinId = ($rawLinkedinId === '' || strtolower($rawLinkedinId) === 'unknown') ? null : $rawLinkedinId;

    $displayName = $isUnknownName ? ($linkedinId ?: 'LinkedIn profile') : $rawName;

    $rawHeadline = trim((string)($profileArray['headline'] ?? ''));
    $headline = ($rawHeadline === '' || strtolower($rawHeadline) === 'unknown') ? null : $rawHeadline;

    $initialsSource = $displayName ?: 'LI';
    $initials = strtoupper(mb_substr($initialsSource, 0, 2));

    // Latest demographics, structured per category
    $rawDemo = is_array($latest['demographics'] ?? null) ? $latest['demographics'] : [];

    $jobTitleItems    = is_array($rawDemo['job_title']    ?? null) ? $rawDemo['job_title']    : [];
    $locationItems    = is_array($rawDemo['location']     ?? null) ? $rawDemo['location']     : [];
    $industryItems    = is_array($rawDemo['industry']     ?? null) ? $rawDemo['industry']     : [];
    $seniorityItems   = is_array($rawDemo['seniority']    ?? null) ? $rawDemo['seniority']    : [];
    $companySizeItems = is_array($rawDemo['company_size'] ?? null) ? $rawDemo['company_size'] : [];
    $companyItems     = is_array($rawDemo['company']      ?? null) ? $rawDemo['company']      : [];

    $resolvePercent = function ($item) {
        $v = $item['percent'] ?? $item['percentage'] ?? null;
        return is_null($v) ? null : (float) $v;
    };

    $cleanLabel = function ($v) {
        $t = trim((string) $v);
        if ($t === '' || strtolower($t) === 'unknown') return null;
        return $t;
    };
@endphp

@if($status === 'empty')
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6">
        <h2 class="text-base font-semibold text-slate-800 dark:text-slate-50 mb-2">No profile connected</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400">Connect and sync a LinkedIn profile to view demographics.</p>
    </div>
@else
    {{-- Header / filters --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-3">
                @if(!empty($profileArray['profile_image_url']))
                    <img src="{{ $profileArray['profile_image_url'] }}"
                         class="h-11 w-11 rounded-full object-cover border border-slate-200 dark:border-slate-700" alt="">
                @else
                    <div class="h-11 w-11 rounded-full bg-gradient-to-br from-indigo-500 to-sky-500 flex items-center justify-center text-sm font-semibold text-white shadow">
                        {{ $initials }}
                    </div>
                @endif
                <div>
                    <div class="text-sm font-semibold text-slate-800 dark:text-slate-50">
                        {{ $displayName }}
                    </div>
                    @if($headline)
                        <div class="text-xs text-slate-500 dark:text-slate-400">
                            {{ $headline }}
                        </div>
                    @endif
                </div>
            </div>

            <form method="get" class="flex flex-wrap items-center gap-2 text-xs">
                <select name="profile_id"
                        class="border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 bg-white dark:bg-slate-900 text-xs text-slate-700 dark:text-slate-200">
                    @foreach($profiles as $p)
                        @php
                            $pName = trim((string)($p->name ?? ''));
                            $pName = ($pName === '' || strtolower($pName) === 'unknown') ? null : $pName;

                            $pId = trim((string)($p->linkedin_id ?? $p->public_identifier ?? ''));
                            $pId = ($pId === '' || strtolower($pId) === 'unknown') ? null : $pId;

                            $optLabel = $pName ?: ($pId ?: ('Profile #'.$p->id));
                        @endphp
                        <option value="{{ $p->id }}" @selected((int) $activeProfileId === (int) $p->id)>
                            {{ $optLabel }}
                        </option>
                    @endforeach
                </select>

                <input type="date" name="from" value="{{ $from }}"
                       class="border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 bg-white dark:bg-slate-900 text-xs text-slate-700 dark:text-slate-200">
                <input type="date" name="to" value="{{ $to }}"
                       class="border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 bg-white dark:bg-slate-900 text-xs text-slate-700 dark:text-slate-200">
                <button type="submit"
                        class="px-3 py-1.5 rounded-full text-xs font-semibold shadow-md cursor-pointer bg-slate-900 text-slate-50 border border-slate-700 hover:scale-[var(--hover-scale)] transition">
                    Apply range
                </button>
            </form>
        </div>
    </div>

    {{-- Latest snapshot --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-50">Latest snapshot</h3>
            <div class="text-[11px] text-slate-500 dark:text-slate-400">
                {{ $latest['snapshot_date'] ?? 'n/a' }}
            </div>
        </div>

        @if(!$latest || empty($rawDemo))
            <p class="text-sm text-slate-500 dark:text-slate-400">No demographics synced yet.</p>
        @else
            <div class="mb-4 text-xs text-slate-500 dark:text-slate-400">
                Followers tracked:
                <span class="font-semibold text-slate-800 dark:text-slate-50">
                    {{ number_format($latest['followers_count'] ?? 0) }}
                </span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                {{-- Job Title --}}
                <div class="rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-4">
                    <div class="text-sm font-semibold text-slate-800 dark:text-slate-50 mb-2">Job Title</div>
                    @if(empty($jobTitleItems))
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">No data</p>
                    @else
                        <div class="space-y-1 text-xs">
                            @foreach(array_slice($jobTitleItems, 0, 15) as $it)
                                @php $label = $cleanLabel($it['label'] ?? null); $percent = $resolvePercent($it); @endphp
                                @if($label)
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-700 dark:text-slate-200 line-clamp-1">{{ $label }}</span>
                                        @if(!is_null($percent))
                                            <span class="text-slate-500 dark:text-slate-400">{{ number_format($percent, 1) }}%</span>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Location --}}
                <div class="rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-4">
                    <div class="text-sm font-semibold text-slate-800 dark:text-slate-50 mb-2">Location</div>
                    @if(empty($locationItems))
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">No data</p>
                    @else
                        <div class="space-y-1 text-xs">
                            @foreach(array_slice($locationItems, 0, 15) as $it)
                                @php $label = $cleanLabel($it['label'] ?? null); $percent = $resolvePercent($it); @endphp
                                @if($label)
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-700 dark:text-slate-200 line-clamp-1">{{ $label }}</span>
                                        @if(!is_null($percent))
                                            <span class="text-slate-500 dark:text-slate-400">{{ number_format($percent, 1) }}%</span>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Industry --}}
                <div class="rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-4">
                    <div class="text-sm font-semibold text-slate-800 dark:text-slate-50 mb-2">Industry</div>
                    @if(empty($industryItems))
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">No data</p>
                    @else
                        <div class="space-y-1 text-xs">
                            @foreach(array_slice($industryItems, 0, 15) as $it)
                                @php $label = $cleanLabel($it['label'] ?? null); $percent = $resolvePercent($it); @endphp
                                @if($label)
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-700 dark:text-slate-200 line-clamp-1">{{ $label }}</span>
                                        @if(!is_null($percent))
                                            <span class="text-slate-500 dark:text-slate-400">{{ number_format($percent, 1) }}%</span>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Seniority --}}
                <div class="rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-4">
                    <div class="text-sm font-semibold text-slate-800 dark:text-slate-50 mb-2">Seniority</div>
                    @if(empty($seniorityItems))
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">No data</p>
                    @else
                        <div class="space-y-1 text-xs">
                            @foreach(array_slice($seniorityItems, 0, 15) as $it)
                                @php $label = $cleanLabel($it['label'] ?? null); $percent = $resolvePercent($it); @endphp
                                @if($label)
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-700 dark:text-slate-200 line-clamp-1">{{ $label }}</span>
                                        @if(!is_null($percent))
                                            <span class="text-slate-500 dark:text-slate-400">{{ number_format($percent, 1) }}%</span>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Company Size --}}
                <div class="rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-4">
                    <div class="text-sm font-semibold text-slate-800 dark:text-slate-50 mb-2">Company Size</div>
                    @if(empty($companySizeItems))
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">No data</p>
                    @else
                        <div class="space-y-1 text-xs">
                            @foreach(array_slice($companySizeItems, 0, 15) as $it)
                                @php $label = $cleanLabel($it['label'] ?? null); $percent = $resolvePercent($it); @endphp
                                @if($label)
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-700 dark:text-slate-200 line-clamp-1">{{ $label }}</span>
                                        @if(!is_null($percent))
                                            <span class="text-slate-500 dark:text-slate-400">{{ number_format($percent, 1) }}%</span>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Company --}}
                <div class="rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-4">
                    <div class="text-sm font-semibold text-slate-800 dark:text-slate-50 mb-2">Company</div>
                    @if(empty($companyItems))
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">No data</p>
                    @else
                        <div class="space-y-1 text-xs">
                            @foreach(array_slice($companyItems, 0, 15) as $it)
                                @php $label = $cleanLabel($it['label'] ?? null); $percent = $resolvePercent($it); @endphp
                                @if($label)
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-700 dark:text-slate-200 line-clamp-1">{{ $label }}</span>
                                        @if(!is_null($percent))
                                            <span class="text-slate-500 dark:text-slate-400">{{ number_format($percent, 1) }}%</span>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        @if(count($companyItems) > 15)
                            <p class="mt-2 text-[11px] text-slate-500 dark:text-slate-400">
                                Showing top 15 companies.
                            </p>
                        @endif
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- Snapshot history --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6">
        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-50 mb-3">Snapshot history</h3>

        @if(!$history || $history->isEmpty())
            <p class="text-sm text-slate-500 dark:text-slate-400">No history for the selected range.</p>
        @else
            <div class="overflow-x-auto text-xs">
                <table class="min-w-full">
                    <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400">
                    <tr>
                        <th class="px-3 py-2 text-left font-medium">Date</th>
                        <th class="px-3 py-2 text-right font-medium">Followers tracked</th>
                        <th class="px-3 py-2 text-left font-medium">Categories count</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @foreach($history as $row)
                        @php $demo = is_array($row->demographics) ? $row->demographics : []; @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/70 transition">
                            <td class="px-3 py-2 text-slate-800 dark:text-slate-50">
                                {{ optional($row->snapshot_date)->toDateString() }}
                            </td>
                            <td class="px-3 py-2 text-right text-slate-600 dark:text-slate-300">
                                {{ number_format($row->followers_count ?? 0) }}
                            </td>
                            <td class="px-3 py-2 text-slate-600 dark:text-slate-300">
                                {{ count($demo) }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $history->links() }}
            </div>
        @endif
    </div>
@endif
@endsection
