@extends('user.layout')

@section('page_title', 'Sync jobs')
@section('page_subtitle', 'Full sync history from your extension and API.')

@section('content')
<div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
    <form method="get" class="grid grid-cols-1 md:grid-cols-5 gap-2 text-xs">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search error, type, source..."
               class="md:col-span-2 border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 bg-white dark:bg-slate-900 text-xs text-slate-700 dark:text-slate-200">

        <select name="status" class="border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 bg-white dark:bg-slate-900 text-xs text-slate-700 dark:text-slate-200">
            <option value="">All statuses</option>
            @foreach(['success','failed','running','queued'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>

        <select name="type" class="border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 bg-white dark:bg-slate-900 text-xs text-slate-700 dark:text-slate-200">
            <option value="">All types</option>
            @foreach(['profile','posts','post_metrics','audience_insights','demographics','creator_metrics','connections'] as $t)
                <option value="{{ $t }}" @selected(request('type') === $t)>{{ str_replace('_',' ', ucfirst($t)) }}</option>
            @endforeach
        </select>

        <input type="text" name="source" value="{{ request('source') }}" placeholder="Source (extension/api)"
               class="border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 bg-white dark:bg-slate-900 text-xs text-slate-700 dark:text-slate-200">

        <button type="submit"
                class="px-3 py-1.5 rounded-full text-xs font-semibold shadow-md cursor-pointer bg-slate-900 text-slate-50 border border-slate-700 hover:scale-[var(--hover-scale)] transition">
            Filter
        </button>
    </form>
</div>

<div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6">
    @if($jobs->isEmpty())
        <p class="text-sm text-slate-500 dark:text-slate-400">No sync jobs found.</p>
    @else
        <div class="space-y-3 text-xs">
            @foreach($jobs as $job)
                @php
                    $statusColor = match($job->status) {
                        'success' => 'bg-emerald-500/10 text-emerald-500 border-emerald-500/30',
                        'failed'  => 'bg-rose-500/10 text-rose-500 border-rose-500/30',
                        'running' => 'bg-amber-500/10 text-amber-500 border-amber-500/30',
                        default   => 'bg-slate-500/10 text-slate-500 border-slate-500/30',
                    };
                @endphp

                <div class="rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-4">
                    <div class="flex items-center justify-between mb-1">
                        <div class="text-[11px] text-slate-500 dark:text-slate-400">
                            <span class="font-semibold text-slate-800 dark:text-slate-50">{{ ucfirst($job->type) }}</span>
                            <span>·</span>
                            <span>{{ ucfirst($job->source) }}</span>
                            @if($job->items_count)
                                <span>·</span>
                                <span>{{ number_format($job->items_count) }} items</span>
                            @endif
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] border {{ $statusColor }}">
                            {{ ucfirst($job->status) }}
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-2 text-[11px] text-slate-500 dark:text-slate-400">
                        <span>Started: {{ optional($job->started_at)->diffForHumans() ?? $job->created_at->diffForHumans() }}</span>
                        @if($job->finished_at)
                            <span>Finished: {{ $job->finished_at->diffForHumans() }}</span>
                        @endif
                    </div>

                    @if($job->status === 'failed' && $job->error_message)
                        <div class="mt-2 text-[11px] text-rose-400">
                            {{ \Illuminate\Support\Str::limit($job->error_message, 200) }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $jobs->links() }}
        </div>
    @endif
</div>
@endsection
