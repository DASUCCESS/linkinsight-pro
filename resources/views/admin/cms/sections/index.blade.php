@extends('admin.layout')

@section('page_title', 'Homepage Sections')
@section('page_subtitle', 'Configure hero, mission, vision, problem, solution, why us, testimonials, CTA and FAQ.')

@section('content')
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-semibold text-slate-800 dark:text-slate-50">{{ $page->title }} – Sections</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Each block maps to a component on the public homepage.
                </p>
            </div>
            @if(session('status_sections'))
                <span class="text-xs px-2 py-1 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-200 font-medium">
                    {{ session('status_sections') }}
                </span>
            @endif
        </div>

        <div class="space-y-4">
            @foreach($sections as $section)
                <form method="POST"
                      action="{{ route('admin.cms.sections.update', ['page' => $page, 'section' => $section]) }}"
                      enctype="multipart/form-data"
                      class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/60 p-4 shadow-sm transition transform hover:scale-[var(--hover-scale)]">
                    @csrf
                    @method('PUT')

                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p class="text-xs font-semibold text-slate-600 dark:text-slate-200 uppercase">
                                {{ $section->key }}
                            </p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                Position {{ $section->position ?? 0 }}
                            </p>
                        </div>
                        <label class="inline-flex items-center gap-2 text-[11px] text-slate-600 dark:text-slate-300">
                            <input type="hidden" name="is_visible" value="0">
                            <input type="checkbox" name="is_visible" value="1"
                                   @checked(old('is_visible', $section->is_visible) ? true : false)
                                   class="rounded border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sky-500 focus:ring-sky-500 cursor-pointer">
                            <span>Show on homepage</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs md:text-sm">
                        <div>
                            <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">
                                Title
                            </label>
                            <input type="text" name="title" value="{{ old('title', $section->title) }}"
                                   class="w-full rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm text-slate-700 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">
                                Subtitle
                            </label>
                            <input type="text" name="subtitle" value="{{ old('subtitle', $section->subtitle) }}"
                                   class="w-full rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm text-slate-700 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">
                            Body (HTML / text)
                        </label>
                        <textarea name="body" rows="3"
                                  class="w-full rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs md:text-sm text-slate-700 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">{{ old('body', $section->body) }}</textarea>
                    </div>

                    {{-- Hero image upload (only for hero section) --}}
                    @if($section->key === 'hero')
                        <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4 text-xs md:text-sm">
                            <div>
                                <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">
                                    Hero image (optional)
                                </label>
                                <input type="file" name="image"
                                       class="w-full rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs file:text-xs file:mr-3 file:px-3 file:py-1.5 file:border-0 file:rounded-full file:bg-slate-900 file:text-slate-50 cursor-pointer">
                                <p class="mt-1 text-[11px] text-slate-400">
                                    Shown on the right side of the hero section.
                                </p>
                            </div>

                            @if($section->image_path)
                                <div>
                                    <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">
                                        Current image
                                    </label>
                                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-2">
                                        <img src="{{ asset('storage/'.$section->image_path) }}"
                                             alt="Hero section image"
                                             class="w-full h-32 object-cover rounded-lg">
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="mt-3 grid grid-cols-1 md:grid-cols-4 gap-3 text-xs">
                        <div>
                            <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">
                                Position
                            </label>
                            <input type="number" name="position" value="{{ old('position', $section->position) }}"
                                   class="w-full rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-1.5 text-xs text-slate-700 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        </div>
                    </div>

                    <div class="flex justify-end mt-4">
                        <button type="submit"
                                class="px-4 py-2 rounded-full text-[11px] md:text-sm font-semibold shadow-xl cursor-pointer bg-gradient-to-r from-indigo-500 to-sky-500 text-white transition transform duration-150 hover:scale-[var(--hover-scale)]">
                            Save Section
                        </button>
                    </div>
                </form>
            @endforeach
        </div>
    </div>
@endsection
