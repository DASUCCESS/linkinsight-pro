@extends('admin.layout')

@section('page_title', 'Homepage Sections')
@section('page_subtitle', 'Configure hero, mission, vision, problem, solution, why us, testimonials, CTA and FAQ.')

@push('head')
    <style>
        .cms-preview-content{
            font-size:.875rem;
            line-height:1.65;
            color:inherit;
        }
        .cms-preview-content h1,
        .cms-preview-content h2,
        .cms-preview-content h3,
        .cms-preview-content h4{
            margin-top:1rem;
            margin-bottom:.5rem;
            font-weight:600;
        }
        .cms-preview-content p{
            margin:.5rem 0;
        }
        .cms-preview-content a{
            text-decoration:underline;
            text-underline-offset:2px;
        }
        .cms-preview-content strong{
            font-weight:600;
        }
        .cms-preview-content ul{
            list-style:disc;
            margin:.5rem 0 .75rem 1.25rem;
            padding-left:1rem;
        }
        .cms-preview-content ol{
            list-style:decimal;
            margin:.5rem 0 .75rem 1.25rem;
            padding-left:1rem;
        }
        .cms-preview-content li{
            margin:.25rem 0;
        }

        .faq-preview details{
            border-radius: 0.75rem;
            border: 1px solid rgba(148,163,184,0.6);
            background: rgba(248,250,252,0.9);
            padding: .7rem .9rem;
            margin-bottom: .6rem;
            box-shadow: 0 8px 24px rgba(15,23,42,0.08);
        }
        .faq-preview summary{
            list-style: none;
            cursor: pointer;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap: 0.75rem;
            font-size: .8rem;
            font-weight: 600;
            color: #0f172a;
        }
        .faq-preview summary::-webkit-details-marker{ display:none; }
        .faq-preview summary::after{
            content:'+';
            width: 22px;
            height: 22px;
            border-radius: 999px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            background:#0f172a;
            color:#fff;
            font-size:.75rem;
            flex-shrink:0;
        }
        .faq-preview details[open] summary::after{
            content:'−';
        }
        .faq-preview p{
            margin-top:.5rem;
            font-size:.8rem;
            color:#475569;
        }
    </style>
@endpush

@section('content')
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6">
        <div class="flex items-center justify-between mb-5">
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

        <div class="space-y-5">
            @foreach($sections as $section)
                @php
                    $key   = (string) $section->key;
                    $label = strtoupper(str_replace('_', ' ', $key));

                    $accent = match ($key) {
                        'hero'         => 'from-indigo-500 to-sky-500',
                        'mission'      => 'from-sky-500 to-cyan-500',
                        'vision'       => 'from-emerald-500 to-lime-500',
                        'problem'      => 'from-rose-500 to-orange-500',
                        'solution'     => 'from-amber-500 to-yellow-500',
                        'why_us'       => 'from-violet-500 to-fuchsia-500',
                        'testimonials' => 'from-cyan-500 to-sky-500',
                        'cta'          => 'from-fuchsia-500 to-rose-500',
                        'faq'          => 'from-slate-500 to-slate-700',
                        default        => 'from-slate-600 to-slate-800',
                    };

                    $existingFaqItems = $section->key === 'faq'
                        ? ($section->settings['items'] ?? [])
                        : [];

                    $oldFaqItems = $section->key === 'faq'
                        ? old('faq_items')
                        : null;

                    $faqInitial = $section->key === 'faq'
                        ? ($oldFaqItems !== null ? array_values($oldFaqItems) : $existingFaqItems)
                        : [];

                    if ($section->key === 'faq' && empty($faqInitial)) {
                        $faqInitial = [
                            ['question' => '', 'answer' => ''],
                        ];
                    }
                @endphp

                <form
                    method="POST"
                    action="{{ route('admin.cms.sections.update', ['page' => $page, 'section' => $section]) }}"
                    enctype="multipart/form-data"
                    class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow-sm transition hover:shadow-lg"
                    x-data="{
                        title: @js(old('title', $section->title)),
                        subtitle: @js(old('subtitle', $section->subtitle)),
                        body: @js(old('body', $section->body)),
                        showPreview: true,
                        @if($section->key === 'faq')
                            faqItems: @js($faqInitial),
                            addFaqItem() {
                                this.faqItems.push({question: '', answer: ''});
                            },
                            removeFaqItem(index) {
                                if (this.faqItems.length > 1) {
                                    this.faqItems.splice(index, 1);
                                }
                            }
                        @endif
                    }"
                >
                    @csrf
                    @method('PUT')

                    <div class="flex items-start justify-between gap-4 mb-4">
                        <div class="flex items-start gap-3">
                            <div class="h-11 w-11 rounded-2xl bg-gradient-to-br {{ $accent }} text-white flex items-center justify-center shadow-md">
                                <span class="text-xs font-bold">{{ strtoupper(substr($key, 0, 2)) }}</span>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-800 dark:text-slate-100">{{ $label }}</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                    Position {{ $section->position ?? 0 }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <label class="inline-flex items-center gap-2 text-[11px] text-slate-600 dark:text-slate-300">
                                <input type="hidden" name="is_visible" value="0">
                                <input
                                    type="checkbox"
                                    name="is_visible"
                                    value="1"
                                    @checked(old('is_visible', $section->is_visible) ? true : false)
                                    class="rounded border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sky-500 focus:ring-sky-500 cursor-pointer"
                                >
                                <span>Show on homepage</span>
                            </label>

                            <button
                                type="button"
                                class="inline-flex items-center px-3 py-1.5 rounded-full text-[11px] font-semibold cursor-pointer
                                       border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950
                                       text-slate-700 dark:text-slate-100 shadow-sm transition hover:bg-white dark:hover:bg-slate-900"
                                @click="showPreview = !showPreview"
                            >
                                <span x-text="showPreview ? 'Hide preview' : 'Show preview'"></span>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">Title</label>
                            <input
                                type="text"
                                name="title"
                                x-model="title"
                                class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3 py-2 text-sm
                                       text-slate-700 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                            >
                        </div>

                        <div>
                            <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">Subtitle</label>
                            <input
                                type="text"
                                name="subtitle"
                                x-model="subtitle"
                                class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3 py-2 text-sm
                                       text-slate-700 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                            >
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">
                            @if($section->key === 'faq')
                                Intro text above FAQ list (optional)
                            @else
                                Body (HTML / text)
                            @endif
                        </label>
                        <textarea
                            name="body"
                            rows="4"
                            x-model="body"
                            class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3 py-2 text-xs md:text-sm
                                   text-slate-700 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                        ></textarea>

                        <div class="mt-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 p-3">
                            <p class="text-[11px] font-semibold text-slate-700 dark:text-slate-200">Formatting guide</p>
                            @if($section->key === 'faq')
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                                    This intro appears above the accordion on the public FAQ section. Use the FAQ items below for the individual questions.
                                </p>
                            @else
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                                    Plain text renders as paragraphs. Use <span class="font-mono">&lt;ul&gt;&lt;li&gt;</span> only when you want a list.
                                </p>
                                <pre class="mt-2 text-[11px] text-slate-600 dark:text-slate-300 overflow-x-auto"><code>&lt;ul&gt;
  &lt;li&gt;&lt;strong&gt;Feature title&lt;/strong&gt;&lt;br&gt;Short explanation&lt;/li&gt;
  &lt;li&gt;&lt;strong&gt;Feature title&lt;/strong&gt;&lt;br&gt;Short explanation&lt;/li&gt;
&lt;/ul&gt;</code></pre>
                            @endif
                        </div>
                    </div>

                    @if($section->key === 'faq')
                        <div class="mt-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 p-3 md:p-4">
                            <div class="flex items-center justify-between gap-2">
                                <div>
                                    <p class="text-[11px] font-semibold text-slate-700 dark:text-slate-200">
                                        FAQ items (accordion)
                                    </p>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                                        Each row is one FAQ entry. Only items with both Question and Answer filled will appear.
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    class="inline-flex items-center px-3 py-1.5 rounded-full text-[11px] font-semibold cursor-pointer
                                           bg-slate-900 text-slate-50 shadow-sm hover:shadow-md"
                                    @click="addFaqItem()"
                                >
                                    Add FAQ
                                </button>
                            </div>

                            <div class="mt-3 space-y-3">
                                <template x-for="(item, index) in faqItems" :key="index">
                                    <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-3">
                                        <div class="flex items-center justify-between gap-2 mb-2">
                                            <p class="text-[11px] font-semibold text-slate-700 dark:text-slate-200">
                                                FAQ <span x-text="index + 1"></span>
                                            </p>
                                            <button
                                                type="button"
                                                class="text-[11px] text-rose-500 hover:text-rose-600 font-semibold cursor-pointer"
                                                @click="removeFaqItem(index)"
                                                x-show="faqItems.length > 1"
                                            >
                                                Remove
                                            </button>
                                        </div>

                                        <div class="space-y-2">
                                            <div>
                                                <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">
                                                    Question
                                                </label>
                                                <input
                                                    type="text"
                                                    :name="`faq_items[${index}][question]`"
                                                    x-model="item.question"
                                                    class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3 py-2 text-xs md:text-sm
                                                           text-slate-700 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                                                >
                                            </div>

                                            <div>
                                                <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">
                                                    Answer (supports HTML)
                                                </label>
                                                <textarea
                                                    :name="`faq_items[${index}][answer]`"
                                                    rows="2"
                                                    x-model="item.answer"
                                                    class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3 py-2 text-xs md:text-sm
                                                           text-slate-700 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                                                ></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <p class="mt-2 text-[11px] text-slate-500 dark:text-slate-400">
                                You can reorder items later by adjusting them here. Save to update the public FAQ accordion.
                            </p>
                        </div>
                    @endif

                    @if($section->key === 'hero')
                        <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">
                                    Hero image (optional)
                                </label>
                                <input
                                    type="file"
                                    name="image"
                                    class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3 py-2 text-xs
                                           file:text-xs file:mr-3 file:px-3 file:py-1.5 file:border-0 file:rounded-full file:bg-slate-900 file:text-slate-50 cursor-pointer"
                                >
                                <p class="mt-1 text-[11px] text-slate-400">
                                    Shown on the right side of the hero section on the homepage.
                                </p>
                            </div>

                            @if($section->image_path)
                                <div>
                                    <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">
                                        Current image
                                    </label>
                                    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 p-2">
                                        <img
                                            src="{{ asset('storage/'.$section->image_path) }}"
                                            alt="Hero section image"
                                            class="w-full h-32 object-cover rounded-lg"
                                        >
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="mt-3 grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-300 mb-1">
                                Position
                            </label>
                            <input
                                type="number"
                                name="position"
                                value="{{ old('position', $section->position) }}"
                                class="w-full rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3 py-1.5 text-xs
                                       text-slate-700 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                            >
                        </div>
                    </div>

                    <div class="mt-4 border-t border-slate-200 dark:border-slate-800 pt-4">
                        <div
                            x-show="showPreview"
                            x-cloak
                            class="rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 p-3 md:p-4"
                        >
                            @if($section->key === 'hero')
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                                    <div class="space-y-2">
                                        <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wide" x-text="subtitle"></p>
                                        <h3 class="text-base md:text-lg font-semibold text-slate-900 dark:text-slate-50" x-text="title"></h3>
                                        <div class="cms-preview-content text-slate-700 dark:text-slate-200" x-html="body"></div>
                                    </div>

                                    <div class="rounded-2xl bg-white/70 dark:bg-slate-900 border border-dashed border-slate-200 dark:border-slate-800 h-32 md:h-40 flex items-center justify-center overflow-hidden">
                                        @if($section->image_path)
                                            <img
                                                src="{{ asset('storage/'.$section->image_path) }}"
                                                alt="Hero image preview"
                                                class="w-full h-full object-cover"
                                            >
                                        @else
                                            <span class="text-[11px] text-slate-400 px-3 text-center">
                                                Hero image preview will appear here after you upload and save.
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @elseif($section->key === 'faq')
                                <div class="space-y-3">
                                    <p class="text-[11px] text-slate-500 uppercase tracking-wide" x-text="subtitle"></p>
                                    <h3 class="text-sm md:text-base font-semibold text-slate-900 dark:text-slate-50" x-text="title"></h3>

                                    <div class="cms-preview-content text-slate-700 dark:text-slate-200" x-html="body"></div>

                                    <div class="faq-preview mt-3">
                                        <template x-for="(item, index) in faqItems" :key="index">
                                            <details x-show="item.question && item.answer">
                                                <summary x-text="item.question"></summary>
                                                <p x-text="item.answer"></p>
                                            </details>
                                        </template>
                                    </div>
                                </div>
                            @else
                                <div class="space-y-1">
                                    <p class="text-[11px] text-slate-500 uppercase tracking-wide" x-text="subtitle"></p>
                                    <h3 class="text-sm md:text-base font-semibold text-slate-900 dark:text-slate-50" x-text="title"></h3>
                                    <div class="cms-preview-content text-slate-700 dark:text-slate-200" x-html="body"></div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex justify-end mt-4">
                        <button
                            type="submit"
                            class="px-4 py-2 rounded-full text-[11px] md:text-sm font-semibold shadow-xl cursor-pointer
                                   bg-gradient-to-r from-indigo-500 to-sky-500 text-white transition hover:shadow-2xl hover:brightness-[1.02]"
                        >
                            Save Section
                        </button>
                    </div>
                </form>
            @endforeach
        </div>
    </div>
@endsection
