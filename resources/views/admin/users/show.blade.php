@extends('admin.layout')

@section('page_title', 'User detail')
@section('page_subtitle', 'View and manage this account.')

@push('head')
    <script src="https://unpkg.com/alpinejs" defer></script>
@endpush

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6"
     x-data="{ showSuspend:false, showDelete:false }">

    {{-- LEFT SIDE --}}
    <div class="lg:col-span-2">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6">

            {{-- Header --}}
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-500 to-sky-500 flex items-center justify-center text-sm font-semibold text-white shadow">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-slate-800 dark:text-slate-50">
                            {{ $user->name }}
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
                    </div>
                </div>

                @if(session('status_user'))
                    <span class="text-xs px-2 py-1 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-200 font-medium">
                        {{ session('status_user') }}
                    </span>
                @endif
            </div>

            {{-- Form --}}
            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4 text-sm">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}"
                               class="w-full rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm text-slate-700 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-sky-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}"
                               class="w-full rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Role</label>
                        <select name="role"
                                class="w-full rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm">
                            <option value="user" @selected(old('role', $user->role) === 'user')>User</option>
                            <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Status</label>
                        <select name="status"
                                class="w-full rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm">
                            <option value="active" @selected(old('status', $user->status) === 'active')>Active</option>
                            <option value="suspended" @selected(old('status', $user->status) === 'suspended')>Suspended</option>
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button type="submit"
                                class="w-full px-4 py-2 rounded-full text-xs font-semibold shadow-xl cursor-pointer bg-gradient-to-r from-indigo-500 to-sky-500 text-white transition transform hover:scale-[var(--hover-scale)]">
                            Save changes
                        </button>
                    </div>
                </div>
            </form>

            {{-- Suspend & Delete --}}
            <div class="mt-6 flex items-center justify-between border-t border-slate-100 dark:border-slate-800 pt-4">

                {{-- Suspend Button --}}
                <button
                    @click="showSuspend = true"
                    class="px-3 py-1.5 rounded-full text-[11px] font-semibold cursor-pointer border
                           @if($user->status === 'active')
                               border-amber-300 text-amber-700 bg-amber-50
                           @else
                               border-emerald-300 text-emerald-700 bg-emerald-50
                           @endif
                           shadow-sm transition transform hover:scale-[var(--hover-scale)]">
                    {{ $user->status === 'active' ? 'Suspend account' : 'Reactivate account' }}
                </button>

                {{-- Delete Button --}}
                <button
                    @click="showDelete = true"
                    class="px-3 py-1.5 rounded-full text-[11px] font-semibold cursor-pointer border border-rose-300 text-rose-700 bg-rose-50 shadow-sm transition transform hover:scale-[var(--hover-scale)]">
                    Delete user
                </button>

            </div>
        </div>
    </div>

    {{-- RIGHT SIDE --}}
    <div class="space-y-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-4 text-xs">
            <h3 class="text-[11px] font-semibold text-slate-600 dark:text-slate-300 uppercase mb-3">Account info</h3>

            <p class="mb-1 text-slate-500">Registered:
                <span class="text-slate-700 dark:text-slate-100">{{ $user->created_at->toDayDateTimeString() }}</span>
            </p>

            <p class="mb-1 text-slate-500">Last update:
                <span class="text-slate-700 dark:text-slate-100">{{ $user->updated_at->toDayDateTimeString() }}</span>
            </p>

            <p class="mb-1 text-slate-500">Email verified:
                <span class="text-slate-700 dark:text-slate-100">
                    {{ $user->email_verified_at ? $user->email_verified_at->toDayDateTimeString() : 'Not verified' }}
                </span>
            </p>
        </div>
    </div>





    {{-- SUSPEND / REACTIVATE MODAL --}}
    <div
        x-show="showSuspend"
        x-transition.opacity
        class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4"
        x-cloak>
        <div
            x-transition.scale
            class="bg-white dark:bg-slate-900 rounded-2xl p-6 shadow-xl border border-slate-200 dark:border-slate-700">

            <h2 class="text-base font-semibold mb-2">
                {{ $user->status === 'active' ? 'Suspend Account?' : 'Reactivate Account?' }}
            </h2>

            <p class="text-sm text-slate-600 dark:text-slate-300 mb-4">
                {{ $user->status === 'active'
                    ? 'This will prevent the user from logging in until reactivated.'
                    : 'This will restore access to the user account.'
                }}
            </p>

        <div class="mt-5 grid grid-cols-2 gap-3">
            <button
                type="button"
                @click="showSuspend = false"
                class="w-full h-10 inline-flex items-center justify-center px-4 text-xs font-semibold rounded-full
                    border border-slate-300 dark:border-slate-600
                    bg-white dark:bg-slate-900
                    text-slate-700 dark:text-slate-200
                    hover:bg-slate-50 dark:hover:bg-slate-800
                    transition">
                Cancel
            </button>

            <form method="POST" action="{{ route('admin.users.suspend', $user) }}" class="w-full">
                @csrf
                <button
                    type="submit"
                    class="w-full h-10 inline-flex items-center justify-center px-4 text-xs font-semibold rounded-full
                        text-white shadow-md transition
                        {{ $user->status === 'active'
                                ? 'bg-amber-600 hover:bg-amber-700'
                                : 'bg-emerald-600 hover:bg-emerald-700' }}">
                    Confirm
                </button>
            </form>
        </div>

        </div>
    </div>





    {{-- DELETE MODAL --}}
    <div
        x-show="showDelete"
        x-transition.opacity
        class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4"
        x-cloak>
        <div
            x-transition.scale
            class="bg-white dark:bg-slate-900 rounded-2xl p-6 shadow-xl border border-rose-300">

            <h2 class="text-base font-semibold mb-2 text-rose-700">
                Delete User?
            </h2>

            <p class="text-sm text-slate-600 dark:text-slate-300 mb-4">
                This action is permanent and cannot be undone.
            </p>

        <div class="mt-5 grid grid-cols-2 gap-3">
            <button
                type="button"
                @click="showDelete = false"
                class="w-full h-10 inline-flex items-center justify-center px-4 text-xs font-semibold rounded-full
                    border border-slate-300 dark:border-slate-600
                    bg-white dark:bg-slate-900
                    text-slate-700 dark:text-slate-200
                    hover:bg-slate-50 dark:hover:bg-slate-800
                    transition">
                Cancel
            </button>

            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="w-full">
                @csrf
                @method('DELETE')
                <button
                    type="submit"
                    class="w-full h-10 inline-flex items-center justify-center px-4 text-xs font-semibold rounded-full
                        text-white bg-rose-600 hover:bg-rose-700 shadow-md transition">
                    Delete Permanently
                </button>
            </form>
        </div>

        </div>
    </div>



</div>
@endsection
