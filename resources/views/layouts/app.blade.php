<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
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

    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
