{{-- resources/views/themes/modern/layouts/public-navigation.blade.php --}}
@php
    use App\Models\Setting;

    $siteName = Setting::getValue('general', 'site_name', config('app.name', 'LinkInsight Pro'));
    $logoPath = Setting::getValue('general', 'logo');
    $topBarText = Setting::getValue('general', 'top_bar_text', 'Unlimited analytics and growth workflows – explore connections powered by AI.');
@endphp

<nav x-data="{ open: false }" class="modern-nav border-b border-slate-200/70">
    {{-- Top announcement bar --}}
    <div class="modern-nav-top">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-3 text-[11px]">
            <p class="text-slate-50/90 truncate">
                {{ $topBarText }}
            </p>
            <div class="hidden sm:flex items-center gap-3">
                <span class="inline-flex items-center gap-1 text-slate-100/90">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                    Live sync enabled
                </span>
            </div>
        </div>
    </div>

    {{-- Main navbar --}}
    <div class="bg-white/90 backdrop-blur">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 cursor-pointer">
                        @if($logoPath)
                            <img src="{{ asset('storage/'.$logoPath) }}" alt="{{ $siteName }}" class="h-8 w-auto object-contain">
                        @else
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-sky-500 text-white text-xs font-bold shadow-lg">
                                LI
                            </span>
                        @endif
                        <span class="text-sm font-semibold text-slate-800">{{ $siteName }}</span>
                    </a>
                </div>

                <div class="hidden md:flex items-center gap-6 text-xs lg:text-sm">
                    <a href="{{ route('home') }}" class="modern-nav-link">Overview</a>
                    <a href="{{ route('page.show', 'about') }}" class="modern-nav-link">Product</a>
                    <a href="{{ route('page.show', 'faq') }}" class="modern-nav-link">Pricing & FAQ</a>
                    <a href="{{ route('page.show', 'contact') }}" class="modern-nav-link">Contact</a>
                </div>

                <div class="hidden md:flex items-center gap-3 text-xs lg:text-sm">
                    @auth
                        <a href="{{ route('admin.dashboard') }}"
                           class="modern-btn-outline">
                            Admin
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="modern-nav-link font-semibold">
                            Log in
                        </a>
                        @if(Route::has('register'))
                            <a href="{{ route('register') }}"
                               class="modern-btn-primary">
                                Try for free
                            </a>
                        @endif
                    @endauth
                </div>

                {{-- Mobile toggle --}}
                <div class="flex md:hidden">
                    <button type="button"
                            @click="open = !open"
                            class="inline-flex items-center justify-center rounded-xl p-2 text-slate-600 hover:bg-slate-100 focus:outline-none cursor-pointer border border-slate-200">
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

        {{-- Mobile menu --}}
        <div class="md:hidden" x-show="open" x-cloak>
            <div class="space-y-1 px-4 pb-4 pt-2 text-sm">
                <a href="{{ route('home') }}" class="modern-mobile-link">Overview</a>
                <a href="{{ route('page.show', 'about') }}" class="modern-mobile-link">Product</a>
                <a href="{{ route('page.show', 'faq') }}" class="modern-mobile-link">Pricing & FAQ</a>
                <a href="{{ route('page.show', 'contact') }}" class="modern-mobile-link">Contact</a>

                @auth
                    <a href="{{ route('admin.dashboard') }}" class="modern-mobile-link">
                        Admin
                    </a>
                @else
                    <a href="{{ route('login') }}" class="modern-mobile-link">
                        Log in
                    </a>
                    @if(Route::has('register'))
                        <a href="{{ route('register') }}"
                           class="block mt-2 text-center modern-btn-primary w-full">
                            Try for free
                        </a>
                    @endif
                @endauth
            </div>
        </div>
    </div>
</nav>
