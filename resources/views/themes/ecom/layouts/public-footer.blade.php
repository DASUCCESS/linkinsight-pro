{{-- resources/views/themes/modern/layouts/public-footer.blade.php --}}
@php
    use App\Models\Setting;

    $siteName = Setting::getValue('general', 'site_name', config('app.name', 'LinkInsight Pro'));
@endphp

<footer class="modern-footer mt-24">
    {{-- Purple CTA-like band above footer, reused from theme colors --}}
    <div class="modern-footer-top">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col md:flex-row items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold tracking-[0.16em] uppercase text-violet-100">Grow with insights</p>
                <h3 class="mt-1 text-lg md:text-xl font-semibold text-white">
                    Turn LinkedIn data into a growth engine for your business.
                </h3>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                @if(Route::has('register'))
                    <a href="{{ route('register') }}" class="modern-btn-invert text-xs sm:text-sm">
                        Get started free
                    </a>
                @endif
                <a href="{{ route('page.show', 'contact') }}" class="modern-btn-ghost text-xs sm:text-sm">
                    Talk to sales
                </a>
            </div>
        </div>
    </div>

    <div class="bg-slate-950 text-slate-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-8 text-xs">
                <div class="space-y-3 md:col-span-2">
                    <div class="inline-flex items-center gap-2">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-sky-500 text-white text-xs font-bold shadow-xl">
                            LI
                        </span>
                        <span class="font-semibold text-slate-50 text-sm">{{ $siteName }}</span>
                    </div>
                    <p class="text-[11px] text-slate-400 leading-relaxed max-w-xs">
                        Unified LinkedIn analytics, content performance and automation in one opinionated dashboard.
                    </p>
                    <div class="flex flex-wrap items-center gap-3 text-[11px] text-slate-400">
                        <span class="inline-flex items-center gap-1">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                            Uptime monitored
                        </span>
                        <span class="inline-flex items-center gap-1">
                            <span class="h-3 w-3 rounded-full border border-slate-500 flex items-center justify-center text-[8px]">S</span>
                            Self-hosted
                        </span>
                    </div>
                </div>

                <div>
                    <h4 class="text-[11px] font-semibold text-slate-200 uppercase tracking-wide mb-3">Product</h4>
                    <ul class="space-y-2 text-[11px] text-slate-400">
                        <li><a href="{{ route('home') }}" class="modern-footer-link">Overview</a></li>
                        <li><a href="{{ route('page.show', 'about') }}" class="modern-footer-link">How it works</a></li>
                        <li><a href="{{ route('page.show', 'faq') }}" class="modern-footer-link">Pricing & FAQ</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-[11px] font-semibold text-slate-200 uppercase tracking-wide mb-3">Company</h4>
                    <ul class="space-y-2 text-[11px] text-slate-400">
                        <li><a href="{{ route('page.show', 'contact') }}" class="modern-footer-link">Contact</a></li>
                        <li><a href="{{ route('page.show', 'terms') }}" class="modern-footer-link">Terms</a></li>
                        <li><a href="{{ route('page.show', 'privacy') }}" class="modern-footer-link">Privacy</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-[11px] font-semibold text-slate-200 uppercase tracking-wide mb-3">Get started</h4>
                    <div class="space-y-2 text-[11px] text-slate-400">
                        @if(Route::has('register'))
                            <a href="{{ route('register') }}"
                               class="inline-flex items-center justify-center px-3 py-1.5 rounded-full text-[11px] font-semibold bg-slate-50 text-slate-900 shadow-xl cursor-pointer transition transform hover:scale-[var(--hover-scale)]">
                                Start free trial
                            </a>
                        @endif
                        <p class="text-[11px] text-slate-500 mt-1">
                            No long term contracts. Cancel any time.
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex flex-col md:flex-row items-center justify-between gap-3 border-t border-slate-800 pt-4">
                <p class="text-[11px] text-slate-500">
                    © {{ date('Y') }} {{ $siteName }}. All rights reserved.
                </p>
                <div class="flex items-center gap-3 text-[11px] text-slate-500">
                    <a href="{{ route('page.show', 'privacy') }}" class="modern-footer-link">Privacy</a>
                    <span class="h-1 w-1 rounded-full bg-slate-700"></span>
                    <a href="{{ route('page.show', 'terms') }}" class="modern-footer-link">Terms</a>
                </div>
            </div>
        </div>
    </div>
</footer>
