@extends('user.layout')

@section('page_title', 'Connections')
@section('page_subtitle', 'Directory of your LinkedIn connections synced via extension.')

@section('content')
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-sm font-semibold">Contacts List</h3>
            <form method="get" class="flex gap-2">
                <input type="text"
                       name="q"
                       value="{{ request('q') }}"
                       placeholder="Search name..."
                       class="text-xs border rounded-lg px-3 py-1 dark:bg-slate-800 dark:border-slate-700">
                <button type="submit"
                        class="px-4 py-1 bg-indigo-600 text-white text-xs rounded-lg cursor-pointer hover:scale-[var(--hover-scale)] transition shadow">
                    Filter
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b dark:border-slate-800 text-slate-500">
                        <th class="pb-3 font-medium">Profile</th>
                        <th class="pb-3 font-medium">Identifier</th>
                        <th class="pb-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-slate-800">
                    @forelse($data['connections'] as $c)
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

                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                            <td class="py-3">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $img }}"
                                         class="h-8 w-8 rounded-full object-cover border border-slate-200 dark:border-slate-700"
                                         alt="{{ $name }}">
                                    <span class="font-semibold text-slate-800 dark:text-slate-100">{{ $name }}</span>
                                </div>
                            </td>

                            <td class="py-3 text-slate-500 max-w-md truncate">{{ $c->public_identifier ?: 'N/A' }}</td>

                            <td class="py-3 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <button type="button"
                                            class="ai-compose-msg-btn inline-flex items-center px-3 py-1 rounded-full text-[11px] font-semibold border border-indigo-300 dark:border-indigo-700 bg-indigo-50/60 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-200 cursor-pointer hover:scale-[var(--hover-scale)] transition shadow"
                                            data-name="{{ e($name) }}"
                                            data-profile-url="{{ e($c->profile_url) }}"
                                            title="Compose intro message with AI">
                                        ✨ Compose Message
                                    </button>
                                    @if(!empty($c->profile_url))
                                        <a href="{{ $c->profile_url }}"
                                           target="_blank"
                                           class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-semibold
                                                  border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900
                                                  text-slate-700 dark:text-slate-100 cursor-pointer
                                                  hover:scale-[var(--hover-scale)] transition shadow">
                                            View Profile
                                        </a>
                                    @else
                                        <span class="text-[11px] text-slate-400">No profile link</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-10 text-center text-slate-400">
                                No connections found. Sync with extension to populate.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $data['connections']->links() }}</div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.ai-compose-msg-btn');
    if (!buttons.length) return;

    async function generateIntro(context) {
        const res = await fetch(@json(route('dashboard.ai-assistant')), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': @json(csrf_token()),
            },
            body: JSON.stringify({ action: 'connection_message', input_text: context })
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
            <div class="absolute inset-0 bg-black/50" data-close="1"></div>
            <div class="relative mx-auto mt-20 max-w-2xl bg-white dark:bg-slate-900 rounded-2xl border p-5">
                <div class="flex justify-between items-center mb-2"><h4 class="text-sm font-semibold">AI Connection Message</h4><button data-close="1" class="px-2 py-1 border rounded text-xs">Close</button></div>
                <p id="connAiContext" class="text-xs text-slate-500 mb-2"></p>
                <textarea id="connAiText" rows="6" class="w-full rounded-xl border px-3 py-2 text-sm"></textarea>
                <div class="flex gap-2 mt-3 justify-end">
                    <a id="connAiProfile" href="#" target="_blank" class="px-3 py-1 rounded border text-xs">View Profile</a>
                    <button id="connAiRegenerate" class="px-3 py-1 rounded border text-xs">Regenerate</button>
                    <button id="connAiCopy" class="px-3 py-1 rounded border text-xs">Copy</button>
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

    async function generateAndShow() {
        const message = await generateIntro(currentContext);
        txtEl.value = message;
    }

    modal.querySelectorAll('[data-close="1"]').forEach(el => el.addEventListener('click', () => modal.classList.add('hidden')));
    regenEl.addEventListener('click', async () => { await generateAndShow(); });
    copyEl.addEventListener('click', async () => { await navigator.clipboard.writeText(txtEl.value || ''); });

    buttons.forEach((btn) => {
        btn.addEventListener('click', async function () {
            const name = this.getAttribute('data-name') || 'there';
            const profileUrl = this.getAttribute('data-profile-url') || '#';
            const context = `Target: ${name}. Draft a short professional intro message.`;
            const original = this.textContent;
            this.textContent = 'Generating...';
            this.disabled = true;

            try {
                currentContext = context;
                ctxEl.textContent = context;
                profileEl.href = profileUrl;
                await generateAndShow();
                modal.classList.remove('hidden');
            } catch (e) {
                alert('Could not generate message now.');
            } finally {
                this.textContent = original;
                this.disabled = false;
            }
        });
    });
});
</script>
@endpush
