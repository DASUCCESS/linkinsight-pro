@extends('user.layout')

@section('page_title', 'Analytics')
@section('page_subtitle', 'Detailed view of your LinkedIn profile metrics and posts.')

@section('content')
    @php
        $status = $summary['status'] ?? 'empty';

        $profile   = $summary['profile'] ?? [];
        $times     = $summary['timeseries'] ?? [];
        $postsMeta = $summary['posts_overview'] ?? [];
        $filter    = $summary['filter'] ?? [];

        // keep demographics only
        $demographics   = $summary['audience_demographics'] ?? [];
        $demoDate       = $demographics['snapshot_date'] ?? null;
        $followersCount = $demographics['followers_count'] ?? null;
        $demoCats       = $demographics['demographics'] ?? [];

        // normalize profile name to avoid "Unknown" showing
        $rawName = trim((string) ($profile['name'] ?? ''));
        $isUnknownName = $rawName === '' || strtolower($rawName) === 'unknown';

        $rawLinkedinId = trim((string) ($profile['linkedin_id'] ?? ''));
        $linkedinId = ($rawLinkedinId === '' || strtolower($rawLinkedinId) === 'unknown') ? null : $rawLinkedinId;

        $displayName = $isUnknownName ? ($linkedinId ?: 'LinkedIn profile') : $rawName;

        $rawHeadline = trim((string) ($profile['headline'] ?? ''));
        $headline = ($rawHeadline === '' || strtolower($rawHeadline) === 'unknown') ? null : $rawHeadline;

        $rawPublicUrl = trim((string) ($profile['public_url'] ?? ''));
        $publicUrl = ($rawPublicUrl === '' || strtolower($rawPublicUrl) === 'unknown') ? null : $rawPublicUrl;

        $initialsSource = $displayName ?: 'LI';
    @endphp

    @if($status === 'empty')
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6">
            <h2 class="text-base font-semibold text-slate-800 dark:text-slate-50 mb-2">
                No LinkedIn profile connected
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-2">
                Once you connect and sync your profile, detailed analytics will appear here.
            </p>
        </div>
    @else
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
                <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-50">
                    AI recommendations & analytics insights
                </h3>
                <span class="inline-flex items-center justify-center text-[11px] px-3 py-1.5 rounded-xl font-semibold shadow-sm
                             border border-indigo-200 dark:border-slate-700
                             bg-indigo-50 dark:bg-slate-800 text-indigo-700 dark:text-slate-100 w-fit">
                    {{ strtoupper($aiRecommendations['source'] ?? 'local') }}
                </span>
            </div>

            <div class="rounded-2xl border border-indigo-100 dark:border-slate-700 bg-indigo-50/70 dark:bg-slate-800/60 p-4 mb-4">
                <p class="text-sm leading-6 text-slate-700 dark:text-slate-200">
                    {{ $aiRecommendations['summary'] ?? 'No AI summary available.' }}
                </p>
            </div>

            @if(!empty($aiRecommendations['recommendations'] ?? []))
                <ul class="space-y-3">
                    @foreach(($aiRecommendations['recommendations'] ?? []) as $item)
                        <li class="flex items-start gap-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 p-3">
                            <span class="mt-1 h-2 w-2 rounded-full bg-indigo-600 shrink-0"></span>
                            <span class="text-sm leading-6 text-slate-700 dark:text-slate-200">{{ $item }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                <div class="flex items-center gap-3">
                    @if(!empty($profile['profile_image_url']))
                        <img src="{{ $profile['profile_image_url'] }}"
                             alt="{{ $displayName }}"
                             class="h-11 w-11 rounded-full object-cover border border-slate-200 dark:border-slate-700">
                    @endif
                    <div>
                        <div class="text-sm font-semibold text-slate-800 dark:text-slate-50">
                            {{ $displayName }}
                        </div>
                        @if($headline)
                            <div class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $headline }}
                            </div>
                        @endif
                        @if($publicUrl)
                            <a href="{{ $publicUrl }}" target="_blank"
                               class="inline-flex items-center mt-1 text-[11px] font-medium text-indigo-600 dark:text-sky-400 hover:text-indigo-700 dark:hover:text-sky-300 cursor-pointer">
                                View on LinkedIn
                            </a>
                        @endif
                    </div>
                </div>

                <form method="get" class="flex flex-wrap items-center gap-2 text-xs">
                    <input type="hidden" name="profile_id" value="{{ request('profile_id') }}">
                    <input type="date"
                           name="from"
                           value="{{ request('from', $filter['from'] ?? '') }}"
                           class="border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 bg-white dark:bg-slate-900 text-xs text-slate-700 dark:text-slate-200 shadow-sm">
                    <input type="date"
                           name="to"
                           value="{{ request('to', $filter['to'] ?? '') }}"
                           class="border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 bg-white dark:bg-slate-900 text-xs text-slate-700 dark:text-slate-200 shadow-sm">
                    <button type="submit"
                            class="inline-flex items-center justify-center px-3 py-2 rounded-xl text-xs font-semibold shadow-md cursor-pointer
                                   bg-indigo-600 hover:bg-indigo-700 text-white border border-indigo-600
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500/30
                                   hover:scale-[var(--hover-scale)] transition">
                        Apply range
                    </button>
                </form>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Connections card REMOVED --}}
                <div class="rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-4 shadow">
                    <div class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-1">
                        Followers
                    </div>
                    <div class="flex items-end justify-between">
                        <div class="text-xl font-semibold text-slate-900 dark:text-slate-50">
                            {{ number_format($profile['followers'] ?? 0) }}
                        </div>
                        <div class="text-[11px] {{ ($profile['followers_change'] ?? 0) >= 0 ? 'text-emerald-500' : 'text-rose-500' }}">
                            {{ ($profile['followers_change'] ?? 0) >= 0 ? '+' : '' }}{{ $profile['followers_change'] ?? 0 }}
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-4 shadow">
                    <div class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-1">
                        Profile views (range)
                    </div>
                    <div class="text-xl font-semibold text-slate-900 dark:text-slate-50">
                        {{ number_format($profile['views_total'] ?? 0) }}
                    </div>
                </div>

                <div class="rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-4 shadow">
                    <div class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-1">
                        Search appearances (range)
                    </div>
                    <div class="text-xl font-semibold text-slate-900 dark:text-slate-50">
                        {{ number_format($profile['search_total'] ?? 0) }}
                    </div>
                </div>

                <div class="rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-4 shadow">
                    <div class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-1">
                        Total posts
                    </div>
                    <div class="text-xl font-semibold text-slate-900 dark:text-slate-50">
                        {{ number_format($postsMeta['total_posts'] ?? 0) }}
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
                <div class="rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-4 shadow">
                    <div class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-1">
                        Impressions (range)
                    </div>
                    <div class="text-xl font-semibold text-slate-900 dark:text-slate-50">
                        {{ number_format($postsMeta['impressions_30d'] ?? 0) }}
                    </div>
                </div>
                <div class="rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-4 shadow">
                    <div class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-1">
                        Engagements (range)
                    </div>
                    <div class="text-xl font-semibold text-slate-900 dark:text-slate-50">
                        {{ number_format($postsMeta['engagements_30d'] ?? 0) }}
                    </div>
                </div>
                <div class="rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-4 shadow">
                    <div class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-1">
                        Avg engagement rate
                    </div>
                    <div class="text-xl font-semibold text-slate-900 dark:text-slate-50">
                        {{ number_format($postsMeta['avg_engagement_rate_30d'] ?? 0, 2) }}%
                    </div>
                </div>
            </div>
        </div>

        {{-- Creator audience analytics REMOVED --}}
        {{-- Connections sample REMOVED --}}

        {{-- Followers demographics --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
                <div>
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-50">
                        Followers demographics
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Breakdown of your followers by category.
                    </p>
                </div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400">
                    {{ $demoDate ? 'Snapshot date: ' . $demoDate : 'No snapshot date' }}
                </div>
            </div>

            @if(empty($demoCats))
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    No demographics available yet. Sync Followers demographics to populate this section.
                </p>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    @foreach($demoCats as $cat => $items)
                        @php
                            $title = ucwords(str_replace('_', ' ', (string) $cat));
                            $items = is_array($items) ? $items : [];
                        @endphp
                        <div class="rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-4">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-2">
                                <div class="text-sm font-semibold text-slate-800 dark:text-slate-50">
                                    {{ $title }}
                                </div>
                                @if(!empty($followersCount))
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400">
                                        Followers: {{ number_format($followersCount) }}
                                    </div>
                                @endif
                            </div>

                            <div class="space-y-1 text-xs">
                                @foreach(array_slice($items, 0, 10) as $it)
                                    @php
                                        $label = trim((string) ($it['label'] ?? ''));
                                        $isUnknownLabel = $label === '' || strtolower($label) === 'unknown';
                                    @endphp

                                    @if(!$isUnknownLabel)
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="text-slate-700 dark:text-slate-200 line-clamp-1">
                                                {{ $label }}
                                            </span>
                                            <span class="text-slate-500 dark:text-slate-400 shrink-0">
                                                {{ number_format((float) ($it['percent'] ?? 0), 1) }}%
                                            </span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>

                            @if(count($items) > 10)
                                <p class="mt-2 text-[11px] text-slate-500 dark:text-slate-400">
                                    Showing top 10 items.
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Posts performance list --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6 overflow-hidden">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                <div>
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-50">
                        Posts performance
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Search and filter all posts synced so far.
                    </p>
                </div>
                <form method="get" class="flex flex-wrap items-center gap-2 text-xs">
                    <input type="hidden" name="profile_id" value="{{ request('profile_id') }}">
                    <input type="hidden" name="from" value="{{ request('from', $filter['from'] ?? '') }}">
                    <input type="hidden" name="to" value="{{ request('to', $filter['to'] ?? '') }}">
                    <input type="text"
                           name="q"
                           value="{{ request('q') }}"
                           placeholder="Search content or URL..."
                           class="border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 bg-white dark:bg-slate-900 text-xs text-slate-700 dark:text-slate-200 shadow-sm min-w-[200px]">
                    <select name="type"
                            class="border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 bg-white dark:bg-slate-900 text-xs text-slate-700 dark:text-slate-200 shadow-sm">
                        <option value="">All types</option>
                        <option value="post" @selected(request('type') === 'post')>Post</option>
                        <option value="article" @selected(request('type') === 'article')>Article</option>
                        <option value="repost" @selected(request('type') === 'repost')>Repost</option>
                    </select>
                    <button type="submit"
                            class="inline-flex items-center justify-center px-3 py-2 rounded-xl text-xs font-semibold shadow-md cursor-pointer
                                   bg-indigo-600 hover:bg-indigo-700 text-white border border-indigo-600
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500/30
                                   hover:scale-[var(--hover-scale)] transition">
                        Filter
                    </button>
                </form>
            </div>

            @if(!$postsPaginated || $postsPaginated->isEmpty())
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    No posts found for the selected filters.
                </p>
            @else
                <div class="overflow-x-auto text-xs">
                    <table class="min-w-full">
                        <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="px-3 py-3 text-left font-medium">Post</th>
                            <th class="px-3 py-3 text-left font-medium">Type</th>
                            <th class="px-3 py-3 text-left font-medium">Published</th>
                            <th class="px-3 py-3 text-right font-medium">Impressions</th>
                            <th class="px-3 py-3 text-right font-medium">Reactions</th>
                            <th class="px-3 py-3 text-right font-medium">Comments</th>
                            <th class="px-3 py-3 text-right font-medium">Reposts</th>
                            <th class="px-3 py-3 text-right font-medium">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @foreach($postsPaginated as $post)
                            @php
                                $latestMetric = $post->latestMetric ?? $post->metrics?->sortByDesc('metric_date')->first();
                            @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/70 transition align-top">
                                <td class="px-3 py-3 max-w-xs">
                                    <div class="flex flex-col">
                                        <span class="text-slate-800 dark:text-slate-50 line-clamp-2">
                                            {{ $post->content_excerpt ?: 'No content preview' }}
                                        </span>
                                        @if($post->permalink)
                                            <a href="{{ $post->permalink }}" target="_blank"
                                               class="text-[11px] font-medium text-indigo-600 dark:text-sky-400 hover:text-indigo-700 dark:hover:text-sky-300 cursor-pointer mt-1">
                                                Open on LinkedIn
                                            </a>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-slate-600 dark:text-slate-300 whitespace-nowrap">
                                    {{ ucfirst($post->post_type ?? 'post') }}
                                </td>
                                <td class="px-3 py-3 text-slate-600 dark:text-slate-300 whitespace-nowrap">
                                    {{ optional($post->posted_at)->format('Y-m-d H:i') ?? 'N/A' }}
                                </td>
                                <td class="px-3 py-3 text-right text-slate-700 dark:text-slate-200 whitespace-nowrap">
                                    {{ number_format($latestMetric->impressions ?? 0) }}
                                </td>
                                <td class="px-3 py-3 text-right text-slate-700 dark:text-slate-200 whitespace-nowrap">
                                    {{ number_format($latestMetric->reactions ?? 0) }}
                                </td>
                                <td class="px-3 py-3 text-right text-slate-700 dark:text-slate-200 whitespace-nowrap">
                                    {{ number_format($latestMetric->comments ?? 0) }}
                                </td>
                                <td class="px-3 py-3 text-right text-slate-700 dark:text-slate-200 whitespace-nowrap">
                                    {{ number_format($latestMetric->reposts ?? 0) }}
                                </td>
                                <td class="px-3 py-3 text-right">
                                    <div class="inline-flex flex-wrap items-center justify-end gap-2 min-w-[160px]">
                                        <button type="button"
                                                class="ai-reply-comment-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[11px] font-semibold shadow-sm
                                                       border border-indigo-200 dark:border-indigo-700
                                                       bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-200
                                                       cursor-pointer hover:bg-indigo-100 dark:hover:bg-indigo-900/30
                                                       hover:scale-[var(--hover-scale)] transition"
                                                data-context="{{ e(($post->content_excerpt ?: 'LinkedIn post') . ' | type: ' . ($post->post_type ?: 'post')) }}"
                                                data-post-url="{{ e($post->permalink) }}"
                                                title="Reply comment idea with AI">
                                            <span>✨</span>
                                            <span>Reply Idea</span>
                                        </button>
                                        @if($post->permalink)
                                            <a href="{{ $post->permalink }}" target="_blank"
                                               class="inline-flex items-center px-3 py-1.5 rounded-xl text-[11px] font-semibold shadow-sm
                                                      border border-slate-200 dark:border-slate-700
                                                      bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-100
                                                      cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-700
                                                      hover:scale-[var(--hover-scale)] transition">
                                                View
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 overflow-x-auto">
                    {{ $postsPaginated->links() }}
                </div>
            @endif
        </div>
    @endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.ai-reply-comment-btn');
    if (!buttons.length) return;

    const spinnerMarkup = '<svg class="animate-spin h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"><circle class="opacity-30" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle><path class="opacity-90" d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path></svg>';

    function setButtonLoading(button, loading, loadingLabel = 'Regenerating...') {
        if (!button) return;

        if (loading) {
            button.disabled = true;
            button.classList.add('opacity-70', 'pointer-events-none');
            button.dataset.originalLabel = button.dataset.originalLabel || button.innerHTML;
            button.innerHTML = `${spinnerMarkup}<span>${loadingLabel}</span>`;
            return;
        }

        button.disabled = false;
        button.classList.remove('opacity-70', 'pointer-events-none');
        button.innerHTML = button.dataset.originalLabel || '<span>✨</span><span>Reply Idea</span>';
    }

    async function requestReplyIdea(contextText) {
        const res = await fetch(@json(route('dashboard.ai-assistant')), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': @json(csrf_token()),
            },
            body: JSON.stringify({
                action: 'reply_comment',
                input_text: contextText || null,
            }),
        });

        if (!res.ok) throw new Error('Request failed');
        const json = await res.json();
        return (json?.data?.items || [])[0] || 'No suggestion returned.';
    }

    if (!document.getElementById('replyAiModal')) {
        const modal = document.createElement('div');
        modal.id = 'replyAiModal';
        modal.className = 'hidden fixed inset-0 z-50';
        modal.innerHTML = `
            <div class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm" data-close="1"></div>
            <div class="relative min-h-full flex items-center justify-center p-4 sm:p-6">
                <div class="w-full max-w-2xl bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xl overflow-hidden">
                    <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/70">
                        <div>
                            <h4 class="text-base font-semibold text-slate-800 dark:text-slate-50">AI Reply Idea</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Generate a quick suggested response for this post.</p>
                        </div>
                        <button data-close="1"
                                type="button"
                                class="inline-flex items-center justify-center px-3 py-2 rounded-xl text-xs font-semibold shadow-sm cursor-pointer
                                       bg-slate-900 dark:bg-slate-700 text-white border border-slate-900 dark:border-slate-600
                                       hover:bg-slate-800 dark:hover:bg-slate-600 transition">
                            Close
                        </button>
                    </div>

                    <div class="px-5 py-5 max-h-[75vh] overflow-y-auto">
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 p-3 mb-4">
                            <p id="replyAiContext" class="text-xs leading-5 text-slate-500 dark:text-slate-400 break-words"></p>
                        </div>

                        <textarea id="replyAiText"
                                  rows="6"
                                  class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-3 text-sm text-slate-700 dark:text-slate-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/30 resize-none"></textarea>

                        <div class="flex flex-wrap gap-2 mt-4 justify-end">
                            <a id="replyAiViewPost"
                               href="#"
                               target="_blank"
                               class="inline-flex items-center justify-center px-3 py-2 rounded-xl text-xs font-semibold shadow-sm
                                      border border-slate-200 dark:border-slate-700
                                      bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-100
                                      hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                                View Post on LinkedIn
                            </a>

                            <button id="replyAiRegenerate"
                                    type="button"
                                    class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold shadow-sm cursor-pointer
                                           border border-indigo-200 dark:border-indigo-700
                                           bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-200
                                           hover:bg-indigo-100 dark:hover:bg-indigo-900/30 transition">
                                Regenerate
                            </button>

                            <button id="replyAiCopy"
                                    type="button"
                                    class="inline-flex items-center justify-center px-3 py-2 rounded-xl text-xs font-semibold shadow-sm cursor-pointer
                                           bg-indigo-600 hover:bg-indigo-700 text-white border border-indigo-600 transition">
                                Copy
                            </button>
                        </div>
                    </div>
                </div>
            </div>`;
        document.body.appendChild(modal);
    }

    const modal = document.getElementById('replyAiModal');
    const contextEl = document.getElementById('replyAiContext');
    const textEl = document.getElementById('replyAiText');
    const viewPostEl = document.getElementById('replyAiViewPost');
    const regenEl = document.getElementById('replyAiRegenerate');
    const copyEl = document.getElementById('replyAiCopy');
    let currentContext = '';

    function openModal() {
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeModal() {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    async function generateModalReply(isRegen = false) {
        if (isRegen) setButtonLoading(regenEl, true);

        try {
            const idea = await requestReplyIdea(currentContext);
            textEl.value = idea;
        } finally {
            if (isRegen) setButtonLoading(regenEl, false);
        }
    }

    modal.querySelectorAll('[data-close="1"]').forEach((el) => {
        el.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });

    regenEl.addEventListener('click', async () => {
        try {
            await generateModalReply(true);
        } catch (e) {
            alert('Could not regenerate AI reply idea now. Please try again.');
        }
    });

    copyEl.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(textEl.value || '');
            copyEl.textContent = 'Copied';
            setTimeout(() => {
                copyEl.textContent = 'Copy';
            }, 1200);
        } catch (e) {
            alert('Could not copy text. Please copy it manually.');
        }
    });

    buttons.forEach((btn) => {
        btn.addEventListener('click', async function () {
            const originalHtml = this.innerHTML;
            this.disabled = true;
            this.classList.add('opacity-70', 'pointer-events-none');
            this.innerHTML = `${spinnerMarkup}<span>Generating...</span>`;

            try {
                currentContext = this.getAttribute('data-context') || '';
                const postUrl = this.getAttribute('data-post-url') || '#';

                viewPostEl.href = postUrl;
                if (postUrl && postUrl !== '#') {
                    viewPostEl.classList.remove('hidden');
                } else {
                    viewPostEl.classList.add('hidden');
                }

                contextEl.textContent = currentContext;
                await generateModalReply();
                openModal();
            } catch (e) {
                alert('Could not generate AI reply idea now. Please try again.');
            } finally {
                this.innerHTML = originalHtml;
                this.disabled = false;
                this.classList.remove('opacity-70', 'pointer-events-none');
            }
        });
    });
});
</script>
@endpush