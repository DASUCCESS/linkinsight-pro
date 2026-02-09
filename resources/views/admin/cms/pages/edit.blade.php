@extends('admin.layout')

@section('page_title', 'Edit Page')
@section('page_subtitle', 'Update page content and SEO metadata.')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div
                class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6"
                x-data="{
                    contentHtml: @js(old('content', $page->content)),
                    showPreview: true,
                    init() {
                        const trix = this.$refs.trixContent;
                        trix.addEventListener('trix-change', (event) => {
                            this.contentHtml = event.target.value;
                        });
                    }
                }"
            >
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-base font-semibold text-slate-800 dark:text-slate-50">{{ $page->title }}</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            /{{ $page->is_home ? '' : $page->slug }}
                        </p>
                    </div>
                    @if(session('status_page'))
                        <span class="text-xs px-2 py-1 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-200 font-medium">
                            {{ session('status_page') }}
                        </span>
                    @endif
                </div>

                <form method="POST" action="{{ route('admin.cms.pages.update', $page) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Title</label>
                            <input
                                type="text"
                                name="title"
                                value="{{ old('title', $page->title) }}"
                                class="w-full rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm text-slate-700 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Slug</label>
                            <input
                                type="text"
                                name="slug"
                                value="{{ old('slug', $page->slug) }}"
                                class="w-full rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm text-slate-700 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                            >
                            <p class="mt-1 text-[11px] text-slate-400">
                                Homepage still renders as / even if slug is "home".
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Type</label>
                            <select
                                name="type"
                                class="w-full rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm text-slate-700 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                            >
                                <option value="page" @selected(old('type', $page->type) === 'page')>Page</option>
                                <option value="system" @selected(old('type', $page->type) === 'system')>System</option>
                            </select>
                        </div>
                        <div class="flex items-center">
                            <label class="inline-flex items-center gap-2 text-xs text-slate-600 dark:text-slate-300 mt-4 md:mt-6">
                                <input type="hidden" name="is_published" value="0">
                                <input
                                    type="checkbox"
                                    name="is_published"
                                    value="1"
                                    @checked(old('is_published', $page->is_published) ? true : false)
                                    class="rounded border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sky-500 focus:ring-sky-500 cursor-pointer"
                                >
                                <span>Published</span>
                            </label>
                        </div>
                        <div class="flex items-center">
                            <label class="inline-flex items-center gap-2 text-xs text-slate-600 dark:text-slate-300 mt-4 md:mt-6">
                                <input type="hidden" name="indexable" value="0">
                                <input
                                    type="checkbox"
                                    name="indexable"
                                    value="1"
                                    @checked(old('indexable', $page->indexable) ? true : false)
                                    class="rounded border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sky-500 focus:ring-sky-500 cursor-pointer"
                                >
                                <span>Allow search engines to index</span>
                            </label>
                        </div>
                    </div>

                    {{-- Content WYSIWYG + preview --}}
                    <div class="border-t border-slate-100 dark:border-slate-800 pt-4 mt-2">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase">
                                Content
                            </p>
                            <button
                                type="button"
                                class="text-[11px] text-sky-600 hover:underline cursor-pointer"
                                @click="showPreview = !showPreview"
                            >
                                <span x-text="showPreview ? 'Hide preview' : 'Show preview'"></span>
                            </button>
                        </div>

                        <input
                            type="hidden"
                            name="content"
                            id="page_content_{{ $page->id }}"
                            :value="contentHtml"
                        >

                        <trix-editor
                            x-ref="trixContent"
                            input="page_content_{{ $page->id }}"
                            class="trix-content text-sm"
                        ></trix-editor>

                        <p class="mt-1 text-[11px] text-slate-400">
                            Use headings, lists and links. The public page will follow this structure.
                        </p>

                        <div
                            x-show="showPreview"
                            x-cloak
                            class="mt-4 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4"
                        >
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-50 mb-2">
                                Live preview
                            </h3>
                            <div class="prose max-w-none text-slate-800 dark:text-slate-100 text-sm" x-html="contentHtml"></div>
                        </div>
                    </div>

                    {{-- SEO metadata (unchanged) --}}
                    <div class="border-t border-slate-100 dark:border-slate-800 pt-4 mt-2">
                        <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase mb-3">
                            SEO Metadata
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm mb-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Meta Title</label>
                                <input
                                    type="text"
                                    name="meta_title"
                                    value="{{ old('meta_title', $page->meta_title) }}"
                                    class="w-full rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm text-slate-700 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Meta Keywords</label>
                                <input
                                    type="text"
                                    name="meta_keywords"
                                    value="{{ old('meta_keywords', $page->meta_keywords) }}"
                                    class="w-full rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm text-slate-700 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                                >
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Meta Description</label>
                            <textarea
                                name="meta_description"
                                rows="3"
                                class="w-full rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm text-slate-700 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                            >{{ old('meta_description', $page->meta_description) }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm mb-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">OG Title</label>
                                <input
                                    type="text"
                                    name="og_title"
                                    value="{{ old('og_title', $page->og_title) }}"
                                    class="w-full rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm text-slate-700 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">OG Description</label>
                                <input
                                    type="text"
                                    name="og_description"
                                    value="{{ old('og_description', $page->og_description) }}"
                                    class="w-full rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm text-slate-700 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                                >
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">OG Image URL</label>
                            <input
                                type="text"
                                name="og_image"
                                value="{{ old('og_image', $page->og_image) }}"
                                class="w-full rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm text-slate-700 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                            >
                        </div>

                        <div class="mb-2">
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">JSON-LD (optional)</label>
                            <textarea
                                name="json_ld"
                                rows="3"
                                class="w-full rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs font-mono text-slate-700 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                            >{{ old('json_ld', $page->json_ld ? json_encode($page->json_ld, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '') }}</textarea>
                            <p class="mt-1 text-[11px] text-slate-400">
                                Paste valid JSON-LD. If invalid, it will be stored as raw text.
                            </p>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button
                            type="submit"
                            class="px-5 py-2.5 rounded-full text-sm font-semibold shadow-xl cursor-pointer bg-gradient-to-r from-indigo-500 to-sky-500 text-white transition-transform duration-150 hover:scale-[var(--hover-scale)]"
                        >
                            Save Page
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-4 text-sm">
                <h3 class="text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase mb-3">Page Details</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-2">
                    Type:
                    <span class="font-medium text-slate-700 dark:text-slate-100">{{ ucfirst($page->type) }}</span>
                </p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-2">
                    Created:
                    <span class="text-slate-700 dark:text-slate-100">{{ $page->created_at->toDayDateTimeString() }}</span>
                </p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-2">
                    Updated:
                    <span class="text-slate-700 dark:text-slate-100">{{ $page->updated_at->toDayDateTimeString() }}</span>
                </p>

                @if($page->is_home)
                    <a
                        href="{{ route('admin.cms.sections.index', $page) }}"
                        class="mt-3 inline-flex items-center px-3 py-1.5 rounded-full text-[11px] font-semibold bg-gradient-to-r from-indigo-500 to-sky-500 text-white shadow cursor-pointer transition-transform duration-150 hover:scale-[var(--hover-scale)]"
                    >
                        Manage homepage sections
                    </a>
                @endif
            </div>
        </div>
    </div>
@endsection
