{{-- resources/views/themes/modern/pages/partials/home-faq.blade.php --}}
@if($faq && $faq->is_visible)
    <section id="faq" class="py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($faq->title)
                <h2 class="text-2xl font-semibold mb-2 text-center"
                    style="color: var(--color-text-primary);">
                    {{ $faq->title }}
                </h2>
            @endif

            @if($faq->subtitle)
                <p class="text-sm mb-4 text-center"
                   style="color: var(--color-text-secondary);">
                    {{ $faq->subtitle }}
                </p>
            @endif

            @if($faq->body)
                <div class="cms-content text-sm mb-6 text-center"
                     style="color: var(--color-text-secondary);">
                    {!! $faq->body !!}
                </div>
            @endif

            @php
                $faqItems = $faq->settings['items'] ?? [];
            @endphp

            @if(!empty($faqItems))
                <div class="cms-faq-2 text-sm"
                     style="color: var(--color-text-secondary);">
                    @foreach($faqItems as $item)
                        @php
                            $question = trim($item['question'] ?? '');
                            $answer   = trim($item['answer'] ?? '');
                        @endphp

                        @if($question && $answer)
                            <details>
                                <summary>{{ $question }}</summary>
                                <p>{!! nl2br(e($answer)) !!}</p>
                            </details>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endif
