@extends('user.layout')

@section('page_title', 'Account settings')
@section('page_subtitle', 'Manage your profile, email and password.')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">

        {{-- Profile information --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm p-4 sm:p-6">
            <div class="mb-4">
                <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-50">
                    Profile information
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Update your name, email address and other basic profile details.
                </p>
            </div>

            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        {{-- Update password --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm p-4 sm:p-6">
            <div class="mb-4">
                <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-50">
                    Update password
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Choose a strong password to keep your account secure.
                </p>
            </div>

            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        {{-- Delete account --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm p-4 sm:p-6">
            <div class="mb-4">
                <h2 class="text-sm font-semibold text-rose-600 dark:text-rose-400">
                    Delete account
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Permanently delete your account and all associated data.
                </p>
            </div>

            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
@endsection
