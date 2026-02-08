<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::orderByDesc('is_home')
            ->orderBy('title')
            ->get();

        return view('admin.cms.pages.index', compact('pages'));
    }

    public function edit(Page $page)
    {
        return view('admin.cms.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $data = $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'slug'             => ['required', 'string', 'max:255', Rule::unique('pages', 'slug')->ignore($page->id)],
            'type'             => ['required', Rule::in(['page', 'system'])],
            'is_published'     => ['nullable', 'boolean'],
            'content'          => ['nullable', 'string'],
            'meta_title'       => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'meta_keywords'    => ['nullable', 'string'],
            'og_title'         => ['nullable', 'string', 'max:255'],
            'og_description'   => ['nullable', 'string'],
            'og_image'         => ['nullable', 'string', 'max:255'],
            'json_ld'          => ['nullable', 'string'],
            'indexable'        => ['nullable', 'boolean'],
        ]);

        $data['is_published'] = $request->boolean('is_published');
        $data['indexable']    = $request->boolean('indexable');

        // allow leaving JSON-LD empty or free text, store as-is; cast will encode as JSON
        if (!empty($data['json_ld'])) {
            $decoded = json_decode($data['json_ld'], true);
            $data['json_ld'] = $decoded ?: $data['json_ld'];
        }

        $page->update($data);

        return redirect()
            ->route('admin.cms.pages.edit', $page)
            ->with('status_page', 'Page updated successfully.');
    }
}
