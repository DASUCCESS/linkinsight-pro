@if(($mission && $mission->is_visible) || ($vision && $vision->is_visible))
    <section id="mission-vision"
             class="py-20 border-t border-b"
             style="background-color: var(--color-primary); border-color: var(--color-secondary);">

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Section Header --}}
            <div class="mb-14 text-center li-reveal li-reveal-1">
                <p class="text-xs font-semibold tracking-[0.2em] uppercase"
                   style="color: #ffffff;">
                    Our direction
                </p>

                <h2 class="mt-3 text-3xl font-semibold"
                    style="color: #ffffff;">
                    Mission and Vision
                </h2>

                <p class="mt-3 text-sm mx-auto max-w-2xl"
                   style="color: rgba(255,255,255,0.85);">
                    Set these from the CMS. The public site updates instantly.
                </p>
            </div>

            {{-- Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                {{-- Mission --}}
                @if($mission && $mission->is_visible)
                    <article
                        class="rounded-2xl p-8 transition-all duration-300 ease-in-out cursor-pointer shadow-xl hover:-translate-y-3 hover:scale-[var(--hover-scale)] hover:shadow-2xl"
                        style="background-color: var(--color-background);
                               border: 2px solid var(--color-secondary);">

                        <div class="inline-flex items-center justify-center px-4 py-1 text-xs font-semibold rounded-full text-white mb-4"
                             style="background-color: var(--color-primary);">
                            Mission
                        </div>

                        <h3 class="text-lg font-semibold mb-2"
                            style="color: var(--color-text-primary);">
                            {{ $mission->title ?: 'Our Mission' }}
                        </h3>

                        @if($mission->subtitle)
                            <p class="text-sm mb-3"
                               style="color: var(--color-text-secondary);">
                                {{ $mission->subtitle }}
                            </p>
                        @endif

                        @if($mission->body)
                            <div class="cms-content text-sm"
                                 style="color: var(--color-text-secondary);">
                                {!! $mission->body !!}
                            </div>
                        @endif
                    </article>
                @endif


                {{-- Vision --}}
                @if($vision && $vision->is_visible)
                    <article
                        class="rounded-2xl p-8 transition-all duration-300 ease-in-out cursor-pointer shadow-xl hover:-translate-y-3 hover:scale-[var(--hover-scale)] hover:shadow-2xl"
                        style="background-color: var(--color-background);
                               border: 2px solid var(--color-secondary);">

                        <div class="inline-flex items-center justify-center px-4 py-1 text-xs font-semibold rounded-full text-white mb-4"
                             style="background-color: var(--color-secondary);">
                            Vision
                        </div>

                        <h3 class="text-lg font-semibold mb-2"
                            style="color: var(--color-text-primary);">
                            {{ $vision->title ?: 'Our Vision' }}
                        </h3>

                        @if($vision->subtitle)
                            <p class="text-sm mb-3"
                               style="color: var(--color-text-secondary);">
                                {{ $vision->subtitle }}
                            </p>
                        @endif

                        @if($vision->body)
                            <div class="cms-content text-sm"
                                 style="color: var(--color-text-secondary);">
                                {!! $vision->body !!}
                            </div>
                        @endif
                    </article>
                @endif

            </div>
        </div>
    </section>
@endif
