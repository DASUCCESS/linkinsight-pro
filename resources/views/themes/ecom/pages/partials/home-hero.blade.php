{{-- resources/views/themes/modern/pages/partials/home-hero.blade.php --}}
@if($hero && $hero->is_visible)
    @php
        $heroImage = $hero->image_path ? asset('storage/'.$hero->image_path) : null;
    @endphp

    <section id="hero" class="pt-12 pb-16 md:pt-16 md:pb-20">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="modern-hero-shell grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
                <div class="space-y-5">
                    @if($hero->subtitle)
                        <div class="modern-hero-tag">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                            {{ $hero->subtitle }}
                        </div>
                    @endif

                    @if($hero->title)
                        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-semibold tracking-tight"
                            style="color: var(--color-text-primary);">
                            {{ $hero->title }}
                        </h1>
                    @endif

                    @if($hero->body)
                        <div class="cms-content max-w-xl text-sm sm:text-base"
                             style="color: var(--color-text-secondary);">
                            {!! $hero->body !!}
                        </div>
                    @endif

                    <div class="flex flex-wrap items-center gap-3 pt-2">
                        @if(Route::has('register'))
                            <a href="{{ route('register') }}"
                               class="modern-btn-primary">
                                Try for free
                            </a>
                        @endif

                        @if(Route::has('login'))
                            <a href="{{ route('login') }}"
                               class="modern-btn-outline">
                                View dashboard
                            </a>
                        @endif
                    </div>

                    {{-- Trust row – still generic but matches layout --}}
                    <div class="modern-trust-row">
                        <div class="modern-trust-pill">
                            <span class="inline-flex">
                                @for($i = 0; $i < 5; $i++)
                                    <svg class="h-3 w-3 text-amber-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.802 2.036a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.802-2.036a1 1 0 00-1.175 0l-2.802 2.036c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                @endfor
                            </span>
                            <span>4.8/5 average rating</span>
                        </div>
                        <div class="modern-trust-pill">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                            <span>Trusted by teams managing LinkedIn funnels</span>
                        </div>
                        <div class="modern-trust-pill">
                            <span class="h-1.5 w-1.5 rounded-full bg-indigo-400"></span>
                            <span>Sync profiles, posts and audience metrics in one place</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="modern-hero-image-card">
                        @if($heroImage)
                            <img src="{{ $heroImage }}"
                                 alt="{{ $hero->title ?? 'Hero image' }}"
                                 class="w-full h-64 md:h-80 object-cover">
                        @else
                            <div class="h-64 md:h-80 flex items-center justify-center text-xs text-center text-slate-300">
                                <span>No hero image set. Upload one from “Homepage sections → hero”.</span>
                            </div>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-[11px] text-slate-500">
                        <div class="flex items-center gap-2 bg-white/80 border border-slate-200 rounded-xl px-3 py-2 shadow-sm">
                            <div class="h-7 w-7 rounded-full bg-indigo-100 flex items-center justify-center text-[11px] font-semibold text-indigo-700">
                                LI
                            </div>
                            <div>
                                <div class="font-semibold text-slate-800 text-xs">Profile analytics</div>
                                <div>Connections, followers, search appearances</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 bg-white/80 border border-slate-200 rounded-xl px-3 py-2 shadow-sm">
                            <div class="h-7 w-7 rounded-full bg-sky-100 flex items-center justify-center text-[11px] font-semibold text-sky-700">
                                📈
                            </div>
                            <div>
                                <div class="font-semibold text-slate-800 text-xs">Content insights</div>
                                <div>Posts, impressions, engagement funnels</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
