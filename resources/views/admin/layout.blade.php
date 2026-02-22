<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ config('app.name') }} Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

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

        trix-editor {
            min-height: 120px;
            border-radius: 0.75rem;
            border: 1px solid rgb(226 232 240);
            padding: 0.5rem 0.75rem;
            background-color: rgb(15 23 42 / 0.02);
        }
        .dark trix-editor {
            border-color: rgb(51 65 85);
            background-color: rgb(15 23 42);
            color: rgb(226 232 240);
        }
        trix-toolbar {
            border-radius: 0.75rem 0.75rem 0 0;
            border: 1px solid rgb(226 232 240);
            border-bottom: 0;
            background-color: rgb(248 250 252);
        }
        .dark trix-toolbar {
            border-color: rgb(51 65 85);
            background-color: rgb(15 23 42);
        }
    </style>

    @stack('head')

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://unpkg.com/trix@2.0.6/dist/trix.umd.min.js"></script>
</head>

<body class="min-h-screen font-sans antialiased bg-slate-100 text-slate-800 dark:bg-slate-950 dark:text-slate-100">
<div class="flex min-h-screen bg-slate-100 dark:bg-slate-950">
    {{-- Desktop Sidebar --}}
    <aside class="hidden lg:flex flex-col w-64 bg-slate-950 dark:bg-slate-900 border-r border-slate-800">
        <div class="h-20 flex items-center px-6 border-b border-slate-800">
            <div class="flex items-center gap-2">
                <div>
                    <div class="text-sm font-semibold tracking-tight text-slate-50">LinkInsight Pro</div>
                    <div class="text-[11px] text-slate-400">Admin dashboard</div>
                </div>
            </div>
        </div>

        <nav class="flex-1 px-3 py-4 space-y-1 text-sm">
            <x-admin.sidebar-item
                :href="route('admin.dashboard')"
                icon="grid"
                label="Dashboard"
                :active="request()->routeIs('admin.dashboard')" />

            <x-admin.sidebar-item
                :href="route('admin.users.index')"
                icon="users"
                label="Users"
                :active="request()->routeIs('admin.users.*')" />

            <x-admin.sidebar-item
                :href="route('admin.analytics.index')"
                icon="bar-chart-2"
                label="Analytics"
                :active="request()->routeIs('admin.analytics.*')" />

            <x-admin.sidebar-item
                :href="route('admin.cms.pages.index')"
                icon="file-text"
                label="Website"
                :active="request()->routeIs('admin.cms.*')" />

            <x-admin.sidebar-item
                :href="route('admin.settings.edit')"
                icon="settings"
                label="Settings"
                :active="request()->routeIs('admin.settings.*')" />
            <x-admin.sidebar-item
                :href="route('admin.themes.index')"
                icon="layers"
                label="Themes"
                :active="request()->routeIs('admin.themes.*')" />

            <div class="mt-6 pt-4 border-t border-slate-800">
                <x-admin.sidebar-item href="#" icon="bell" label="Notifications" :active="false" />
            </div>
        </nav>

        <div class="p-4 border-t border-slate-800">
            <div class="bg-gradient-to-r from-indigo-500 to-sky-500 rounded-2xl p-3 shadow-2xl">
                <div class="text-xs text-slate-100 mb-1 font-semibold">System Status</div>
                <div class="flex items-center justify-between text-[11px] text-slate-100/90">
                    <span>Version 1.0.0</span>
                    <span class="px-2 py-0.5 rounded-full bg-slate-900/40">CodeCanyon ready</span>
                </div>
            </div>
        </div>
    </aside>

    {{-- Mobile Sidebar --}}
    <aside id="mobileSidebar"
           class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-950 dark:bg-slate-900 border-r border-slate-800 transform -translate-x-full transition-transform duration-200 lg:hidden">
        <div class="h-20 flex items-center px-6 border-b border-slate-800">
            <div class="flex items-center gap-2">
                <div>
                    <div class="text-sm font-semibold tracking-tight text-slate-50">LinkInsight Pro</div>
                    <div class="text-[11px] text-slate-400">Admin dashboard</div>
                </div>
            </div>
            <button type="button"
                    onclick="closeMobileSidebar()"
                    class="ml-auto inline-flex items-center justify-center h-8 w-8 rounded-xl bg-slate-900 border border-slate-700 text-slate-300 cursor-pointer transition hover:bg-slate-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <nav class="flex-1 px-3 py-4 space-y-1 text-sm overflow-y-auto">
            <x-admin.sidebar-item
                :href="route('admin.dashboard')"
                icon="grid"
                label="Dashboard"
                :active="request()->routeIs('admin.dashboard')" />

            <x-admin.sidebar-item
                :href="route('admin.users.index')"
                icon="users"
                label="Users"
                :active="request()->routeIs('admin.users.*')" />

            <x-admin.sidebar-item
                :href="route('admin.analytics.index')"
                icon="bar-chart-2"
                label="Analytics"
                :active="request()->routeIs('admin.analytics.*')" />

            <x-admin.sidebar-item
                :href="route('admin.settings.edit')"
                icon="settings"
                label="Settings"
                :active="request()->routeIs('admin.settings.*')" />

            <x-admin.sidebar-item
                :href="route('admin.themes.index')"
                icon="layers"
                label="Themes"
                :active="request()->routeIs('admin.themes.*')" />

            <div class="mt-6 pt-4 border-t border-slate-800">
                <x-admin.sidebar-item href="#" icon="bell" label="Notifications" :active="false" />
            </div>
        </nav>
    </aside>

    <div id="mobileSidebarBackdrop"
         onclick="closeMobileSidebar()"
         class="fixed inset-0 z-30 bg-black/40 opacity-0 pointer-events-none transition-opacity duration-200 lg:hidden"></div>

    {{-- Main area --}}
    <div class="flex-1 flex flex-col bg-slate-100 dark:bg-slate-900/40">
        {{-- Top navbar --}}
        <header class="h-20 bg-slate-100/90 dark:bg-slate-900/80 backdrop-blur border-b border-slate-200 dark:border-slate-800 flex items-center">
            <div class="flex-1 px-4 lg:px-8 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <button type="button"
                            onclick="openMobileSidebar()"
                            class="lg:hidden inline-flex items-center justify-center h-10 w-10 rounded-2xl bg-white dark:bg-slate-900 shadow-md border border-slate-200 dark:border-slate-700 cursor-pointer transition hover:shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-600 dark:text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div>
                        <h1 class="text-base lg:text-lg font-semibold text-slate-800 dark:text-slate-50">
                            @yield('page_title', 'Dashboard')
                        </h1>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            @yield('page_subtitle', 'Overview of your LinkedIn analytics platform.')
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3 lg:gap-4">
                    <div class="hidden md:flex items-center bg-white dark:bg-slate-900 rounded-full shadow-md border border-slate-200 dark:border-slate-700 px-3 py-1.5 w-56 lg:w-64">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                        </svg>
                        <input
                            class="ml-2 flex-1 text-xs bg-transparent outline-none text-slate-600 dark:text-slate-200 placeholder:text-slate-400 dark:placeholder:text-slate-500 border-0 focus:ring-0"
                            placeholder="Search in admin..." />
                    </div>

                    <button type="button"
                            onclick="toggleTheme()"
                            class="h-10 w-10 rounded-full bg-white dark:bg-slate-900 shadow-md border border-slate-200 dark:border-slate-700 flex items-center justify-center cursor-pointer transition hover:shadow-lg">
                        <svg id="themeToggleIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-700 dark:text-slate-100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"></svg>
                    </button>

                    <button class="relative h-10 w-10 rounded-full bg-white dark:bg-slate-900 shadow-md border border-slate-200 dark:border-slate-700 flex items-center justify-center cursor-pointer transition hover:shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-600 dark:text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5" />
                        </svg>
                        <span class="absolute top-1 right-1 inline-flex h-2 w-2 rounded-full bg-rose-500"></span>
                    </button>

                    <div class="flex items-center gap-3">
                        <div class="hidden md:flex flex-col items-end">
                            <span class="text-xs font-medium text-slate-700 dark:text-slate-100">{{ auth()->user()->name }}</span>
                            <span class="text-[10px] text-slate-500 dark:text-slate-400">Administrator</span>
                        </div>
                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-500 to-sky-500 flex items-center justify-center text-xs font-semibold text-white shadow-md">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="hidden md:inline-flex items-center px-3 py-1.5 rounded-full text-[11px] font-semibold cursor-pointer
                                           border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-100
                                           shadow-md transition hover:shadow-lg">
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

<script>
    (function () {
        const root = document.documentElement;
        const storedTheme = localStorage.getItem('linkinsight_theme');
        if (storedTheme === 'dark') root.classList.add('dark');
        else root.classList.remove('dark');
        updateThemeIcon();
    })();

    function toggleTheme() {
        const root = document.documentElement;
        const isDark = root.classList.toggle('dark');
        localStorage.setItem('linkinsight_theme', isDark ? 'dark' : 'light');
        updateThemeIcon();
    }

    function updateThemeIcon() {
        const icon = document.getElementById('themeToggleIcon');
        const isDark = document.documentElement.classList.contains('dark');
        if (!icon) return;

        if (isDark) {
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />';
        } else {
            icon.innerHTML =
                '<circle cx="12" cy="12" r="4" />' +
                '<path stroke-linecap="round" stroke-linejoin="round" d="M12 2v2M12 20v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M2 12h2M20 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42" />';
        }
    }

    function openMobileSidebar() {
        const sidebar = document.getElementById('mobileSidebar');
        const backdrop = document.getElementById('mobileSidebarBackdrop');
        if (!sidebar || !backdrop) return;
        sidebar.classList.remove('-translate-x-full');
        backdrop.classList.remove('pointer-events-none');
        backdrop.classList.remove('opacity-0');
    }

    function closeMobileSidebar() {
        const sidebar = document.getElementById('mobileSidebar');
        const backdrop = document.getElementById('mobileSidebarBackdrop');
        if (!sidebar || !backdrop) return;
        sidebar.classList.add('-translate-x-full');
        backdrop.classList.add('pointer-events-none');
        backdrop.classList.add('opacity-0');
    }
</script>

@stack('scripts')
</body>
</html>
