@if($hero && $hero->is_visible)
    @php
        $heroImage = $hero->image_path ? asset('storage/'.$hero->image_path) : null;
    @endphp

    <section id="hero" class="py-16" style="background-color: var(--color-background);">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
                {{-- Text side --}}
                <div class="space-y-5">
                    @if($hero->subtitle)
                        <p class="inline-flex items-center rounded-full border px-3 py-1 text-[11px] font-semibold"
                           style="border-color: var(--color-border);
                                  background: var(--color-card);
                                  color: var(--color-text-primary);">
                            <span class="h-1.5 w-1.5 rounded-full mr-2"
                                  style="background-color: var(--color-accent);"></span>
                            {{ $hero->subtitle }}
                        </p>
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
                               class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-semibold text-white cursor-pointer transition-transform duration-200 hover:-translate-y-0.5 hover:scale-[var(--hover-scale)] li-btn"
                               style="background: var(--color-primary); border-radius: var(--btn-radius);">
                                Get started
                            </a>
                        @endif

                        @if(Route::has('login'))
                            <a href="{{ route('login') }}"
                               class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-semibold cursor-pointer transition-transform duration-200 hover:-translate-y-0.5 hover:scale-[var(--hover-scale)] li-btn-outline"
                               style="border-radius: var(--btn-radius);">
                                View dashboard
                            </a>
                        @endif
                    </div>

                </div>

                {{-- Image side --}}
                <div>
                    <div class="li-micro-card rounded-3xl p-4 lg:p-5"
                         style="background: var(--color-card);
                                border: 1px var(--color-border);
                                box-shadow: 0 18px 55px rgba(15,23,42,0.25);">
                        @if($heroImage)
                            <div class="rounded-2xl overflow-hidden">
                                <img src="{{ $heroImage }}"
                                     alt="{{ $hero->title ?? 'Hero image' }}"
                                     class="w-full h-64 lg:h-72 object-cover">
                            </div>
                        @else
                            <div class="h-64 lg:h-72 flex items-center justify-center text-xs text-center rounded-2xl border border-dashed"
                                 style="border-color: var(--color-border);
                                        color: var(--color-text-secondary);
                                        background: var(--color-background);">
                                <span>No hero image set. Upload one from “Homepage sections → hero”.</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
