{{-- resources/views/themes/modern/pages/partials/home-cta.blade.php --}}
@if($cta && $cta->is_visible)
    <section id="cta" class="py-16">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="li-cta-panel"
                 style="
                    border-radius: 2rem;
                    background: linear-gradient(135deg,
                        color-mix(in srgb, var(--color-primary) 85%, #000) 0%,
                        color-mix(in srgb, var(--color-secondary) 75%, #000) 50%,
                        color-mix(in srgb, var(--color-accent) 35%, #000) 100%);
                    border-color: color-mix(in srgb, var(--color-primary) 65%, var(--color-border));
                    box-shadow: 0 34px 120px rgba(15,23,42,0.55);
                 ">
                <div class="relative text-center text-slate-50">
                    @if($cta->title)
                        <h2 class="text-2xl font-semibold mb-2">
                            {{ $cta->title }}
                        </h2>
                    @endif

                    @if($cta->subtitle)
                        <p class="text-sm mb-3 text-slate-100/90">
                            {{ $cta->subtitle }}
                        </p>
                    @endif

                    @if($cta->body)
                        <div class="cms-content text-sm mb-6 mx-auto max-w-2xl text-slate-100/85">
                            {!! $cta->body !!}
                        </div>
                    @endif

                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        @if(Route::has('register'))
                            <a href="{{ route('register') }}"
                               class="modern-btn-invert">
                                Start now
                            </a>
                        @endif

                        <a href="#faq"
                           class="modern-btn-ghost">
                            View FAQ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
