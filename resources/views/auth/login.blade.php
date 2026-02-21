@extends(theme_view('layouts.public'))

@section('content')
<section class="py-16 lg:py-20 bg-white">
    <div class="max-w-md mx-auto px-4">

        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-6 lg:p-8">

            <div class="mb-6 text-center">
                <h1 class="text-xl font-semibold text-slate-800">
                    Sign in to your account
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    Access your LinkedIn analytics dashboard
                </p>
            </div>

            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" type="email" name="email"
                        :value="old('email')" required autofocus
                        class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <div class="flex justify-between items-center">
                        <x-input-label for="password" :value="__('Password')" />

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                               class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">
                                Forgot password?
                            </a>
                        @endif
                    </div>

                    <x-text-input id="password" type="password" name="password"
                        required class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <label class="flex items-center">
                    <input type="checkbox" name="remember"
                           class="rounded border-slate-300 text-indigo-600 shadow-sm">
                    <span class="ml-2 text-sm text-slate-600">Remember me</span>
                </label>

                <button type="submit"
                        class="w-full py-2.5 rounded-xl bg-indigo-600 text-white font-semibold
                               hover:bg-indigo-700 shadow-md hover:shadow-lg transition">
                    Log in
                </button>

                @if (Route::has('register'))
                <p class="text-center text-sm text-slate-500">
                    Don't have an account?
                    <a href="{{ route('register') }}"
                       class="font-semibold text-indigo-600 hover:text-indigo-700">
                        Create one
                    </a>
                </p>
                @endif
            </form>

        </div>
    </div>
</section>
@endsection
