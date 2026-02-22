@extends('admin.layout')

@section('page_title', 'Themes')
@section('page_subtitle', 'Manage public website themes.')

@section('content')
    <div class="space-y-6">

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="rounded-2xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-xs text-emerald-800 shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-2xl bg-rose-50 border border-rose-200 px-4 py-3 text-xs text-rose-800 shadow-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- Header + upload --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-stretch">
            <div class="lg:col-span-2 flex flex-col justify-between gap-4 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-md p-4 md:p-5">
                <div>
                    <h2 class="text-sm md:text-base font-semibold text-slate-800 dark:text-slate-50">
                        Installed themes
                    </h2>
                    <p class="text-xs md:text-[13px] text-slate-500 dark:text-slate-400 mt-1">
                        Upload new theme packages, install them, then switch the active theme for the public site.
                    </p>
                </div>

                <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-3 text-[11px]">
                    <div class="flex items-center gap-2">
                        <div>
                            <p class="font-semibold text-slate-800 dark:text-slate-50">Active theme</p>
                            <p class="font-mono text-[11px] text-slate-500 dark:text-slate-400">
                                {{ $activeSlug ?: 'default' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between sm:justify-start gap-3">
                        <div>
                            <p class="text-[11px] font-semibold text-slate-700 dark:text-slate-100">
                                Previous theme
                            </p>
                            <p class="font-mono text-[11px] text-slate-500 dark:text-slate-400">
                                {{ $previousSlug ?: 'None' }}
                            </p>
                        </div>

                        <form method="POST" action="{{ route('admin.themes.rollback') }}">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center px-3 py-1.5 rounded-full text-[11px] font-semibold cursor-pointer
                                           border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900
                                           text-slate-700 dark:text-slate-100 shadow-sm hover:shadow-md transition">
                                Rollback
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Upload card --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-md p-4 md:p-5">
                <form method="POST"
                      action="{{ route('admin.themes.upload') }}"
                      enctype="multipart/form-data"
                      class="space-y-3">
                    @csrf

                    <div>
                        <p class="text-xs font-semibold text-slate-800 dark:text-slate-50">
                            Upload new theme (ZIP)
                        </p>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                            Expected structure at ZIP root:
                            <span class="font-mono">theme.json</span>,
                            <span class="font-mono">views/</span>,
                            <span class="font-mono">assets/</span> (optional).
                        </p>
                    </div>

                    <input
                        type="file"
                        name="theme_zip"
                        accept=".zip"
                        required
                        class="block w-full text-xs text-slate-600 dark:text-slate-200
                               border border-slate-300 dark:border-slate-700 rounded-xl cursor-pointer
                               bg-white dark:bg-slate-900 px-3 py-2 focus:outline-none focus:ring-1 focus:ring-slate-400"
                    >
                    @error('theme_zip')
                        <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                    @enderror

                    <button type="submit"
                            class="w-full inline-flex items-center justify-center px-3 py-2 rounded-full text-[11px] font-semibold cursor-pointer
                                   bg-slate-900 dark:bg-slate-100 text-slate-50 dark:text-slate-900
                                   shadow-md hover:shadow-lg transition">
                        Upload and install
                    </button>
                </form>
            </div>
        </div>

        {{-- Theme list --}}
        <div class="space-y-3">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                <div>
                    <h2 class="text-sm md:text-base font-semibold text-slate-800 dark:text-slate-50">
                        Theme list
                    </h2>
                    <p class="text-xs md:text-[13px] text-slate-500 dark:text-slate-400 mt-0.5">
                        Activate a theme to update the public site design. You can rollback to the previously active theme.
                    </p>
                </div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400">
                    Total themes:
                    <span class="font-semibold text-slate-700 dark:text-slate-100">{{ $themes->count() }}</span>
                </div>
            </div>

            @if($themes->isEmpty())
                <div class="mt-3 rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 text-center">
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-100">
                        No themes installed yet.
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Upload a theme ZIP above to get started.
                    </p>
                </div>
            @else
                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
                    @foreach($themes as $theme)
                        @php
                            $isActive = $theme->slug === $activeSlug;
                        @endphp

                        <div class="flex flex-col bg-white dark:bg-slate-900 rounded-2xl border
                                    {{ $isActive ? 'border-emerald-300 dark:border-emerald-500/60' : 'border-slate-200 dark:border-slate-800' }}
                                    shadow-md p-4 md:p-5 h-full transition transform hover:shadow-xl hover:scale-[var(--hover-scale)]">
                            <div class="flex items-start gap-3">
                                <div class="h-10 w-10 rounded-2xl bg-gradient-to-br from-indigo-500 to-sky-500 flex items-center justify-center text-xs font-bold text-white shadow">
                                    {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-50 truncate">
                                                {{ $theme->name }}
                                            </h3>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">
                                                slug:
                                                <span class="font-mono">{{ $theme->slug }}</span>
                                            </p>
                                        </div>

                                        @if($isActive)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/10 text-emerald-600 border border-emerald-200">
                                                Active
                                            </span>
                                        @endif
                                    </div>

                                    <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                                        Version {{ $theme->version ?? '1.0.0' }}
                                        <span class="mx-1">·</span>
                                        {{ $theme->author ?: 'Unknown author' }}
                                    </p>
                                </div>
                            </div>

                            @if($theme->screenshot)
                                <div class="mt-4 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-800 h-40 md:h-56">
                                    <img src="{{ asset($theme->screenshot) }}"
                                         alt="{{ $theme->name }} preview"
                                         class="w-full h-full object-cover">
                                </div>
                            @else
                                <div class="mt-4 rounded-xl border border-dashed border-slate-300 dark:border-slate-700 flex items-center justify-center h-24 md:h-28 text-[11px] text-slate-400 dark:text-slate-500">
                                    No screenshot provided
                                </div>
                            @endif

                            <div class="mt-4 flex items-center justify-between gap-3 text-[11px]">
                                <div class="text-slate-500 dark:text-slate-400 truncate">
                                    Path:
                                    <span class="font-mono">{{ $theme->path }}</span>
                                </div>

                                @if(!$isActive)
                                    <form method="POST" action="{{ route('admin.themes.activate', $theme) }}">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex items-center px-3 py-1.5 rounded-full text-[11px] font-semibold cursor-pointer
                                                       border border-slate-300 dark:border-slate-600
                                                       bg-slate-900 dark:bg-slate-100
                                                       text-slate-50 dark:text-slate-900
                                                       shadow-md transition hover:shadow-lg">
                                            Activate
                                        </button>
                                    </form>
                                @else
                                    <span class="text-[11px] text-emerald-600">
                                        Currently in use
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
