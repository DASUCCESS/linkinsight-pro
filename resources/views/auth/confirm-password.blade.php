@extends(theme_view('layouts.public'))

@section('content')
<section class="py-16 bg-white">
    <div class="max-w-md mx-auto px-4">

        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-6 lg:p-8">

            <h1 class="text-lg font-semibold text-slate-800 mb-4">
                Confirm your password
            </h1>

            <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
                @csrf

                <div>
                    <x-input-label for="password" :value="__('Password')" />
                    <x-text-input id="password" type="password"
                        name="password" required class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <button type="submit"
                        class="w-full py-2.5 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-700">
                    Confirm password
                </button>
            </form>

        </div>
    </div>
</section>
@endsection
