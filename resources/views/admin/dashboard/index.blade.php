@extends('admin.layout')

@section('page_title', 'Dashboard')
@section('page_subtitle', 'High-level overview of platform usage and LinkedIn sync activity.')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-4 flex items-center justify-between cursor-pointer transition transform hover:scale-[var(--hover-scale)]">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Total users</p>
                <p class="mt-2 text-xl font-semibold text-slate-800">
                    {{ number_format($stats['total_users'] ?? 0) }}
                </p>
                <p class="mt-1 text-[11px] text-slate-400">
                    Registered accounts.
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

        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-4 flex items-center justify-between cursor-pointer transition transform hover:scale-[var(--hover-scale)]">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">LinkedIn profiles</p>
                <p class="mt-2 text-xl font-semibold text-slate-800">
                    {{ number_format($stats['total_profiles'] ?? 0) }}
                </p>
                <p class="mt-1 text-[11px] text-slate-400">
                    Profiles synced across all users.
                </p>
            </div>
            <div class="h-11 w-11 rounded-2xl flex items-center justify-center bg-gradient-to-br from-indigo-500 to-indigo-600 text-white shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4h16v6H4zM4 14h9v6H4z" />
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-4 flex items-center justify-between cursor-pointer transition transform hover:scale-[var(--hover-scale)]">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Synced posts</p>
                <p class="mt-2 text-xl font-semibold text-slate-800">
                    {{ number_format($stats['total_posts'] ?? 0) }}
                </p>
                <p class="mt-1 text-[11px] text-slate-400">
                    Total posts stored in the system.
                </p>
            </div>
            <div class="h-11 w-11 rounded-2xl flex items-center justify-center bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 5h14v14H5z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 9h6v6H9z" />
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-4 flex items-center justify-between cursor-pointer transition transform hover:scale-[var(--hover-scale)]">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Sync jobs (24h)</p>
                <p class="mt-2 text-xl font-semibold text-slate-800">
                    {{ number_format($stats['sync_jobs_24h'] ?? 0) }}
                </p>
                <p class="mt-1 text-[11px] text-slate-400">
                    Profile and post sync runs in last 24 hours.
                </p>
            </div>
            <div class="h-11 w-11 rounded-2xl flex items-center justify-center bg-gradient-to-br from-amber-400 to-amber-500 text-white shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3" />
                    <circle cx="12" cy="12" r="9" />
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-4 flex items-center justify-between cursor-pointer transition transform hover:scale-[var(--hover-scale)]">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Active users (24h)</p>
                <p class="mt-2 text-xl font-semibold text-slate-800">
                    {{ number_format($stats['active_users_24h'] ?? 0) }}
                </p>
                <p class="mt-1 text-[11px] text-slate-400">
                    Users with at least one sync job in last 24 hours.
                </p>
            </div>
            <div class="h-11 w-11 rounded-2xl flex items-center justify-center bg-gradient-to-br from-rose-500 to-rose-600 text-white shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2" />
                    <circle cx="9" cy="9" r="3" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 21v-2a3 3 0 00-2-2.82" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 3.13a3 3 0 010 5.74" />
                </svg>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-4">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Top users by posts</p>
                    <p class="text-xs text-slate-400 mt-0.5">Users with the highest number of synced posts.</p>
                </div>
                <form method="get" class="flex items-center gap-2 text-xs">
                    <input type="text"
                           name="q"
                           value="{{ request('q') }}"
                           placeholder="Search user..."
                           class="border border-slate-200 rounded-lg px-2 py-1 text-xs text-slate-700">
                    <button type="submit"
                            class="px-3 py-1.5 rounded-full text-xs font-semibold shadow-md cursor-pointer bg-slate-900 text-slate-50 border border-slate-700 hover:scale-[var(--hover-scale)] transition">
                        Search
                    </button>
                </form>
            </div>

            @if($topUsers->isEmpty())
                <p class="text-sm text-slate-500">No users yet.</p>
            @else
                <div class="overflow-x-auto text-xs">
                    <table class="min-w-full">
                        <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="px-3 py-2 text-left font-medium">User</th>
                            <th class="px-3 py-2 text-left font-medium">Profiles</th>
                            <th class="px-3 py-2 text-left font-medium">Posts</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                        @foreach($topUsers as $user)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-3 py-2">
                                    <div class="flex flex-col">
                                        <span class="font-semibold text-slate-800">{{ $user->name }}</span>
                                        <span class="text-[11px] text-slate-500">{{ $user->email }}</span>
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    {{ number_format($user->linkedin_profiles_count ?? 0) }}
                                </td>
                                <td class="px-3 py-2">
                                    {{ number_format($user->linkedin_posts_count ?? 0) }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $topUsers->links() }}
                </div>
            @endif
        </div>

        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-4">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Recent sync jobs</p>
                    <p class="text-xs text-slate-400 mt-0.5">Latest profile and post sync events.</p>
                </div>
                <span class="text-[11px] text-slate-400">
                    {{ number_format($recentSyncJobs->total()) }} total
                </span>
            </div>

            @if($recentSyncJobs->isEmpty())
                <p class="text-sm text-slate-500">No sync jobs yet.</p>
            @else
                <div class="space-y-3 text-xs">
                    @foreach($recentSyncJobs as $job)
                        @php
                            $statusColor = match($job->status) {
                                'success' => 'bg-emerald-500/10 text-emerald-500 border-emerald-500/30',
                                'failed'  => 'bg-rose-500/10 text-rose-500 border-rose-500/30',
                                'running' => 'bg-amber-500/10 text-amber-500 border-amber-500/30',
                                default   => 'bg-slate-500/10 text-slate-500 border-slate-500/30',
                            };
                        @endphp
                        <div class="rounded-xl bg-slate-50 border border-slate-200 p-3">
                            <div class="flex items-center justify-between mb-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-slate-700">
                                        {{ $job->user?->name ?? 'Unknown user' }}
                                    </span>
                                    <span class="text-[11px] text-slate-400">
                                        {{ ucfirst($job->type) }} · {{ ucfirst($job->source) }}
                                    </span>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] border {{ $statusColor }}">
                                    {{ ucfirst($job->status) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-slate-500">
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
                                <div class="mt-1 text-[11px] text-rose-500">
                                    {{ \Illuminate\Support\Str::limit($job->error_message, 90) }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="mt-3">
                    {{ $recentSyncJobs->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
