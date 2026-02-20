<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Page;
use function theme_view;

class PageController extends Controller
{
    public function home()
    {
        $page = Page::home()->published()->firstOrFail();
        $sections = $page->sections()->visible()->get();

        return view(theme_view('pages.home'), [
            'page' => $page,
            'sections' => $sections,
        ]);
    }

    public function show(string $slug)
    {
        $page = Page::published()
            ->where('slug', $slug)
            ->firstOrFail();

        return view(theme_view('pages.show'), [
            'page' => $page,
        ]);
    }
}
