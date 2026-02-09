@extends('user.layout')

@section('page_title', 'Analytics')
@section('page_subtitle', 'Detailed view of your LinkedIn profile metrics.')

@section('content')
    @php
        $status = $summary['status'] ?? 'empty';
    @endphp

    @if($status === 'empty')
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6">
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-50 mb-2">
                No LinkedIn profile connected
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Once you connect and sync your profile, detailed analytics will appear here.
            </p>
        </div>
    @else
        {{-- For now, you can reuse the same cards and charts as on the dashboard, or keep this as a placeholder. --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6">
            <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-50 mb-2">
                Detailed analytics
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
                This page will host filters, date ranges and deeper breakdowns. For now, use the main dashboard for charts and KPIs.
            </p>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Next steps: add date filters, post-type filters, and competitor comparisons here using the same
                service and timeseries data we already have.
            </p>
        </div>
    @endif
@endsection
