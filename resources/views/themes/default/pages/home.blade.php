@extends(theme_view('layouts.public'))

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

    @include(theme_view('pages.partials.home-hero'), ['hero' => $hero])
    @include(theme_view('pages.partials.home-mission-vision'), ['mission' => $mission, 'vision' => $vision])
    @include(theme_view('pages.partials.home-why-us'), ['whyUs' => $whyUs])
    @include(theme_view('pages.partials.home-problem-solution'), ['problem' => $problem, 'solution' => $solution])
    @include(theme_view('pages.partials.home-testimonials'), ['testimonials' => $testimonials])
    @include(theme_view('pages.partials.home-cta'), ['cta' => $cta])
    @include(theme_view('pages.partials.home-faq'), ['faq' => $faq])
@endsection
