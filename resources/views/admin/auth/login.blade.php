@extends('admin.auth-layout')

@section('page_title', 'Admin Login')
@section('page_subtitle', 'Access the LinkInsight Pro admin console.')

@section('content')
<div class="flex items-center justify-center min-h-[70vh] bg-slate-50">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl p-6 lg:p-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-base font-semibold text-slate-900">Admin Sign In</h1>
                    <p class="text-xs text-slate-500 mt-1">
                        Manage users, analytics, themes and platform settings.
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-medium text-slate-700">
                        {{ __('Email') }}
                    </label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                        class="mt-1 block w-full rounded-xl border-slate-300 bg-white text-sm text-slate-900
                               focus:border-indigo-500 focus:ring-indigo-500"
                    >
                    @error('email')
                        <p class="mt-2 text-[11px] text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <div class="flex items-center justify-between">
                        <label for="password" class="block text-xs font-medium text-slate-700">
                            {{ __('Password') }}
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                               class="text-[11px] font-medium text-sky-600 hover:text-sky-500">
                                {{ __('Forgot?') }}
                            </a>
                        @endif
                    </div>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        class="mt-1 block w-full rounded-xl border-slate-300 bg-white text-sm text-slate-900
                               focus:border-indigo-500 focus:ring-indigo-500"
                    >
                    @error('password')
                        <p class="mt-2 text-[11px] text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <label for="remember_admin" class="inline-flex items-center">
                        <input
                            id="remember_admin"
                            type="checkbox"
                            class="rounded border-slate-300 text-indigo-500 shadow-sm focus:ring-indigo-500"
                            name="remember"
                        >
                        <span class="ms-2 text-[11px] text-slate-600">
                            {{ __('Remember this device') }}
                        </span>
                    </label>
                </div>

                <button
                    type="submit"
                    class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-xs font-semibold text-white
                           bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500
                           shadow-md hover:shadow-xl transition cursor-pointer"
                >
                    {{ __('Sign in as admin') }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
