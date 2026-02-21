@extends('admin.layout')

@section('page_title', 'Create Admin')
@section('page_subtitle', 'Register a new admin account.')

@section('content')
<div class="flex items-center justify-center min-h-[70vh]">
    <div class="w-full max-w-md">
        <div class="bg-slate-950/80 dark:bg-slate-950 rounded-2xl border border-slate-800 shadow-2xl p-6 lg:p-8">
            <div class="mb-6">
                <h1 class="text-base font-semibold text-slate-50">Create Admin Account</h1>
                <p class="text-xs text-slate-400 mt-1">
                    Use this form carefully. Admins have full access to the platform.
                </p>
            </div>

            <form method="POST" action="{{ route('admin.register.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="name" class="block text-xs font-medium text-slate-200">
                        {{ __('Name') }}
                    </label>
                    <input
                        id="name"
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        required
                        autofocus
                        autocomplete="name"
                        class="mt-1 block w-full rounded-xl border-slate-700 bg-slate-900 text-sm text-slate-100
                               focus:border-indigo-500 focus:ring-indigo-500"
                    >
                    @error('name')
                        <p class="mt-2 text-[11px] text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-xs font-medium text-slate-200">
                        {{ __('Email') }}
                    </label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autocomplete="username"
                        class="mt-1 block w-full rounded-xl border-slate-700 bg-slate-900 text-sm text-slate-100
                               focus:border-indigo-500 focus:ring-indigo-500"
                    >
                    @error('email')
                        <p class="mt-2 text-[11px] text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-xs font-medium text-slate-200">
                            {{ __('Password') }}
                        </label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="new-password"
                            class="mt-1 block w-full rounded-xl border-slate-700 bg-slate-900 text-sm text-slate-100
                                   focus:border-indigo-500 focus:ring-indigo-500"
                        >
                        @error('password')
                            <p class="mt-2 text-[11px] text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-medium text-slate-200">
                            {{ __('Confirm Password') }}
                        </label>
                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            class="mt-1 block w-full rounded-xl border-slate-700 bg-slate-900 text-sm text-slate-100
                                   focus:border-indigo-500 focus:ring-indigo-500"
                        >
                    </div>
                </div>

                <button
                    type="submit"
                    class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-xs font-semibold text-white
                           bg-emerald-600 hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500
                           shadow-md hover:shadow-xl transition cursor-pointer"
                >
                    {{ __('Create admin account') }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
