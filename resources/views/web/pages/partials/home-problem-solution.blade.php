@if(($problem && $problem->is_visible) || ($solution && $solution->is_visible))
    <section id="problem-solution" class="py-16" style="background-color: var(--color-background);">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                @if($problem && $problem->is_visible)
                    <article class="li-split-card li-reveal li-reveal-2">
                        <div class="li-split-accent" style="background: var(--color-accent);"></div>
                        @if($problem->title)
                            <h3 class="text-sm font-semibold mb-1" style="color: var(--color-text-primary);">
                                {{ $problem->title }}
                            </h3>
                        @endif
                        @if($problem->subtitle)
                            <p class="text-xs mb-2" style="color: var(--color-text-secondary);">
                                {{ $problem->subtitle }}
                            </p>
                        @endif
                        @if($problem->body)
                            <div class="cms-content text-sm" style="color: var(--color-text-secondary);">
                                {!! $problem->body !!}
                            </div>
                        @endif
                    </article>
                @endif

                @if($solution && $solution->is_visible)
                    <article class="li-split-card li-split-dark li-reveal li-reveal-3">
                        <div class="li-split-accent" style="background: var(--color-primary);"></div>
                        @if($solution->title)
                            <h3 class="text-sm font-semibold mb-1" style="color: #fff;">
                                {{ $solution->title }}
                            </h3>
                        @endif
                        @if($solution->subtitle)
                            <p class="text-xs mb-2 opacity-85" style="color: #fff;">
                                {{ $solution->subtitle }}
                            </p>
                        @endif
                        @if($solution->body)
                            <div class="cms-content text-sm opacity-95" style="color: #fff;">
                                {!! $solution->body !!}
                            </div>
                        @endif
                    </article>
                @endif
            </div>
        </div>
    </section>
@endif
