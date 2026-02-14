@extends('user.layout')

@section('page_title', 'Connections')
@section('page_subtitle', 'Full connections directory synced from your extension.')

@section('content')
@php
    $status      = $data['status'] ?? 'empty';
    $profile     = $data['profile'] ?? [];
    $connections = $data['connections'] ?? null;
    $filter      = $data['filter'] ?? [];
@endphp

@if($status === 'empty')
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6">
        <h2 class="text-base font-semibold text-slate-800 dark:text-slate-50 mb-2">No profile connected</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400">Connect and sync a LinkedIn profile to view connections.</p>
    </div>
@else
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div class="flex items-center gap-3">
                @if(!empty($profile['profile_image_url']))
                    <img src="{{ $profile['profile_image_url'] }}" class="h-11 w-11 rounded-full object-cover border border-slate-200 dark:border-slate-700" alt="">
                @else
                    <div class="h-11 w-11 rounded-full bg-gradient-to-br from-indigo-500 to-sky-500 flex items-center justify-center text-sm font-semibold text-white shadow">
                        {{ strtoupper(substr($profile['name'] ?? 'LI', 0, 2)) }}
                    </div>
                @endif
                <div>
                    <div class="text-sm font-semibold text-slate-800 dark:text-slate-50">{{ $profile['name'] ?? 'LinkedIn profile' }}</div>
                    @if(!empty($profile['headline']))
                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $profile['headline'] }}</div>
                    @endif
                </div>
            </div>

            <form method="get" class="grid grid-cols-1 md:grid-cols-6 gap-2 text-xs w-full lg:max-w-4xl">
                <input type="hidden" name="profile_id" value="{{ request('profile_id') }}">

                <input type="text" name="q" value="{{ request('q', $filter['q'] ?? '') }}" placeholder="Search name, headline, url..."
                       class="md:col-span-2 border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 bg-white dark:bg-slate-900 text-xs text-slate-700 dark:text-slate-200">

                <input type="text" name="location" value="{{ request('location', $filter['location'] ?? '') }}" placeholder="Location"
                       class="border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 bg-white dark:bg-slate-900 text-xs text-slate-700 dark:text-slate-200">

                <input type="text" name="industry" value="{{ request('industry', $filter['industry'] ?? '') }}" placeholder="Industry"
                       class="border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 bg-white dark:bg-slate-900 text-xs text-slate-700 dark:text-slate-200">

                <input type="number" name="degree" value="{{ request('degree', $filter['degree'] ?? '') }}" placeholder="Degree"
                       class="border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 bg-white dark:bg-slate-900 text-xs text-slate-700 dark:text-slate-200">

                <button type="submit"
                        class="px-3 py-1.5 rounded-full text-xs font-semibold shadow-md cursor-pointer bg-slate-900 text-slate-50 border border-slate-700 hover:scale-[var(--hover-scale)] transition">
                    Filter
                </button>

                <input type="date" name="from" value="{{ request('from', $filter['from'] ?? '') }}"
                       class="border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 bg-white dark:bg-slate-900 text-xs text-slate-700 dark:text-slate-200">
                <input type="date" name="to" value="{{ request('to', $filter['to'] ?? '') }}"
                       class="border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 bg-white dark:bg-slate-900 text-xs text-slate-700 dark:text-slate-200">
            </form>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6">
        @if(!$connections || $connections->isEmpty())
            <p class="text-sm text-slate-500 dark:text-slate-400">No connections found for the selected filters.</p>
        @else
            <div class="overflow-x-auto text-xs">
                <table class="min-w-full">
                    <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400">
                    <tr>
                        <th class="px-3 py-2 text-left font-medium">Person</th>
                        <th class="px-3 py-2 text-left font-medium">Location</th>
                        <th class="px-3 py-2 text-left font-medium">Industry</th>
                        <th class="px-3 py-2 text-right font-medium">Degree</th>
                        <th class="px-3 py-2 text-left font-medium">Connected</th>
                        <th class="px-3 py-2 text-right font-medium">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @foreach($connections as $c)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/70 transition">
                            <td class="px-3 py-2">
                                <div class="flex items-center gap-2">
                                    @if($c->profile_image_url)
                                        <img src="{{ $c->profile_image_url }}" class="h-8 w-8 rounded-full object-cover border border-slate-200 dark:border-slate-700" alt="">
                                    @else
                                        <div class="h-8 w-8 rounded-full bg-gradient-to-br from-indigo-500 to-sky-500 flex items-center justify-center text-[11px] font-semibold text-white shadow">
                                            {{ strtoupper(substr($c->full_name ?? 'CN', 0, 2)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="text-xs font-semibold text-slate-800 dark:text-slate-50">{{ $c->full_name ?? 'Unknown' }}</div>
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400">{{ \Illuminate\Support\Str::limit($c->headline ?? '', 70) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-2 text-slate-600 dark:text-slate-300">{{ $c->location ?? 'n/a' }}</td>
                            <td class="px-3 py-2 text-slate-600 dark:text-slate-300">{{ $c->industry ?? 'n/a' }}</td>
                            <td class="px-3 py-2 text-right text-slate-600 dark:text-slate-300">{{ $c->degree ?? 0 }}</td>
                            <td class="px-3 py-2 text-slate-600 dark:text-slate-300">
                                {{ $c->connected_at ? $c->connected_at->format('Y-m-d H:i') : 'n/a' }}
                            </td>
                            <td class="px-3 py-2 text-right">
                                @if($c->profile_url)
                                    <a href="{{ $c->profile_url }}" target="_blank"
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
                {{ $connections->links() }}
            </div>
        @endif
    </div>
@endif
@endsection
