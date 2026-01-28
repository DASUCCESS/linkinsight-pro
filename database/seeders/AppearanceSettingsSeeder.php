<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class AppearanceSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'primary_color' => '#4f46e5',
            'secondary_color' => '#0ea5e9',
            'accent_color' => '#f97316',
            'background_color' => '#020617',
            'card_color' => '#0f172a',
            'border_color' => '#1e293b',
            'text_primary' => '#e5e7eb',
            'text_secondary' => '#9ca3af',
            'button_radius' => '0.75rem',
            'hover_scale' => '1.05',
        ];

        foreach ($defaults as $key => $value) {
            Setting::setValue('appearance', $key, $value);
        }
    }
}
