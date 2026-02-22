@extends(theme_view('layouts.public'))

@section('content')
    <div class="max-w-3xl mx-auto py-10 px-4">
        <h1 class="text-3xl md:text-4xl font-semibold mb-4" style="color: var(--color-text-primary);">
            {{ $page->title }}
        </h1>

        @if($page->content)
            <div class="cms-content max-w-none" style="color: var(--color-text-secondary);">
                {!! $page->content !!}
            </div>
        @else
            <p class="text-sm" style="color: var(--color-text-secondary);">Content coming soon.</p>
        @endif
    </div>
@endsection
