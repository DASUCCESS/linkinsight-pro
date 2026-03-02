@extends('user.layout')

@section('page_title', 'AI Assistant')
@section('page_subtitle', 'AI workspace for LinkedIn insights and social media brainstorming.')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-semibold">AI Generator</h3>
            <a href="https://www.linkedin.com/feed/" target="_blank" class="px-3 py-1.5 rounded-full text-xs font-semibold border">Open LinkedIn</a>
        </div>

        <div class="grid grid-cols-2 gap-2 text-xs mb-3">
            <button type="button" class="ai-workflow-btn px-3 py-2 rounded-full border font-semibold" data-action="weekly_insights">Generate Weekly Insights</button>
            <button type="button" class="ai-workflow-btn px-3 py-2 rounded-full border font-semibold" data-action="post_ideas">Suggest Post Ideas</button>
        </div>

        <textarea id="aiInputText" rows="4" class="w-full rounded-xl border px-3 py-2 text-sm" placeholder="Paste context, post draft, or what you want help with..."></textarea>

        <div class="mt-4 rounded-xl border p-4 bg-slate-50 dark:bg-slate-900/40">
            <div class="flex items-center justify-between mb-2">
                <p id="aiAssistantTitle" class="text-sm font-semibold">Output</p>
                <div class="flex gap-2">
                    <button type="button" id="regenAiOutputBtn" class="px-2 py-1 rounded-full text-xs border">Regenerate</button>
                    <button type="button" id="copyAiOutputBtn" class="px-2 py-1 rounded-full text-xs border">Copy</button>
                </div>
            </div>
            <ul id="aiAssistantList" class="space-y-1 list-disc list-inside text-sm">
                <li>Select an action to generate output.</li>
            </ul>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6">
        <h3 class="text-base font-semibold mb-3">AI Chat</h3>
        <div class="flex gap-2 mb-2 text-xs">
            <button type="button" id="chatModeLinkedin" class="px-3 py-1 rounded-full border font-semibold">LinkedIn Activity</button>
            <button type="button" id="chatModeBrainstorm" class="px-3 py-1 rounded-full border">Brainstorm</button>
        </div>
        <div id="chatLog" class="h-72 overflow-auto rounded-xl border p-3 text-xs space-y-2 bg-slate-50 dark:bg-slate-900/40">
            <div class="text-slate-500">Ask anything. AI can suggest posts and write copy for you.</div>
        </div>
        <textarea id="chatInput" rows="3" class="w-full mt-3 rounded-xl border px-3 py-2 text-sm" placeholder="Type your question..."></textarea>
        <div class="flex justify-end mt-2">
            <button type="button" id="chatSendBtn" class="px-3 py-1.5 rounded-full text-xs font-semibold border">Send</button>
        </div>
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
        const res = await fetch(@json(route('dashboard.ai-assistant')), {method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':@json(csrf_token()),'X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({action,input_text:aiInput.value||null})});
        const payload = await res.json();
        const data = payload?.data || {};
        aiTitle.textContent = data.title || 'Output';
        const items = Array.isArray(data.items) ? data.items : [];
        aiList.innerHTML = items.length ? items.map(i => `<li>${String(i)}</li>`).join('') : '<li>No output.</li>';
    }

    buttons.forEach(b => b.addEventListener('click', () => run(b.dataset.action)));
    regenBtn.addEventListener('click', () => { if (lastAction) run(lastAction); });
    copyBtn.addEventListener('click', async () => await navigator.clipboard.writeText(Array.from(aiList.querySelectorAll('li')).map(li => li.textContent).join('\n')));

    const chatLog = document.getElementById('chatLog');
    const chatInput = document.getElementById('chatInput');
    const chatSendBtn = document.getElementById('chatSendBtn');
    const modeLinkedin = document.getElementById('chatModeLinkedin');
    const modeBrainstorm = document.getElementById('chatModeBrainstorm');
    let chatMode = 'linkedin_activity';

    function addLine(role, text) {
        const div = document.createElement('div');
        div.className = role === 'user' ? 'text-right' : 'text-left';
        div.textContent = text;
        chatLog.appendChild(div);
        chatLog.scrollTop = chatLog.scrollHeight;
    }

    modeLinkedin.addEventListener('click', () => { chatMode = 'linkedin_activity'; modeLinkedin.classList.add('font-semibold'); modeBrainstorm.classList.remove('font-semibold'); });
    modeBrainstorm.addEventListener('click', () => { chatMode = 'brainstorm'; modeBrainstorm.classList.add('font-semibold'); modeLinkedin.classList.remove('font-semibold'); });

    chatSendBtn.addEventListener('click', async () => {
        const message = (chatInput.value || '').trim();
        if (!message) return;
        addLine('user', 'You: ' + message);
        chatInput.value = '';
        addLine('ai', 'AI: Generating...');
        try {
            const res = await fetch(@json(route('ai.assistant.chat')), {method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':@json(csrf_token()),'X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({mode:chatMode,message})});
            const json = await res.json();
            chatLog.lastElementChild.textContent = 'AI: ' + (json.reply || 'No response');
        } catch {
            chatLog.lastElementChild.textContent = 'AI: Failed to respond right now.';
        }
    });
});
</script>
@endpush
