@extends(theme_view('layouts.public'))

@section('content')
<section class="py-16 bg-white">
    <div class="max-w-md mx-auto px-4">

        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-6 lg:p-8">

            <h1 class="text-lg font-semibold text-slate-800 mb-2">
                Reset your password
            </h1>

            <p class="text-sm text-slate-500 mb-4">
                Enter your email and we will send you a reset link.
            </p>

            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                @csrf

                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" type="email" name="email"
                        :value="old('email')" required autofocus
                        class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <button type="submit"
                        class="w-full py-2.5 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-700">
                    Send reset link
                </button>
            </form>

        </div>
    </div>
</section>
@endsection
