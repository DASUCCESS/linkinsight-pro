@extends('user.layout')

@section('page_title', 'AI Assistant')
@section('page_subtitle', 'Create content, brainstorm ideas, and generate polished LinkedIn-ready drafts.')

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="xl:col-span-2 space-y-6">
        <section class="relative overflow-hidden bg-gradient-to-br from-indigo-600 via-violet-600 to-slate-900 rounded-3xl p-6 text-white shadow-2xl">
            <div class="absolute -right-16 -top-12 h-40 w-40 bg-white/10 rounded-full blur-2xl"></div>
            <div class="absolute -left-20 -bottom-20 h-52 w-52 bg-fuchsia-300/20 rounded-full blur-3xl"></div>
            <div class="relative">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.2em] text-indigo-100/90">AI Studio</p>
                        <h3 class="text-xl md:text-2xl font-semibold">Generate smarter LinkedIn content faster</h3>
                    </div>
                    <a href="https://www.linkedin.com/feed/" target="_blank" class="inline-flex items-center gap-1 px-4 py-2 rounded-full text-xs font-semibold bg-white/15 border border-white/30 hover:bg-white/25 transition">Open LinkedIn</a>
                </div>

                <div class="grid grid-cols-2 gap-2 text-xs">
                    <button type="button" class="ai-workflow-btn px-3 py-2 rounded-full border border-white/25 bg-white/10 hover:bg-white/20 transition font-semibold" data-action="weekly_insights">Weekly Insights</button>
                    <button type="button" class="ai-workflow-btn px-3 py-2 rounded-full border border-white/25 bg-white/10 hover:bg-white/20 transition font-semibold" data-action="post_ideas">Post Ideas</button>
                </div>
            </div>
        </section>

        <section class="bg-white dark:bg-slate-900 rounded-3xl shadow-xl border border-slate-200 dark:border-slate-800 p-6">
            <div class="flex items-center justify-between gap-2 mb-3">
                <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Prompt / Context</h4>
                <span class="text-[11px] text-slate-400">Add topic, audience, and goals</span>
            </div>
            <textarea id="aiInputText" rows="4" class="w-full rounded-2xl border-slate-300 dark:border-slate-700 bg-white/90 dark:bg-slate-900 px-4 py-3 text-sm" placeholder="Example: Generate a weekly insight summary for my content performance."></textarea>
        </section>

        <section class="bg-white dark:bg-slate-900 rounded-3xl shadow-xl border border-slate-200 dark:border-slate-800 p-6">
            <div class="flex items-center justify-between gap-2 mb-4">
                <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-100">LinkedIn Article / Blog Post Generator</h4>
                <span class="text-[11px] text-slate-400">Tell AI what to write</span>
            </div>

            <div class="grid md:grid-cols-2 gap-3 mb-3">
                <input id="articleTopic" type="text" class="w-full rounded-xl border-slate-300 dark:border-slate-700 px-3 py-2 text-sm" placeholder="Article topic (e.g., Productivity for remote teams)">
                <input id="articleAudience" type="text" class="w-full rounded-xl border-slate-300 dark:border-slate-700 px-3 py-2 text-sm" placeholder="Audience (e.g., Startup founders, marketers)">
            </div>
            <div class="grid md:grid-cols-2 gap-3 mb-3">
                <input id="articleGoal" type="text" class="w-full rounded-xl border-slate-300 dark:border-slate-700 px-3 py-2 text-sm" placeholder="Goal (e.g., educate, generate leads)">
                <select id="articleTone" class="w-full rounded-xl border-slate-300 dark:border-slate-700 px-3 py-2 text-sm">
                    <option value="Professional">Tone: Professional</option>
                    <option value="Thought leadership">Tone: Thought leadership</option>
                    <option value="Conversational">Tone: Conversational</option>
                    <option value="Data-driven">Tone: Data-driven</option>
                </select>
            </div>
            <textarea id="articleNotes" rows="4" class="w-full rounded-xl border-slate-300 dark:border-slate-700 px-3 py-2 text-sm" placeholder="Extra instructions (key points, story, CTA, examples, word count)..."></textarea>

            <div class="mt-3 flex flex-wrap gap-2">
                <button type="button" id="suggestArticleIdeasBtn" class="px-3 py-2 rounded-full text-xs font-semibold border border-slate-300 dark:border-slate-600">Suggest Article Ideas</button>
                <button type="button" id="generateArticlePostBtn" class="px-3 py-2 rounded-full text-xs font-semibold border border-slate-300 dark:border-slate-600">Generate LinkedIn Article Post</button>
            </div>

            <p class="text-[11px] text-slate-500 mt-2">Use “Suggest Article Ideas” first if you need inspiration, then generate a full article you can post on LinkedIn.</p>

            <div class="mt-5 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 bg-slate-50/80 dark:bg-slate-900/50">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                    <p id="aiAssistantTitle" class="text-sm font-semibold">Output</p>
                    <div class="flex gap-2">
                        <button type="button" id="regenAiOutputBtn" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs border border-slate-300 dark:border-slate-600">Regenerate</button>
                        <button type="button" id="copyAiOutputBtn" class="px-3 py-1.5 rounded-full text-xs border border-slate-300 dark:border-slate-600">Copy</button>
                    </div>
                </div>
                <ul id="aiAssistantList" class="space-y-2 list-disc list-inside text-sm text-slate-700 dark:text-slate-200">
                    <li>Generate weekly insights, post ideas, or create a full LinkedIn article.</li>
                </ul>
            </div>
        </section>
    </div>

    <aside class="bg-white dark:bg-slate-900 rounded-3xl shadow-xl border border-slate-200 dark:border-slate-800 p-6">
        <h3 class="text-base font-semibold mb-3">AI Chat</h3>
        <div class="flex gap-2 mb-3 text-xs">
            <button type="button" id="chatModeLinkedin" class="px-3 py-1 rounded-full border font-semibold">LinkedIn Activity</button>
            <button type="button" id="chatModeBrainstorm" class="px-3 py-1 rounded-full border">Brainstorm</button>
        </div>
        <div id="chatLog" class="h-80 overflow-auto rounded-2xl border p-3 text-xs space-y-2 bg-slate-50 dark:bg-slate-900/40">
            <div class="text-slate-500">Ask anything. AI can suggest posts, article structures, and copy drafts.</div>
        </div>
        <textarea id="chatInput" rows="3" class="w-full mt-3 rounded-2xl border px-3 py-2 text-sm" placeholder="Type your question..."></textarea>
        <div class="flex justify-end mt-2">
            <button type="button" id="chatSendBtn" class="px-4 py-1.5 rounded-full text-xs font-semibold border">Send</button>
        </div>
    </aside>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const spinnerMarkup = '<svg class="animate-spin h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"><circle class="opacity-30" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle><path class="opacity-90" d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path></svg>';
    const buttons = document.querySelectorAll('.ai-workflow-btn');
    const aiList = document.getElementById('aiAssistantList');
    const aiTitle = document.getElementById('aiAssistantTitle');
    const aiInput = document.getElementById('aiInputText');
    const copyBtn = document.getElementById('copyAiOutputBtn');
    const regenBtn = document.getElementById('regenAiOutputBtn');
    const suggestArticleIdeasBtn = document.getElementById('suggestArticleIdeasBtn');
    const generateArticlePostBtn = document.getElementById('generateArticlePostBtn');
    const articleTopic = document.getElementById('articleTopic');
    const articleAudience = document.getElementById('articleAudience');
    const articleGoal = document.getElementById('articleGoal');
    const articleTone = document.getElementById('articleTone');
    const articleNotes = document.getElementById('articleNotes');
    let lastRequest = null;

    function setButtonLoading(button, loading, label = 'Regenerate') {
        if (!button) return;
        if (loading) {
            button.disabled = true;
            button.dataset.originalLabel = button.dataset.originalLabel || button.textContent.trim();
            button.innerHTML = `${spinnerMarkup}<span>${label}</span>`;
            return;
        }

        button.disabled = false;
        button.textContent = button.dataset.originalLabel || label;
    }

    function buildArticlePrompt() {
        return [
            `Topic: ${(articleTopic.value || '').trim() || 'LinkedIn growth'}`,
            `Audience: ${(articleAudience.value || '').trim() || 'Professionals on LinkedIn'}`,
            `Goal: ${(articleGoal.value || '').trim() || 'Educate and drive engagement'}`,
            `Tone: ${(articleTone.value || '').trim() || 'Professional'}`,
            `Additional instructions: ${(articleNotes.value || '').trim() || 'Use clear subheadings, practical examples, and a strong CTA.'}`,
        ].join('\n');
    }

    async function run(action, isRegen = false, inputText = null, title = null) {
        const resolvedInput = inputText ?? aiInput.value ?? null;
        lastRequest = { action, inputText: resolvedInput, title };
        aiTitle.textContent = title || 'Generating...';
        aiList.innerHTML = '<li>Please wait...</li>';
        if (isRegen) setButtonLoading(regenBtn, true);

        try {
            const res = await fetch(@json(route('dashboard.ai-assistant')), {method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':@json(csrf_token()),'X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({action,input_text:resolvedInput})});
            const payload = await res.json();
            const data = payload?.data || {};
            aiTitle.textContent = title || data.title || 'Output';
            const items = Array.isArray(data.items) ? data.items : [];
            aiList.innerHTML = items.length ? items.map(i => `<li>${String(i)}</li>`).join('') : '<li>No output.</li>';
        } catch {
            aiTitle.textContent = 'Output';
            aiList.innerHTML = '<li>Could not generate output right now. Please try again.</li>';
        } finally {
            if (isRegen) setButtonLoading(regenBtn, false);
        }
    }

    buttons.forEach(b => b.addEventListener('click', () => run(b.dataset.action)));

    suggestArticleIdeasBtn.addEventListener('click', () => {
        const articlePrompt = buildArticlePrompt() + '\nTask: Suggest 6 strong LinkedIn article/blog ideas with clear angles.';
        run('post_ideas', false, articlePrompt, 'Suggested Article Ideas');
    });

    generateArticlePostBtn.addEventListener('click', () => {
        const articlePrompt = buildArticlePrompt() + '\nTask: Write a complete blog-style LinkedIn article ready to post.';
        run('article_post', false, articlePrompt, 'LinkedIn Article Draft');
    });

    regenBtn.addEventListener('click', () => {
        if (!lastRequest) return;
        run(lastRequest.action, true, lastRequest.inputText, lastRequest.title);
    });

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
