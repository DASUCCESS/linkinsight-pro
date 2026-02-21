@extends(theme_view('layouts.public'))

@section('content')
<section class="py-16 lg:py-20 bg-white">
    <div class="max-w-md mx-auto px-4">

        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-6 lg:p-8">

            <div class="mb-6 text-center">
                <h1 class="text-xl font-semibold text-slate-800">
                    Create your account
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    Start tracking your LinkedIn growth today
                </p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf

                <div>
                    <x-input-label for="name" :value="__('Name')" />
                    <x-text-input id="name" type="text" name="name"
                        :value="old('name')" required autofocus
                        class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" type="email" name="email"
                        :value="old('email')" required
                        class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password" :value="__('Password')" />
                    <x-text-input id="password" type="password"
                        name="password" required class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                    <x-text-input id="password_confirmation" type="password"
                        name="password_confirmation" required
                        class="mt-1 block w-full" />
                </div>

                <button type="submit"
                        class="w-full py-2.5 rounded-xl bg-indigo-600 text-white font-semibold
                               hover:bg-indigo-700 shadow-md hover:shadow-lg transition">
                    Create account
                </button>

                <p class="text-center text-sm text-slate-500">
                    Already registered?
                    <a href="{{ route('login') }}"
                       class="font-semibold text-indigo-600 hover:text-indigo-700">
                        Sign in
                    </a>
                </p>
            </form>

        </div>
    </div>
</section>
@endsection
