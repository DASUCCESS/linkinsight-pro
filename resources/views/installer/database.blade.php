@extends('installer.layout')

@section('step_number', '3')

@section('content')
    <h2 class="text-xl font-semibold mb-4">Database Configuration</h2>

    <form method="POST" action="{{ route('installer.database.save') }}" class="space-y-4">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm mb-1">Database Host</label>
                <input type="text" name="db_host" value="{{ old('db_host', '127.0.0.1') }}"
                       class="w-full rounded-xl bg-slate-800 border border-slate-600 px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-indigo-500/60">
            </div>

            <div>
                <label class="block text-sm mb-1">Database Port</label>
                <input type="text" name="db_port" value="{{ old('db_port', '3306') }}"
                       class="w-full rounded-xl bg-slate-800 border border-slate-600 px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-indigo-500/60">
            </div>

            <div>
                <label class="block text-sm mb-1">Database Name</label>
                <input type="text" name="db_database" value="{{ old('db_database') }}"
                       class="w-full rounded-xl bg-slate-800 border border-slate-600 px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-indigo-500/60">
            </div>

            <div>
                <label class="block text-sm mb-1">Database Username</label>
                <input type="text" name="db_username" value="{{ old('db_username') }}"
                       class="w-full rounded-xl bg-slate-800 border border-slate-600 px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-indigo-500/60">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm mb-1">Database Password</label>
                <input type="password" name="db_password"
                       class="w-full rounded-xl bg-slate-800 border border-slate-600 px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-indigo-500/60">
            </div>
        </div>

        <div class="flex justify-between pt-2">
            <a href="{{ route('installer.permissions') }}"
               class="px-4 py-2 rounded-full text-sm font-semibold shadow cursor-pointer
                      transition transform duration-200 hover:scale-105
                      bg-slate-800 border border-slate-600">
                Back
            </a>

            <button type="submit"
                    class="px-4 py-2 rounded-full text-sm font-semibold shadow-xl cursor-pointer
                           transition transform duration-200 hover:scale-105
                           bg-gradient-to-r from-indigo-500 to-sky-500">
                Save and Run Migration
            </button>
        </div>
    </form>
@endsection
