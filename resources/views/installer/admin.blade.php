@extends('installer.layout')

@section('step_number', '4')

@section('content')
    <h2 class="text-xl font-semibold mb-4">Admin & Site Configuration</h2>

    <form method="POST" action="{{ route('installer.admin.save') }}" class="space-y-4">
        @csrf

        <div class="bg-slate-800/80 rounded-xl p-4 shadow-lg space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1">Site Name</label>
                    <input type="text" name="site_name" value="{{ old('site_name') }}"
                           class="w-full rounded-xl bg-slate-900 border border-slate-600 px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-indigo-500/60">
                </div>

                <div>
                    <label class="block text-sm mb-1">Timezone</label>
                    <select name="timezone"
                            class="w-full rounded-xl bg-slate-900 border border-slate-600 px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-indigo-500/60">
                        @php
                            $selectedTz = old('timezone', $defaultTimezone ?? config('app.timezone'));
                        @endphp
                        @foreach ($timezones ?? [] as $tz)
                            <option value="{{ $tz }}" @selected($selectedTz === $tz)>{{ $tz }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm mb-1">Locale</label>
                    <select name="locale"
                            class="w-full rounded-xl bg-slate-900 border border-slate-600 px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-indigo-500/60">
                        @php
                            $selectedLocale = old('locale', $defaultLocale ?? app()->getLocale());
                        @endphp
                        @foreach ($locales ?? [] as $locale)
                            <option value="{{ $locale }}" @selected($selectedLocale === $locale)>{{ strtoupper($locale) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-slate-800/80 rounded-xl p-4 shadow-lg space-y-4">
            <h3 class="font-medium mb-2">Admin Account</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1">Admin Name</label>
                    <input type="text" name="admin_name" value="{{ old('admin_name') }}"
                           class="w-full rounded-xl bg-slate-900 border border-slate-600 px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-indigo-500/60">
                </div>

                <div>
                    <label class="block text-sm mb-1">Admin Email</label>
                    <input type="email" name="admin_email" value="{{ old('admin_email') }}"
                           class="w-full rounded-xl bg-slate-900 border border-slate-600 px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-indigo-500/60">
                </div>

                <div>
                    <label class="block text-sm mb-1">Admin Password</label>
                    <input type="password" name="admin_password"
                           class="w-full rounded-xl bg-slate-900 border border-slate-600 px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-indigo-500/60">
                </div>

                <div>
                    <label class="block text-sm mb-1">Confirm Password</label>
                    <input type="password" name="admin_password_confirmation"
                           class="w-full rounded-xl bg-slate-900 border border-slate-600 px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-indigo-500/60">
                </div>
            </div>
        </div>

        <div class="flex justify-between pt-2">
            <a href="{{ route('installer.database') }}"
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
