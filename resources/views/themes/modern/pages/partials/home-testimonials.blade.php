{{-- resources/views/themes/modern/pages/partials/home-testimonials.blade.php --}}
@if($testimonials && $testimonials->is_visible)
    <section id="testimonials" class="py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            @if($testimonials->title)
                <h2 class="text-2xl font-semibold mb-2"
                    style="color: var(--color-text-primary);">
                    {{ $testimonials->title }}
                </h2>
            @endif

            @if($testimonials->subtitle)
                <p class="text-sm mb-6"
                   style="color: var(--color-text-secondary);">
                    {{ $testimonials->subtitle }}
                </p>
            @endif

            @if($testimonials->body)
                <div class="modern-feature-card mx-auto max-w-2xl bg-white/90">
                    <div class="cms-content text-sm" style="color: var(--color-text-secondary);">
                        {!! $testimonials->body !!}
                    </div>
                </div>
            @endif
        </div>
    </section>
@endif
