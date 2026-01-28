<?php

use App\Models\Setting;

if (! function_exists('app_color')) {
    function app_color(string $key, string $fallback = '#ffffff'): string
    {
        $value = Setting::getValue('appearance', $key);

        return is_string($value) ? $value : $fallback;
    }
}
