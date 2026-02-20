@if($whyUs && $whyUs->is_visible)
    <section id="why-us" class="py-16 relative overflow-hidden"
             style="background: linear-gradient(180deg, var(--color-background) 0%, color-mix(in srgb, var(--color-background) 86%, var(--color-secondary)) 100%);">
        <div class="pointer-events-none absolute inset-0 opacity-55"
             style="background:
                radial-gradient(circle at 18% 65%, color-mix(in srgb, var(--color-accent) 14%, transparent) 0, transparent 60%),
                radial-gradient(circle at 88% 25%, color-mix(in srgb, var(--color-primary) 12%, transparent) 0, transparent 58%);">
        </div>

        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-10 text-center li-reveal li-reveal-1">
                <p class="text-[11px] font-semibold tracking-[0.2em] uppercase" style="color: var(--color-accent);">
                    Why this product
                </p>
                <h2 class="mt-2 text-2xl font-semibold" style="color: var(--color-text-primary);">
                    {{ $whyUs->title ?: 'Why choose LinkInsight Pro' }}
                </h2>
                @if($whyUs->subtitle)
                    <p class="mt-2 text-sm mx-auto max-w-2xl" style="color: var(--color-text-secondary);">
                        {{ $whyUs->subtitle }}
                    </p>
                @endif
            </div>

            @if($whyUs->body)
                <div class="cms-content cms-feature-grid-2 text-sm li-reveal li-reveal-2"
                     style="color: var(--color-text-secondary);">
                    {!! $whyUs->body !!}
                </div>
            @endif
        </div>
    </section>
@endif
