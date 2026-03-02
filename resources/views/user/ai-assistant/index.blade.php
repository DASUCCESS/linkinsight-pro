@extends('user.layout')

@section('page_title', 'AI Assistant')
@section('page_subtitle', 'Generate ideas, replies, messages, and post improvements from your synced analytics.')

@section('content')
<div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-base font-semibold text-slate-800 dark:text-slate-50">AI Workspace</h3>
        <span class="text-xs px-2 py-1 rounded-full border border-slate-200 dark:border-slate-700 text-slate-500">{{ strtoupper($aiRecommendations['source'] ?? 'local') }}</span>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-xs mb-3">
        <button type="button" class="ai-workflow-btn px-3 py-2 rounded-full border font-semibold" data-action="weekly_insights">Generate Weekly Insights</button>
        <button type="button" class="ai-workflow-btn px-3 py-2 rounded-full border font-semibold" data-action="post_ideas">Suggest Post Ideas</button>
        <button type="button" class="ai-workflow-btn px-3 py-2 rounded-full border font-semibold" data-action="write_comment">Write a Comment</button>
        <button type="button" class="ai-workflow-btn px-3 py-2 rounded-full border font-semibold" data-action="reply_comment">Reply to Comment</button>
        <button type="button" class="ai-workflow-btn px-3 py-2 rounded-full border font-semibold" data-action="connection_message">Draft Connection Message</button>
        <button type="button" class="ai-workflow-btn px-3 py-2 rounded-full border font-semibold" data-action="improve_post">Improve My Post</button>
    </div>

    <textarea id="aiInputText" rows="4" class="w-full rounded-xl border px-3 py-2 text-sm" placeholder="Optional context..."></textarea>

    <div class="mt-4 rounded-xl border p-4 bg-slate-50 dark:bg-slate-900/40">
        <div class="flex items-center justify-between mb-2">
            <p id="aiAssistantTitle" class="text-sm font-semibold">Output</p>
            <div class="flex gap-2">
                <button type="button" id="regenAiOutputBtn" class="px-2 py-1 rounded-full text-xs border">Regenerate</button>
                <button type="button" id="copyAiOutputBtn" class="px-2 py-1 rounded-full text-xs border">Copy</button>
            </div>
        </div>
        <ul id="aiAssistantList" class="space-y-1 list-disc list-inside text-sm">
            <li>Select an AI action to begin.</li>
        </ul>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.ai-workflow-btn');
    const aiList = document.getElementById('aiAssistantList');
    const aiTitle = document.getElementById('aiAssistantTitle');
    const aiInput = document.getElementById('aiInputText');
    const copyBtn = document.getElementById('copyAiOutputBtn');
    const regenBtn = document.getElementById('regenAiOutputBtn');
    let lastAction = null;

    async function run(action) {
        lastAction = action;
        aiTitle.textContent = 'Generating...';
        aiList.innerHTML = '<li>Please wait...</li>';
        const res = await fetch(@json(route('dashboard.ai-assistant')), {
            method: 'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN':@json(csrf_token()),'X-Requested-With':'XMLHttpRequest'},
            body: JSON.stringify({action, input_text: aiInput.value || null})
        });
        const payload = await res.json();
        const data = payload?.data || {};
        aiTitle.textContent = data.title || 'Output';
        const items = Array.isArray(data.items) ? data.items : [];
        aiList.innerHTML = items.length ? items.map(i => `<li>${String(i)}</li>`).join('') : '<li>No output.</li>';
    }

    buttons.forEach(b => b.addEventListener('click', () => run(b.dataset.action)));
    if (regenBtn) regenBtn.addEventListener('click', () => { if (lastAction) run(lastAction); });
    if (copyBtn) copyBtn.addEventListener('click', async () => {
        const text = Array.from(aiList.querySelectorAll('li')).map(li => li.textContent).join('\n');
        await navigator.clipboard.writeText(text);
    });
});
</script>
@endpush
