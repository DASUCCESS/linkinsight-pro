{{-- resources/views/themes/modern/pages/partials/home-why-us.blade.php --}}
@if($whyUs && $whyUs->is_visible)
    <section id="why-us" class="py-16 relative overflow-hidden">
        <div class="pointer-events-none absolute inset-0 opacity-40"
             style="background:
                radial-gradient(circle at 12% 65%, color-mix(in srgb, var(--color-accent) 18%, transparent) 0, transparent 60%),
                radial-gradient(circle at 88% 20%, color-mix(in srgb, var(--color-primary) 16%, transparent) 0, transparent 60%);">
        </div>

        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-8 text-center">
                <p class="text-[11px] font-semibold tracking-[0.2em] uppercase" style="color: var(--color-accent);">
                    Why teams choose this
                </p>
                <h2 class="mt-2 text-2xl md:text-3xl font-semibold" style="color: var(--color-text-primary);">
                    {{ $whyUs->title ?: 'Why choose LinkInsight Pro' }}
                </h2>
                @if($whyUs->subtitle)
                    <p class="mt-2 text-sm mx-auto max-w-2xl" style="color: var(--color-text-secondary);">
                        {{ $whyUs->subtitle }}
                    </p>
                @endif
            </div>

            @if($whyUs->body)
                <div class="cms-content cms-feature-grid-2 text-sm"
                     style="color: var(--color-text-secondary);">
                    {!! $whyUs->body !!}
                </div>
            @endif
        </div>
    </section>
@endif
