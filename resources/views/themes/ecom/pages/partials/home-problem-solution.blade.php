{{-- resources/views/themes/modern/pages/partials/home-problem-solution.blade.php --}}
@if(($problem && $problem->is_visible) || ($solution && $solution->is_visible))
    <section id="problem-solution" class="py-16">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-[1.1fr,minmax(0,1fr)] gap-10 items-start">
            @if($problem && $problem->is_visible)
                <div>
                    @if($problem->title)
                        <h2 class="text-2xl md:text-3xl font-semibold mb-2" style="color: var(--color-text-primary);">
                            {{ $problem->title }}
                        </h2>
                    @endif
                    @if($problem->subtitle)
                        <p class="text-sm mb-3" style="color: var(--color-text-secondary);">
                            {{ $problem->subtitle }}
                        </p>
                    @endif
                    @if($problem->body)
                        <div class="cms-content text-sm"
                             style="color: var(--color-text-secondary);">
                            {!! $problem->body !!}
                        </div>
                    @endif
                </div>
            @endif

            @if($solution && $solution->is_visible)
                <aside class="modern-feature-card bg-white">
                    @if($solution->title)
                        <h3 class="text-sm font-semibold mb-1" style="color: var(--color-text-primary);">
                            {{ $solution->title }}
                        </h3>
                    @endif
                    @if($solution->subtitle)
                        <p class="text-xs mb-3" style="color: var(--color-text-secondary);">
                            {{ $solution->subtitle }}
                        </p>
                    @endif
                    @if($solution->body)
                        <div class="cms-content text-xs"
                             style="color: var(--color-text-secondary);">
                            {!! $solution->body !!}
                        </div>
                    @endif
                </aside>
            @endif
        </div>
    </section>
@endif
