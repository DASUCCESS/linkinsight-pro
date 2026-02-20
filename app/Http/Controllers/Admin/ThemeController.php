<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Theme;
use App\Services\ThemeManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ThemeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(ThemeManager $manager): View
    {
        $manager->syncFromFilesystem();

        $themes = Theme::query()
            ->orderBy('name')
            ->get();

        $activeSlug = $manager->getActiveSlug();
        $previousSlug = Setting::getValue('appearance', 'previous_theme');

        return view('admin.themes.index', compact('themes', 'activeSlug', 'previousSlug'));
    }

    public function activate(Theme $theme, ThemeManager $manager, Request $request): RedirectResponse
    {
        $manager->setActive($theme);

        return redirect()
            ->route('admin.themes.index')
            ->with('success', 'Theme activated successfully.');
    }

    public function rollback(ThemeManager $manager): RedirectResponse
    {
        $previous = $manager->rollbackToPrevious();

        if (! $previous) {
            return redirect()
                ->route('admin.themes.index')
                ->with('error', 'No previous theme to rollback to.');
        }

        return redirect()
            ->route('admin.themes.index')
            ->with('success', 'Rolled back to theme: '.$previous->name.'.');
    }

    public function upload(Request $request, ThemeManager $manager): RedirectResponse
    {
        $validated = $request->validate([
            'theme_zip' => ['required', 'file', 'mimes:zip', 'max:51200'],
        ]);

        try {
            $theme = $manager->installFromZip($validated['theme_zip']);

            return redirect()
                ->route('admin.themes.index')
                ->with('success', 'Theme "'.$theme->name.'" installed successfully. You can now activate it.');
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.themes.index')
                ->with('error', 'Theme upload failed: '.$e->getMessage());
        }
    }
}
