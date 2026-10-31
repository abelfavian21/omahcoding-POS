{{-- Widget chat customer service. Dipasang di landing/index.blade.php lewat @include. Pakai token warna landing (--neon, --panel, --edge). --}}
<div id="cs-chat" data-send-url="{{ route('chat.send') }}" data-poll-url="{{ route('chat.poll') }}">
    <button type="button" id="cs-toggle" class="cs-fab" aria-expanded="false" aria-controls="cs-panel" aria-label="Buka chat bantuan">
        <span class="material-symbols-outlined cs-ico-open" aria-hidden="true">chat</span>
        <span class="material-symbols-outlined cs-ico-close" aria-hidden="true">close</span>
        <span class="cs-badge" id="cs-badge" hidden></span>
    </button>

    <section id="cs-panel" class="cs-panel" role="dialog" aria-label="Chat bantuan CashierStore" hidden>
        <header class="cs-head">
            <span class="cs-avatar" aria-hidden="true"><span class="material-symbols-outlined">support_agent</span></span>
            <div class="cs-title">
                <strong>Mirae</strong>
                <span id="cs-status" class="cs-status" role="status" data-state="ai">AI CashierStore</span>
            </div>
            <button type="button" id="cs-close" class="cs-x" aria-label="Tutup chat"><span class="material-symbols-outlined" aria-hidden="true">close</span></button>
        </header>

        <div id="cs-log" class="cs-log" role="log" aria-live="polite" aria-relevant="additions"></div>

        <p class="cs-note">Jangan kirim data sensitif (password, PIN, data kartu). Percakapan disimpan dan dapat dibaca tim kami.</p>

        <form id="cs-form" class="cs-form" autocomplete="off">
            <label for="cs-input" class="sr-only">Tulis pesan</label>
            <textarea id="cs-input" rows="1" maxlength="{{ (int) config('chat.message_max_length') }}" placeholder="Tulis pertanyaan..." enterkeyhint="send"></textarea>
            <button type="submit" id="cs-send" class="cs-send" aria-label="Kirim pesan"><span class="material-symbols-outlined" aria-hidden="true">send</span></button>
        </form>
        <button type="button" id="cs-new" class="cs-new" hidden>Mulai percakapan baru</button>
    </section>
</div>

@verbatim
<style>
    #cs-chat { position: fixed; right: max(1rem, env(safe-area-inset-right, 0px)); bottom: max(1rem, env(safe-area-inset-bottom, 0px)); z-index: 55; font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
    #cs-chat *, #cs-chat *::before, #cs-chat *::after { box-sizing: border-box; }
    #cs-chat [hidden] { display: none !important; }

    #cs-chat .cs-fab { position: relative; width: 3.5rem; height: 3.5rem; border-radius: 999px; border: 0; cursor: pointer; display: grid; place-items: center; background: var(--neon); color: #181829; box-shadow: 0 10px 30px -8px rgba(210,248,57,.55); transition: transform .15s, box-shadow .2s; }
    #cs-chat .cs-fab:hover { transform: translateY(-2px); box-shadow: 0 0 28px rgba(210,248,57,.45); }
    #cs-chat .cs-fab .material-symbols-outlined { font-size: 26px; }
    #cs-chat .cs-ico-close { display: none; }
    #cs-chat .cs-fab[aria-expanded="true"] .cs-ico-open { display: none; }
    #cs-chat .cs-fab[aria-expanded="true"] .cs-ico-close { display: block; }
    #cs-chat .cs-badge { position: absolute; top: -2px; right: -2px; min-width: 1.15rem; height: 1.15rem; padding: 0 .3rem; border-radius: 999px; background: #F87171; color: #fff; font-size: .7rem; font-weight: 800; line-height: 1.15rem; text-align: center; border: 2px solid var(--ink); }

    #cs-chat .cs-panel { position: absolute; right: 0; bottom: 4.5rem; width: min(24rem, calc(100vw - 2rem)); height: min(34rem, calc(100vh - 7rem)); height: min(34rem, calc(100dvh - 7rem)); display: flex; flex-direction: column; background: var(--panel); color: #F1F2F8; border: 1px solid var(--edge); border-radius: 1.5rem; overflow: hidden; box-shadow: 0 30px 80px -30px rgba(210,248,57,.25), 0 20px 50px rgba(0,0,0,.5); animation: cs-in .2s ease; }
    @keyframes cs-in { from { opacity: 0; transform: translateY(12px) scale(.98); } to { opacity: 1; transform: none; } }

    #cs-chat .cs-head { display: flex; align-items: center; gap: .75rem; padding: .85rem 1rem; border-bottom: 1px solid var(--edge); }
    #cs-chat .cs-avatar { width: 2.25rem; height: 2.25rem; border-radius: 999px; background: var(--neon); color: #181829; display: grid; place-items: center; flex: none; }
    #cs-chat .cs-avatar .material-symbols-outlined { font-size: 20px; }
    #cs-chat .cs-title { flex: 1; min-width: 0; display: flex; flex-direction: column; line-height: 1.25; }
    #cs-chat .cs-title strong { font-size: .9rem; font-weight: 800; }
    #cs-chat .cs-status { font-size: .75rem; color: #A5A9B8; }
    #cs-chat .cs-status[data-state="human"] { color: var(--neon); }
    #cs-chat .cs-x { width: 2.25rem; height: 2.25rem; border-radius: 999px; border: 0; background: transparent; color: #C5C8D8; cursor: pointer; display: grid; place-items: center; }
    #cs-chat .cs-x:hover { background: rgba(255,255,255,.08); color: #fff; }

    #cs-chat .cs-log { flex: 1; overflow-y: auto; overscroll-behavior: contain; padding: 1rem; display: flex; flex-direction: column; gap: .6rem; }
    #cs-chat .cs-msg { max-width: 85%; padding: .6rem .85rem; border-radius: 1.1rem; font-size: .875rem; line-height: 1.45; white-space: pre-wrap; overflow-wrap: anywhere; word-break: break-word; }
    #cs-chat .cs-msg.visitor { align-self: flex-end; background: var(--neon); color: #181829; font-weight: 600; border-bottom-right-radius: .35rem; }
    #cs-chat .cs-msg.ai, #cs-chat .cs-msg.admin { align-self: flex-start; background: #111222; border: 1px solid var(--edge); border-bottom-left-radius: .35rem; }
    #cs-chat .cs-msg.admin { border-color: rgba(210,248,57,.5); }
    #cs-chat .cs-msg.sys { align-self: center; max-width: 95%; background: transparent; color: #A5A9B8; font-size: .75rem; text-align: center; }
    #cs-chat .cs-msg.pending { opacity: .65; }
    #cs-chat .cs-who { display: block; font-size: .7rem; font-weight: 800; color: var(--neon); margin-bottom: .15rem; }
    #cs-chat .cs-typing { display: flex; gap: .3rem; align-items: center; padding: .75rem .9rem; }
    #cs-chat .cs-typing i { width: .4rem; height: .4rem; border-radius: 999px; background: #A5A9B8; animation: cs-dot 1.2s infinite ease-in-out; }
    #cs-chat .cs-typing i:nth-child(2) { animation-delay: .15s; }
    #cs-chat .cs-typing i:nth-child(3) { animation-delay: .3s; }
    @keyframes cs-dot { 0%, 80%, 100% { opacity: .3; transform: translateY(0); } 40% { opacity: 1; transform: translateY(-3px); } }

    #cs-chat .cs-note { margin: 0; padding: .5rem 1rem; border-top: 1px solid var(--edge); font-size: .72rem; line-height: 1.4; color: #A5A9B8; }
    #cs-chat .cs-form { display: flex; align-items: flex-end; gap: .5rem; padding: .75rem; border-top: 1px solid var(--edge); }
    #cs-chat .cs-form textarea { flex: 1; min-width: 0; resize: none; max-height: 7rem; padding: .65rem .85rem; border-radius: 1rem; border: 1px solid #34364F; background: #111222; color: #F1F2F8; font: inherit; font-size: .875rem; line-height: 1.4; }
    #cs-chat .cs-form textarea::placeholder { color: #8E93AD; }
    #cs-chat .cs-form textarea:focus { border-color: var(--neon); outline: none; }
    #cs-chat .cs-send { width: 2.75rem; height: 2.75rem; flex: none; border-radius: 999px; border: 0; cursor: pointer; display: grid; place-items: center; background: var(--neon); color: #181829; }
    #cs-chat .cs-send:disabled { opacity: .5; cursor: not-allowed; }
    #cs-chat .cs-new { margin: .75rem; height: 2.75rem; border-radius: 999px; border: 1px solid var(--edge); background: transparent; color: #F1F2F8; font: inherit; font-weight: 700; font-size: .875rem; cursor: pointer; }
    #cs-chat .cs-new:hover { border-color: var(--neon); background: rgba(210,248,57,.06); }

    @media (max-width: 639px) {
        #cs-chat .cs-panel { position: fixed; left: 0; right: 0; bottom: 0; width: auto; height: min(80vh, 34rem); height: min(80dvh, 34rem); border-radius: 1.5rem 1.5rem 0 0; padding-bottom: env(safe-area-inset-bottom, 0px); }
        #cs-chat.open .cs-fab { display: none; }
    }
    @media (prefers-reduced-motion: reduce) {
        #cs-chat .cs-panel, #cs-chat .cs-typing i { animation: none; }
        #cs-chat .cs-fab { transition: none; }
    }
</style>

<script>
(() => {
    const root = document.getElementById('cs-chat');
    if (!root) return;
    const $ = id => document.getElementById(id);
    const toggle = $('cs-toggle'), panel = $('cs-panel'), log = $('cs-log'), form = $('cs-form'), input = $('cs-input'),
          sendBtn = $('cs-send'), statusEl = $('cs-status'), badge = $('cs-badge'), newBtn = $('cs-new'), closeBtn = $('cs-close');
    const SEND_URL = root.dataset.sendUrl, POLL_URL = root.dataset.pollUrl;
    const KEY = 'cs_chat_token', TOKEN_RE = /^[A-Za-z0-9]{40}$/;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const WELCOME = 'Halo, aku Mirae, AI CashierStore! Tanya soal fitur, cara mulai, atau pembayaran. Kalau perlu, admin kami bisa ikut membalas di sini.';

    // localStorage bisa dimatikan (mode privat / kebijakan browser): semua akses dibungkus try/catch
    const store = {
        get() { try { const t = localStorage.getItem(KEY); return TOKEN_RE.test(t || '') ? t : null; } catch (e) { return null; } },
        set(t) { try { localStorage.setItem(KEY, t); } catch (e) {} },
        clear() { try { localStorage.removeItem(KEY); } catch (e) {} },
    };

    let token = store.get(), lastId = 0, status = 'ai';
    let isOpen = false, sending = false, polling = false, capped = false, firstLoad = false, unread = 0, timer = null, idle = 0;
    const seen = new Set();
    let typingEl = null;

    // Semua teks masuk lewat textContent / createTextNode: isi pesan tidak pernah ditafsirkan sebagai HTML.
    function bubble(role, text, who) {
        const el = document.createElement('div');
        el.className = 'cs-msg ' + role;
        if (who) { const w = document.createElement('span'); w.className = 'cs-who'; w.textContent = who; el.appendChild(w); }
        el.appendChild(document.createTextNode(text));
        return el;
    }
    const scrollDown = () => { log.scrollTop = log.scrollHeight; };
    function addToLog(el) {
        const stick = log.scrollHeight - log.scrollTop - log.clientHeight < 80;
        if (typingEl && typingEl.parentNode === log) log.insertBefore(el, typingEl); else log.appendChild(el);
        if (stick || isOpen) scrollDown();
    }
    const sys = text => addToLog(bubble('sys', text));

    function showTyping(on) {
        if (on && !typingEl) {
            typingEl = document.createElement('div');
            typingEl.className = 'cs-msg ai cs-typing';
            typingEl.setAttribute('aria-hidden', 'true');
            typingEl.innerHTML = '<i></i><i></i><i></i>';   // markup statis, tanpa data pengguna
            log.appendChild(typingEl); scrollDown();
        } else if (!on && typingEl) { typingEl.remove(); typingEl = null; }
    }

    function applyStatus() {
        const closed = status === 'closed';
        const waitingAi = sending && status === 'ai';
        statusEl.textContent = closed ? 'Percakapan ditutup' : status === 'human' ? 'Admin sedang menangani' : waitingAi ? 'Mirae sedang mengetik...' : 'AI CashierStore';
        statusEl.dataset.state = closed ? 'closed' : status;
        form.hidden = closed || capped;
        newBtn.hidden = !(closed || capped);
    }

    function render(messages) {
        const added = [];
        (messages || []).forEach(m => {
            if (seen.has(m.id)) return;
            seen.add(m.id);
            if (m.id > lastId) lastId = m.id;
            const el = bubble(m.role, m.body, m.role === 'admin' ? 'Admin' : '');
            if (m.at) el.title = new Date(m.at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            addToLog(el);
            added.push(m);
        });
        return added;
    }

    function resetAll() {
        token = null; lastId = 0; status = 'ai'; capped = false; unread = 0; idle = 0; firstLoad = true;
        seen.clear(); typingEl = null; log.replaceChildren();
        badge.hidden = true;
        addToLog(bubble('ai', WELCOME));
        applyStatus();
    }

    const headers = extra => Object.assign({ 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, token ? { 'X-Chat-Token': token } : {}, extra || {});

    async function poll() {
        if (!token || polling || sending || document.hidden) return;
        polling = true;
        try {
            const res = await fetch(POLL_URL + '?after=' + lastId, { credentials: 'same-origin', headers: headers() });
            if (res.status === 404) { store.clear(); resetAll(); return; }   // token tidak dikenal server
            if (!res.ok) return;
            const data = await res.json();
            const added = render(data.messages);
            idle = added.length ? 0 : idle + 1;   // makin lama sepi, makin jarang polling
            status = data.status; applyStatus();
            const incoming = added.filter(m => m.role !== 'visitor').length;
            if (firstLoad && !isOpen && incoming) { unread += incoming; badge.textContent = unread > 9 ? '9+' : String(unread); badge.hidden = false; }
            firstLoad = true;
        } catch (e) { /* jaringan putus: coba lagi di putaran berikut */ } finally { polling = false; }
    }

    function loop() {
        clearTimeout(timer);
        if (status === 'closed') return;   // percakapan ditutup: tidak ada yang perlu ditunggu
        // Dasar: terbuka 4 detik, tertutup 20 detik. Tiap 10 poll kosong, jeda ditambah satu kali dasar, maksimal 60 detik.
        const base = isOpen ? 4000 : 20000;
        const delay = Math.min(60000, base * (1 + Math.floor(idle / 10)));
        timer = setTimeout(async () => { await poll(); loop(); }, delay);
    }

    function setOpen(v) {
        isOpen = v;
        root.classList.toggle('open', v);
        panel.hidden = !v;
        toggle.setAttribute('aria-expanded', String(v));
        toggle.setAttribute('aria-label', v ? 'Tutup chat bantuan' : 'Buka chat bantuan');
        if (v) { idle = 0; unread = 0; badge.hidden = true; scrollDown(); setTimeout(() => input.focus(), 30); poll(); }
        loop();
    }

    function adopt(data) {
        if (data.token && data.token !== token) {   // percakapan baru (token lama tidak dikenal / dimulai ulang)
            resetAll(); token = data.token; store.set(token);
        }
        status = data.status || status;
        render(data.messages);
    }

    function fail(res, data, text) {
        const msg = data && data.message;
        if (res.status === 409) { status = 'closed'; return; }
        if (res.status === 429 && data.error === 'limit') { capped = true; sys(msg); return; }
        if (res.status === 419) { sys('Sesi halaman habis. Muat ulang halaman lalu coba lagi.'); }
        else if (res.status === 429 || res.status === 422) { sys(msg || 'Pesan belum bisa dikirim. Coba lagi sebentar.'); }
        else { sys('Terjadi kesalahan. Coba lagi.'); }
        if (!input.value) { input.value = text; autosize(); }
    }

    async function send(text) {
        sending = true; sendBtn.disabled = true; idle = 0;
        const pending = bubble('visitor', text); pending.classList.add('pending'); addToLog(pending);
        if (status === 'ai') showTyping(true);
        applyStatus();
        try {
            const res = await fetch(SEND_URL, {
                method: 'POST', credentials: 'same-origin',
                headers: headers({ 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf }),
                body: JSON.stringify({ message: text, after: lastId }),
            });
            const data = await res.json().catch(() => ({}));
            pending.remove();
            if (res.ok) adopt(data); else fail(res, data, text);
            loop();   // jadwalkan ulang (loop berhenti saat percakapan ditutup)
        } catch (e) {
            pending.remove(); sys('Koneksi bermasalah. Coba lagi.');
            if (!input.value) { input.value = text; autosize(); }
        } finally {
            showTyping(false); sending = false; sendBtn.disabled = false; applyStatus(); if (isOpen && !form.hidden) input.focus();
        }
    }

    function autosize() { input.style.height = 'auto'; input.style.height = Math.min(input.scrollHeight, 112) + 'px'; }

    form.addEventListener('submit', e => {
        e.preventDefault();
        const text = input.value.trim();
        if (!text || sending || status === 'closed' || capped) return;
        input.value = ''; autosize();
        send(text);
    });
    input.addEventListener('input', autosize);
    input.addEventListener('keydown', e => {
        if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) {
            e.preventDefault();
            if (form.requestSubmit) form.requestSubmit(); else form.dispatchEvent(new Event('submit', { cancelable: true }));
        }
    });
    toggle.addEventListener('click', () => setOpen(!isOpen));
    closeBtn.addEventListener('click', () => { setOpen(false); toggle.focus(); });
    panel.addEventListener('keydown', e => { if (e.key === 'Escape') { setOpen(false); toggle.focus(); } });
    newBtn.addEventListener('click', () => { store.clear(); resetAll(); input.focus(); });
    document.addEventListener('visibilitychange', () => { if (!document.hidden) { idle = 0; poll(); loop(); } });

    resetAll();
    firstLoad = false;      // pesan riwayat yang dimuat saat halaman dibuka tidak dihitung "belum dibaca"
    token = store.get();    // resetAll() mengosongkan token; pulihkan dari localStorage
    if (token) poll();
    loop();
})();
</script>
@endverbatim
