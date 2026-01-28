<?php

namespace App\Services\Installer;

use Illuminate\Support\Facades\File;

class PermissionsChecker
{
    public function check(): array
    {
        $paths = config('installer.writable_paths');

        $items = [];
        $passed = true;

        foreach ($paths as $path) {
            $absolutePath = base_path($path);
            $isWritable = File::isWritable($absolutePath);

            $items[] = [
                'path' => $absolutePath,
                'writable' => $isWritable,
            ];

            if (! $isWritable) {
                $passed = false;
            }
        }

        return [
            'items' => $items,
            'passed' => $passed,
        ];
    }
}
