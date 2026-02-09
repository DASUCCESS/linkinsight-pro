@props([
    'href' => '#',
    'label' => '',
    'icon' => null,
    'active' => false,
])

@php
    $baseClasses = 'flex items-center justify-between px-3 py-2 rounded-xl text-xs font-medium cursor-pointer transition transform duration-150';
    $activeClasses = 'bg-slate-800 text-slate-50 shadow-xl border border-slate-600';
    $inactiveClasses = 'text-slate-400 hover:text-slate-100 hover:bg-slate-900 border border-transparent hover:border-slate-700 hover:shadow-lg hover:scale-[var(--hover-scale)]';
@endphp

<a href="{{ $href }}" class="{{ $baseClasses }} {{ $active ? $activeClasses : $inactiveClasses }}">
    <span class="inline-flex items-center gap-2">
        @if($icon && \Illuminate\Support\Facades\View::exists('components.admin.icons.'.$icon))
            <span class="h-7 w-7 rounded-xl flex items-center justify-center bg-slate-900/80 border border-slate-700">
                @include('components.admin.icons.'.$icon)
            </span>
        @endif

        <span>{{ $label }}</span>
    </span>
</a>
