@extends('user.layout')

@section('page_title', 'Connections')
@section('page_subtitle', 'Directory of your LinkedIn connections synced via extension.')

@section('content')
    @php
        // Safely get connections from $data, or null if not present
        $connections = $data['connections'] ?? null;
    @endphp

    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6 overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-50">
                Contacts List
            </h3>

            <form method="get" class="flex flex-wrap items-center gap-2">
                <input type="text"
                       name="q"
                       value="{{ request('q') }}"
                       placeholder="Search name..."
                       class="text-xs border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 shadow-sm min-w-[200px]">
                <button type="submit"
                        class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-xs font-semibold shadow-md cursor-pointer
                               bg-indigo-600 hover:bg-indigo-700 text-white border border-indigo-600
                               focus:outline-none focus:ring-2 focus:ring-indigo-500/30
                               hover:scale-[var(--hover-scale)] transition">
                    Filter
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[720px]">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400">
                        <th class="pb-3 pr-3 font-medium">Profile</th>
                        <th class="pb-3 px-3 font-medium">Identifier</th>
                        <th class="pb-3 pl-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @if($connections && count($connections))
                        @foreach($connections as $c)
                            @php
                                $rawName = trim((string) ($c->full_name ?? ''));
                                $rawPid  = trim((string) ($c->public_identifier ?? ''));

                                $name = ($rawName !== '' && strtolower($rawName) !== 'unknown')
                                    ? $rawName
                                    : (($rawPid !== '' && strtolower($rawPid) !== 'unknown') ? $rawPid : 'Connection');

                                $img = !empty($c->profile_image_url)
                                    ? $c->profile_image_url
                                    : ('https://ui-avatars.com/api/?name=' . urlencode($name));
                            @endphp

                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition align-top">
                                <td class="py-3 pr-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <img src="{{ $img }}"
                                             class="h-9 w-9 rounded-full object-cover border border-slate-200 dark:border-slate-700 shrink-0"
                                             alt="{{ $name }}">
                                        <span class="font-semibold text-slate-800 dark:text-slate-100 break-words">
                                            {{ $name }}
                                        </span>
                                    </div>
                                </td>

                                <td class="py-3 px-3 text-slate-500 dark:text-slate-400 max-w-md">
                                    <div class="truncate">
                                        {{ $c->public_identifier ?: 'N/A' }}
                                    </div>
                                </td>

                                <td class="py-3 pl-3 text-right">
                                    <div class="inline-flex flex-wrap items-center justify-end gap-2 min-w-[220px]">
                                        <button type="button"
                                                class="ai-compose-msg-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[11px] font-semibold shadow-sm
                                                       border border-indigo-200 dark:border-indigo-700
                                                       bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-200
                                                       cursor-pointer hover:bg-indigo-100 dark:hover:bg-indigo-900/30
                                                       hover:scale-[var(--hover-scale)] transition"
                                                data-name="{{ e($name) }}"
                                                data-profile-url="{{ e($c->profile_url) }}"
                                                title="Compose intro message with AI">
                                            <span>✨</span>
                                            <span>Compose Message</span>
                                        </button>

                                        @if(!empty($c->profile_url))
                                            <a href="{{ $c->profile_url }}"
                                               target="_blank"
                                               class="inline-flex items-center px-3 py-1.5 rounded-xl text-[11px] font-semibold shadow-sm
                                                      border border-slate-200 dark:border-slate-700
                                                      bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-100
                                                      cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-700
                                                      hover:scale-[var(--hover-scale)] transition">
                                                View Profile
                                            </a>
                                        @else
                                            <span class="text-[11px] text-slate-400 dark:text-slate-500">
                                                No profile link
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="3" class="py-10 text-center text-slate-400 dark:text-slate-500">
                                No connections found. Sync with extension to populate.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="mt-4 overflow-x-auto">
            @if($connections instanceof \Illuminate\Contracts\Pagination\Paginator || $connections instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
                {{ $connections->links() }}
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.ai-compose-msg-btn');
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
        button.innerHTML = button.dataset.originalLabel || '<span>✨</span><span>Compose Message</span>';
    }

    async function generateIntro(context) {
        const res = await fetch(@json(route('dashboard.ai-assistant')), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': @json(csrf_token()),
            },
            body: JSON.stringify({
                action: 'connection_message',
                input_text: context
            })
        });

        if (!res.ok) throw new Error('Request failed');
        const payload = await res.json();
        return (payload?.data?.items || [])[0] || 'Hi, great to connect with you.';
    }

    if (!document.getElementById('connAiModal')) {
        const modal = document.createElement('div');
        modal.id = 'connAiModal';
        modal.className = 'hidden fixed inset-0 z-50';
        modal.innerHTML = `
            <div class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm" data-close="1"></div>
            <div class="relative min-h-full flex items-center justify-center p-4 sm:p-6">
                <div class="w-full max-w-2xl bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xl overflow-hidden">
                    <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/70">
                        <div>
                            <h4 class="text-base font-semibold text-slate-800 dark:text-slate-50">AI Connection Message</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Generate a short professional intro message for this connection.</p>
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
                            <p id="connAiContext" class="text-xs leading-5 text-slate-500 dark:text-slate-400 break-words"></p>
                        </div>

                        <textarea id="connAiText"
                                  rows="6"
                                  class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-3 text-sm text-slate-700 dark:text-slate-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/30 resize-none"></textarea>

                        <div class="flex flex-wrap gap-2 mt-4 justify-end">
                            <a id="connAiProfile"
                               href="#"
                               target="_blank"
                               class="inline-flex items-center justify-center px-3 py-2 rounded-xl text-xs font-semibold shadow-sm
                                      border border-slate-200 dark:border-slate-700
                                      bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-100
                                      hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                                View Profile
                            </a>

                            <button id="connAiRegenerate"
                                    type="button"
                                    class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold shadow-sm cursor-pointer
                                           border border-indigo-200 dark:border-indigo-700
                                           bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-200
                                           hover:bg-indigo-100 dark:hover:bg-indigo-900/30 transition">
                                Regenerate
                            </button>

                            <button id="connAiCopy"
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

    const modal = document.getElementById('connAiModal');
    const ctxEl = document.getElementById('connAiContext');
    const txtEl = document.getElementById('connAiText');
    const profileEl = document.getElementById('connAiProfile');
    const regenEl = document.getElementById('connAiRegenerate');
    const copyEl = document.getElementById('connAiCopy');
    let currentContext = '';

    function openModal() {
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeModal() {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    async function generateAndShow(isRegen = false) {
        if (isRegen) setButtonLoading(regenEl, true);

        try {
            const message = await generateIntro(currentContext);
            txtEl.value = message;
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
            await generateAndShow(true);
        } catch (e) {
            alert('Could not generate message now.');
        }
    });

    copyEl.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(txtEl.value || '');
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
            const name = this.getAttribute('data-name') || 'there';
            const profileUrl = this.getAttribute('data-profile-url') || '#';
            const context = `Target: ${name}. Draft a short professional intro message.`;
            const originalHtml = this.innerHTML;

            this.disabled = true;
            this.classList.add('opacity-70', 'pointer-events-none');
            this.innerHTML = `${spinnerMarkup}<span>Generating...</span>`;

            try {
                currentContext = context;
                ctxEl.textContent = context;

                profileEl.href = profileUrl;
                if (profileUrl && profileUrl !== '#') {
                    profileEl.classList.remove('hidden');
                } else {
                    profileEl.classList.add('hidden');
                }

                await generateAndShow();
                openModal();
            } catch (e) {
                alert('Could not generate message now.');
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