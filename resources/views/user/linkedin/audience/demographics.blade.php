@extends('user.layout')

@section('page_title', 'Followers demographics')
@section('page_subtitle', 'Full demographics snapshots synced from your extension.')

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
        <p class="text-sm text-slate-500 dark:text-slate-400">Connect and sync a LinkedIn profile to view demographics.</p>
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

    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-50">Latest snapshot</h3>
            <div class="text-[11px] text-slate-500 dark:text-slate-400">
                {{ $latest['snapshot_date'] ?? 'n/a' }}
            </div>
        </div>

        @if(!$latest)
            <p class="text-sm text-slate-500 dark:text-slate-400">No demographics synced yet.</p>
        @else
            <div class="mb-4 text-xs text-slate-500 dark:text-slate-400">
                Followers tracked:
                <span class="font-semibold text-slate-800 dark:text-slate-50">
                    {{ number_format($latest['followers_count'] ?? 0) }}
                </span>
            </div>

            @php
                $demographics = is_array($latest['demographics'] ?? null) ? $latest['demographics'] : [];
            @endphp

            @if(empty($demographics))
                <p class="text-sm text-slate-500 dark:text-slate-400">No demographic breakdown available.</p>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    @foreach($demographics as $category => $items)
                        @php
                            $title = ucwords(str_replace('_', ' ', (string) $category));
                            $items = is_array($items) ? $items : [];
                        @endphp
                        <div class="rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-4">
                            <div class="text-sm font-semibold text-slate-800 dark:text-slate-50 mb-2">{{ $title }}</div>

                            @if(empty($items))
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">No data</p>
                            @else
                                <div class="space-y-1 text-xs">
                                    @foreach(array_slice($items, 0, 15) as $it)
                                        <div class="flex items-center justify-between">
                                            <span class="text-slate-700 dark:text-slate-200 line-clamp-1">{{ $it['label'] ?? 'Unknown' }}</span>
                                            <span class="text-slate-500 dark:text-slate-400">
                                                {{ number_format((float) ($it['percent'] ?? $it['percentage'] ?? 0), 1) }}%
                                            </span>
                                        </div>
                                    @endforeach
                                </div>

                                @if(count($items) > 15)
                                    <p class="mt-2 text-[11px] text-slate-500 dark:text-slate-400">Showing top 15 items.</p>
                                @endif
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
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
                        <th class="px-3 py-2 text-right font-medium">Followers tracked</th>
                        <th class="px-3 py-2 text-left font-medium">Categories count</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @foreach($history as $row)
                        @php
                            $demo = is_array($row->demographics) ? $row->demographics : [];
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/70 transition">
                            <td class="px-3 py-2 text-slate-800 dark:text-slate-50">{{ optional($row->snapshot_date)->toDateString() }}</td>
                            <td class="px-3 py-2 text-right text-slate-600 dark:text-slate-300">{{ number_format($row->followers_count ?? 0) }}</td>
                            <td class="px-3 py-2 text-slate-600 dark:text-slate-300">{{ count($demo) }}</td>
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
