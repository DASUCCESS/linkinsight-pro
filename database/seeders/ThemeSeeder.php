<?php

namespace Database\Seeders;

use App\Models\Theme;
use Illuminate\Database\Seeder;

class ThemeSeeder extends Seeder
{
    public function run(): void
    {
        Theme::firstOrCreate(
            ['slug' => 'default'],
            [
                'name'         => 'Default',
                'path'         => 'themes/default',
                'version'      => '1.0.0',
                'author'       => 'LinkInsight Pro',
                'screenshot'   => null,
                'is_installed' => true,
                'is_active'    => true,
            ]
        );
    }
}
