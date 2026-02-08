@extends('admin.layout')

@section('page_title', 'CMS Pages')
@section('page_subtitle', 'Manage public pages and homepage sections.')

@section('content')
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-semibold text-slate-800 dark:text-slate-50">Pages</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Homepage, About, Contact, Terms, Privacy and FAQ.
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-xs md:text-sm text-left">
                <thead class="text-[11px] uppercase tracking-wide text-slate-400 dark:text-slate-500">
                    <tr>
                        <th class="py-2 pr-4">Title</th>
                        <th class="py-2 px-4">Slug</th>
                        <th class="py-2 px-4">Type</th>
                        <th class="py-2 px-4">Status</th>
                        <th class="py-2 px-4">Indexable</th>
                        <th class="py-2 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-200">
                    @foreach($pages as $page)
                        <tr>
                            <td class="py-3 pr-4">
                                <div class="flex items-center gap-2">
                                    @if($page->is_home)
                                        <span class="inline-flex items-center justify-center h-6 w-6 rounded-xl bg-gradient-to-br from-indigo-500 to-sky-500 text-[10px] text-white shadow">
                                            H
                                        </span>
                                    @endif
                                    <div>
                                        <div class="font-medium">{{ $page->title }}</div>
                                        <div class="text-[11px] text-slate-400">
                                            {{ $page->type }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 align-middle">
                                <span class="px-2 py-1 rounded-full bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-[11px]">
                                    /{{ $page->is_home ? '' : $page->slug }}
                                </span>
                            </td>
                            <td class="py-3 px-4 align-middle text-[11px]">
                                {{ ucfirst($page->type) }}
                            </td>
                            <td class="py-3 px-4 align-middle">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold
                                    @if($page->is_published)
                                        bg-emerald-50 text-emerald-600 border border-emerald-200
                                    @else
                                        bg-slate-100 dark:bg-slate-800 text-slate-500 border border-slate-200 dark:border-slate-700
                                    @endif">
                                    {{ $page->is_published ? 'Published' : 'Draft' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 align-middle">
                                @if($page->indexable)
                                    <span class="text-[11px] text-emerald-500">Indexable</span>
                                @else
                                    <span class="text-[11px] text-slate-400">Noindex</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 align-middle text-right">
                                <a href="{{ route('admin.cms.pages.edit', $page) }}"
                                   class="inline-flex items-center px-3 py-1.5 rounded-full text-[11px] font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-100 shadow-sm cursor-pointer transition transform hover:scale-[var(--hover-scale)]">
                                    Edit
                                </a>
                                @if($page->is_home)
                                    <a href="{{ route('admin.cms.sections.index', $page) }}"
                                       class="inline-flex items-center ml-2 px-3 py-1.5 rounded-full text-[11px] font-semibold bg-gradient-to-r from-indigo-500 to-sky-500 text-white shadow cursor-pointer transition transform hover:scale-[var(--hover-scale)]">
                                        Homepage sections
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
