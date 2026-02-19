@extends('user.layout')

@section('page_title', 'Creator audience metrics')
@section('page_subtitle', 'Full creator audience metrics synced from your extension.')

@section('content')
@php
    $status  = $data['status'] ?? 'empty';
    $profile = $data['profile'] ?? [];
    $latest  = $data['latest'] ?? null;
    $history = $data['history'] ?? null;
    $filter  = $data['filter'] ?? [];

    $rawName = trim((string)($profile['name'] ?? ''));
    $isUnknownName = $rawName === '' || strtolower($rawName) === 'unknown';

    $rawLinkedinId = trim((string)($profile['linkedin_id'] ?? ''));
    $linkedinId = ($rawLinkedinId === '' || strtolower($rawLinkedinId) === 'unknown') ? null : $rawLinkedinId;

    $displayName = $isUnknownName ? ($linkedinId ?: 'LinkedIn profile') : $rawName;

    $rawHeadline = trim((string)($profile['headline'] ?? ''));
    $headline = ($rawHeadline === '' || strtolower($rawHeadline) === 'unknown') ? null : $rawHeadline;

    $initialsSource = $displayName ?: 'LI';
    $initials = strtoupper(mb_substr($initialsSource, 0, 2));
@endphp

@if($status === 'empty')
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6">
        <h2 class="text-base font-semibold text-slate-800 dark:text-slate-50 mb-2">No profile connected</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400">Connect and sync a LinkedIn profile to view creator metrics.</p>
    </div>
@else
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-3">
                @if(!empty($profile['profile_image_url']))
                    <img src="{{ $profile['profile_image_url'] }}" class="h-11 w-11 rounded-full object-cover border border-slate-200 dark:border-slate-700" alt="">
                @else
                    <div class="h-11 w-11 rounded-full bg-gradient-to-br from-indigo-500 to-sky-500 flex items-center justify-center text-sm font-semibold text-white shadow">
                        {{ $initials }}
                    </div>
                @endif
                <div>
                    <div class="text-sm font-semibold text-slate-800 dark:text-slate-50">{{ $displayName }}</div>
                    @if($headline)
                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $headline }}</div>
                    @endif
                </div>
            </div>

            <form method="get" class="flex flex-wrap items-center gap-2 text-xs">
                <input type="hidden" name="profile_id" value="{{ request('profile_id') }}">
                <input type="date" name="from" value="{{ request('from', $filter['from'] ?? '') }}"
                       class="border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 bg-white dark:bg-slate-900 text-xs text-slate-700 dark:text-slate-200">
                <input type="date" name="to" value="{{ request('to', $filter['to'] ?? '') }}"
                       class="border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 bg-white dark:bg-slate-900 text-xs text-slate-700 dark:text-slate-200">
                <button type="submit"
                        class="px-3 py-1.5 rounded-full text-xs font-semibold shadow-md cursor-pointer bg-slate-900 text-slate-50 border border-slate-700 hover:scale-[var(--hover-scale)] transition">
                    Apply range
                </button>
            </form>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-50">Latest metrics</h3>
            <div class="text-[11px] text-slate-500 dark:text-slate-400">
                {{ $latest['metric_date'] ?? 'n/a' }}
            </div>
        </div>

        @if(!$latest || empty($latest['metrics']))
            <p class="text-sm text-slate-500 dark:text-slate-400">No creator metrics synced yet.</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                @foreach(array_slice($latest['metrics'], 0, 12) as $m)
                    <div class="rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-4 shadow">
                        <div class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-1">
                            {{ $m['label'] ?? 'Metric' }}
                        </div>
                        <div class="text-xl font-semibold text-slate-900 dark:text-slate-50">
                            @php $v = $m['value'] ?? 0; @endphp
                            @if(is_numeric($v))
                                {{ number_format((float) $v, 0) }}
                            @else
                                {{ \Illuminate\Support\Str::limit((string) $v, 40) }}
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6">
        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-50 mb-3">Metric history</h3>

        @if(!$history || $history->isEmpty())
            <p class="text-sm text-slate-500 dark:text-slate-400">No history for the selected range.</p>
        @else
            <div class="overflow-x-auto text-xs">
                <table class="min-w-full">
                    <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400">
                    <tr>
                        <th class="px-3 py-2 text-left font-medium">Date</th>
                        <th class="px-3 py-2 text-left font-medium">Metric keys</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @foreach($history as $row)
                        @php
                            $arr = is_array($row->metrics) ? array_keys($row->metrics) : [];
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/70 transition">
                            <td class="px-3 py-2 text-slate-800 dark:text-slate-50">{{ optional($row->metric_date)->toDateString() }}</td>
                            <td class="px-3 py-2 text-slate-600 dark:text-slate-300">
                                {{ collect($arr)->take(10)->implode(', ') ?: 'n/a' }}
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
