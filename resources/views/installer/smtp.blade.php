@extends('installer.layout')

@section('step_number', '5')

@section('content')
    <h2 class="text-xl font-semibold mb-4">SMTP Configuration</h2>

    <form method="POST" action="{{ route('installer.smtp.save') }}" class="space-y-4">
        @csrf

        <div class="bg-slate-800/80 rounded-xl p-4 shadow-lg space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1">Mail Host</label>
                    <input type="text" name="mail_host" value="{{ old('mail_host') }}"
                           class="w-full rounded-xl bg-slate-900 border border-slate-600 px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-indigo-500/60">
                </div>

                <div>
                    <label class="block text-sm mb-1">Mail Port</label>
                    <input type="text" name="mail_port" value="{{ old('mail_port') }}"
                           class="w-full rounded-xl bg-slate-900 border border-slate-600 px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-indigo-500/60">
                </div>

                <div>
                    <label class="block text-sm mb-1">Mail Username</label>
                    <input type="text" name="mail_username" value="{{ old('mail_username') }}"
                           class="w-full rounded-xl bg-slate-900 border border-slate-600 px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-indigo-500/60">
                </div>

                <div>
                    <label class="block text-sm mb-1">Mail Password</label>
                    <input type="password" name="mail_password"
                           class="w-full rounded-xl bg-slate-900 border border-slate-600 px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-indigo-500/60">
                </div>

                <div>
                    <label class="block text-sm mb-1">Encryption</label>
                    <input type="text" name="mail_encryption" value="{{ old('mail_encryption', 'tls') }}"
                           class="w-full rounded-xl bg-slate-900 border border-slate-600 px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-indigo-500/60">
                </div>

                <div>
                    <label class="block text-sm mb-1">From Address</label>
                    <input type="email" name="mail_from_address" value="{{ old('mail_from_address') }}"
                           class="w-full rounded-xl bg-slate-900 border border-slate-600 px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-indigo-500/60">
                </div>

                <div>
                    <label class="block text-sm mb-1">From Name</label>
                    <input type="text" name="mail_from_name" value="{{ old('mail_from_name') }}"
                           class="w-full rounded-xl bg-slate-900 border border-slate-600 px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-indigo-500/60">
                </div>

                <div>
                    <label class="block text-sm mb-1">Test Email (optional)</label>
                    <input type="email" name="test_email" value="{{ old('test_email') }}"
                           class="w-full rounded-xl bg-slate-900 border border-slate-600 px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-indigo-500/60">
                    <p class="mt-1 text-xs text-slate-400">
                        If provided, a test email will be sent after saving.
                    </p>
                </div>
            </div>
        </div>

        <div class="flex justify-between pt-2">
            <a href="{{ route('installer.admin') }}"
               class="px-4 py-2 rounded-full text-sm font-semibold shadow cursor-pointer
                      transition transform duration-200 hover:scale-105
                      bg-slate-800 border border-slate-600">
                Back
            </a>

            <button type="submit"
                    class="px-4 py-2 rounded-full text-sm font-semibold shadow-xl cursor-pointer
                           transition transform duration-200 hover:scale-105
                           bg-gradient-to-r from-indigo-500 to-sky-500">
                Save and Continue
            </button>
        </div>
    </form>
@endsection
