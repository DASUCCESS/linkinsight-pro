<?php

use App\Services\ThemeManager;

if (! function_exists('active_theme_slug')) {
    function active_theme_slug(): string
    {
        return app(ThemeManager::class)->getActiveSlug();
    }
}

if (! function_exists('theme_view')) {
    function theme_view(string $view): string
    {
        return 'themes.'.active_theme_slug().'.'.$view;
    }
}
