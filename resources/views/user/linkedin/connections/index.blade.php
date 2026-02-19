@extends('user.layout')

@section('page_title', 'Connections')
@section('page_subtitle', 'Directory of your LinkedIn connections synced via extension.')

@section('content')
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-sm font-semibold">Contacts List</h3>
            <form method="get" class="flex gap-2">
                <input type="text"
                       name="q"
                       value="{{ request('q') }}"
                       placeholder="Search name..."
                       class="text-xs border rounded-lg px-3 py-1 dark:bg-slate-800 dark:border-slate-700">
                <button type="submit"
                        class="px-4 py-1 bg-indigo-600 text-white text-xs rounded-lg cursor-pointer hover:scale-[var(--hover-scale)] transition shadow">
                    Filter
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b dark:border-slate-800 text-slate-500">
                        <th class="pb-3 font-medium">Profile</th>
                        <th class="pb-3 font-medium">Headline</th>
                        <th class="pb-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-slate-800">
                    @forelse($data['connections'] as $c)
                        @php
                            $rawName = trim((string) ($c->full_name ?? ''));
                            $rawPid  = trim((string) ($c->public_identifier ?? ''));

                            $name = ($rawName !== '' && strtolower($rawName) !== 'unknown')
                                ? $rawName
                                : (($rawPid !== '' && strtolower($rawPid) !== 'unknown') ? $rawPid : 'Connection');

                            $img = !empty($c->profile_image_url)
                                ? $c->profile_image_url
                                : ('https://ui-avatars.com/api/?name=' . urlencode($name));

                            $headlineRaw = trim((string) ($c->headline ?? ''));
                            $headline = ($headlineRaw !== '' && strtolower($headlineRaw) !== 'unknown') ? $headlineRaw : 'N/A';
                        @endphp

                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                            <td class="py-3">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $img }}"
                                         class="h-8 w-8 rounded-full object-cover border border-slate-200 dark:border-slate-700"
                                         alt="{{ $name }}">
                                    <span class="font-semibold text-slate-800 dark:text-slate-100">{{ $name }}</span>
                                </div>
                            </td>

                            <td class="py-3 text-slate-500 max-w-md truncate">{{ $headline }}</td>

                            <td class="py-3 text-right">
                                @if(!empty($c->profile_url))
                                    <a href="{{ $c->profile_url }}"
                                       target="_blank"
                                       class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-semibold
                                              border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900
                                              text-slate-700 dark:text-slate-100 cursor-pointer
                                              hover:scale-[var(--hover-scale)] transition shadow">
                                        View Profile
                                    </a>
                                @else
                                    <span class="text-[11px] text-slate-400">No profile link</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-10 text-center text-slate-400">
                                No connections found. Sync with extension to populate.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $data['connections']->links() }}</div>
    </div>
@endsection
