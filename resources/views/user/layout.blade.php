    <!DOCTYPE html>
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ sidebarOpen: false }" x-cloak>
    <head>
        <meta charset="utf-8">
        <title>@yield('page_title', config('app.name').' | Dashboard')</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @php
            use App\Models\Setting;

            $globalSeo = [
                'meta_title'       => Setting::getValue('seo', 'meta_title'),
                'meta_description' => Setting::getValue('seo', 'meta_description'),
                'meta_keywords'    => Setting::getValue('seo', 'meta_keywords'),
            ];

            $seoTitle = $globalSeo['meta_title'] ?? config('app.name', 'LinkInsight Pro');
            $seoDescription = $globalSeo['meta_description'] ?? '';
            $seoKeywords = $globalSeo['meta_keywords'] ?? null;
        @endphp

        <meta name="title" content="{{ $seoTitle }}">
        @if($seoDescription)
            <meta name="description" content="{{ $seoDescription }}">
        @endif
        @if($seoKeywords)
            <meta name="keywords" content="{{ $seoKeywords }}">
        @endif

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            :root {
                --color-primary: {{ app_color('primary_color') }};
                --color-secondary: {{ app_color('secondary_color') }};
                --color-accent: {{ app_color('accent_color') }};
                --color-background: {{ app_color('background_color') }};
                --color-card: {{ app_color('card_color') }};
                --color-border: {{ app_color('border_color') }};
                --color-text-primary: {{ app_color('text_primary') }};
                --color-text-secondary: {{ app_color('text_secondary') }};
                --btn-radius: {{ app_color('button_radius', '0.75rem') }};
                --hover-scale: {{ app_color('hover_scale', '1.05') }};
            }

            body {
                background-color: var(--color-background);
                color: var(--color-text-primary);
            }
        </style>
    </head>

    <body class="min-h-screen font-sans antialiased"
        x-data="{ darkMode: false }"
        x-init="
            darkMode = localStorage.getItem('linkinsight_theme') === 'dark';
            if (darkMode) { document.documentElement.classList.add('dark'); }
        "
        x-on:toggle-dark.window="
            darkMode = !darkMode;
            if (darkMode) {
                document.documentElement.classList.add('dark');
                localStorage.setItem('linkinsight_theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('linkinsight_theme', 'light');
            }
        ">

    <div class="flex min-h-screen bg-slate-900 dark:bg-slate-950">
        {{-- Sidebar --}}
        <aside class="hidden lg:flex flex-col w-64 bg-slate-950 border-r border-slate-800">
            <div class="h-20 flex items-center px-6 border-b border-slate-800">
                <div class="flex items-center gap-2">
                    <div>
                        <div class="text-sm font-semibold tracking-tight text-slate-50">LinkInsight Pro</div>
                        <div class="text-[11px] text-slate-400">User dashboard</div>
                    </div>
                </div>
            </div>

            <nav class="flex-1 px-3 py-4 space-y-1 text-sm text-slate-200">
                <x-admin.sidebar-item
                    :href="route('dashboard')"
                    icon="grid"
                    label="Dashboard"
                    :active="request()->routeIs('dashboard')" />

                <div class="mt-4 mb-2 px-3">
                    <div class="text-[10px] uppercase tracking-wider text-slate-400">
                        LinkedIn
                    </div>
                </div>

                <x-admin.sidebar-item
                    :href="route('user.linkedin.profiles.index')"
                    icon="users"
                    label="Profiles"
                    :active="request()->routeIs('user.linkedin.profiles.*')" />

                <x-admin.sidebar-item
                    :href="route('user.linkedin.analytics.index')"
                    icon="bar-chart-2"
                    label="Analytics"
                    :active="request()->routeIs('user.linkedin.analytics.*')" />

                <x-admin.sidebar-item
                    :href="route('user.linkedin.audience_insights.index')"
                    icon="activity"
                    label="Audience Insights"
                    :active="request()->routeIs('user.linkedin.audience_insights.*')" />

                <x-admin.sidebar-item
                    :href="route('user.linkedin.demographics.index')"
                    icon="pie-chart"
                    label="Demographics"
                    :active="request()->routeIs('user.linkedin.demographics.*')" />

                <x-admin.sidebar-item
                    :href="route('user.linkedin.creator_metrics.index')"
                    icon="trending-up"
                    label="Creator Metrics"
                    :active="request()->routeIs('user.linkedin.creator_metrics.*')" />

                <x-admin.sidebar-item
                    :href="route('user.linkedin.connections.index')"
                    icon="user-plus"
                    label="Connections"
                    :active="request()->routeIs('user.linkedin.connections.*')" />

                <x-admin.sidebar-item
                    :href="route('user.linkedin.sync_jobs.index')"
                    icon="refresh-cw"
                    label="Sync Jobs"
                    :active="request()->routeIs('user.linkedin.sync_jobs.*')" />

                <div class="mt-4 mb-2 px-3">
                    <div class="text-[10px] uppercase tracking-wider text-slate-400">
                        Account
                    </div>
                </div>

                <x-admin.sidebar-item
                    :href="route('profile.edit')"
                    icon="settings"
                    label="Account settings"
                    :active="request()->routeIs('profile.*')" />

                <div class="mt-6 pt-4 border-t border-slate-800">
                    <button type="button"
                            @click="$dispatch('toggle-dark')"
                            class="w-full flex items-center justify-between px-3 py-2 rounded-2xl bg-slate-900 border border-slate-700 text-xs cursor-pointer shadow-md hover:scale-[var(--hover-scale)] transition">
                        <span class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                :class="darkMode ? 'text-yellow-300' : 'text-slate-300'"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 3v2.25M18.364 5.636l-1.59 1.59M21 12h-2.25M18.364 18.364l-1.59-1.59M12 18.75V21M7.227 16.773l-1.59 1.59M5.25 12H3M7.227 7.227l-1.59-1.59M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                            </svg>
                            <span>Dark mode</span>
                        </span>
                        <span class="text-[10px]" x-text="darkMode ? 'On' : 'Off'"></span>
                    </button>
                </div>
            </nav>

            <div class="p-4 border-t border-slate-800">
                <div class="bg-gradient-to-r from-indigo-500 to-sky-500 rounded-2xl p-3 shadow-2xl">
                    <div class="text-xs text-slate-100 mb-1 font-semibold">
                        Plan status
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-slate-100/90">
                        <span>Personal workspace</span>
                        <span class="px-2 py-0.5 rounded-full bg-slate-900/40">Active</span>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Mobile sidebar --}}
        <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 flex lg:hidden">
            <div class="fixed inset-0 bg-black/40" @click="sidebarOpen = false"></div>

            <aside class="relative z-50 w-64 bg-slate-950 border-r border-slate-800 flex flex-col">
                <div class="h-16 flex items-center justify-between px-4 border-b border-slate-800">
                    <div class="flex items-center gap-2">
                        <div class="h-8 w-8 rounded-2xl bg-gradient-to-br from-indigo-500 to-sky-500 flex items-center justify-center text-[11px] font-bold text-white shadow-xl">
                            LI
                        </div>
                        <div>
                            <div class="text-xs font-semibold tracking-tight text-slate-50">LinkInsight Pro</div>
                            <div class="text-[10px] text-slate-400">User dashboard</div>
                        </div>
                    </div>

                    <button @click="sidebarOpen = false"
                            class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-slate-900 border border-slate-700 text-slate-300 cursor-pointer">
                        ✕
                    </button>
                </div>

                <nav class="flex-1 px-3 py-4 space-y-1 text-sm text-slate-200">
                    <x-admin.sidebar-item
                        :href="route('dashboard')"
                        icon="grid"
                        label="Dashboard"
                        :active="request()->routeIs('dashboard')" />

                    <div class="mt-4 mb-2 px-3">
                        <div class="text-[10px] uppercase tracking-wider text-slate-400">
                            LinkedIn
                        </div>
                    </div>

                    <x-admin.sidebar-item
                        :href="route('user.linkedin.profiles.index')"
                        icon="users"
                        label="Profiles"
                        :active="request()->routeIs('user.linkedin.profiles.*')" />

                    <x-admin.sidebar-item
                        :href="route('user.linkedin.analytics.index')"
                        icon="bar-chart-2"
                        label="Analytics"
                        :active="request()->routeIs('user.linkedin.analytics.*')" />

                    <x-admin.sidebar-item
                        :href="route('user.linkedin.audience_insights.index')"
                        icon="activity"
                        label="Audience Insights"
                        :active="request()->routeIs('user.linkedin.audience_insights.*')" />

                    <x-admin.sidebar-item
                        :href="route('user.linkedin.demographics.index')"
                        icon="pie-chart"
                        label="Demographics"
                        :active="request()->routeIs('user.linkedin.demographics.*')" />

                    <x-admin.sidebar-item
                        :href="route('user.linkedin.creator_metrics.index')"
                        icon="trending-up"
                        label="Creator Metrics"
                        :active="request()->routeIs('user.linkedin.creator_metrics.*')" />

                    <x-admin.sidebar-item
                        :href="route('user.linkedin.connections.index')"
                        icon="user-plus"
                        label="Connections"
                        :active="request()->routeIs('user.linkedin.connections.*')" />

                    <x-admin.sidebar-item
                        :href="route('user.linkedin.sync_jobs.index')"
                        icon="refresh-cw"
                        label="Sync Jobs"
                        :active="request()->routeIs('user.linkedin.sync_jobs.*')" />

                    <div class="mt-4 mb-2 px-3">
                        <div class="text-[10px] uppercase tracking-wider text-slate-400">
                            Account
                        </div>
                    </div>

                    <x-admin.sidebar-item
                        :href="route('profile.edit')"
                        icon="settings"
                        label="Account settings"
                        :active="request()->routeIs('profile.*')" />

                    <form method="POST" action="{{ route('logout') }}" class="mt-2 px-1">
                        @csrf
                        <button type="submit"
                                class="w-full inline-flex items-center justify-between px-3 py-2 rounded-2xl bg-slate-900 border border-slate-700 text-xs cursor-pointer shadow-md hover:scale-[var(--hover-scale)] transition">
                            <span class="text-slate-200">Sign out</span>
                            <span class="text-[10px] text-slate-400">Logout</span>
                        </button>
                    </form>
                </nav>
            </aside>
        </div>

        {{-- Main area --}}
        <div class="flex-1 flex flex-col bg-slate-100 dark:bg-slate-900/60">
            {{-- Top navbar --}}
            <header class="h-20 bg-slate-100/90 dark:bg-slate-900/80 backdrop-blur border-b border-slate-200 dark:border-slate-800 flex items-center">
                <div class="flex-1 px-4 lg:px-8 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <button class="lg:hidden inline-flex items-center justify-center h-10 w-10 rounded-2xl bg-white dark:bg-slate-900 shadow-md border border-slate-200 dark:border-slate-700 cursor-pointer transition transform hover:scale-[var(--hover-scale)]"
                                @click="sidebarOpen = true">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-600 dark:text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <div>
                            <h1 class="text-base lg:text-lg font-semibold text-slate-800 dark:text-slate-50">
                                @yield('page_title', 'Dashboard')
                            </h1>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                @yield('page_subtitle', 'Monitor your LinkedIn growth and activity.')
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <button type="button"
                                @click="$dispatch('toggle-dark')"
                                class="hidden md:inline-flex items-center justify-center h-9 w-9 rounded-full bg-white dark:bg-slate-900 shadow-md border border-slate-200 dark:border-slate-700 cursor-pointer transition transform hover:scale-[var(--hover-scale)]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                :class="darkMode ? 'text-yellow-300' : 'text-slate-500'"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 3v2.25M18.364 5.636l-1.59 1.59M21 12h-2.25M18.364 18.364l-1.59-1.59M12 18.75V21M7.227 16.773l-1.59 1.59M5.25 12H3M7.227 7.227l-1.59-1.59M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                            </svg>
                        </button>

                        <div class="flex items-center gap-3">
                            <div class="hidden md:flex flex-col items-end">
                                <span class="text-xs font-medium text-slate-700 dark:text-slate-100">{{ auth()->user()->name }}</span>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400">User</span>
                            </div>

                            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-500 to-sky-500 flex items-center justify-center text-xs font-semibold text-white shadow-md">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.75 7.5a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 19.125a7.125 7.125 0 0114.25 0A1.125 1.125 0 0117.625 20.25H6.375A1.125 1.125 0 014.5 19.125z" />
                                </svg>
                            </div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="hidden md:inline-flex items-center px-3 py-1.5 rounded-full text-[11px] font-semibold cursor-pointer border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 shadow-md text-slate-700 dark:text-slate-100 transition transform hover:scale-[var(--hover-scale)]">
                                    Sign out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 px-4 lg:px-8 py-6 lg:py-8">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
    </body>
    </html>

