@extends(theme_view('layouts.public'))

@section('content')
<section class="py-16 bg-white">
    <div class="max-w-md mx-auto px-4">

        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-6 lg:p-8 text-center">

            <h1 class="text-lg font-semibold text-slate-800 mb-2">
                Verify your email
            </h1>

            <p class="text-sm text-slate-500 mb-4">
                Check your inbox and click the verification link.
            </p>

            @if (session('status') == 'verification-link-sent')
                <div class="mb-4 text-green-600 text-sm font-medium">
                    Verification link sent again.
                </div>
            @endif

            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit"
                        class="w-full py-2.5 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-700">
                    Resend verification email
                </button>
            </form>

        </div>
    </div>
</section>
@endsection
