@php
    use App\Models\Setting;

    $siteName = Setting::getValue('general', 'site_name', config('app.name', 'LinkInsight Pro'));
@endphp

<footer class="border-t border-slate-200 bg-white/90 backdrop-blur mt-12">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-sm">
            <div class="space-y-3">
                <div class="inline-flex items-center gap-2">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-sky-500 text-white text-xs font-bold shadow">
                        LI
                    </span>
                    <span class="font-semibold text-slate-900 text-sm">{{ $siteName }}</span>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Data driven LinkedIn analytics, automation and growth insights for modern teams.
                </p>
            </div>

            <div>
                <h4 class="text-xs font-semibold text-slate-900 uppercase tracking-wide mb-3">Product</h4>
                <ul class="space-y-2 text-xs text-slate-600">
                    <li><a href="{{ route('home') }}" class="hover:text-slate-900 cursor-pointer">Overview</a></li>
                    <li><a href="{{ route('page.show', 'faq') }}" class="hover:text-slate-900 cursor-pointer">FAQ</a></li>
                    <li><a href="{{ route('page.show', 'about') }}" class="hover:text-slate-900 cursor-pointer">About</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-xs font-semibold text-slate-900 uppercase tracking-wide mb-3">Company</h4>
                <ul class="space-y-2 text-xs text-slate-600">
                    <li><a href="{{ route('page.show', 'contact') }}" class="hover:text-slate-900 cursor-pointer">Contact</a></li>
                    <li><a href="{{ route('page.show', 'terms') }}" class="hover:text-slate-900 cursor-pointer">Terms</a></li>
                    <li><a href="{{ route('page.show', 'privacy') }}" class="hover:text-slate-900 cursor-pointer">Privacy</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-xs font-semibold text-slate-900 uppercase tracking-wide mb-3">Get started</h4>
                <div class="space-y-2 text-xs text-slate-600">
                    @if(Route::has('register'))
                        <a href="{{ route('register') }}"
                           class="inline-flex items-center justify-center px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-900 text-white shadow-xl cursor-pointer transition transform hover:scale-[var(--hover-scale)]">
                            Start free trial
                        </a>
                    @endif>
                    <p class="text-[11px] text-slate-500">
                        No long term contracts. Cancel any time.
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-8 flex flex-col md:flex-row items-center justify-between gap-3 border-t border-slate-100 pt-4">
            <p class="text-[11px] text-slate-400">
                © {{ date('Y') }} {{ $siteName }}. All rights reserved.
            </p>
            <div class="flex items-center gap-3 text-[11px] text-slate-400">
                <a href="{{ route('page.show', 'privacy') }}" class="hover:text-slate-700 cursor-pointer">Privacy</a>
                <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                <a href="{{ route('page.show', 'terms') }}" class="hover:text-slate-700 cursor-pointer">Terms</a>
            </div>
        </div>
    </div>
</footer>
