@extends('installer.layout')

@section('step_number', '6')

@section('content')
    <h2 class="text-xl font-semibold mb-4">License Configuration</h2>

    <form method="POST" action="{{ route('installer.license.save') }}" class="space-y-4">
        @csrf

        <div class="bg-slate-800/80 rounded-xl p-4 shadow-lg space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1">License Mode</label>
                    <select name="mode"
                            class="w-full rounded-xl bg-slate-900 border border-slate-600 px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-indigo-500/60">
                        @php
                            $selectedMode = old('mode', $defaultMode ?? 'codecanyon');
                        @endphp
                        <option value="codecanyon" @selected($selectedMode === 'codecanyon')>Codecanyon Purchase</option>
                        <option value="external" @selected($selectedMode === 'external')>External License Server</option>
                        <option value="owner" @selected($selectedMode === 'owner')>Owner Mode</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm mb-1">Purchase Code</label>
                    <input type="text" name="purchase_code" value="{{ old('purchase_code') }}"
                           class="w-full rounded-xl bg-slate-900 border border-slate-600 px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-indigo-500/60">
                </div>

                <div>
                    <label class="block text-sm mb-1">Buyer Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="w-full rounded-xl bg-slate-900 border border-slate-600 px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-indigo-500/60">
                </div>

                <div>
                    <label class="block text-sm mb-1">Domain</label>
                    <input type="text" name="domain" value="{{ old('domain', $appUrl ?? config('app.url')) }}"
                           class="w-full rounded-xl bg-slate-900 border border-slate-600 px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-indigo-500/60">
                    <p class="mt-1 text-xs text-slate-400">
                        Prefilled with your application URL. Adjust if needed.
                    </p>
                </div>
            </div>
        </div>

        <div class="flex justify-between pt-2">
            <a href="{{ route('installer.smtp') }}"
               class="px-4 py-2 rounded-full text-sm font-semibold shadow cursor-pointer
                      transition transform duration-200 hover:scale-105
                      bg-slate-800 border border-slate-600">
                Back
            </a>

            <button type="submit"
                    class="px-4 py-2 rounded-full text-sm font-semibold shadow-xl cursor-pointer
                           transition transform duration-200 hover:scale-105
                           bg-gradient-to-r from-indigo-500 to-sky-500">
                Validate and Finish
            </button>
        </div>
    </form>
@endsection
