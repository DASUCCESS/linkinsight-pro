@if(($mission && $mission->is_visible) || ($vision && $vision->is_visible))
    <section id="mission-vision" class="py-14 modern-section-muted">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-[1.3fr,minmax(0,1fr)] gap-10 items-start">
                <div>
                    <p class="text-[11px] font-semibold tracking-[0.2em] uppercase text-slate-500">
                        Product philosophy
                    </p>
                    <h2 class="mt-2 text-2xl md:text-3xl font-semibold" style="color: var(--color-text-primary);">
                        A single place to understand and grow your LinkedIn presence.
                    </h2>
                    <p class="mt-3 text-sm" style="color: var(--color-text-secondary);">
                        Configure these blocks directly from the CMS. The public site updates immediately when you adjust copy or tone.
                    </p>
                </div>

                <div class="space-y-6">
                    @if($mission && $mission->is_visible)
                        <article class="modern-feature-card">
                            <p class="text-[11px] font-semibold tracking-[0.18em] uppercase text-slate-500 mb-1">Mission</p>
                            <h3 class="text-sm font-semibold mb-1"
                                style="color: var(--color-text-primary);">
                                {{ $mission->title ?: 'Our mission' }}
                            </h3>
                            @if($mission->subtitle)
                                <p class="text-xs mb-2" style="color: var(--color-text-secondary);">
                                    {{ $mission->subtitle }}
                                </p>
                            @endif
                            @if($mission->body)
                                <div class="cms-content text-xs"
                                     style="color: var(--color-text-secondary);">
                                    {!! $mission->body !!}
                                </div>
                            @endif
                        </article>
                    @endif

                    @if($vision && $vision->is_visible)
                        <article class="modern-feature-card">
                            <p class="text-[11px] font-semibold tracking-[0.18em] uppercase text-slate-500 mb-1">Vision</p>
                            <h3 class="text-sm font-semibold mb-1"
                                style="color: var(--color-text-primary);">
                                {{ $vision->title ?: 'Our vision' }}
                            </h3>
                            @if($vision->subtitle)
                                <p class="text-xs mb-2" style="color: var(--color-text-secondary);">
                                    {{ $vision->subtitle }}
                                </p>
                            @endif
                            @if($vision->body)
                                <div class="cms-content text-xs"
                                     style="color: var(--color-text-secondary);">
                                    {!! $vision->body !!}
                                </div>
                            @endif
                        </article>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endif
