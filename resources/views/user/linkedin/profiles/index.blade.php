@extends('user.layout')

@section('page_title', 'LinkedIn profiles')
@section('page_subtitle', 'Manage the LinkedIn profiles connected to your workspace.')

@section('content')
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-50">
                Connected profiles
            </h2>
            <button type="button"
                    id="btnConnectNewProfile"
                    class="px-3 py-1.5 rounded-full text-xs font-semibold shadow-md cursor-pointer
                           bg-gradient-to-r from-indigo-500 to-sky-500 text-white
                           transition transform duration-200 hover:scale-[var(--hover-scale)]">
                Connect new profile
            </button>
        </div>

        @if($profiles->isEmpty())
            <p class="text-sm text-slate-500 dark:text-slate-400">
                No LinkedIn profiles connected yet. Use the browser extension or connection flow to add your first profile.
            </p>
        @else
            <div class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-2 text-left font-medium">Profile</th>
                        <th class="px-4 py-2 text-left font-medium">Headline</th>
                        <th class="px-4 py-2 text-left font-medium">URL</th>
                        <th class="px-4 py-2 text-left font-medium">Primary</th>
                        <th class="px-4 py-2 text-right font-medium">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @foreach($profiles as $profile)
                        <tr class="bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-900/70 transition">
                            <td class="px-4 py-2">
                                <div class="flex items-center gap-2">
                                    @if($profile->profile_image_url)
                                        <img src="{{ $profile->profile_image_url }}" alt="{{ $profile->name }}"
                                             class="h-8 w-8 rounded-full object-cover border border-slate-200 dark:border-slate-700">
                                    @else
                                        <div class="h-8 w-8 rounded-full bg-gradient-to-br from-indigo-500 to-sky-500 flex items-center justify-center text-[11px] font-semibold text-white shadow">
                                            {{ strtoupper(substr($profile->name ?? 'LI', 0, 2)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="text-xs font-semibold text-slate-800 dark:text-slate-50">
                                            {{ $profile->name }}
                                        </div>
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400">
                                            {{ $profile->public_url }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-2 text-xs text-slate-600 dark:text-slate-300">
                                {{ \Illuminate\Support\Str::limit($profile->headline, 60) }}
                            </td>
                            <td class="px-4 py-2 text-xs">
                                <a href="{{ $profile->public_url }}" target="_blank"
                                   class="text-indigo-500 hover:text-indigo-400 cursor-pointer">
                                    Open
                                </a>
                            </td>
                            <td class="px-4 py-2 text-xs">
                                @if($profile->is_primary)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/10 text-emerald-500 border border-emerald-500/30">
                                        Primary
                                    </span>
                                @else
                                    <span class="text-[11px] text-slate-400">Secondary</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-xs text-right">
                                <div class="inline-flex items-center gap-2">
                                    <button type="button"
                                            class="px-2.5 py-1 rounded-full border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-[11px] cursor-pointer hover:scale-[var(--hover-scale)] transition">
                                        Set primary
                                    </button>
                                    <button type="button"
                                            class="px-2.5 py-1 rounded-full border border-rose-300 bg-rose-50 text-[11px] text-rose-600 cursor-pointer hover:scale-[var(--hover-scale)] transition">
                                        Remove
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
