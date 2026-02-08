<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageSection;
use Illuminate\Http\Request;

class PageSectionController extends Controller
{
    public function index(Page $page)
    {
        $sections = $page->sections()->orderBy('position')->get();

        return view('admin.cms.sections.index', compact('page', 'sections'));
    }

    public function update(Request $request, Page $page, PageSection $section)
    {
        if ($section->page_id !== $page->id) {
            abort(404);
        }

        $data = $request->validate([
            'title'      => ['nullable', 'string', 'max:255'],
            'subtitle'   => ['nullable', 'string', 'max:255'],
            'body'       => ['nullable', 'string'],
            'position'   => ['nullable', 'integer', 'min:0'],
            'is_visible' => ['nullable', 'boolean'],
            'image'      => ['nullable', 'image', 'max:4096'], 
        ]);

        $data['is_visible'] = $request->boolean('is_visible');
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('uploads/sections', 'public');
            $data['image_path'] = $path;
        }

        $section->update($data);

        return redirect()
            ->route('admin.cms.sections.index', $page)
            ->with('status_sections', 'Section updated.');
    }

}
