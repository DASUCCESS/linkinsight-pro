@extends(theme_view('layouts.public'))

@section('content')
<section class="py-16 bg-white">
    <div class="max-w-md mx-auto px-4">

        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-6 lg:p-8">

            <h1 class="text-lg font-semibold text-slate-800 mb-4">
                Set a new password
            </h1>

            <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
                @csrf

                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" type="email" name="email"
                        :value="old('email', $request->email)" required
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
                        name="password_confirmation" required class="mt-1 block w-full" />
                </div>

                <button type="submit"
                        class="w-full py-2.5 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-700">
                    Reset password
                </button>
            </form>

        </div>
    </div>
</section>
@endsection
