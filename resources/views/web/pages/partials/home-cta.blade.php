@if($cta && $cta->is_visible)
    <section id="cta" class="py-16" style="background-color: var(--color-primary);">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="li-cta-panel li-reveal li-reveal-1">
                <div class="relative text-center">
                    @if($cta->title)
                        <h2 class="text-2xl font-semibold mb-2"
                            style="color: var(--color-text-primary);">
                            {{ $cta->title }}
                        </h2>
                    @endif

                    @if($cta->subtitle)
                        <p class="text-sm mb-4"
                           style="color: var(--color-text-secondary);">
                            {{ $cta->subtitle }}
                        </p>
                    @endif

                    @if($cta->body)
                        <div class="cms-content text-sm mb-6 mx-auto max-w-2xl"
                             style="color: var(--color-text-secondary);">
                            {!! $cta->body !!}
                        </div>
                    @endif

                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        @if(Route::has('register'))
                            <a href="{{ route('register') }}"
                               class="inline-flex items-center justify-center px-6 py-3 text-sm font-semibold text-white cursor-pointer transition-transform duration-200 hover:-translate-y-0.5 hover:scale-[var(--hover-scale)] li-btn"
                               style="background: var(--color-primary); border-radius: var(--btn-radius);">
                                Start now
                            </a>
                        @endif

                        <a href="#faq"
                           class="inline-flex items-center justify-center px-6 py-3 text-sm font-semibold cursor-pointer transition-transform duration-200 hover:-translate-y-0.5 hover:scale-[var(--hover-scale)] li-btn-outline"
                           style="border-radius: var(--btn-radius);">
                            View FAQ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
