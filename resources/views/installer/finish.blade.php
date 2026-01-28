@extends('installer.layout')

@section('step_number', '7')

@section('content')
    <h2 class="text-xl font-semibold mb-4">Installation Complete</h2>

    <div class="bg-slate-800/80 rounded-xl p-6 shadow-lg text-center space-y-4">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-emerald-500/10 border border-emerald-500/40 shadow-xl mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-emerald-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                      d="M16.707 5.293a1 1 0 010 1.414l-7.25 7.25a1 1 0 01-1.414 0l-3-3a1 1 0 111.414-1.414L8.5 11.586l6.543-6.543a1 1 0 011.414 0z"
                      clip-rule="evenodd" />
            </svg>
        </div>

        <p class="text-lg font-semibold">
            LinkInsight Pro has been installed successfully.
        </p>

        <p class="text-sm text-slate-300">
            You can now log in to your dashboard and start configuring your LinkedIn analytics, themes, and other settings.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
            <a href="{{ route('login') }}"
               class="px-5 py-2.5 rounded-full text-sm font-semibold shadow-xl cursor-pointer
                      transition transform duration-200 hover:scale-105
                      bg-gradient-to-r from-indigo-500 to-sky-500">
                Go to Login
            </a>

            <a href="{{ url('/') }}"
               class="px-5 py-2.5 rounded-full text-sm font-semibold shadow cursor-pointer
                      transition transform duration-200 hover:scale-105
                      bg-slate-800 border border-slate-600">
                Visit Homepage
            </a>
        </div>
    </div>
@endsection
