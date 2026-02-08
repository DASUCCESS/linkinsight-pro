@extends('layouts.public')

@section('content')
    <div class="max-w-3xl mx-auto py-10 px-4">
        <h1 class="text-3xl font-semibold mb-4 text-slate-900">{{ $page->title }}</h1>

        @if($page->content)
            <div class="prose max-w-none text-slate-800">
                {!! $page->content !!}
            </div>
        @else
            <p class="text-slate-500">Content coming soon.</p>
        @endif
    </div>
@endsection
