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

        // Base rules for all sections
        $baseRules = [
            'title'      => ['nullable', 'string', 'max:255'],
            'subtitle'   => ['nullable', 'string', 'max:255'],
            'body'       => ['nullable', 'string'],
            'position'   => ['nullable', 'integer', 'min:0'],
            'is_visible' => ['nullable', 'boolean'],
            'image'      => ['nullable', 'image', 'max:4096'],
        ];

        // Extra rules for FAQ section only
        $faqRules = [];
        if ($section->key === 'faq') {
            $faqRules = [
                'faq_items'            => ['nullable', 'array'],
                'faq_items.*.question' => ['nullable', 'string'],
                'faq_items.*.answer'   => ['nullable', 'string'],
            ];
        }

        $data = $request->validate(array_merge($baseRules, $faqRules));

        $data['is_visible'] = $request->boolean('is_visible');

        // Handle FAQ items → save into settings.items
        if ($section->key === 'faq') {
            $faqItemsInput = $request->input('faq_items', []);
            $normalized = [];

            if (is_array($faqItemsInput)) {
                foreach ($faqItemsInput as $item) {
                    $question = trim($item['question'] ?? '');
                    $answer   = trim($item['answer'] ?? '');

                    // Only keep rows with both fields filled
                    if ($question !== '' && $answer !== '') {
                        $normalized[] = [
                            'question' => $question,
                            'answer'   => $answer,
                        ];
                    }
                }
            }

            $settings = $section->settings ?? [];
            $settings['items'] = $normalized;

            $data['settings'] = $settings;
        }

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
