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
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium">Profile</th>
                            <th class="px-4 py-2 text-left font-medium">LinkedIn ID</th>
                            <th class="px-4 py-2 text-right font-medium">Connections</th>
                            <th class="px-4 py-2 text-right font-medium">Followers</th>
                            <th class="px-4 py-2 text-left font-medium">URL</th>
                            <th class="px-4 py-2 text-left font-medium">Primary</th>
                        </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @foreach($profiles as $profile)
                            @php
                                $rawName = trim($profile->name ?? '');
                                $isUnknownName = $rawName === '' || strtolower($rawName) === 'unknown';

                                $linkedinId = trim($profile->linkedin_id ?? '');
                                $linkedinId = ($linkedinId === '' || strtolower($linkedinId) === 'unknown') ? null : $linkedinId;

                                $displayName = $isUnknownName
                                    ? ($linkedinId ?: 'LinkedIn profile')
                                    : $rawName;

                                $publicUrl = trim($profile->public_url ?? '');
                                $publicUrl = $publicUrl === '' ? null : $publicUrl;

                                $connections = is_null($profile->connections_count) ? null : (int) $profile->connections_count;
                                $followers   = is_null($profile->followers_count) ? null : (int) $profile->followers_count;

                                $initialsSource = $displayName ?: 'LI';
                                $initials = strtoupper(mb_substr($initialsSource, 0, 2));
                            @endphp

                            <tr class="bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-900/70 transition">

                                {{-- Profile --}}
                                <td class="px-4 py-2">
                                    <div class="flex items-center gap-2">
                                        @if(!empty($profile->profile_image_url))
                                            <img src="{{ $profile->profile_image_url }}"
                                                 alt="{{ $displayName }}"
                                                 class="h-8 w-8 rounded-full object-cover border border-slate-200 dark:border-slate-700">
                                        @else
                                            <div class="h-8 w-8 rounded-full bg-gradient-to-br from-indigo-500 to-sky-500 flex items-center justify-center text-[11px] font-semibold text-white shadow">
                                                {{ $initials }}
                                            </div>
                                        @endif

                                        <div>
                                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-50">
                                                {{ $displayName }}
                                            </div>

                                            @if($publicUrl)
                                                <div class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-1">
                                                    {{ $publicUrl }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- LinkedIn ID --}}
                                <td class="px-4 py-2 text-xs text-slate-600 dark:text-slate-300">
                                    @if($linkedinId)
                                        {{ $linkedinId }}
                                    @endif
                                </td>

                                {{-- Connections --}}
                                <td class="px-4 py-2 text-xs text-right text-slate-800 dark:text-slate-50">
                                    @if(!is_null($connections))
                                        {{ number_format($connections) }}
                                    @endif
                                </td>

                                {{-- Followers --}}
                                <td class="px-4 py-2 text-xs text-right text-slate-800 dark:text-slate-50">
                                    @if(!is_null($followers))
                                        {{ number_format($followers) }}
                                    @endif
                                </td>

                                {{-- URL --}}
                                <td class="px-4 py-2 text-xs">
                                    @if($publicUrl)
                                        <a href="{{ $publicUrl }}"
                                           target="_blank"
                                           class="text-indigo-500 hover:text-indigo-400 cursor-pointer">
                                            Open
                                        </a>
                                    @endif
                                </td>

                                {{-- Primary --}}
                                <td class="px-4 py-2 text-xs">
                                    @if($profile->is_primary)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/10 text-emerald-500 border border-emerald-500/30">
                                            Primary
                                        </span>
                                    @else
                                        <span class="text-[11px] text-slate-400">Secondary</span>
                                    @endif
                                </td>

                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                @if(method_exists($profiles, 'links'))
                    <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/60">
                        {{ $profiles->links() }}
                    </div>
                @endif
            </div>
        @endif
    </div>
@endsection
