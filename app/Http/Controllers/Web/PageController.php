<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Page;

class PageController extends Controller
{
    public function home()
    {
        $page = Page::home()->published()->firstOrFail();
        $sections = $page->sections()->visible()->get();

        return view('web.pages.home', compact('page', 'sections'));
    }

    public function show(string $slug)
    {
        $page = Page::published()
            ->where('slug', $slug)
            ->firstOrFail();

        return view('web.pages.show', compact('page'));
    }
}
