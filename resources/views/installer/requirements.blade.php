@extends('installer.layout')

@section('step_number', '1')

@section('content')
    <h2 class="text-xl font-semibold mb-4">Server Requirements</h2>

    <div class="space-y-4">
        <div class="bg-slate-800/80 rounded-xl p-4 shadow-lg">
            <h3 class="font-medium mb-2">PHP Version</h3>
            <p class="text-sm">
                Required: {{ $results['php']['required'] }}  
                Current: {{ $results['php']['current'] }}
            </p>
            @if ($results['php']['passed'])
                <p class="mt-1 text-xs text-emerald-400">OK</p>
            @else
                <p class="mt-1 text-xs text-red-400">PHP version is lower than required.</p>
            @endif
        </div>

        <div class="bg-slate-800/80 rounded-xl p-4 shadow-lg">
            <h3 class="font-medium mb-2">PHP Extensions</h3>
            @if ($results['extensions']['passed'])
                <p class="text-sm text-emerald-400">All required extensions are enabled.</p>
            @else
                <p class="text-sm text-red-400 mb-2">Missing extensions:</p>
                <ul class="text-sm list-disc list-inside text-red-300">
                    @foreach ($results['extensions']['missing'] as $ext)
                        <li>{{ $ext }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

        <form action="{{ route('installer.requirements.next') }}" method="POST" class="mt-4 flex justify-end">
            @csrf
            <button type="submit"
                    class="px-4 py-2 rounded-full text-sm font-semibold shadow-xl cursor-pointer
                           transition transform duration-200 hover:scale-105
                           bg-gradient-to-r from-indigo-500 to-sky-500">
                Continue
            </button>
        </form>
    </div>
@endsection
