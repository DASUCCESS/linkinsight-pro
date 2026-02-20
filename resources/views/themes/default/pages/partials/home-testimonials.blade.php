@if($testimonials && $testimonials->is_visible)
    <section id="testimonials" class="py-16" style="background-color: var(--color-background);">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($testimonials->title)
                <h2 class="text-2xl font-semibold text-center mb-2 li-reveal li-reveal-1"
                    style="color: var(--color-text-primary);">
                    {{ $testimonials->title }}
                </h2>
            @endif

            @if($testimonials->subtitle)
                <p class="text-sm text-center mb-8 li-reveal li-reveal-2"
                   style="color: var(--color-text-secondary);">
                    {{ $testimonials->subtitle }}
                </p>
            @endif

            @if($testimonials->body)
                <div
                    class="li-testimonial-shell li-reveal li-reveal-3 transform-gpu"
                    style="
                        border-radius: 1.75rem;
                        border: 1px solid var(--color-border);
                        background: linear-gradient(
                            180deg,
                            color-mix(in srgb, var(--color-card) 70%, rgba(255,255,255,0.25)) 0%,
                            color-mix(in srgb, var(--color-background) 92%, transparent) 100%
                        );
                        box-shadow: 0 22px 75px rgba(15,23,42,0.20);
                        padding: 1.5rem;
                        cursor: pointer;
                        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
                    "
                    onmouseover="this.style.transform='translateY(-6px) scale(' + getComputedStyle(document.documentElement).getPropertyValue('--hover-scale') + ')'; this.style.borderColor='var(--color-primary)'; this.style.boxShadow='0 34px 110px rgba(15,23,42,0.32)';"
                    onmouseout="this.style.transform=''; this.style.borderColor='var(--color-border)'; this.style.boxShadow='0 22px 75px rgba(15,23,42,0.20)';"
                >
                    <div class="cms-content text-sm" style="color: var(--color-text-secondary);">
                        {!! $testimonials->body !!}
                    </div>
                </div>
            @endif
        </div>
    </section>
@endif
