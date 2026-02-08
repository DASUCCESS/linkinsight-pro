@extends('layouts.public')

@section('content')
    @php
        /** @var \Illuminate\Support\Collection $sections */
        $sectionsByKey = $sections->keyBy('key');

        $hero         = $sectionsByKey->get('hero');
        $mission      = $sectionsByKey->get('mission');
        $vision       = $sectionsByKey->get('vision');
        $problem      = $sectionsByKey->get('problem');
        $solution     = $sectionsByKey->get('solution');
        $whyUs        = $sectionsByKey->get('why_us');
        $testimonials = $sectionsByKey->get('testimonials');
        $cta          = $sectionsByKey->get('cta');
        $faq          = $sectionsByKey->get('faq');
    @endphp

    {{-- HERO --}}
    @if($hero && $hero->is_visible)
        @php
            $heroImage = $hero->image_path ? asset('storage/'.$hero->image_path) : null;
        @endphp

        <section id="hero" class="bg-slate-50">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-14 pb-16 lg:pt-16 lg:pb-20">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
                    <div class="space-y-5">
                        @if($hero->subtitle)
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                {{ $hero->subtitle }}
                            </p>
                        @endif

                        @if($hero->title)
                            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-semibold tracking-tight text-slate-900">
                                {{ $hero->title }}
                            </h1>
                        @endif

                        @if($hero->body)
                            <div class="text-sm sm:text-base text-slate-600 leading-relaxed">
                                {!! $hero->body !!}
                            </div>
                        @endif

                        <div class="flex flex-wrap items-center gap-3 pt-2">
                            @if(Route::has('register'))
                                <a href="{{ route('register') }}"
                                   class="inline-flex items-center justify-center px-5 py-2.5 rounded-full text-sm font-semibold text-white shadow-xl cursor-pointer transition transform hover:scale-[var(--hover-scale)]"
                                   style="background: var(--color-primary);">
                                    Get started
                                </a>
                            @endif

                            @if(Route::has('login'))
                                <a href="{{ route('login') }}"
                                   class="inline-flex items-center justify-center px-4 py-2.5 rounded-full text-sm font-semibold border border-slate-200 bg-white text-slate-700 shadow-md cursor-pointer transition transform hover:scale-[var(--hover-scale)]">
                                    View dashboard
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="rounded-3xl bg-white border border-slate-200 shadow-xl p-4 lg:p-5">
                        @if($heroImage)
                            <img src="{{ $heroImage }}"
                                 alt="{{ $hero->title ?? 'Hero image' }}"
                                 class="w-full h-64 lg:h-72 object-cover rounded-2xl">
                        @else
                            <div class="h-full flex items-center justify-center text-xs text-slate-400 text-center">
                                <span>No hero image set. Upload one from “Homepage sections → hero”.</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- MISSION / VISION / WHY US AS FEATURE CARDS --}}
    @if(($mission && $mission->is_visible) || ($vision && $vision->is_visible) || ($whyUs && $whyUs->is_visible))
        <section id="overview" class="bg-white">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach([$mission, $vision, $whyUs] as $section)
                        @if($section && $section->is_visible)
                            <div class="rounded-2xl bg-slate-50 border border-slate-200 shadow-xl p-5 flex flex-col gap-2">
                                @if($section->title)
                                    <h2 class="text-sm font-semibold text-slate-900">
                                        {{ $section->title }}
                                    </h2>
                                @endif
                                @if($section->subtitle)
                                    <p class="text-xs text-slate-500">
                                        {{ $section->subtitle }}
                                    </p>
                                @endif
                                @if($section->body)
                                    <div class="text-xs text-slate-600 leading-relaxed">
                                        {!! $section->body !!}
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- PROBLEM / SOLUTION --}}
    @if(($problem && $problem->is_visible) || ($solution && $solution->is_visible))
        <section id="problem-solution" class="bg-slate-50">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    @if($problem && $problem->is_visible)
                        <div class="rounded-2xl bg-white border border-slate-200 shadow-xl p-6">
                            @if($problem->title)
                                <h2 class="text-sm font-semibold text-slate-900 mb-1">
                                    {{ $problem->title }}
                                </h2>
                            @endif
                            @if($problem->subtitle)
                                <p class="text-xs text-slate-500 mb-2">
                                    {{ $problem->subtitle }}
                                </p>
                            @endif
                            @if($problem->body)
                                <div class="prose prose-sm max-w-none text-slate-600">
                                    {!! $problem->body !!}
                                </div>
                            @endif
                        </div>
                    @endif

                    @if($solution && $solution->is_visible)
                        <div class="rounded-2xl bg-white border border-slate-200 shadow-xl p-6">
                            @if($solution->title)
                                <h2 class="text-sm font-semibold text-slate-900 mb-1">
                                    {{ $solution->title }}
                                </h2>
                            @endif
                            @if($solution->subtitle)
                                <p class="text-xs text-slate-500 mb-2">
                                    {{ $solution->subtitle }}
                                </p>
                            @endif
                            @if($solution->body)
                                <div class="prose prose-sm max-w-none text-slate-600">
                                    {!! $solution->body !!}
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif

    {{-- WHY US FULL WIDTH (BODY FROM ADMIN) --}}
    @if($whyUs && $whyUs->is_visible && $whyUs->body)
        <section id="why-us" class="bg-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
                @if($whyUs->title)
                    <h2 class="text-2xl font-semibold text-slate-900 text-center mb-3">
                        {{ $whyUs->title }}
                    </h2>
                @endif
                @if($whyUs->subtitle)
                    <p class="text-sm text-slate-500 text-center mb-6">
                        {{ $whyUs->subtitle }}
                    </p>
                @endif
                <div class="prose prose-sm max-w-none text-slate-600">
                    {!! $whyUs->body !!}
                </div>
            </div>
        </section>
    @endif

    {{-- TESTIMONIALS --}}
    @if($testimonials && $testimonials->is_visible)
        <section id="testimonials" class="bg-slate-50">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
                @if($testimonials->title)
                    <h2 class="text-2xl font-semibold text-slate-900 text-center mb-3">
                        {{ $testimonials->title }}
                    </h2>
                @endif
                @if($testimonials->subtitle)
                    <p class="text-sm text-slate-500 text-center mb-6">
                        {{ $testimonials->subtitle }}
                    </p>
                @endif
                @if($testimonials->body)
                    <div class="prose prose-sm max-w-none text-slate-600">
                        {!! $testimonials->body !!}
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- CTA --}}
    @if($cta && $cta->is_visible)
        <section id="cta" class="bg-white">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-14 text-center">
                @if($cta->title)
                    <h2 class="text-2xl font-semibold text-slate-900 mb-2">
                        {{ $cta->title }}
                    </h2>
                @endif
                @if($cta->subtitle)
                    <p class="text-sm text-slate-500 mb-4">
                        {{ $cta->subtitle }}
                    </p>
                @endif
                @if($cta->body)
                    <div class="prose prose-sm max-w-none text-slate-600 mb-6 text-left sm:text-center mx-auto">
                        {!! $cta->body !!}
                    </div>
                @endif

                @if(Route::has('register'))
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center justify-center px-5 py-2.5 rounded-full text-sm font-semibold text-white shadow-xl cursor-pointer transition transform hover:scale-[var(--hover-scale)]"
                       style="background: var(--color-primary);">
                        Start now
                    </a>
                @endif
            </div>
        </section>
    @endif

    {{-- FAQ --}}
    @if($faq && $faq->is_visible)
        <section id="faq" class="bg-slate-50">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
                @if($faq->title)
                    <h2 class="text-2xl font-semibold text-slate-900 mb-2 text-center">
                        {{ $faq->title }}
                    </h2>
                @endif
                @if($faq->subtitle)
                    <p class="text-sm text-slate-500 mb-6 text-center">
                        {{ $faq->subtitle }}
                    </p>
                @endif
                @if($faq->body)
                    <div class="prose prose-sm max-w-none text-slate-600">
                        {!! $faq->body !!}
                    </div>
                @endif
            </div>
        </section>
    @endif
@endsection
