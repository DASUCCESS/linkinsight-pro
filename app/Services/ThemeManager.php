<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Theme;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;

class ThemeManager
{
    public function syncFromFilesystem(): void
    {
        $base = resource_path('views/themes');

        if (! File::exists($base)) {
            return;
        }

        foreach (File::directories($base) as $dir) {
            $slug = basename($dir);

            Theme::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => Str::title(str_replace(['-', '_'], ' ', $slug)),
                    'path' => 'themes/'.$slug,
                    'version' => '1.0.0',
                    'author' => config('app.name'),
                    'is_installed' => true,
                ]
            );
        }
    }

    public function getActiveSlug(): string
    {
        return Cache::rememberForever('active_theme_slug', function () {
            $theme = Theme::active();

            if ($theme) {
                return $theme->slug;
            }

            $fallback = Setting::getValue('appearance', 'active_theme', 'default');

            if (! Theme::where('slug', $fallback)->exists()) {
                $fallback = 'default';
            }

            return $fallback;
        });
    }

    public function setActive(Theme $theme): void
    {
        DB::transaction(function () use ($theme) {
            $current = Theme::active();

            if ($current && $current->id !== $theme->id) {
                $current->update(['is_active' => false]);
                Setting::setValue('appearance', 'previous_theme', $current->slug);
            }

            $theme->update([
                'is_installed' => true,
                'is_active' => true,
            ]);

            Setting::setValue('appearance', 'active_theme', $theme->slug);

            Cache::forget('active_theme_slug');
        });
    }

    public function rollbackToPrevious(): ?Theme
    {
        $previousSlug = Setting::getValue('appearance', 'previous_theme');

        if (! $previousSlug) {
            return null;
        }

        $previous = Theme::where('slug', $previousSlug)->first();

        if (! $previous) {
            return null;
        }

        $this->setActive($previous);

        return $previous;
    }

    /**
     * Install or update a theme from a ZIP upload.
     * Expects structure:
     *   theme.json
     *   views/...
     *   assets/... (optional)
     */
    public function installFromZip(UploadedFile $file): Theme
    {
        $tmp = $file->store('tmp/themes');
        $zipPath = storage_path('app/'.$tmp);

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('Unable to open theme zip file.');
        }

        $extractPath = storage_path('app/tmp/theme_'.uniqid());

        if (File::exists($extractPath)) {
            File::deleteDirectory($extractPath);
        }
        File::makeDirectory($extractPath, 0755, true);

        if (! $zip->extractTo($extractPath)) {
            $zip->close();
            File::delete($zipPath);
            throw new \RuntimeException('Unable to extract theme zip file.');
        }

        $zip->close();
        File::delete($zipPath);

        $manifestPath = $extractPath.'/theme.json';
        if (! File::exists($manifestPath)) {
            File::deleteDirectory($extractPath);
            throw new \RuntimeException('theme.json not found in theme package.');
        }

        $manifest = json_decode(File::get($manifestPath), true) ?: [];
        $name = $manifest['name'] ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $slug = Str::slug($manifest['slug'] ?? $name);
        $version = $manifest['version'] ?? '1.0.0';
        $author = $manifest['author'] ?? config('app.name');
        $screenshotRelative = $manifest['screenshot'] ?? null;

        $viewsSource = $extractPath.'/views';
        if (! File::isDirectory($viewsSource)) {
            File::deleteDirectory($extractPath);
            throw new \RuntimeException('views/ folder not found in theme package.');
        }

        $viewsTarget = resource_path('views/themes/'.$slug);
        if (File::exists($viewsTarget)) {
            File::deleteDirectory($viewsTarget);
        }
        File::makeDirectory($viewsTarget, 0755, true);

        File::copyDirectory($viewsSource, $viewsTarget);

        $assetsSource = $extractPath.'/assets';
        $assetsTarget = public_path('themes/'.$slug);
        $hasAssets = false;

        if (File::isDirectory($assetsSource)) {
            if (File::exists($assetsTarget)) {
                File::deleteDirectory($assetsTarget);
            }
            File::makeDirectory($assetsTarget, 0755, true);
            File::copyDirectory($assetsSource, $assetsTarget);
            $hasAssets = true;
        }

        File::deleteDirectory($extractPath);

        $screenshotPath = null;
        if ($screenshotRelative && $hasAssets) {
            $screenshotPath = 'themes/'.$slug.'/'.$screenshotRelative;
        }

        $theme = Theme::updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'slug' => $slug,
                'path' => 'themes/'.$slug,
                'version' => $version,
                'author' => $author,
                'screenshot' => $screenshotPath,
                'is_installed' => true,
            ]
        );

        Cache::forget('active_theme_slug');

        return $theme;
    }
}
