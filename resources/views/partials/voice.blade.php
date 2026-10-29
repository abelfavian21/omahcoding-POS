{{-- Perintah suara (Web Speech API, bahasa Indonesia). Halaman bisa mendaftarkan perintah lewat Voice.register(fn). --}}
@php
  $voiceNav = collect($nav ?? [])->reject(fn ($n) => ($n['admin'] && ! (auth()->user()?->isAdmin())) || ($n['route'] === 'shifts.index' && ! ($shiftOn ?? true)))
      ->map(fn ($n) => ['route' => $n['route'], 'label' => $n['label'], 'url' => route($n['route'])])->values();
@endphp
<script>window.VOICE_NAV = @json($voiceNav);</script>

<style>
  /* Mic suara: kecil di HP, dan menyingkir ke atas saat keranjang (bottom sheet) / modal terbuka supaya tidak menutupi tombol. */
  @media (max-width: 1023px) {
    #voiceRoot { bottom: calc(5rem + env(safe-area-inset-bottom, 0px)); }
    #voiceRoot #voiceHelpBtn { width: 2.25rem; height: 2.25rem; }
    #voiceRoot #voiceHelpBtn .material-symbols-outlined { font-size: 20px; }
    #voiceRoot #voiceMic { width: 3rem; height: 3rem; }
    #voiceRoot #voiceMicIcon { font-size: 24px !important; }
  }
  @media (max-width: 1279px) {
    body:has(#cartPanel:not(.translate-y-full)) #voiceRoot { bottom: auto; top: calc(0.5rem + env(safe-area-inset-top, 0px)); right: 0.5rem; flex-direction: column-reverse; align-items: flex-end; }
  }
  @media (max-width: 639px) {
    body:has(#doneModal:not(.hidden), #qrisModal:not(.hidden), #qrisManualModal:not(.hidden), #variantModal:not(.hidden), #custModal:not(.hidden)) #voiceRoot { bottom: auto; top: calc(0.5rem + env(safe-area-inset-top, 0px)); right: 0.5rem; flex-direction: column-reverse; align-items: flex-end; }
  }
  @media (max-width: 1279px) {
    body:has(#cartPanel:not(.translate-y-full)) #voiceRoot #voiceMic,
    body:has(#doneModal:not(.hidden), #qrisModal:not(.hidden), #qrisManualModal:not(.hidden), #variantModal:not(.hidden), #custModal:not(.hidden)) #voiceRoot #voiceMic { width: 2.5rem; height: 2.5rem; }
  }
</style>
<div id="voiceRoot" class="fixed z-[60] bottom-20 right-4 lg:bottom-6 lg:right-6 flex flex-col items-end gap-2 pointer-events-none">
  <div id="voiceToast" class="hidden pointer-events-auto max-w-[min(22rem,calc(100vw-2rem))] rounded-2xl bg-slate-900 text-white shadow-2xl border border-slate-700 px-4 py-3 text-sm">
    <div id="voiceToastText" class="font-semibold leading-snug"></div>
    <div id="voiceToastBtns" class="hidden mt-3 flex gap-2">
      <button type="button" id="voiceYes" class="flex-1 h-9 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold">Ya</button>
      <button type="button" id="voiceNo" class="flex-1 h-9 rounded-lg bg-slate-700 hover:bg-slate-600 text-white text-xs font-bold">Batal</button>
    </div>
  </div>
  <div class="flex items-center gap-2 pointer-events-auto">
    <button type="button" id="voiceHelpBtn" title="Daftar perintah suara" class="w-11 h-11 rounded-full bg-[#181829] text-white shadow-lg grid place-items-center hover:bg-slate-700">
      <span class="material-symbols-outlined">help</span>
    </button>
    <button type="button" id="voiceMic" title="Perintah suara" class="w-14 h-14 rounded-full bg-lime-accent hover:bg-[#DCFA5C] text-slate-950 shadow-xl shadow-lime-accent/30 grid place-items-center transition">
      <span id="voiceMicIcon" class="material-symbols-outlined" style="font-size:28px">mic</span>
    </button>
  </div>
</div>

<div id="voiceHelp" class="hidden fixed inset-0 z-[70] bg-slate-950/50 grid place-items-center p-4">
  <div class="card w-full max-w-lg max-h-[85vh] overflow-y-auto p-6">
    <div class="flex items-center justify-between mb-3">
      <h3 class="font-extrabold text-lg">Perintah Suara</h3>
      <button type="button" id="voiceHelpClose" class="p-1"><span class="material-symbols-outlined">close</span></button>
    </div>
    <p class="text-xs text-slate-500 mb-4">Tekan tombol mikrofon, lalu bicara biasa. Gunakan Chrome atau Edge dengan internet aktif. Mikrofon tetap mendengarkan sampai kamu mematikannya.</p>
    <div class="space-y-4 text-sm">
      <div><div class="font-bold mb-1">Di halaman kasir</div>
        <ul class="list-disc pl-5 space-y-1 text-slate-600">
          <li>"dua kopi susu dan satu croissant"</li>
          <li>"hapus kopi susu" · "kurangi es teh satu" · "ubah kopi susu jadi tiga"</li>
          <li>"kosongkan keranjang"</li>
          <li>"dine in" · "takeaway" · "meja lima"</li>
          <li>"pelanggan Budi" · "tanpa pelanggan"</li>
          <li>"diskon lima ribu"</li>
          <li>"bayar tunai" · "bayar QRIS" · "bayar kartu"</li>
          <li>"uang diterima lima puluh ribu" · "uang pas"</li>
          <li>"total berapa"</li>
          <li>"bayar" lalu jawab "ya" atau "batal"</li>
          <li>"cari kopi" · "kategori minuman" · "tampilkan semua"</li>
          <li>"pesanan baru" (setelah pembayaran berhasil)</li>
          <li>"roti medium dua" (sebut nama varian setelah nama produk)</li>
          <li>"cek stok roti" Â· "stok menipis" Â· "bahan apa yang habis"</li>
        </ul></div>
      <div><div class="font-bold mb-1">Semua halaman</div>
        <ul class="list-disc pl-5 space-y-1 text-slate-600">
          <li>"buka dashboard / kasir / laporan / shift / produk / bahan baku / stok barang / pelanggan / staff / pengaturan / audit log / kantor / panduan"</li>
          <li>"kembali" Â· "muat ulang"</li>
          <li>"mode gelap" · "mode terang"</li>
          <li>"bantuan" · "berhenti mendengarkan"</li>
        </ul></div>
    </div>
    <label class="flex items-center gap-2 mt-5 text-sm font-semibold cursor-pointer">
      <input id="voiceTts" type="checkbox" class="rounded border-slate-300 text-brand-600"> Suara balasan (komputer ikut berbicara)
    </label>
  </div>
</div>

@verbatim
<script>
(() => {
  const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
  const $ = id => document.getElementById(id);
  let rec = null, active = false, speaking = false, ignoreUntil = 0, pending = null, toastTimer = null;
  let ttsOn = localStorage.getItem('voice_tts') !== '0';
  const handlers = [];

  /* ---------- Util teks & angka ---------- */
  const norm = s => String(s || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '')
    .replace(/(\d)[.,](?=\d{3}\b)/g, '$1').replace(/(\d+)\s*(rb|k)\b/g, '$1 ribu')
    .replace(/\b(rp|rupiah)\b/g, ' ').replace(/[^a-z0-9\s]/g, ' ').replace(/\s+/g, ' ').trim();
  const tokens = s => norm(s).split(' ').filter(Boolean);

  const UNITS = { nol: 0, satu: 1, dua: 2, tiga: 3, empat: 4, lima: 5, enam: 6, tujuh: 7, delapan: 8, sembilan: 9 };
  const BIG = ['sepuluh', 'sebelas', 'belas', 'puluh', 'ratus', 'ribu', 'juta', 'seratus', 'seribu', 'sejuta'];
  const isDig = t => /^\d+$/.test(t), isSmall = t => isDig(t) || t in UNITS, isNum = t => isSmall(t) || BIG.includes(t);
  function runValue(ts) {
    let total = 0, cur = 0, small = 0;
    for (const t of ts) {
      if (isDig(t)) small = +t; else if (t in UNITS) small = UNITS[t];
      else if (t === 'sepuluh') cur += 10; else if (t === 'sebelas') cur += 11;
      else if (t === 'belas') { cur += small + 10; small = 0; }
      else if (t === 'puluh') { cur += (small || 1) * 10; small = 0; }
      else if (t === 'seratus') cur += 100;
      else if (t === 'ratus') { cur += (small || 1) * 100; small = 0; }
      else if (t === 'seribu') total += 1000;
      else if (t === 'ribu') { total += ((cur + small) || 1) * 1000; cur = small = 0; }
      else if (t === 'sejuta') total += 1e6;
      else if (t === 'juta') { total += ((cur + small) || 1) * 1e6; cur = small = 0; }
    }
    return total + cur + small;
  }
  /** Cari semua angka (digit / kata) pada array token -> [{start,end,value}] */
  function numbers(ts) {
    const out = [];
    for (let i = 0; i < ts.length; i++) {
      if (!isNum(ts[i])) continue;
      let j = i + 1;
      while (j < ts.length && isNum(ts[j]) && !(isSmall(ts[j]) && isSmall(ts[j - 1]))) j++;
      out.push({ start: i, end: j, value: runValue(ts.slice(i, j)) }); i = j - 1;
    }
    return out;
  }

  /* ---------- Pencocokan nama (toleran salah dengar) ---------- */
  const lev = (a, b) => { const m = a.length, n = b.length; if (!m) return n; if (!n) return m; let p = Array.from({ length: n + 1 }, (_, j) => j);
    for (let i = 1; i <= m; i++) { const c = [i]; for (let j = 1; j <= n; j++) c[j] = Math.min(p[j] + 1, c[j - 1] + 1, p[j - 1] + (a[i - 1] === b[j - 1] ? 0 : 1)); p = c; } return p[n]; };
  const tokMatch = (a, b) => { if (a === b) return true; const L = Math.max(a.length, b.length);
    if (Math.min(a.length, b.length) >= 3 && (a.startsWith(b) || b.startsWith(a))) return true;
    return (L >= 4 && lev(a, b) <= 1) || (L >= 7 && lev(a, b) <= 2); };
  /** seg: array token ucapan; items: [{name}] -> {product} | {ambiguous:[...]} | null */
  function matchProduct(seg, items) {
    const cand = [];
    for (const p of items) {
      const pt = tokens(p.name); if (!pt.length) continue;
      const pm = pt.filter(t => seg.some(s => tokMatch(t, s))).length;
      const sm = seg.filter(s => pt.some(t => tokMatch(t, s))).length;
      if (pm >= pt.length || (seg.length && sm >= seg.length)) cand.push({ p, full: pm >= pt.length, n: pt.length });
    }
    if (!cand.length) return null;
    const full = cand.filter(c => c.full).sort((a, b) => b.n - a.n);
    if (full.length) return { product: full[0].p };
    if (cand.length === 1) return { product: cand[0].p };
    return { ambiguous: cand.slice(0, 3).map(c => c.p) };
  }
  const spokenRp = n => Math.round(n).toLocaleString('id-ID') + ' rupiah';

  /* ---------- UI: toast, status mic, bantuan ---------- */
  function toast(msg, sticky = false) {
    $('voiceToastText').textContent = msg; $('voiceToast').classList.remove('hidden');
    clearTimeout(toastTimer);
    if (!sticky) toastTimer = setTimeout(() => { if (!pending) $('voiceToast').classList.add('hidden'); }, 6000);
  }
  function ui() {
    const m = $('voiceMic'); m.classList.toggle('bg-red-600', active); m.classList.toggle('animate-pulse', active);
    m.classList.toggle('bg-brand-600', !active); $('voiceMicIcon').textContent = active ? 'mic' : 'mic_off';
    m.title = active ? 'Klik untuk mematikan mikrofon' : 'Klik untuk mulai perintah suara';
    try { sessionStorage.setItem('voice_on', active ? '1' : '0'); } catch (e) {}
  }
  const openHelp = () => $('voiceHelp').classList.remove('hidden');
  $('voiceHelpBtn').onclick = openHelp;
  $('voiceHelpClose').onclick = () => $('voiceHelp').classList.add('hidden');
  $('voiceHelp').addEventListener('click', e => { if (e.target === $('voiceHelp')) $('voiceHelp').classList.add('hidden'); });
  $('voiceTts').checked = ttsOn;
  $('voiceTts').onchange = e => { ttsOn = e.target.checked; localStorage.setItem('voice_tts', ttsOn ? '1' : '0'); };

  /* ---------- Suara balasan ---------- */
  function speak(msg) {
    if (!ttsOn || !('speechSynthesis' in window) || !msg) return;
    try {
      speechSynthesis.cancel();
      const u = new SpeechSynthesisUtterance(msg); u.lang = 'id-ID'; u.rate = 1.05; speaking = true;
      const done = () => { speaking = false; ignoreUntil = Date.now() + 900; };
      u.onend = u.onerror = done; speechSynthesis.speak(u);
      // jaga-jaga bila browser tidak punya suara / event onend tidak datang: jangan menahan perintah terlalu lama
      setTimeout(() => { if (speaking && !speechSynthesis.speaking && !speechSynthesis.pending) done(); }, 500);
      setTimeout(() => { if (speaking) done(); }, Math.min(7000, 700 + msg.length * 75));
    } catch (e) { speaking = false; }
  }
  const say = (display, spoken) => { toast(display); speak(spoken || display); };

  /* ---------- Konfirmasi ya / batal ---------- */
  function clearPending() { if (pending) clearTimeout(pending.timer); pending = null; $('voiceToastBtns').classList.add('hidden'); }
  function confirmAction(display, yesFn, spoken) {
    clearPending(); toast(display, true); $('voiceToastBtns').classList.remove('hidden'); speak(spoken || display);
    pending = { yes: yesFn, timer: setTimeout(() => { clearPending(); toast('Konfirmasi kedaluwarsa.'); }, 15000) };
  }
  $('voiceYes').onclick = () => { const p = pending; clearPending(); $('voiceToast').classList.add('hidden'); p && p.yes(); };
  $('voiceNo').onclick = () => { clearPending(); say('Dibatalkan'); };

  /* ---------- Perintah global ---------- */
  const NAV_ALIAS = { dashboard: ['dashboard', 'beranda', 'utama'], pos: ['kasir', 'pos', 'order line', 'order', 'pesanan', 'transaksi'],
    'reports.index': ['laporan', 'report', 'penjualan'], 'products.index': ['produk', 'barang', 'menu'], 'stock.index': ['stok', 'stock', 'persediaan'],
    'customers.index': ['pelanggan', 'customer', 'member'], 'staff.index': ['staff', 'staf', 'karyawan', 'pegawai'], office: ['kantor', 'office', 'kantor virtual'] };

  /* ---- Navigasi suara: semua tab sidebar, pilih alias terpanjang yang cocok ---- */
  Object.assign(NAV_ALIAS, {
    dashboard: ['dashboard', 'beranda', 'utama', 'ringkasan'],
    pos: ['kasir', 'pos', 'order line', 'order', 'pesanan', 'transaksi'],
    'reports.index': ['laporan penjualan', 'laporan', 'report', 'penjualan'],
    'shifts.index': ['shift kasir', 'shift', 'sif'],
    'products.index': ['produk', 'barang', 'menu', 'daftar menu'],
    'ingredients.index': ['stok bahan baku', 'stok bahan', 'bahan baku', 'bahan', 'resep', 'bom'],
    'stock.index': ['stok barang', 'stok', 'stock', 'persediaan', 'gudang'],
    'customers.index': ['pelanggan', 'customer', 'member'],
    'staff.index': ['staff', 'staf', 'karyawan', 'pegawai'],
    'settings.edit': ['pengaturan', 'setelan', 'setting', 'settings', 'seting'],
    'audit.index': ['audit log', 'audit', 'log aktivitas', 'riwayat aktivitas'],
    office: ['kantor virtual', 'kantor', 'office'],
    help: ['buku panduan', 'panduan', 'tutorial', 'manual'],
  });
  const NAV_TRIGGER = /\b(?:buka|bukakan|bukain|pergi ke|masuk ke|pindah ke|menuju|ke halaman|ke tab|halaman|tab)\s+(?:halaman\s+|tab\s+|menu\s+)?(.+)$/;
  const navHit = (route, text) => (NAV_ALIAS[route] || []).reduce((best, a) => (new RegExp('\\b' + a).test(text) && a.length > best ? a.length : best), 0);
  function navCommand(t) {
    if (/^(?:kembali|balik|mundur|halaman sebelumnya)$/.test(t)) { say('Kembali'); setTimeout(() => history.back(), 500); return true; }
    if (/^(?:muat ulang|refresh|segarkan)(?: halaman)?$/.test(t)) { say('Memuat ulang'); setTimeout(() => location.reload(), 500); return true; }
    const m = t.match(NAV_TRIGGER);
    if (!m) return false;
    let best = null, bestLen = 0;
    for (const n of (window.VOICE_NAV || [])) {
      const len = navHit(n.route, m[1]);
      if (len > bestLen) { best = n; bestLen = len; }
    }
    if (best) { say('Membuka ' + best.label); setTimeout(() => { location.href = best.url; }, 700); return true; }
    // menu dikenali tapi tidak boleh dibuka akun ini (khusus admin / shift nonaktif)
    const known = Object.keys(NAV_ALIAS).some(r => !(window.VOICE_NAV || []).some(n => n.route === r) && navHit(r, m[1]) > 0);
    if (known) { say('Menu itu tidak tersedia untuk akun ini'); return true; }
    return false;
  }

  function globalCommand(t) { if (navCommand(t)) return true;
    if (/\b(berhenti|matikan|stop|setop)\b.*\b(mendengar|mendengarkan|mikrofon|suara|mic)\b/.test(t)) { stop('Mikrofon dimatikan'); return true; }
    if (/\b(bantuan|perintah suara|help)\b/.test(t)) { openHelp(); return true; }
    if (/\bmode (gelap|dark|terang|light)\b/.test(t)) {
      const wantDark = /\b(gelap|dark)\b/.test(t), isDark = document.documentElement.classList.contains('dark');
      if (wantDark !== isDark && window.toggleTheme) window.toggleTheme(); say(wantDark ? 'Mode gelap aktif' : 'Mode terang aktif'); return true;
    }
    const m = t.match(/\b(?:buka|pergi ke|masuk ke|halaman)\s+(?:halaman\s+)?(.+)$/);
    if (m) {
      for (const n of (window.VOICE_NAV || [])) {
        if ((NAV_ALIAS[n.route] || []).some(a => m[1].includes(a))) { say('Membuka ' + n.label); setTimeout(() => { location.href = n.url; }, 700); return true; }
      }
    }
    return false;
  }

  function process(raw) {
    const t = norm(raw); if (!t) return;
    toast('“' + raw.trim() + '”');
    if (pending) {
      const p = pending;
      if (/\b(ya|iya|ok|oke|betul|benar|lanjut|konfirmasi|gas|setuju)\b/.test(t)) { clearPending(); p.yes(); return; }
      if (/\b(tidak|batal|jangan|nanti|enggak|gak|cancel|stop)\b/.test(t)) { clearPending(); say('Dibatalkan'); return; }
      clearPending();
    }
    if (globalCommand(t)) return;
    for (const h of handlers) { try { if (h(t, raw)) return; } catch (e) { console.error(e); } }
    toast('Tidak dikenali: “' + raw.trim() + '”');
  }

  /* ---------- Pengenalan ucapan ---------- */
  function build() {
    rec = new SR(); rec.lang = 'id-ID'; rec.continuous = true; rec.interimResults = true; rec.maxAlternatives = 1;
    rec.onresult = e => {
      let interim = '';
      for (let i = e.resultIndex; i < e.results.length; i++) {
        const r = e.results[i], txt = r[0].transcript;
        if (r.isFinal) { if (!speaking && Date.now() > ignoreUntil) process(txt); } else interim += txt;
      }
      if (interim && !speaking) toast('Mendengar… ' + interim);
    };
    rec.onerror = e => {
      if (e.error === 'no-speech' || e.error === 'aborted') return;
      const msg = { 'not-allowed': 'Izin mikrofon ditolak. Izinkan mikrofon di pengaturan browser.', 'service-not-allowed': 'Izin mikrofon ditolak.',
        network: 'Pengenalan suara butuh koneksi internet.', 'audio-capture': 'Mikrofon tidak ditemukan.' }[e.error] || ('Suara error: ' + e.error);
      active = false; toast(msg); ui();
    };
    rec.onend = () => { if (active) setTimeout(() => { try { rec.start(); } catch (e) {} }, 250); else ui(); };
  }
  function start() {
    if (!SR) return toast('Browser belum mendukung perintah suara. Gunakan Chrome atau Edge.');
    if (!window.isSecureContext) return toast('Perintah suara butuh HTTPS atau localhost.');
    if (!rec) build();
    active = true; try { rec.start(); } catch (e) {} ui();
    say('Mikrofon aktif. Silakan bicara.', 'Mikrofon aktif');
  }
  function stop(msg) { active = false; try { rec && rec.stop(); } catch (e) {} ui(); if (msg) say(msg); }

  $('voiceMic').onclick = () => (active ? stop('Mikrofon dimatikan') : start());
  ui();
  try { if (sessionStorage.getItem('voice_on') === '1' && SR && window.isSecureContext) { build(); active = true; try { rec.start(); } catch (e) {} ui(); } } catch (e) {}

  window.Voice = { norm, tokens, numbers, tokMatch, matchProduct, spokenRp, say, confirm: confirmAction, register: fn => handlers.push(fn), process };
})();
</script>
@endverbatim
