<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        use App\Models\Setting;

        /** @var \App\Models\Page|null $pageModel */
        $pageModel = isset($page) ? $page : null;

        // Global SEO defaults from Settings
        $globalSeo = [
            'meta_title'       => Setting::getValue('seo', 'meta_title'),
            'meta_description' => Setting::getValue('seo', 'meta_description'),
            'meta_keywords'    => Setting::getValue('seo', 'meta_keywords'),
            'og_title'         => Setting::getValue('seo', 'og_title'),
            'og_description'   => Setting::getValue('seo', 'og_description'),
            'og_image'         => Setting::getValue('seo', 'og_image'),
            'canonical_url'    => Setting::getValue('seo', 'canonical_url'),
        ];

        // Priority: page SEO -> global SEO -> app defaults
        $seoTitle = $pageModel?->meta_title
            ?? $globalSeo['meta_title']
            ?? $pageModel?->title
            ?? config('app.name', 'LinkInsight Pro');

        $seoDescription = $pageModel?->meta_description
            ?? $globalSeo['meta_description']
            ?? config('app.description', '');

        $seoKeywords = $pageModel?->meta_keywords
            ?? $globalSeo['meta_keywords'];

        $ogTitle = $pageModel?->og_title
            ?? $globalSeo['og_title']
            ?? $seoTitle;

        $ogDescription = $pageModel?->og_description
            ?? $globalSeo['og_description']
            ?? $seoDescription;

        $ogImage = $pageModel?->og_image
            ?? $globalSeo['og_image'];

        $indexable = $pageModel?->indexable;
        if ($indexable === null) {
            $indexable = true;
        }

        $canonical = $globalSeo['canonical_url'] ?: url()->current();
    @endphp

    <title>{{ $seoTitle }}</title>

    @if($seoDescription)
        <meta name="description" content="{{ $seoDescription }}">
    @endif

    @if($seoKeywords)
        <meta name="keywords" content="{{ $seoKeywords }}">
    @endif

    @if($indexable)
        <meta name="robots" content="index,follow">
    @else
        <meta name="robots" content="noindex,nofollow">
    @endif

    <link rel="canonical" href="{{ $canonical }}">

    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $ogTitle }}">
    @if($ogDescription)
        <meta property="og:description" content="{{ $ogDescription }}">
    @endif
    <meta property="og:url" content="{{ url()->current() }}">
    @if($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
    @endif

    @if($pageModel?->json_ld)
        @php
            $jsonLd = is_array($pageModel->json_ld)
                ? json_encode($pageModel->json_ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                : $pageModel->json_ld;
        @endphp
        <script type="application/ld+json">
            {!! $jsonLd !!}
        </script>
    @endif

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --color-primary: {{ app_color('primary_color') }};
            --color-secondary: {{ app_color('secondary_color') }};
            --color-accent: {{ app_color('accent_color') }};
            --color-background: {{ app_color('background_color') }};
            --color-card: {{ app_color('card_color') }};
            --color-border: {{ app_color('border_color') }};
            --color-text-primary: {{ app_color('text_primary') }};
            --color-text-secondary: {{ app_color('text_secondary') }};
            --btn-radius: {{ app_color('button_radius', '0.75rem') }};
            --hover-scale: {{ app_color('hover_scale', '1.05') }};
        }

        body {
            background-color: var(--color-background);
            color: var(--color-text-primary);
        }
    </style>

    @stack('head')
</head>
<body class="font-sans antialiased">
<div class="min-h-screen flex flex-col bg-slate-50">
    @include('layouts.public-navigation')

    <main class="flex-1">
        @yield('content')
    </main>

    @include('layouts.public-footer')
</div>
</body>
</html>
