@extends('user.layout')

@section('page_title', 'AI Studio')
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
                <div id="aiArticleOutput" class="hidden text-sm text-slate-700 dark:text-slate-200 whitespace-pre-wrap leading-7"></div>
            </div>
        </section>
    </div>

    <aside class="bg-white dark:bg-slate-900 rounded-3xl shadow-xl border border-slate-200 dark:border-slate-800 p-0 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-900/60">
            <h3 class="text-sm font-semibold">AI Chat Assistant</h3>
            <p class="text-xs text-slate-500 mt-1">Use this for LinkedIn strategy from your synced data: weekly actions, post ideas, and networking DMs.</p>
            <div class="grid grid-cols-3 gap-2 mt-3 text-xs">
                <button type="button" data-chat-mode="linkedin_activity" class="chat-mode-btn px-2.5 py-1.5 rounded-full border font-semibold">LinkedIn Data</button>
                <button type="button" data-chat-mode="brainstorm" class="chat-mode-btn px-2.5 py-1.5 rounded-full border">Brainstorm</button>
                <button type="button" data-chat-mode="connection_message" class="chat-mode-btn px-2.5 py-1.5 rounded-full border">Networking DM</button>
            </div>
            <div class="mt-3">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 mb-1">Suggested prompts</p>
                <div id="chatQuickPrompts" class="flex flex-wrap gap-2"></div>
            </div>
        </div>

        <div id="chatLog" class="h-80 overflow-auto p-4 space-y-3 bg-white dark:bg-slate-900/40"></div>

        <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/60">
            <textarea id="chatInput" rows="3" class="w-full rounded-2xl border-slate-300 dark:border-slate-700 px-3 py-2 text-sm" placeholder="Ask a LinkedIn-focused question..."></textarea>
            <div class="flex justify-between items-center mt-2">
                <p class="text-[11px] text-slate-500">Enter to send • Shift+Enter for a new line</p>
                <button type="button" id="chatSendBtn" class="px-4 py-1.5 rounded-full text-xs font-semibold border border-slate-300 dark:border-slate-600">Send</button>
            </div>
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
    const aiArticleOutput = document.getElementById('aiArticleOutput');
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


    function escapeHtml(value) {
        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#39;');
    }

    function renderOutput(action, items) {
        if (action === 'article_post') {
            const article = String((items || [])[0] || '').trim();
            aiList.classList.add('hidden');
            aiArticleOutput.classList.remove('hidden');
            aiArticleOutput.innerHTML = article ? escapeHtml(article) : 'No output.';
            return;
        }

        aiArticleOutput.classList.add('hidden');
        aiArticleOutput.textContent = '';
        aiList.classList.remove('hidden');
        aiList.innerHTML = items.length ? items.map(i => `<li>${escapeHtml(String(i))}</li>`).join('') : '<li>No output.</li>';
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
        aiArticleOutput.classList.add('hidden');
        aiArticleOutput.textContent = '';
        aiList.classList.remove('hidden');
        aiList.innerHTML = '<li>Please wait...</li>';
        if (isRegen) setButtonLoading(regenBtn, true);

        try {
            const res = await fetch(@json(route('dashboard.ai-assistant')), {method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':@json(csrf_token()),'X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({action,input_text:resolvedInput})});
            const payload = await res.json();
            const data = payload?.data || {};
            aiTitle.textContent = title || data.title || 'Output';
            const items = Array.isArray(data.items) ? data.items : [];
            renderOutput(action, items);
        } catch {
            aiTitle.textContent = 'Output';
            aiArticleOutput.classList.add('hidden');
            aiArticleOutput.textContent = '';
            aiList.classList.remove('hidden');
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

    copyBtn.addEventListener('click', async () => {
        const articleVisible = !aiArticleOutput.classList.contains('hidden');
        const articleText = (aiArticleOutput.textContent || '').trim();
        const listText = Array.from(aiList.querySelectorAll('li')).map(li => li.textContent).join('\n');
        await navigator.clipboard.writeText(articleVisible ? articleText : listText);
    });

    const chatLog = document.getElementById('chatLog');
    const chatInput = document.getElementById('chatInput');
    const chatSendBtn = document.getElementById('chatSendBtn');
    const chatQuickPrompts = document.getElementById('chatQuickPrompts');
    const chatModeButtons = Array.from(document.querySelectorAll('.chat-mode-btn'));
    let chatMode = 'linkedin_activity';
    let isSendingChat = false;

    const promptsByMode = {
        linkedin_activity: [
            'Based on my latest metrics, what should I post this week?',
            'What are my top growth risks and how do I fix them?',
            'Turn my analytics into a 5-day content plan.'
        ],
        brainstorm: [
            'Brainstorm 5 carousel ideas with strong first slides.',
            'Give me 10 high-performing hooks for my niche.',
            'Suggest 7 thought-leadership post angles for this month.'
        ],
        connection_message: [
            'Write 3 custom LinkedIn connection requests for a SaaS VP Marketing.',
            'Create a follow-up DM after an accepted invite.',
            'Write a short networking message after reading someone's post.'
        ]
    };

    const modeLabels = {
        linkedin_activity: 'LinkedIn Data',
        brainstorm: 'Brainstorm',
        connection_message: 'Networking DM'
    };

    function addMessageBubble(role, bodyHtml) {
        const row = document.createElement('div');
        row.className = role === 'user' ? 'flex justify-end' : 'flex justify-start';

        const bubble = document.createElement('div');
        bubble.className = role === 'user'
            ? 'max-w-[85%] rounded-2xl rounded-br-md bg-indigo-600 text-white px-3 py-2 text-sm shadow-sm whitespace-pre-wrap'
            : 'max-w-[92%] rounded-2xl rounded-bl-md bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-100 px-3 py-2 text-sm shadow-sm whitespace-pre-wrap';
        bubble.innerHTML = bodyHtml;

        row.appendChild(bubble);
        chatLog.appendChild(row);
        chatLog.scrollTop = chatLog.scrollHeight;

        return bubble;
    }

    function escapeAndBreak(text) {
        return escapeHtml(String(text || '')).replaceAll('\n', '<br>');
    }

    function renderAiResponse(reply, items) {
        const normalizedItems = Array.isArray(items)
            ? items.map(item => String(item || '').trim()).filter(Boolean)
            : [];

        if (normalizedItems.length > 1) {
            return `<div class="font-semibold mb-1">AI</div><ul class="list-disc list-inside space-y-1">${normalizedItems.map(item => `<li>${escapeAndBreak(item)}</li>`).join('')}</ul>`;
        }

        const mainText = normalizedItems[0] || String(reply || '').trim() || 'No response yet. Try asking with more specific context.';
        return `<div class="font-semibold mb-1">AI</div><div>${escapeAndBreak(mainText)}</div>`;
    }

    function renderQuickPrompts() {
        const prompts = promptsByMode[chatMode] || [];
        chatQuickPrompts.innerHTML = prompts.map(prompt => `<button type="button" class="chat-quick-prompt px-2.5 py-1 rounded-full border border-slate-300 dark:border-slate-600 text-[11px] hover:bg-slate-100 dark:hover:bg-slate-800">${escapeHtml(prompt)}</button>`).join('');
        Array.from(chatQuickPrompts.querySelectorAll('.chat-quick-prompt')).forEach((button, index) => {
            button.addEventListener('click', () => {
                chatInput.value = prompts[index] || '';
                chatInput.focus();
            });
        });
    }

    function setChatMode(mode) {
        chatMode = mode;
        chatModeButtons.forEach(button => {
            const active = button.dataset.chatMode === mode;
            button.classList.toggle('font-semibold', active);
            button.classList.toggle('border-indigo-500', active);
            button.classList.toggle('text-indigo-600', active);
        });
        renderQuickPrompts();
    }

    async function sendChatMessage() {
        const message = (chatInput.value || '').trim();
        if (!message || isSendingChat) return;

        isSendingChat = true;
        chatSendBtn.disabled = true;

        addMessageBubble('user', `<div class="font-semibold mb-1">You • ${escapeHtml(modeLabels[chatMode] || 'Chat')}</div><div>${escapeAndBreak(message)}</div>`);
        chatInput.value = '';
        const pendingBubble = addMessageBubble('ai', '<div class="font-semibold mb-1">AI</div><div>Thinking...</div>');

        try {
            const res = await fetch(@json(route('ai.assistant.chat')), {
                method:'POST',
                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':@json(csrf_token()),'X-Requested-With':'XMLHttpRequest'},
                body:JSON.stringify({mode:chatMode,message})
            });
            const json = await res.json();
            pendingBubble.innerHTML = renderAiResponse(json.reply || '', json.items || []);
        } catch {
            pendingBubble.innerHTML = '<div class="font-semibold mb-1">AI</div><div>Failed to respond right now. Please try again.</div>';
        } finally {
            isSendingChat = false;
            chatSendBtn.disabled = false;
            chatLog.scrollTop = chatLog.scrollHeight;
        }
    }

    chatModeButtons.forEach(button => {
        button.addEventListener('click', () => setChatMode(button.dataset.chatMode || 'linkedin_activity'));
    });

    chatSendBtn.addEventListener('click', sendChatMessage);
    chatInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            sendChatMessage();
        }
    });

    setChatMode('linkedin_activity');
    addMessageBubble('ai', '<div class="font-semibold mb-1">AI</div><div>Ask me for weekly LinkedIn actions, new post concepts, or networking DM drafts. I will tailor suggestions to your synced data.</div>');
});
</script>
@endpush
