@extends('admin.layout')

@section('page_title', 'Users')
@section('page_subtitle', 'Manage accounts, roles and status.')

@section('content')
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
            <div>
                <h2 class="text-base font-semibold text-slate-800 dark:text-slate-50">Users</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    View all registered users, update their roles and create new accounts.
                </p>
            </div>

            <div class="flex flex-col md:flex-row md:items-center gap-3">
                <form method="GET" class="flex flex-wrap items-center gap-2 text-xs md:text-sm">
                    <input
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Search name or email"
                        class="h-9 rounded-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                    >

                    <select
                        name="role"
                        class="h-9 rounded-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                    >
                        <option value="">All roles</option>
                        <option value="user" @selected(request('role') === 'user')>User</option>
                        <option value="admin" @selected(request('role') === 'admin')>Admin</option>
                    </select>

                    <select
                        name="status"
                        class="h-9 rounded-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                    >
                        <option value="">All status</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="suspended" @selected(request('status') === 'suspended')>Suspended</option>
                    </select>

                    <button
                        type="submit"
                        class="h-9 px-4 rounded-full text-xs font-semibold shadow cursor-pointer bg-slate-900 dark:bg-slate-50 text-slate-50 dark:text-slate-900 transition transform hover:scale-[var(--hover-scale)] inline-flex items-center justify-center"
                    >
                        Filter
                    </button>

                    <a
                        href="{{ route('admin.users.create') }}"
                        class="h-9 inline-flex items-center justify-center px-4 rounded-full text-[11px] font-semibold shadow-xl cursor-pointer bg-gradient-to-r from-indigo-500 to-sky-500 text-white transition transform hover:scale-[var(--hover-scale)]"
                    >
                        + Add user
                    </a>
                </form>
            </div>
        </div>

        @if(session('status_user'))
            <div class="mb-3 text-xs px-3 py-2 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200">
                {{ session('status_user') }}
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="min-w-full text-xs md:text-sm text-left">
                <thead class="text-[11px] uppercase tracking-wide text-slate-400 dark:text-slate-500">
                <tr>
                    <th class="py-2 pr-4">User</th>
                    <th class="py-2 px-4">Email</th>
                    <th class="py-2 px-4">Role</th>
                    <th class="py-2 px-4">Status</th>
                    <th class="py-2 px-4 text-right">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-200">
                @forelse($users as $user)
                    <tr>
                        <td class="py-3 pr-4 align-middle">
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-full bg-gradient-to-br from-indigo-500 to-sky-500 flex items-center justify-center text-[11px] font-semibold text-white shadow">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="font-medium">{{ $user->name }}</div>
                                    <div class="text-[11px] text-slate-400">
                                        ID #{{ $user->id }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-4 align-middle">
                            {{ $user->email }}
                        </td>
                        <td class="py-3 px-4 align-middle">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold
                                @if($user->role === 'admin')
                                    bg-indigo-50 text-indigo-600 border border-indigo-200
                                @else
                                    bg-slate-100 dark:bg-slate-800 text-slate-500 border border-slate-200 dark:border-slate-700
                                @endif">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 align-middle">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold
                                @if($user->status === 'active')
                                    bg-emerald-50 text-emerald-600 border border-emerald-200
                                @else
                                    bg-rose-50 text-rose-600 border border-rose-200
                                @endif">
                                {{ ucfirst($user->status) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 align-middle text-right">
                            <a href="{{ route('admin.users.show', $user) }}"
                               class="inline-flex items-center px-3 py-1.5 rounded-full text-[11px] font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-100 shadow-sm cursor-pointer transition transform hover:scale-[var(--hover-scale)]">
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-4 text-center text-xs text-slate-400">
                            No users found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </div>
@endsection
