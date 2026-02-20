@extends('admin.layout')

@section('page_title', 'Themes')
@section('page_subtitle', 'Manage public website themes.')

@section('content')
    @if(session('success'))
        <div class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-xs text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded-xl bg-rose-50 border border-rose-200 px-4 py-3 text-xs text-rose-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        <div class="lg:col-span-2 flex flex-col justify-center">
            <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-50">
                Installed themes
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Upload theme zip packages, install them, then switch the active theme for the public site.
            </p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-md p-4">
            <form method="POST" action="{{ route('admin.themes.upload') }}" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-100">
                    Upload new theme (ZIP)
                </label>
                <input
                    type="file"
                    name="theme_zip"
                    accept=".zip"
                    class="block w-full text-xs text-slate-600 dark:text-slate-200 border border-slate-300 dark:border-slate-700 rounded-xl cursor-pointer bg-white dark:bg-slate-900 focus:outline-none focus:ring-1 focus:ring-slate-400"
                    required
                >
                @error('theme_zip')
                    <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                @enderror

                <button type="submit"
                        class="w-full inline-flex items-center justify-center px-3 py-2 rounded-full text-[11px] font-semibold cursor-pointer
                               bg-slate-900 dark:bg-slate-100 text-slate-50 dark:text-slate-900 shadow-md transition hover:shadow-lg">
                    Upload and install
                </button>

                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                    Expected structure: theme.json, views/, assets/ (optional).
                </p>
            </form>
        </div>
    </div>

    <div class="mb-6 flex items-center justify-between gap-3">
        <div>
            <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-50">
                Theme list
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Switch the public site design by activating a theme. Rollback restores the previous active theme.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <form method="POST" action="{{ route('admin.themes.rollback') }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center px-3 py-1.5 rounded-full text-[11px] font-semibold cursor-pointer
                               border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-100
                               shadow-md transition hover:shadow-lg">
                    Rollback to previous
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @foreach($themes as $theme)
            @php
                $isActive = $theme->slug === $activeSlug;
            @endphp

            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-md p-4 flex flex-col justify-between transition transform hover:shadow-xl hover:scale-[var(--hover-scale)]">
                <div class="flex items-start gap-3">
                    <div class="h-10 w-10 rounded-2xl bg-gradient-to-br from-indigo-500 to-sky-500 flex items-center justify-center text-xs font-bold text-white shadow">
                        {{ strtoupper(substr($theme->name, 0, 2)) }}
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-50">
                            {{ $theme->name }}
                        </h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">
                            slug: <span class="font-mono">{{ $theme->slug }}</span>
                        </p>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                            Version {{ $theme->version ?? '1.0.0' }} · {{ $theme->author ?: 'Unknown author' }}
                        </p>
                    </div>
                    @if($isActive)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/10 text-emerald-600 border border-emerald-200">
                            Active
                        </span>
                    @endif
                </div>

                @if($theme->screenshot)
                    <div class="mt-4 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-800">
                        <img src="{{ asset($theme->screenshot) }}" alt="{{ $theme->name }} preview" class="w-full h-32 object-cover">
                    </div>
                @endif

                <div class="mt-4 flex items-center justify-between gap-3 text-[11px]">
                    <div class="text-slate-500 dark:text-slate-400">
                        Path: <span class="font-mono">{{ $theme->path }}</span>
                    </div>

                    @if(!$isActive)
                        <form method="POST" action="{{ route('admin.themes.activate', $theme) }}">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center px-3 py-1.5 rounded-full text-[11px] font-semibold cursor-pointer
                                           border border-slate-300 dark:border-slate-600 bg-slate-900 dark:bg-slate-100 text-slate-50 dark:text-slate-900
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
@endsection
