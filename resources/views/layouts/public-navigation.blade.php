@php
    use App\Models\Setting;

    $siteName = Setting::getValue('general', 'site_name', config('app.name', 'LinkInsight Pro'));
    $logoPath = Setting::getValue('general', 'logo');
@endphp

<nav x-data="{ open: false }" class="bg-white/90 backdrop-blur border-b border-slate-200">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    @if($logoPath)
                        <img src="{{ asset('storage/'.$logoPath) }}" alt="{{ $siteName }}" class="h-8 w-auto object-contain">
                    @else
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-sky-500 text-white text-xs font-bold shadow">
                            LI
                        </span>
                    @endif
                    <span class="text-sm font-semibold text-slate-800">{{ $siteName }}</span>
                </a>
            </div>

            <div class="hidden md:flex items-center gap-8 text-sm">
                <a href="{{ route('home') }}" class="text-slate-600 hover:text-slate-900 cursor-pointer transition">
                    Home
                </a>
                <a href="{{ route('page.show', 'about') }}" class="text-slate-600 hover:text-slate-900 cursor-pointer transition">
                    About
                </a>
                <a href="{{ route('page.show', 'faq') }}" class="text-slate-600 hover:text-slate-900 cursor-pointer transition">
                    FAQ
                </a>
                <a href="{{ route('page.show', 'contact') }}" class="text-slate-600 hover:text-slate-900 cursor-pointer transition">
                    Contact
                </a>
            </div>

            <div class="hidden md:flex items-center gap-3 text-sm">
                @auth
                    <a href="{{ route('admin.dashboard') }}"
                       class="px-3 py-1.5 rounded-full border border-slate-200 text-slate-700 bg-white shadow-sm cursor-pointer transition transform hover:scale-[var(--hover-scale)]">
                        Admin
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="px-3 py-1.5 rounded-full border border-slate-200 text-slate-700 bg-white shadow-sm cursor-pointer transition transform hover:scale-[var(--hover-scale)]">
                        Login
                    </a>
                @endauth
            </div>

            <div class="flex md:hidden">
                <button type="button"
                        @click="open = !open"
                        class="inline-flex items-center justify-center rounded-md p-2 text-slate-600 hover:bg-slate-100 focus:outline-none cursor-pointer">
                    <span class="sr-only">Open main menu</span>
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                         viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path x-show="!open" stroke-linecap="round" stroke-linejoin="round"
                              d="M4 6h16M4 12h16M4 18h16" />
                        <path x-show="open" stroke-linecap="round" stroke-linejoin="round"
                              d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div class="md:hidden" x-show="open" x-cloak>
        <div class="space-y-1 px-4 pb-4 pt-2 text-sm">
            <a href="{{ route('home') }}" class="block px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100 cursor-pointer">
                Home
            </a>
            <a href="{{ route('page.show', 'about') }}" class="block px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100 cursor-pointer">
                About
            </a>
            <a href="{{ route('page.show', 'faq') }}" class="block px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100 cursor-pointer">
                FAQ
            </a>
            <a href="{{ route('page.show', 'contact') }}" class="block px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100 cursor-pointer">
                Contact
            </a>

            @auth
                <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100 cursor-pointer">
                    Admin
                </a>
            @else
                <a href="{{ route('login') }}" class="block px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100 cursor-pointer">
                    Login
                </a>
            @endauth
        </div>
    </div>
</nav>
