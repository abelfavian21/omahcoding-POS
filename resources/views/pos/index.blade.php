@extends('layouts.app')
@section('title', 'Order Line')
@section('content')
<div class="grid xl:grid-cols-[1fr_390px] gap-5 items-start">

  {{-- ============ MENU ============ --}}
  <div>
    <div class="text-xs text-slate-500 mb-1">Order Line › <span class="text-brand-600 font-semibold">Pilih Menu</span></div>
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
      <h1 class="text-3xl font-extrabold tracking-tight">{{ (auth()->user()?->business?->name ?? config('pos.store_name')) }} Menu</h1>
      <input id="search" class="input !w-full sm:!w-72" placeholder="Cari menu…" autocomplete="off">
    </div>
    <div id="cats" class="flex gap-2 overflow-x-auto pb-2 mb-4"></div>
    <div id="grid" class="grid grid-cols-2 md:grid-cols-3 gap-4"></div>
    <p id="empty" class="hidden text-center text-slate-500 py-16">Menu tidak ditemukan.</p>
  </div>

  {{-- ============ KERANJANG ============ --}}
  <aside id="cartPanel" class="card xl:sticky xl:top-6 flex flex-col overflow-hidden xl:max-h-[calc(100vh-3rem)]">
    <div class="p-5 border-b border-slate-100">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-lg font-extrabold" id="custName">Walk-in Customer</div>
          <div class="text-xs text-slate-500" id="custMeta">Belum memilih pelanggan</div>
        </div>
        <div class="flex gap-1">
          <button id="custBtn" class="btn-secondary !h-9 !px-3 text-xs"><span class="material-symbols-outlined">person_search</span> Pelanggan</button>
        </div>
      </div>
      <div id="allergy" class="hidden mt-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs font-semibold px-3 py-2"></div>

      <div class="grid grid-cols-2 gap-2 mt-4">
        <div class="inline-flex p-1 rounded-xl bg-brand-50 col-span-2" id="typeTabs">
          <button data-t="dine_in" class="flex-1 h-9 rounded-lg text-sm font-semibold">Dine-in</button>
          <button data-t="takeaway" class="flex-1 h-9 rounded-lg text-sm font-semibold">Takeaway</button>
        </div>
        <input id="table" class="input col-span-2" placeholder="Nomor meja (mis. 4)">
      </div>
    </div>

    <div class="flex-1 overflow-y-auto p-5 min-h-[120px]">
      <div class="text-xs font-bold text-slate-500 mb-3">Pesanan <span class="text-brand-600">{{ $nextInvoice }}</span></div>
      <div id="lines" class="space-y-3"></div>
      <p id="noLines" class="text-sm text-slate-400 text-center py-8">Keranjang kosong.<br>Ketuk menu untuk menambah.</p>
    </div>

    <div class="p-5 border-t border-slate-100 space-y-3 text-sm">
      <div class="font-bold">Ringkasan Pembayaran</div>
      <div class="flex justify-between text-slate-600"><span>Subtotal</span><span id="sSub">Rp 0</span></div>
      <div class="flex justify-between items-center text-slate-600"><span>Diskon (Rp)</span>
        <input id="discount" type="number" min="0" step="1000" value="0" class="input !h-9 !w-28 text-right"></div>
      <div class="flex justify-between text-slate-600"><span>{{ $taxLabel }} ({{ rtrim(rtrim(number_format($taxRate * 100, 1), '0'), '.') }}%)</span><span id="sTax">Rp 0</span></div>
      <div class="flex justify-between items-end border-t border-dashed border-slate-200 pt-3"><span class="font-bold">Total</span><span class="text-2xl font-extrabold" id="sTotal">Rp 0</span></div>

      <div class="grid grid-cols-3 gap-2" id="payTabs">
        <button data-m="cash" class="h-10 rounded-xl text-sm font-semibold">Tunai</button>
        <button data-m="qris" class="h-10 rounded-xl text-sm font-semibold">QRIS</button>
        <button data-m="card" class="h-10 rounded-xl text-sm font-semibold">Kartu</button>
      </div>
      <div id="cashBox">
        <input id="paid" type="number" min="0" class="input" placeholder="Uang diterima">
        <div class="flex flex-wrap gap-2 mt-2" id="quick"></div>
        <div class="flex justify-between mt-2"><span class="text-slate-600">Kembalian</span><b id="sChange">Rp 0</b></div>
      </div>

      <p id="err" class="hidden text-xs font-semibold text-red-600"></p>
      <button id="charge" class="btn-primary w-full !h-12 !rounded-full text-base" disabled>Bayar Rp 0</button>
      <button id="clear" class="btn-ghost w-full !h-9 text-xs">Kosongkan keranjang</button>
    </div>
  </aside>
</div>

{{-- Tombol keranjang untuk layar kecil --}}
<button id="jump" class="xl:hidden fixed bottom-4 left-4 right-4 z-30 btn-primary !h-12 shadow-xl">Lihat keranjang · <span id="jumpTotal">Rp 0</span></button>

{{-- Modal pilih pelanggan --}}
<div id="custModal" class="hidden fixed inset-0 z-50 bg-slate-900/40 grid place-items-center p-4">
  <div class="card w-full max-w-md p-5">
    <div class="flex justify-between items-center mb-3"><h3 class="font-bold text-lg">Pilih Pelanggan</h3><button data-close class="p-1"><span class="material-symbols-outlined">close</span></button></div>
    <input id="custSearch" class="input" placeholder="Cari nama / telepon / kode…" autocomplete="off">
    <div id="custResults" class="mt-3 space-y-1 max-h-60 overflow-y-auto"></div>
    <div class="border-t border-slate-100 mt-4 pt-4">
      <div class="text-xs font-bold text-slate-500 mb-2">Atau tambah pelanggan baru</div>
      <div class="grid grid-cols-2 gap-2"><input id="newName" class="input" placeholder="Nama"><input id="newPhone" class="input" placeholder="No. telepon"></div>
      <p id="newErr" class="text-xs text-red-600 mt-1 hidden"></p>
      <button id="newBtn" class="btn-secondary w-full mt-2">Simpan & pilih</button>
    </div>
    <button id="walkin" class="btn-ghost w-full mt-2 text-sm">Lanjut sebagai Walk-in</button>
  </div>
</div>

{{-- Modal sukses --}}
<div id="doneModal" class="hidden fixed inset-0 z-50 bg-slate-900/40 grid place-items-center p-4">
  <div class="card w-full max-w-sm p-6 text-center">
    <div class="w-14 h-14 mx-auto rounded-full bg-emerald-50 text-emerald-600 grid place-items-center mb-3"><span class="material-symbols-outlined" style="font-size:32px">check_circle</span></div>
    <h3 class="text-xl font-extrabold">Pembayaran Berhasil</h3>
    <div class="text-sm text-slate-500 mt-1" id="doneInv"></div>
    <div class="text-3xl font-extrabold mt-3" id="doneTotal"></div>
    <div class="text-sm text-slate-600 mt-1" id="doneChange"></div>
    <div class="grid gap-2 mt-5">
      <a id="doneView" target="_blank" class="btn-secondary"><span class="material-symbols-outlined">receipt_long</span> Lihat / Cetak Struk</a>
      <a id="doneDl" class="btn-ghost">Unduh Struk PDF</a>
      <button id="doneNew" class="btn-primary">Pesanan Baru</button>
    </div>
  </div>
</div>

@push('scripts')
<script>
const PRODUCTS = @json($products);
const CATEGORIES = @json($categories);
const TAX_RATE = {{ $taxRate }};
const URLS = { customers: @json(route('pos.customers')), newCustomer: @json(route('pos.customers.store')), checkout: @json(route('pos.checkout')) };
const CSRF = document.querySelector('meta[name=csrf-token]').content;

const $ = id => document.getElementById(id);
const rp = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');
const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

let cart = {};            // product_id -> qty
let cat = 0, q = '';
let customer = @json($customer);
let orderType = 'dine_in', method = 'cash';
const byId = Object.fromEntries(PRODUCTS.map(p => [p.id, p]));

/* ---------- Menu ---------- */
function renderCats() {
  const all = [{id: 0, name: 'Semua Menu', emoji: '🍽️'}, ...CATEGORIES];
  $('cats').innerHTML = all.map(c => `<button data-c="${c.id}" class="shrink-0 px-4 h-10 rounded-full text-sm font-semibold border ${cat === c.id ? 'bg-brand-600 text-white border-brand-600' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'}">${esc(c.name)} ${esc(c.emoji)}</button>`).join('');
}
function renderGrid() {
  const list = PRODUCTS.filter(p => (!cat || p.category_id === cat) && p.name.toLowerCase().includes(q));
  $('empty').classList.toggle('hidden', list.length > 0);
  $('grid').innerHTML = list.map(p => {
    const n = cart[p.id] || 0, out = p.stock <= 0, max = n >= p.stock;
    const media = p.image ? `<img src="${esc(p.image)}" class="w-full h-full object-cover" alt="">` : `<span class="text-6xl">${esc(p.emoji)}</span>`;
    return `<div class="card p-3 ${out ? 'opacity-60' : ''}">
      <div class="aspect-square rounded-xl bg-gradient-to-b from-amber-50 to-orange-50 grid place-items-center overflow-hidden relative">${media}
        ${out ? '<span class="absolute top-2 left-2 pill pill-red">Habis</span>' : (p.stock <= 5 ? `<span class="absolute top-2 left-2 pill pill-amber">Sisa ${p.stock}</span>` : '')}</div>
      <div class="font-bold mt-3 leading-tight min-h-[2.5rem]">${esc(p.name)}</div>
      <div class="flex items-center justify-between mt-2 pt-2 border-t border-slate-100">
        <b>${rp(p.price)}</b>
        <div class="inline-flex items-center gap-2 rounded-full bg-slate-50 border border-slate-200 px-1 h-9">
          <button data-dec="${p.id}" class="w-7 h-7 rounded-full text-slate-500" ${n ? '' : 'disabled'}>−</button>
          <span class="w-5 text-center text-sm font-bold">${n}</span>
          <button data-inc="${p.id}" class="w-7 h-7 rounded-full bg-brand-600 text-white disabled:opacity-40" ${out || max ? 'disabled' : ''}>+</button>
        </div></div></div>`;
  }).join('');
}

/* ---------- Keranjang ---------- */
function totals() {
  const sub = Object.entries(cart).reduce((s, [id, n]) => s + byId[id].price * n, 0);
  const disc = Math.min(Math.max(+$('discount').value || 0, 0), sub);
  const tax = Math.round((sub - disc) * TAX_RATE);
  return { sub, disc, tax, total: sub - disc + tax };
}
function renderCart() {
  const ids = Object.keys(cart);
  $('noLines').classList.toggle('hidden', ids.length > 0);
  $('lines').innerHTML = ids.map(id => {
    const p = byId[id], n = cart[id];
    return `<div class="flex items-center gap-3">
      <div class="w-11 h-11 rounded-xl bg-amber-50 grid place-items-center text-xl overflow-hidden shrink-0">${p.image ? `<img src="${esc(p.image)}" class="w-full h-full object-cover">` : esc(p.emoji)}</div>
      <div class="flex-1 min-w-0"><div class="font-semibold text-sm truncate">${esc(p.name)}</div><div class="text-xs text-slate-500">${n} × ${rp(p.price)}</div></div>
      <div class="font-bold text-sm">${rp(p.price * n)}</div></div>`;
  }).join('');

  const t = totals();
  $('sSub').textContent = rp(t.sub); $('sTax').textContent = rp(t.tax); $('sTotal').textContent = rp(t.total);
  $('charge').textContent = 'Bayar ' + rp(t.total); $('jumpTotal').textContent = rp(t.total);

  const paid = +$('paid').value || 0;
  $('sChange').textContent = rp(Math.max(0, paid - t.total));
  $('quick').innerHTML = t.total > 0 ? [t.total, ...[20000, 50000, 100000].filter(v => v > t.total)].map((v, i) => `<button data-q="${v}" class="px-3 h-8 rounded-lg bg-brand-50 text-brand-700 text-xs font-semibold">${i === 0 ? 'Uang pas' : rp(v)}</button>`).join('') : '';

  const ok = t.total > 0 && (method !== 'cash' || paid >= t.total);
  $('charge').disabled = !ok;
}
function renderMeta() {
  $('custName').textContent = customer ? customer.name : 'Walk-in Customer';
  $('custMeta').textContent = customer ? `${customer.code} · ${customer.tier.toUpperCase()} · ${customer.points} poin` : 'Belum memilih pelanggan';
  const a = $('allergy'); a.classList.toggle('hidden', !(customer && customer.allergy));
  if (customer && customer.allergy) a.textContent = '⚠ ' + customer.allergy;
  document.querySelectorAll('#typeTabs button').forEach(b => { const on = b.dataset.t === orderType; b.className = `flex-1 h-9 rounded-lg text-sm font-semibold ${on ? 'bg-white text-brand-700 shadow-sm' : 'text-slate-500'}`; });
  document.querySelectorAll('#payTabs button').forEach(b => { const on = b.dataset.m === method; b.className = `h-10 rounded-xl text-sm font-semibold border ${on ? 'bg-brand-600 text-white border-brand-600' : 'bg-white text-slate-700 border-slate-200'}`; });
  $('table').classList.toggle('hidden', orderType !== 'dine_in');
  $('cashBox').classList.toggle('hidden', method !== 'cash');
}
const refresh = () => { renderGrid(); renderCart(); renderMeta(); };

/* ---------- Events ---------- */
$('cats').addEventListener('click', e => { const b = e.target.closest('[data-c]'); if (b) { cat = +b.dataset.c; renderCats(); renderGrid(); } });
$('search').addEventListener('input', e => { q = e.target.value.toLowerCase(); renderGrid(); });
$('grid').addEventListener('click', e => {
  const inc = e.target.closest('[data-inc]'), dec = e.target.closest('[data-dec]');
  if (inc) { const id = inc.dataset.inc; if ((cart[id] || 0) < byId[id].stock) cart[id] = (cart[id] || 0) + 1; }
  if (dec) { const id = dec.dataset.dec; if (--cart[id] <= 0) delete cart[id]; }
  if (inc || dec) { $('err').classList.add('hidden'); refresh(); }
});
$('typeTabs').addEventListener('click', e => { const b = e.target.closest('[data-t]'); if (b) { orderType = b.dataset.t; renderMeta(); } });
$('payTabs').addEventListener('click', e => { const b = e.target.closest('[data-m]'); if (b) { method = b.dataset.m; renderMeta(); renderCart(); } });
$('discount').addEventListener('input', renderCart);
$('paid').addEventListener('input', renderCart);
$('quick').addEventListener('click', e => { const b = e.target.closest('[data-q]'); if (b) { $('paid').value = b.dataset.q; renderCart(); } });
$('clear').addEventListener('click', () => { cart = {}; $('discount').value = 0; $('paid').value = ''; refresh(); });
$('jump').addEventListener('click', () => $('cartPanel').scrollIntoView({ behavior: 'smooth' }));

/* ---------- Pelanggan ---------- */
const modal = $('custModal');
let timer;
async function searchCustomers() {
  const r = await fetch(URLS.customers + '?q=' + encodeURIComponent($('custSearch').value), { headers: { Accept: 'application/json' } });
  const list = await r.json();
  $('custResults').innerHTML = list.length ? list.map(c => `<button data-pick='${esc(JSON.stringify(c))}' class="w-full text-left px-3 py-2 rounded-xl hover:bg-brand-50"><b>${esc(c.name)}</b> <span class="pill pill-slate !py-0 !text-[10px]">${esc(c.tier)}</span><div class="text-xs text-slate-500">${esc(c.phone || '-')} · ${esc(c.code)}</div></button>`).join('') : '<p class="text-sm text-slate-500 px-3 py-2">Tidak ada hasil.</p>';
}
const pick = c => { customer = c; modal.classList.add('hidden'); renderMeta(); };
$('custBtn').addEventListener('click', () => { modal.classList.remove('hidden'); $('custSearch').value = ''; searchCustomers(); $('custSearch').focus(); });
$('custSearch').addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(searchCustomers, 250); });
$('custResults').addEventListener('click', e => { const b = e.target.closest('[data-pick]'); if (b) pick(JSON.parse(b.dataset.pick)); });
$('walkin').addEventListener('click', () => pick(null));
modal.addEventListener('click', e => { if (e.target === modal || e.target.closest('[data-close]')) modal.classList.add('hidden'); });
$('newBtn').addEventListener('click', async () => {
  const err = $('newErr'); err.classList.add('hidden');
  const r = await fetch(URLS.newCustomer, { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': CSRF }, body: JSON.stringify({ name: $('newName').value, phone: $('newPhone').value }) });
  const d = await r.json();
  if (!r.ok) { err.textContent = d.errors ? Object.values(d.errors)[0][0] : (d.message || 'Gagal menyimpan'); err.classList.remove('hidden'); return; }
  $('newName').value = $('newPhone').value = ''; pick(d);
});

/* ---------- Checkout ---------- */
$('charge').addEventListener('click', async () => {
  const btn = $('charge'), err = $('err'); err.classList.add('hidden');
  btn.disabled = true; btn.textContent = 'Memproses…';
  const t = totals();
  try {
    const r = await fetch(URLS.checkout, {
      method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': CSRF },
      body: JSON.stringify({
        items: Object.entries(cart).map(([id, qty]) => ({ product_id: +id, qty })),
        customer_id: customer?.id ?? null, order_type: orderType, table_no: $('table').value || null,
        discount: t.disc, payment_method: method, paid_amount: method === 'cash' ? (+$('paid').value || 0) : t.total,
      }),
    });
    const d = await r.json();
    if (!r.ok) throw new Error(d.errors ? Object.values(d.errors)[0][0] : (d.message || 'Transaksi gagal'));
    $('doneInv').textContent = d.invoice_no; $('doneTotal').textContent = rp(d.total);
    $('doneChange').textContent = d.change > 0 ? 'Kembalian ' + rp(d.change) : '';
    $('doneView').href = d.receipt_url; $('doneDl').href = d.download_url;
    $('doneModal').classList.remove('hidden');
  } catch (e) { err.textContent = e.message; err.classList.remove('hidden'); }
  renderCart();
});
$('doneNew').addEventListener('click', () => { location.href = @json(route('pos')); }); // muat ulang: stok & nomor invoice segar

/* ---------- Perintah suara (kasir) ---------- */
if (window.Voice) {
  const V = Voice;
  const FILL = new Set(['tolong','ya','mas','mbak','kak','dong','deh','aja','saja','nya','yang','itu','ini','tambah','tambahkan','tambahin','pesan','pesankan','pesen','order','beli','minta','mau','porsi','gelas','cup','buah','biji','pcs','piring','mangkok','untuk','buat','hapus','buang','batalkan','batal','kurangi','kurangin','kurang','keluarkan','ubah','ganti','jadikan','jadi','set','cancel']);
  const METHOD_NAME = { cash: 'tunai', qris: 'QRIS', card: 'kartu' };
  const sayTotal = () => { const t = totals(); return { show: 'Total ' + rp(t.total), say: 'Total ' + V.spokenRp(t.total) }; };
  const touch = () => { $('err').classList.add('hidden'); refresh(); };

  function itemCommand(t) {
    const removing = /\b(hapus|buang|batalkan|batal|kurangi|kurangin|kurang|keluarkan|cancel)\b/.test(t);
    const setting = /\b(ubah|ganti|jadikan)\b/.test(t) && /\bjadi\b/.test(t);
    const segs = t.split(/\b(?:dan|sama|lalu|terus|kemudian|juga)\b/).map(x => x.trim()).filter(Boolean);
    const done = [], problems = []; let matched = 0;
    for (const seg of segs) {
      const toks = V.tokens(seg), runs = V.numbers(toks);
      const rest = toks.filter((_, i) => !runs.some(r => i >= r.start && i < r.end) && !FILL.has(toks[i]));
      if (!rest.length) continue;
      const m = V.matchProduct(rest, PRODUCTS);
      if (!m) continue;
      matched++;
      if (m.ambiguous) { problems.push('Maksudnya ' + m.ambiguous.map(p => p.name).join(' atau ') + '?'); continue; }
      const p = m.product, have = cart[p.id] || 0;
      const qty = runs.length ? Math.min(Math.max(runs[runs.length - (setting ? 1 : runs.length)].value, 0), 99) : 1;
      if (setting) {
        if (qty <= 0) { delete cart[p.id]; done.push(p.name + ' dihapus'); }
        else if (qty > p.stock) { cart[p.id] = p.stock; problems.push('Stok ' + p.name + ' hanya ' + p.stock); }
        else { cart[p.id] = qty; done.push(p.name + ' jadi ' + qty); }
      } else if (removing) {
        if (!have) { problems.push(p.name + ' belum ada di keranjang'); continue; }
        const hapusSemua = /\b(hapus|buang|batalkan|batal|keluarkan|cancel)\b/.test(t) && !runs.length;
        const left = hapusSemua ? 0 : have - qty;
        if (left <= 0) delete cart[p.id]; else cart[p.id] = left;
        done.push(p.name + ' dikurangi');
      } else {
        if (p.stock <= 0) { problems.push(p.name + ' habis'); continue; }
        const add = Math.min(qty || 1, p.stock - have);
        if (add <= 0) { problems.push('Stok ' + p.name + ' sudah maksimal'); continue; }
        cart[p.id] = have + add; done.push(add + ' ' + p.name);
      }
    }
    if (!matched) return false;
    touch();
    const tot = sayTotal();
    const lines = [];
    if (done.length) lines.push((removing || setting ? 'Diubah: ' : 'Ditambahkan: ') + done.join(', ') + '. ' + tot.show);
    if (problems.length) lines.push(problems.join('. '));
    V.say(lines.join(' · '), (done.length ? (removing || setting ? 'Diubah. ' : 'Ditambahkan. ') + done.join(', ') + '. ' + tot.say + '. ' : '') + problems.join('. '));
    return true;
  }

  V.register((t) => {
    // Setelah pembayaran berhasil
    if (!$('doneModal').classList.contains('hidden')) {
      if (/\b(pesanan baru|order baru|transaksi baru|lanjut|tutup)\b/.test(t)) { V.say('Pesanan baru'); $('doneNew').click(); return true; }
      if (/\b(struk|cetak)\b/.test(t)) { $('doneView').click(); return true; }
      return false;
    }
    if ($('custModal') && !$('custModal').classList.contains('hidden')) return false;

    if (/\b(kosongkan|bersihkan|reset)\b|\b(hapus|batalkan|batal) (semua|keranjang|pesanan)\b/.test(t)) {
      cart = {}; $('discount').value = 0; $('paid').value = ''; touch(); V.say('Keranjang dikosongkan'); return true;
    }
    if (/\b(takeaway|take away|bawa pulang|dibungkus)\b/.test(t)) { orderType = 'takeaway'; renderMeta(); V.say('Takeaway'); return true; }
    if (/\b(dine in|dinein|makan (di )?sini|makan (di )?tempat)\b/.test(t)) { orderType = 'dine_in'; renderMeta(); V.say('Dine in'); return true; }
    const mejaM = t.match(/\bmeja(?: nomor)?\s+(.+)$/);
    if (mejaM) {
      const tk = V.tokens(mejaM[1]), nr = V.numbers(tk);
      const val = nr.length && nr[0].start === 0 ? String(nr[0].value) : (tk[0] || '').toUpperCase();
      if (val) { orderType = 'dine_in'; $('table').value = val; renderMeta(); V.say('Meja ' + val); return true; }
    }
    if (/\b(diskon|potongan|discount)\b/.test(t)) {
      const nr = V.numbers(V.tokens(t)), v = /\b(tanpa|hapus|nol)\b/.test(t) ? 0 : (nr.length ? nr[nr.length - 1].value : null);
      if (v === null) { V.say('Sebutkan nominal diskon, misalnya diskon lima ribu'); return true; }
      $('discount').value = v; renderCart(); V.say('Diskon ' + rp(v), 'Diskon ' + V.spokenRp(v)); return true;
    }
    const mm = t.match(/\b(tunai|cash|qris|kartu|debit|kredit)\b/);
    if (mm) {
      method = mm[1] === 'qris' ? 'qris' : (/kartu|debit|kredit/.test(mm[1]) ? 'card' : 'cash'); renderMeta(); renderCart();
      const nr = V.numbers(V.tokens(t)), amt = nr.length ? nr[nr.length - 1].value : 0;
      if (method === 'cash' && amt >= 1000) { $('paid').value = amt; renderCart(); V.say('Tunai, uang diterima ' + rp(amt), 'Tunai, uang diterima ' + V.spokenRp(amt)); }
      else V.say('Metode ' + METHOD_NAME[method]);
      return true;
    }
    if (/\buang pas\b|^pas$/.test(t)) { method = 'cash'; $('paid').value = totals().total; renderMeta(); renderCart(); V.say('Uang pas'); return true; }
    if (/\b(diterima|terima|dibayar|dibayarkan|bayar|uang|cash)\b/.test(t)) {
      const nr = V.numbers(V.tokens(t)), amt = nr.length ? nr[nr.length - 1].value : 0;
      if (amt >= 1000) { method = 'cash'; $('paid').value = amt; renderMeta(); renderCart(); const ch = Math.max(0, amt - totals().total);
        V.say('Uang diterima ' + rp(amt) + (ch ? ' · kembalian ' + rp(ch) : ''), 'Uang diterima ' + V.spokenRp(amt) + (ch ? '. Kembalian ' + V.spokenRp(ch) : '')); return true; }
    }
    if (/\b(tanpa pelanggan|walk in|walkin|pelanggan umum)\b/.test(t)) { pick(null); V.say('Walk in'); return true; }
    const cm = t.match(/\b(?:pelanggan|member|customer|atas nama)\s+(.+)$/);
    if (cm) {
      const name = cm[1].replace(/\b(bernama|nama)\b/g, '').trim();
      fetch(URLS.customers + '?q=' + encodeURIComponent(name), { headers: { Accept: 'application/json' } }).then(r => r.json()).then(list => {
        if (!list.length) return V.say('Pelanggan ' + name + ' tidak ditemukan');
        const exact = list.find(c => V.norm(c.name) === name) || list[0]; pick(exact); V.say('Pelanggan ' + exact.name);
      }).catch(() => V.say('Gagal mencari pelanggan'));
      return true;
    }
    if (/\b(total|kembalian|berapa)\b/.test(t)) {
      const tt = totals(), paid = +$('paid').value || 0;
      if (!Object.keys(cart).length) { V.say('Keranjang masih kosong'); return true; }
      const ch = method === 'cash' && paid >= tt.total ? paid - tt.total : null;
      V.say('Total ' + rp(tt.total) + (ch !== null ? ' · kembalian ' + rp(ch) : ''), 'Total ' + V.spokenRp(tt.total) + (ch !== null ? '. Kembalian ' + V.spokenRp(ch) : '')); return true;
    }
    if (/\b(bayar|selesai|selesaikan|proses|checkout|lunas|charge)\b/.test(t)) {
      const tt = totals();
      if (!Object.keys(cart).length) { V.say('Keranjang masih kosong'); return true; }
      if (method === 'cash' && (+$('paid').value || 0) < tt.total) { V.say('Uang diterima kurang. Sebutkan uang diterima, atau katakan uang pas.'); return true; }
      V.confirm('Bayar ' + rp(tt.total) + ' dengan ' + METHOD_NAME[method] + '? Ucapkan ya atau batal.', () => {
        $('charge').click();
        setTimeout(() => {
          if (!$('doneModal').classList.contains('hidden')) {
            const ch = $('doneChange').textContent; V.say('Pembayaran berhasil. ' + ch, 'Pembayaran berhasil');
          } else V.say($('err').textContent || 'Pembayaran belum berhasil');
        }, 1800);
      }, 'Bayar ' + V.spokenRp(tt.total) + ' dengan ' + METHOD_NAME[method] + '? Ucapkan ya atau batal.');
      return true;
    }
    if (/\b(tampilkan semua|semua menu|reset pencarian|hapus pencarian)\b/.test(t)) { q = ''; $('search').value = ''; cat = 0; renderCats(); renderGrid(); V.say('Menampilkan semua menu'); return true; }
    const km = t.match(/\bkategori\s+(.+)$/);
    if (km) {
      const m = V.matchProduct(V.tokens(km[1]), CATEGORIES);
      const c = m && (m.product || (m.ambiguous && m.ambiguous[0]));
      if (c) { cat = c.id; renderCats(); renderGrid(); V.say('Kategori ' + c.name); } else V.say('Kategori tidak ditemukan');
      return true;
    }
    const sm = t.match(/\bcari(?: menu)?\s+(.+)$/);
    if (sm) { q = sm[1]; $('search').value = sm[1]; renderGrid(); V.say('Mencari ' + sm[1]); return true; }
    return itemCommand(t);
  });
}

renderCats(); refresh();
</script>
@endpush
@endsection
