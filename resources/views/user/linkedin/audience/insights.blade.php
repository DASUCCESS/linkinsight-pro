@extends('user.layout')

@section('page_title', 'Audience insights')
@section('page_subtitle', 'Full audience insights snapshots synced from your extension.')

@section('content')
@php
    $status          = $data['status'] ?? 'empty';
    $profiles        = $data['profiles'] ?? collect();
    $activeProfileId = $data['active_profile_id'] ?? null;
    $profileArray    = $data['profile'] ?? [];
    $latest          = $data['latest'] ?? null;
    $history         = $data['history'] ?? null;
    $filter          = $data['filter'] ?? [];

    $from = request('from', $filter['from'] ?? '');
    $to   = request('to', $filter['to'] ?? '');
@endphp

@if($status === 'empty')
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6">
        <h2 class="text-base font-semibold text-slate-800 dark:text-slate-50 mb-2">No profile connected</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400">Connect and sync a LinkedIn profile to view audience insights.</p>
    </div>
@else
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-3">
                @if(!empty($profileArray['profile_image_url']))
                    <img src="{{ $profileArray['profile_image_url'] }}" class="h-11 w-11 rounded-full object-cover border border-slate-200 dark:border-slate-700" alt="">
                @else
                    <div class="h-11 w-11 rounded-full bg-gradient-to-br from-indigo-500 to-sky-500 flex items-center justify-center text-sm font-semibold text-white shadow">
                        {{ strtoupper(substr($profileArray['name'] ?? 'LI', 0, 2)) }}
                    </div>
                @endif
                <div>
                    <div class="text-sm font-semibold text-slate-800 dark:text-slate-50">{{ $profileArray['name'] ?? 'LinkedIn profile' }}</div>
                    @if(!empty($profileArray['headline']))
                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $profileArray['headline'] }}</div>
                    @endif
                </div>
            </div>

            <form method="get" class="flex flex-wrap items-center gap-2 text-xs">
                <select name="profile_id"
                        class="border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 bg-white dark:bg-slate-900 text-xs text-slate-700 dark:text-slate-200">
                    @foreach($profiles as $p)
                        <option value="{{ $p->id }}" @selected((int) $activeProfileId === (int) $p->id)>
                            {{ $p->name ?? $p->public_identifier ?? ('Profile #'.$p->id) }}
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

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-50">Latest snapshot</h3>
                <div class="text-[11px] text-slate-500 dark:text-slate-400">
                    {{ $latest['snapshot_date'] ?? 'n/a' }}
                </div>
            </div>

            @if(!$latest)
                <p class="text-sm text-slate-500 dark:text-slate-400">No audience insights synced yet.</p>
            @else
                @php
                    $blocks = [
                        'Top locations'      => $latest['top_locations'] ?? [],
                        'Top industries'     => $latest['top_industries'] ?? [],
                        'Top job titles'     => $latest['top_job_titles'] ?? [],
                        'Engagement sources' => $latest['engagement_sources'] ?? [],
                    ];
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    @foreach($blocks as $title => $items)
                        <div class="rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-4">
                            <div class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-2">{{ $title }}</div>
                            @if(empty($items))
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">No data</p>
                            @else
                                <ul class="space-y-1">
                                    @foreach(array_slice($items, 0, 12) as $it)
                                        <li class="flex items-center justify-between">
                                            <span class="text-slate-700 dark:text-slate-200 line-clamp-1">{{ $it['label'] ?? 'Unknown' }}</span>
                                            @if(!is_null($it['value'] ?? null))
                                                <span class="text-slate-500 dark:text-slate-400">
                                                    {{ is_numeric($it['value']) ? number_format((float) $it['value']) : $it['value'] }}
                                                </span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

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
                            <th class="px-3 py-2 text-left font-medium">Top locations</th>
                            <th class="px-3 py-2 text-left font-medium">Top industries</th>
                            <th class="px-3 py-2 text-left font-medium">Top job titles</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @foreach($history as $row)
                            @php
                                $loc = collect($row->top_locations ?? [])->pluck('label')->take(3)->implode(', ');
                                $ind = collect($row->top_industries ?? [])->pluck('label')->take(3)->implode(', ');
                                $job = collect($row->top_job_titles ?? [])->pluck('label')->take(3)->implode(', ');
                            @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/70 transition">
                                <td class="px-3 py-2 text-slate-800 dark:text-slate-50">
                                    {{ optional($row->snapshot_date)->toDateString() }}
                                </td>
                                <td class="px-3 py-2 text-slate-600 dark:text-slate-300">
                                    {{ $loc ?: 'n/a' }}
                                </td>
                                <td class="px-3 py-2 text-slate-600 dark:text-slate-300">
                                    {{ $ind ?: 'n/a' }}
                                </td>
                                <td class="px-3 py-2 text-slate-600 dark:text-slate-300">
                                    {{ $job ?: 'n/a' }}
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
    </div>
@endif
@endsection
