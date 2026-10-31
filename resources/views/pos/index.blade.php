@extends('layouts.app')
@section('title', __('pos.title'))
@section('content')
@if (! $shiftOpen && ($shiftRequired ?? true))
  <div class="mb-4 flex flex-wrap items-center justify-between gap-2 rounded-2xl bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 text-sm font-semibold">
    <span>{{ __('pos.shift_warn') }}</span>
    <a href="{{ route('shifts.index') }}" class="btn-dark !h-9 !px-4 text-xs text-lime-accent">{{ __('pos.open_shift') }}</a>
  </div>
@endif
<div class="grid grid-cols-[minmax(0,1fr)] xl:grid-cols-[minmax(0,1fr)_390px] gap-5 items-start">

  {{-- ============ MENU ============ --}}
  <div class="pb-24 xl:pb-0 min-w-0">
    <div class="text-xs text-slate-400 font-semibold mb-1">{{ __('pos.title') }} › <span class="text-slate-700">{{ ($isRetail ?? false) ? __('pos.catalog') : __('pos.pick_menu') }}</span></div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
      <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">{{ (auth()->user()?->business?->name ?? config('pos.store_name')) }} {{ ($isRetail ?? false) ? __('pos.catalog_short') : __('pos.menu') }}</h1>
      <div class="relative w-full sm:w-72">
        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" style="font-size:19px">search</span>
        <input id="search" class="input input-pill" placeholder="{{ ($isRetail ?? false) ? __('pos.search_product') : __('pos.search_menu') }}" autocomplete="off">
      </div>
    </div>
    <div id="cats" class="flex gap-2 overflow-x-auto no-scrollbar pb-2 mb-4 -mx-4 px-4 sm:mx-0 sm:px-0"></div>
    <div id="grid" class="grid grid-cols-1 min-[560px]:grid-cols-2 md:grid-cols-3 2xl:grid-cols-4 gap-2.5 sm:gap-4"></div>
    <p id="empty" class="hidden text-center text-slate-500 py-16">{{ ($isRetail ?? false) ? __('pos.no_product') : __('pos.no_menu') }}</p>
  </div>

  {{-- ============ KERANJANG ============ --}}
  <div id="cartBackdrop" class="hidden fixed inset-0 z-[35] bg-slate-950/50 backdrop-blur-sm xl:hidden"></div>
  <aside id="cartPanel" role="dialog" aria-label="{{ __('pos.cart') }}"
         class="card flex flex-col fixed inset-x-0 bottom-0 z-40 max-h-[92dvh] overflow-y-auto overscroll-contain !rounded-b-none translate-y-full transition-transform duration-300 ease-out
                sm:inset-x-0 sm:mx-auto sm:max-w-lg
                xl:static xl:translate-y-0 xl:max-w-none xl:mx-0 xl:z-auto xl:!rounded-3xl xl:sticky xl:top-6 xl:max-h-[calc(100vh-3rem)] xl:overflow-hidden">
    <div class="xl:hidden sticky top-0 z-10 flex items-center justify-between gap-2 px-4 pt-3 pb-2 border-b border-slate-100" style="background: var(--surface)">
      <div class="flex items-center gap-2">
        <span class="w-9 h-1 rounded-full bg-slate-300 absolute left-1/2 -translate-x-1/2 top-1.5"></span>
        <span class="font-extrabold text-base mt-1">{{ __('pos.cart') }}</span>
        <span id="sheetCount" class="pill pill-slate mt-1">{{ __('pos.items', ['n' => 0]) }}</span>
      </div>
      <button type="button" id="sheetClose" class="p-2 -mr-2 rounded-xl" aria-label="{{ __('pos.close_cart') }}"><span class="material-symbols-outlined">close</span></button>
    </div>
    <div class="p-4 sm:p-5 border-b border-slate-100">
      <div class="flex items-center justify-between gap-2">
        <div>
          <div class="text-lg font-extrabold" id="custName">{{ __('pos.walkin') }}</div>
          <div class="text-xs text-slate-500" id="custMeta">{{ __('pos.no_customer') }}</div>
        </div>
        <div class="flex gap-1">
          <button id="custBtn" class="btn-secondary !h-10 !px-4 text-xs"><span class="material-symbols-outlined">person_search</span> {{ __('pos.customer') }}</button>
        </div>
      </div>
      <div id="allergy" class="hidden mt-3 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-xs font-semibold px-3 py-2"></div>

      @if (! ($isRetail ?? false))
      <div class="flex items-center gap-2 mt-3">
        <div class="inline-flex p-1 rounded-full bg-slate-100 flex-1" id="typeTabs">
          <button data-t="dine_in" class="flex-1 h-10 sm:h-9 rounded-full text-sm font-semibold">{{ __('pos.dine_in') }}</button>
          <button data-t="takeaway" class="flex-1 h-10 sm:h-9 rounded-full text-sm font-semibold">{{ __('pos.takeaway') }}</button>
        </div>
        <input id="table" class="input !w-24 shrink-0 text-center" placeholder="{{ __('pos.table_no') }}" inputmode="numeric" aria-label="{{ __('pos.table_aria') }}">
      </div>
      @endif
    </div>

    <div class="p-4 sm:p-5 xl:flex-1 xl:overflow-y-auto xl:min-h-[120px]">
      <div class="flex items-center justify-between text-xs font-bold text-slate-500 mb-3">
        <span>{{ __('pos.order') }} <span id="invNo" class="text-slate-900">{{ $nextInvoice }}</span></span>
        @if ($isRetail ?? false)
          <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-400">
            <span class="material-symbols-outlined !text-[14px]">storefront</span> {{ __('pos.retail') }}
          </span>
        @endif
      </div>
      <div id="lines" class="space-y-3"></div>
      <p id="noLines" class="text-sm text-slate-400 text-center py-8">{{ __('pos.cart_empty') }}<br>{{ ($isRetail ?? false) ? __('pos.add_product') : __('pos.add_menu') }}</p>
    </div>

    <div class="p-4 sm:p-5 border-t border-slate-100 space-y-3 text-sm xl:shrink-0">
      <div class="font-bold">{{ __('pos.pay_summary') }}</div>
      <div class="flex justify-between text-slate-600"><span>{{ __('pos.subtotal') }}</span><span id="sSub">Rp 0</span></div>
      <div class="flex justify-between items-center text-slate-600"><span>{{ __('pos.discount') }}</span>
        <input id="discount" type="number" min="0" step="1000" value="0" class="input !h-9 !w-28 text-right"></div>
      <div class="flex justify-between text-slate-600"><span>{{ $taxLabel }} ({{ rtrim(rtrim(number_format($taxRate * 100, 1), '0'), '.') }}%)</span><span id="sTax">Rp 0</span></div>
      <div class="flex justify-between items-end border-t border-dashed border-slate-200 pt-3"><span class="font-bold">{{ __('pos.total') }}</span><span class="text-[28px] leading-[34px] tracking-[-0.03em] font-extrabold" id="sTotal">Rp 0</span></div>

      <div class="grid grid-cols-3 gap-2" id="payTabs">
        <button data-m="cash" class="h-10 rounded-2xl text-sm font-semibold">{{ __('pos.cash') }}</button>
        <button data-m="qris" class="h-10 rounded-2xl text-sm font-semibold">QRIS</button>
        <button data-m="card" class="h-10 rounded-2xl text-sm font-semibold">{{ __('pos.card') }}</button>
      </div>
      <div id="cashBox">
        <input id="paid" type="number" min="0" inputmode="numeric" class="input" placeholder="{{ __('pos.paid_ph') }}">
        <div class="flex flex-wrap gap-2 mt-2" id="quick"></div>
        <div class="flex justify-between mt-2"><span class="text-slate-600">{{ __('pos.change') }}</span><b id="sChange">Rp 0</b></div>
      </div>
      <div id="refBox" class="hidden">
        <input id="ref" maxlength="60" class="input" placeholder="{{ __('pos.ref_ph') }}">
      </div>

      <div class="sticky bottom-0 -mx-4 sm:-mx-5 -mb-4 sm:-mb-5 px-4 sm:px-5 pt-3 border-t border-slate-100 space-y-2 xl:static xl:m-0 xl:p-0 xl:border-0" style="background: var(--surface); padding-bottom: calc(0.75rem + env(safe-area-inset-bottom, 0px))">
        <p id="err" class="hidden text-xs font-semibold text-red-600"></p>
        <button id="charge" class="btn-primary w-full !h-12 text-base disabled:opacity-50 disabled:cursor-not-allowed" disabled>{{ __('pos.pay') }} Rp 0</button>
        <button id="clear" class="btn-ghost w-full !h-9 text-xs">{{ __('pos.clear_cart') }}</button>
      </div>
    </div>
  </aside>
</div>

{{-- Modal Pilih Varian Produk --}}
<div id="variantModal" class="hidden fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm grid items-end sm:place-items-center p-0 sm:p-4 transition-all">
  <div class="card w-full max-w-lg p-5 sm:p-6 !rounded-b-none sm:!rounded-3xl max-h-[92vh] flex flex-col shadow-2xl border border-slate-700/50 bg-[#181928] text-white" style="max-height:min(92vh,92dvh)">
    <div class="flex items-start justify-between pb-4 border-b border-slate-800">
      <div>
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full bg-lime-accent"></span>
          <h3 class="font-extrabold text-xl tracking-tight text-white" id="varModalTitle">{{ __('pos.choose_variant') }}</h3>
        </div>
        <p class="text-xs text-slate-400 mt-1" id="varModalBasePrice">{{ __('pos.base_price') }}: Rp 0</p>
      </div>
      <button type="button" id="varModalClose" class="p-2 -mr-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/60 transition"><span class="material-symbols-outlined">close</span></button>
    </div>

    <div id="varModalGroups" class="py-4 space-y-5 overflow-y-auto flex-1 no-scrollbar pr-1"></div>

    <div class="pt-4 border-t border-slate-800 flex items-center justify-between gap-3 bg-[#181928]" style="padding-bottom:calc(1.25rem + env(safe-area-inset-bottom,0px))">
      <div>
        <div class="text-[11px] uppercase tracking-wider text-slate-400 font-bold">{{ __('pos.total_per_item') }}</div>
        <div class="text-2xl font-black text-lime-accent tracking-tight" id="varModalTotal">Rp 0</div>
      </div>
      <button type="button" id="varModalConfirm" class="btn-primary !h-12 !px-6 text-sm font-bold shadow-lime inline-flex items-center gap-2">
        <span class="material-symbols-outlined !text-lg">add_shopping_cart</span> {{ __('pos.add') }}
      </button>
    </div>
  </div>
</div>

{{-- Tombol keranjang untuk layar kecil --}}
<button id="jump" class="hidden xl:hidden fixed left-4 right-4 z-30 btn-dark !h-14 !justify-between shadow-2xl text-lime-accent sm:left-auto sm:right-6 sm:w-80" style="bottom: calc(1rem + env(safe-area-inset-bottom, 0px))" aria-label="{{ __('pos.view_cart') }}"><span class="inline-flex items-center gap-2"><span class="material-symbols-outlined">shopping_bag</span><span><span id="jumpCount">{{ __('pos.items', ['n' => 0]) }}</span></span></span><span class="inline-flex items-center gap-1"><span id="jumpTotal">Rp 0</span><span class="material-symbols-outlined">expand_less</span></span></button>

{{-- Modal pilih pelanggan --}}
<div id="custModal" class="hidden fixed inset-0 z-50 bg-slate-950/50 backdrop-blur-sm grid items-end sm:place-items-center sm:p-4">
  <div class="card w-full max-w-md p-5 !rounded-b-none sm:!rounded-3xl max-h-[92vh] overflow-y-auto">
    <div class="flex justify-between items-center mb-3"><h3 class="font-bold text-lg">{{ __('pos.choose_customer') }}</h3><button data-close class="p-1"><span class="material-symbols-outlined">close</span></button></div>
    <input id="custSearch" class="input" placeholder="{{ __('pos.cust_search_ph') }}" autocomplete="off">
    <div id="custResults" class="mt-3 space-y-1 max-h-60 overflow-y-auto"></div>
    <div class="border-t border-slate-100 mt-4 pt-4">
      <div class="text-xs font-bold text-slate-500 mb-2">{{ __('pos.or_new') }}</div>
      <div class="grid grid-cols-2 gap-2"><input id="newName" class="input" placeholder="{{ __('pos.name') }}"><input id="newPhone" class="input" placeholder="{{ __('pos.phone') }}"></div>
      <p id="newErr" class="text-xs text-red-600 mt-1 hidden"></p>
      <button id="newBtn" class="btn-secondary w-full mt-2">{{ __('pos.save_pick') }}</button>
    </div>
    <button id="walkin" class="btn-ghost w-full mt-2 text-sm">{{ __('pos.continue_walkin') }}</button>
  </div>
</div>

{{-- Modal sukses (di tengah layar) --}}
<style>
  @keyframes donePop { from { opacity: 0; transform: scale(.92) translateY(8px); } to { opacity: 1; transform: none; } }
  .done-pop { animation: donePop .22s ease-out; }
</style>
<div id="doneModal" class="hidden fixed inset-0 z-50 bg-slate-950/50 backdrop-blur-sm grid place-items-center p-4 overflow-y-auto">
  <div class="done-pop card w-full max-w-sm p-6 text-center !rounded-3xl my-auto shadow-2xl">
    <div class="w-14 h-14 mx-auto rounded-full bg-lime-accent text-slate-950 grid place-items-center mb-3"><span class="material-symbols-outlined fill-1" style="font-size:32px">check_circle</span></div>
    <h3 class="text-xl font-extrabold">{{ __('pos.pay_success') }}</h3>
    <div class="text-sm text-slate-500 mt-1" id="doneInv"></div>
    <div class="text-[32px] tracking-[-0.03em] font-extrabold mt-3" id="doneTotal"></div>
    <div class="text-sm text-slate-600 mt-1" id="doneChange"></div>
    <div class="grid gap-2 mt-5">
      <button id="doneThermal" type="button" class="btn-primary" style="display:none"><span class="material-symbols-outlined">print</span> {{ __('pos.print_thermal') }}</button>
      <a id="doneView" target="_blank" class="btn-secondary"><span class="material-symbols-outlined">receipt_long</span> {{ __('pos.view_pdf') }}</a>
      <a id="doneDl" class="btn-ghost">{{ __('pos.dl_pdf') }}</a>
      <button id="doneNew" class="btn-primary">{{ __('pos.new_order') }}</button>
    </div>
  </div>
</div>

{{-- Modal QRIS dinamis (Midtrans) --}}
<div id="qrisModal" class="hidden fixed inset-0 z-50 bg-slate-950/50 backdrop-blur-sm grid items-end sm:place-items-center sm:p-4">
  <div class="card w-full max-w-sm p-5 sm:p-6 text-center !rounded-b-none sm:!rounded-3xl max-h-[94vh] overflow-y-auto" style="padding-bottom: calc(1.25rem + env(safe-area-inset-bottom, 0px))">
    <div class="flex items-center justify-between gap-2">
      <h3 class="font-extrabold text-lg">{{ __('pos.qris_title') }}</h3>
      <span id="qrisBadge" class="pill pill-amber">{{ __('pos.waiting') }}</span>
    </div>
    <div class="text-[32px] tracking-[-0.03em] font-extrabold mt-2" id="qrisTotal">Rp 0</div>
    <div class="relative mx-auto mt-3 w-60 h-60 rounded-2xl border border-slate-200 overflow-hidden grid place-items-center" style="background:#fff">
      <img id="qrisImg" alt="{{ __('pos.qris_alt') }}" class="w-full h-full object-contain p-2" style="background:#fff">
      <div id="qrisOverlay" class="hidden absolute inset-0 grid place-items-center text-sm font-extrabold px-4" style="background:rgba(255,255,255,.92);color:#181829"></div>
    </div>
    <div id="qrisTimer" class="mt-3 text-sm font-bold text-slate-500">{{ __('pos.preparing_qr') }}</div>
    <p id="qrisMsg" class="text-xs text-slate-500 mt-1">{{ __('pos.qris_scan_msg') }}</p>
    <div id="qrisSandbox" class="hidden mt-3 rounded-2xl bg-amber-50 border border-amber-200 text-amber-700 text-left text-xs px-3 py-2.5">
      <b>{{ __('pos.sandbox_title') }}</b> {{ __('pos.sandbox_a') }}
      <a href="https://simulator.sandbox.midtrans.com/qris/index" target="_blank" rel="noopener" class="underline font-bold">{{ __('pos.sandbox_link') }}</a>
      {{ __('pos.sandbox_b') }}
      <div class="mt-1.5 flex items-center gap-2"><code id="qrisGwUrl" class="flex-1 min-w-0 truncate rounded-lg bg-white/60 px-2 py-1 text-[11px]"></code>
      <button type="button" id="qrisCopy" class="shrink-0 rounded-lg bg-white/70 px-2.5 py-1 font-bold">{{ __('pos.copy') }}</button></div>
    </div>
    <div class="grid gap-2 mt-4">
      <button id="qrisRegen" class="btn-primary hidden">{{ __('pos.regen') }}</button>
      <button id="qrisCancel" class="btn-secondary">{{ __('pos.cancel_pay') }}</button>
      <button id="qrisClose" class="btn-secondary hidden">{{ __('pos.close') }}</button>
    </div>
  </div>
</div>

{{-- Modal QRIS manual (gambar QRIS toko dari Pengaturan) --}}
<div id="qrisManualModal" class="hidden fixed inset-0 z-50 bg-slate-950/50 backdrop-blur-sm grid items-end sm:place-items-center sm:p-4">
  <div class="card w-full max-w-sm p-5 sm:p-6 text-center !rounded-b-none sm:!rounded-3xl max-h-[94vh] overflow-y-auto" style="padding-bottom: calc(1.25rem + env(safe-area-inset-bottom, 0px))">
    <h3 class="font-extrabold text-lg">{{ __('pos.qris_title') }}</h3>
    <div class="mx-auto mt-3 w-60 max-w-full rounded-2xl border border-slate-200 overflow-hidden grid place-items-center" style="background:#fff;aspect-ratio:1/1">
      <img id="mqImg" alt="{{ __('pos.qris_store_alt') }}" class="w-full h-full object-contain p-2" style="background:#fff">
    </div>
    <a id="mqOpen" target="_blank" rel="noopener" class="inline-block text-xs font-bold underline mt-2">{{ __('pos.open_full') }}</a>
    <div class="text-xs text-slate-500 mt-3">{{ __('pos.amount_due') }}</div>
    <div class="text-[32px] tracking-[-0.03em] font-extrabold" id="mqTotal">Rp 0</div>
    <p class="text-xs text-slate-500 mt-1">{{ __('pos.manual_msg') }}</p>
    <div id="mqRefBox" class="hidden mt-3 text-left">
      <label class="label" for="mqRef">{{ __('pos.confirm_code') }}</label>
      <input id="mqRef" maxlength="60" class="input" placeholder="{{ __('pos.confirm_ph') }}" autocomplete="off">
    </div>
    <p id="mqErr" class="hidden text-xs font-semibold text-red-600 mt-2"></p>
    <div class="grid gap-2 mt-4">
      <button id="mqConfirm" class="btn-primary !h-12 text-base disabled:opacity-50">{{ __('pos.confirm_pay') }}</button>
      <button id="mqCancel" class="btn-secondary">{{ __('pos.cancel') }}</button>
    </div>
  </div>
</div>

@push('scripts')
<script>
const PRODUCTS = @json($products);
const CATEGORIES = @json($categories);
const TAX_RATE = {{ $taxRate }};
const URLS = { customers: @json(route('pos.customers')), newCustomer: @json(route('pos.customers.store')), checkout: @json(route('pos.checkout')), state: @json(route('pos.state')), qris: @json(route('pos.qris.store')) };
const ICONS = @json(\App\Support\Icons::map());   // emoji lama -> nama ikon vektor
function iconName(v, fb = 'restaurant') {
  v = String(v || '').replace(/[\uFE0F\u200D]/g, '').trim();
  if (!v) return fb;
  if (/^[a-z][a-z0-9_]{1,40}$/.test(v)) return v;
  return ICONS[v] || ICONS[[...v][0]] || fb;
}
/** Ikon Material Symbols; size = px (0 = ikut kelas), cls = kelas tambahan. */
function ico(v, size = 20, fb = 'restaurant', cls = '') {
  return `<span class="material-symbols-outlined ${cls}" ${size ? `style="font-size:${size}px"` : ''} aria-hidden="true">${iconName(v, fb)}</span>`;
}
const QRIS_MANUAL_URL = @json($qrisManualUrl ?? null);   // gambar QRIS statis bisnis (Pengaturan); null = belum diunggah
const QRIS_NEEDS_REF = @json($qrisNeedsRef ?? false);     // di modal QRIS manual: tampilkan input kode konfirmasi (opsi admin, default mati)
const QRIS_DYNAMIC = @json($qrisDynamic ?? false);   // true bila MIDTRANS_SERVER_KEY terisi; false = QRIS manual seperti sebelumnya
const RECEIPT_PRINT = @json($receiptPrint ?? false);          // cetak struk thermal aktif (Pengaturan)
const RECEIPT_AUTO = @json($receiptAutoPrint ?? false);       // langsung cetak setelah bayar berhasil
const IS_RETAIL = @json($isRetail ?? false);         // true untuk minimarket / kelontong: tanpa dine-in & nomor meja
const CSRF = document.querySelector('meta[name=csrf-token]').content;
const LANG = @json(__('pos'));   // terjemahan sesuai bahasa yang dipilih di Pengaturan
/** Terjemahan dengan placeholder :nama, mis. tr('left_n', { n: 3 }). */
const tr = (k, r = {}) => String(LANG[k] ?? k).replace(/:(\w+)/g, (m, n) => r[n] ?? m);
const itemsText = n => tr(n === 1 ? 'item_one' : 'items', { n });

const $ = id => document.getElementById(id);
const rp = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');
const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

let cart = [];            // [{ key, product_id, variant_ids: [], qty, unit_price }]
let idemKey = null;       // kunci anti-dobel: dipakai ulang HANYA bila respons server tidak sempat diterima
const uuid = () => ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g, c => (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16));
let cat = 0, q = '';
let customer = @json($customer);
let orderType = IS_RETAIL ? 'takeaway' : 'dine_in', method = 'cash';
const needsRef = () => method === 'card' || (method === 'qris' && !QRIS_DYNAMIC && !QRIS_MANUAL_URL && QRIS_NEEDS_REF);
const byId = Object.fromEntries(PRODUCTS.map(p => [p.id, p]));

let modalProduct = null;
let selectedVariants = {}; // group_name -> variant_id

function makeCartKey(productId, variantIds = []) {
  const sorted = [...variantIds].sort((a, b) => a - b);
  return productId + ':' + sorted.join('-');
}

function getProductTotalQty(productId) {
  return cart.filter(item => item.product_id === productId).reduce((sum, item) => sum + item.qty, 0);
}

/* ---------- Menu ---------- */
function renderCats() {
  const all = [{id: 0, name: IS_RETAIL ? tr('all_products') : tr('all_menu'), emoji: IS_RETAIL ? 'inventory_2' : 'restaurant_menu'}, ...CATEGORIES];
  $('cats').innerHTML = all.map(c => `<button data-c="${c.id}" class="chip shrink-0 inline-flex items-center gap-1.5 ${cat === c.id ? 'chip-on' : ''}">${ico(c.emoji, 18)}${esc(c.name)}</button>`).join('');
}
function renderGrid() {
  const list = PRODUCTS.filter(p => (!cat || p.category_id === cat) && p.name.toLowerCase().includes(q));
  $('empty').classList.toggle('hidden', list.length > 0);
  $('grid').innerHTML = list.map(p => {
    const totalQty = getProductTotalQty(p.id);
    const hasRecipe = p.has_recipe;
    const out = !hasRecipe && p.stock <= 0;
    const max = !hasRecipe && totalQty >= p.stock;
    const media = p.image ? `<img src="${esc(p.image)}" class="w-full h-full object-cover" alt="" loading="lazy">` : ico(p.emoji, 0, 'restaurant', '!text-[38px] sm:!text-[64px] text-slate-400');
    
    let badge = '';
    if (hasRecipe) {
      badge = '<span class="absolute top-1.5 left-1.5 sm:top-2 sm:left-2 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider bg-slate-950/90 text-lime-accent border border-lime-accent/50 shadow-md backdrop-blur-sm"><span class="material-symbols-outlined" style="font-size:13px;text-transform:none;letter-spacing:normal">receipt_long</span><span class="hidden sm:block">' + tr('recipe_badge') + '</span></span>';
    } else if (out) {
      badge = '<span class="absolute top-1.5 left-1.5 sm:top-2 sm:left-2 pill pill-red !text-[10px] shadow-sm">' + tr('sold_out') + '</span>';
    } else if (p.stock <= 5) {
      badge = `<span class="absolute top-1.5 left-1.5 sm:top-2 sm:left-2 pill pill-amber !text-[10px] shadow-sm">${tr('left_n', { n: p.stock })}</span>`;
    }

    const hasVars = p.variants && p.variants.length > 0;

    return `<div class="card p-2.5 sm:p-3 flex sm:block items-center gap-3 ${out ? 'opacity-60' : ''}" style="${stockStyle(p)}">
      <div class="w-[72px] h-[72px] sm:w-full sm:h-auto sm:aspect-square shrink-0 rounded-2xl bg-slate-100 grid place-items-center overflow-hidden relative">${media}${badge}${stockBadge(p)}</div>
      <div class="flex-1 min-w-0 sm:mt-3">
        <div class="font-bold text-sm sm:text-base leading-tight line-clamp-2 sm:min-h-[2.5rem]">${esc(p.name)}</div>
        <div class="mt-1.5 sm:mt-2 sm:pt-2 sm:border-t border-slate-100 flex items-center justify-between gap-2">
          <b class="text-sm sm:text-base">${rp(p.price)}</b>
          <div class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-1 h-11 sm:h-9 shrink-0">
            @if (true)
            <button data-dec="${p.id}" aria-label="${esc(tr('dec_aria', { name: p.name }))}" class="w-10 h-10 sm:w-7 sm:h-7 rounded-full text-lg sm:text-base text-slate-500 disabled:opacity-40" ${totalQty ? '' : 'disabled'}>−</button>
            <span class="w-6 text-center text-sm font-bold">${totalQty}</span>
            <button data-inc="${p.id}" aria-label="${esc(tr('inc_aria', { name: p.name }))}" class="w-10 h-10 sm:w-7 sm:h-7 rounded-full text-lg sm:text-base bg-lime-accent text-slate-950 font-bold disabled:opacity-40" ${out || max ? 'disabled' : ''}>+</button>
            @endif
          </div>
        </div>
        ${hasVars ? `<div class="mt-2 text-center sm:text-left"><button data-var="${p.id}" class="text-[11px] font-bold text-brand-600 hover:underline inline-flex items-center gap-1"><span class="material-symbols-outlined !text-sm">tune</span> ${tr('variants_n', { n: p.variants.length })}</button></div>` : ''}
      </div></div>`;
  }).join('');
}

/* ---------- Keranjang ---------- */
function totals() {
  const sub = cart.reduce((s, item) => s + (item.unit_price * item.qty), 0);
  const disc = Math.min(Math.max(+$('discount').value || 0, 0), sub);
  const tax = Math.round((sub - disc) * TAX_RATE);
  return { sub, disc, tax, total: sub - disc + tax };
}

function renderCart() {
  $('noLines').classList.toggle('hidden', cart.length > 0);
  $('lines').innerHTML = cart.map(item => {
    const p = byId[item.product_id];
    const max = !p.has_recipe && getProductTotalQty(p.id) >= p.stock;
    const varNames = item.variants_summary ? `<div class="text-[11px] text-brand-600 font-semibold line-clamp-1">${esc(item.variants_summary)}</div>` : '';

    return `<div class="flex items-center gap-3">
      <div class="w-12 h-12 rounded-xl bg-slate-100 grid place-items-center overflow-hidden shrink-0 text-slate-500">${p.image ? `<img src="${esc(p.image)}" class="w-full h-full object-cover" alt="">` : ico(p.emoji, 24)}</div>
      <div class="flex-1 min-w-0">
        <div class="font-semibold text-sm leading-tight line-clamp-1">${esc(p.name)}</div>
        ${varNames}
        <div class="text-xs text-slate-500">${rp(item.unit_price)} × ${item.qty} = <b class="text-slate-700">${rp(item.unit_price * item.qty)}</b></div>
      </div>
      <div class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-1 h-10 shrink-0">
        <button data-ldec="${item.key}" aria-label="${tr('decrease')}" class="w-8 h-8 rounded-full text-lg text-slate-500">−</button>
        <span class="w-5 text-center text-sm font-bold">${item.qty}</span>
        <button data-linc="${item.key}" aria-label="${tr('increase')}" class="w-8 h-8 rounded-full text-lg bg-lime-accent text-slate-950 font-bold disabled:opacity-40" ${max ? 'disabled' : ''}>+</button>
      </div></div>`;
  }).join('');

  const t = totals();
  $('sSub').textContent = rp(t.sub); $('sTax').textContent = rp(t.tax); $('sTotal').textContent = rp(t.total);
  $('charge').textContent = LANG.pay + ' ' + rp(t.total); $('jumpTotal').textContent = rp(t.total);
  const qtyAll = cart.reduce((a, b) => a + b.qty, 0);
  $('jumpCount').textContent = itemsText(qtyAll); $('sheetCount').textContent = itemsText(qtyAll);
  if (!cart.length && sheetIsOpen()) setSheet(false);
  else if (!sheetIsOpen()) $('jump').classList.toggle('hidden', !cart.length);

  const paid = +$('paid').value || 0;
  $('sChange').textContent = rp(Math.max(0, paid - t.total));
  $('quick').innerHTML = t.total > 0 ? [t.total, ...[20000, 50000, 100000].filter(v => v > t.total)].map((v, i) => `<button data-q="${v}" class="px-3 h-9 rounded-full bg-slate-100 text-slate-700 hover:bg-lime-accent hover:text-slate-950 text-xs font-bold transition">${i === 0 ? tr('exact') : rp(v)}</button>`).join('') : '';

  const ok = t.total > 0 && (method === 'cash' ? paid >= t.total : !needsRef() || $('ref').value.trim() !== '');
  $('charge').disabled = !ok;
}
function renderMeta() {
  $('custName').textContent = customer ? customer.name : tr('walkin');
  $('custMeta').textContent = customer ? tr('cust_meta', { code: customer.code, tier: customer.tier.toUpperCase(), points: customer.points }) : tr('no_customer');
  const a = $('allergy'); a.classList.toggle('hidden', !(customer && customer.allergy));
  if (customer && customer.allergy) a.innerHTML = ico('warning', 16) + ' ' + esc(customer.allergy);
  document.querySelectorAll('#typeTabs button').forEach(b => { const on = b.dataset.t === orderType; b.className = `flex-1 h-9 rounded-full text-sm font-semibold ${on ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500'}`; });
  document.querySelectorAll('#payTabs button').forEach(b => { const on = b.dataset.m === method; b.className = `h-10 rounded-2xl text-sm font-bold border ${on ? 'chip-on' : 'bg-white text-slate-700 border-slate-200'}`; });
  if ($('table')) $('table').classList.toggle('hidden', orderType !== 'dine_in');
  $('cashBox').classList.toggle('hidden', method !== 'cash');
  $('refBox').classList.toggle('hidden', !needsRef());
}
const refresh = () => { renderGrid(); renderCart(); renderMeta(); };

/* ---------- Modal Varian ---------- */
function openVariantModal(product) {
  modalProduct = product;
  selectedVariants = {};
  $('varModalTitle').textContent = product.name;
  $('varModalBasePrice').textContent = tr('base_price') + ': ' + rp(product.price);

  const groups = {};
  (product.variants || []).forEach(v => {
    if (!groups[v.group]) groups[v.group] = [];
    groups[v.group].push(v);
  });

  const groupKeys = Object.keys(groups);

  // Jika produk hanya punya 1 varian atau opsi tidak banyak (misal Large, Cheese Jumbo tanpa grouping khusus):
  // Kasir bisa memilih 1 varian saja atau tidak memilih sama sekali (default standar)
  let html = '';

  for (const [groupName, vars] of Object.entries(groups)) {
    // Cek apakah ada opsi default atau opsional (bisa pilih salah satu di dalam grup, atau uncheck)
    html += `<div class="space-y-2.5">
      <div class="flex items-center justify-between">
        <span class="text-xs font-bold text-slate-300 uppercase tracking-wider">${esc(groupName)}</span>
        <button type="button" onclick="clearVarGroup('${esc(groupName)}')" class="text-[11px] text-slate-400 hover:text-lime-accent transition">${tr('reset_var')}</button>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
        ${vars.map(v => {
          const isSelected = selectedVariants[groupName] === v.id;
          return `
          <div onclick="selectVar('${esc(groupName)}', ${v.id})" 
               id="var_card_${v.id}" style="${varStyle(v, product)}"
               class="var-option-card cursor-pointer rounded-2xl p-3.5 border transition-all flex items-center justify-between ${isSelected ? 'border-lime-accent bg-lime-accent/10 ring-1 ring-lime-accent' : 'border-slate-800 bg-slate-900/60 hover:border-slate-700 hover:bg-slate-800/40'}">
            <div class="min-w-0 pr-2">
              <div class="text-sm font-bold text-white tracking-tight truncate">${esc(v.name)}</div>
              <div class="text-xs font-semibold ${v.price > 0 ? 'text-lime-accent' : 'text-slate-400'} mt-0.5">
                ${v.price > 0 ? '+ ' + rp(v.price) : tr('no_extra')}${varNote(v, product)}
              </div>
            </div>
            <div id="var_check_${v.id}" class="w-6 h-6 rounded-full border flex items-center justify-center shrink-0 transition-all ${isSelected ? 'border-lime-accent bg-lime-accent text-slate-950 font-black' : 'border-slate-700 bg-slate-800/80 text-transparent'}">
              <span class="material-symbols-outlined !text-base leading-none">check</span>
            </div>
          </div>
        `}).join('')}
      </div>
    </div>`;
  }

  $('varModalGroups').innerHTML = html;
  calcVarTotal();
  $('variantModal').classList.remove('hidden');
}

function selectVar(groupName, variantId) {
  // Jika sudah dipilih, klik lagi untuk uncheck (opsional)
  if (selectedVariants[groupName] === variantId) {
    delete selectedVariants[groupName];
  } else {
    selectedVariants[groupName] = variantId;
  }
  updateVarUI(groupName);
  calcVarTotal();
}

function clearVarGroup(groupName) {
  delete selectedVariants[groupName];
  updateVarUI(groupName);
  calcVarTotal();
}

function updateVarUI(groupName) {
  if (!modalProduct) return;
  const varsInGroup = (modalProduct.variants || []).filter(v => v.group === groupName);
  const activeId = selectedVariants[groupName];

  varsInGroup.forEach(v => {
    const card = $('var_card_' + v.id);
    const check = $('var_check_' + v.id);
    if (!card || !check) return;

    if (v.id === activeId) {
      card.className = 'var-option-card cursor-pointer rounded-2xl p-3.5 border transition-all flex items-center justify-between border-lime-accent bg-lime-accent/10 ring-1 ring-lime-accent';
      check.className = 'w-6 h-6 rounded-full border flex items-center justify-center shrink-0 transition-all border-lime-accent bg-lime-accent text-slate-950 font-black';
    } else {
      card.className = 'var-option-card cursor-pointer rounded-2xl p-3.5 border transition-all flex items-center justify-between border-slate-800 bg-slate-900/60 hover:border-slate-700 hover:bg-slate-800/40';
      check.className = 'w-6 h-6 rounded-full border flex items-center justify-center shrink-0 transition-all border-slate-700 bg-slate-800/80 text-transparent';
    }
  });
}

function calcVarTotal() {
  if (!modalProduct) return;
  let total = modalProduct.price;
  const vIds = Object.values(selectedVariants);
  (modalProduct.variants || []).forEach(v => {
    if (vIds.includes(v.id)) total += v.price;
  });
  $('varModalTotal').textContent = rp(total);
}

$('varModalClose').addEventListener('click', () => {
  $('variantModal').classList.add('hidden');
  modalProduct = null;
});

$('varModalConfirm').addEventListener('click', () => {
  if (!modalProduct) return;
  const p = modalProduct;
  const vIds = Object.values(selectedVariants);
  let extraPrice = 0;
  const summaries = [];

  (p.variants || []).forEach(v => {
    if (vIds.includes(v.id)) {
      extraPrice += v.price;
      summaries.push(v.name);
    }
  });

  const unitPrice = p.price + extraPrice;
  const key = makeCartKey(p.id, vIds);

  const existing = cart.find(i => i.key === key);
  if (existing) {
    if (p.has_recipe || getProductTotalQty(p.id) < p.stock) {
      existing.qty++;
    }
  } else {
    if (p.has_recipe || getProductTotalQty(p.id) < p.stock) {
      cart.push({
        key,
        product_id: p.id,
        variant_ids: vIds,
        variants_summary: summaries.join(', '),
        qty: 1,
        unit_price: unitPrice,
      });
    }
  }

  $('variantModal').classList.add('hidden');
  modalProduct = null;
  $('err').classList.add('hidden');
  refresh();
});

/* ---------- Events ---------- */
$('cats').addEventListener('click', e => { const b = e.target.closest('[data-c]'); if (b) { cat = +b.dataset.c; renderCats(); renderGrid(); } });
$('search').addEventListener('input', e => { q = e.target.value.toLowerCase(); renderGrid(); });
$('grid').addEventListener('click', e => {
  const varBtn = e.target.closest('[data-var]');
  if (varBtn) {
    const id = +varBtn.dataset.var;
    return openVariantModal(byId[id]);
  }

  const inc = e.target.closest('[data-inc]'), dec = e.target.closest('[data-dec]');
  if (inc) {
    const id = +inc.dataset.inc;
    const p = byId[id];
    if (p.variants && p.variants.length > 0) {
      return openVariantModal(p);
    }
    const key = makeCartKey(id, []);
    const existing = cart.find(i => i.key === key);
    if (existing) {
      if (p.has_recipe || getProductTotalQty(id) < p.stock) existing.qty++;
    } else {
      if (p.has_recipe || getProductTotalQty(id) < p.stock) {
        cart.push({ key, product_id: id, variant_ids: [], variants_summary: '', qty: 1, unit_price: p.price });
      }
    }
  }
  if (dec) {
    const id = +dec.dataset.dec;
    // kurangi item terakhir dari produk ini
    for (let i = cart.length - 1; i >= 0; i--) {
      if (cart[i].product_id === id) {
        if (--cart[i].qty <= 0) cart.splice(i, 1);
        break;
      }
    }
  }
  if (inc || dec) { $('err').classList.add('hidden'); refresh(); }
});
if ($('typeTabs')) $('typeTabs').addEventListener('click', e => { const b = e.target.closest('[data-t]'); if (b) { orderType = b.dataset.t; renderMeta(); } });
$('payTabs').addEventListener('click', e => { const b = e.target.closest('[data-m]'); if (b) { method = b.dataset.m; renderMeta(); renderCart(); } });
$('discount').addEventListener('input', renderCart);
$('paid').addEventListener('input', renderCart);
$('ref').addEventListener('input', renderCart);
$('quick').addEventListener('click', e => { const b = e.target.closest('[data-q]'); if (b) { $('paid').value = b.dataset.q; renderCart(); } });
$('clear').addEventListener('click', () => { cart = []; $('discount').value = 0; $('paid').value = ''; refresh(); });
$('lines').addEventListener('click', e => {
  const inc = e.target.closest('[data-linc]'), dec = e.target.closest('[data-ldec]');
  if (inc) {
    const key = inc.dataset.linc;
    const item = cart.find(i => i.key === key);
    if (item) {
      const p = byId[item.product_id];
      if (p.has_recipe || getProductTotalQty(p.id) < p.stock) item.qty++;
    }
  }
  if (dec) {
    const key = dec.dataset.ldec;
    const idx = cart.findIndex(i => i.key === key);
    if (idx !== -1) {
      if (--cart[idx].qty <= 0) cart.splice(idx, 1);
    }
  }
  if (inc || dec) { $('err').classList.add('hidden'); refresh(); }
});

/* ---------- Keranjang sebagai bottom sheet (HP/tablet); di layar lebar (xl) tetap panel samping ---------- */
const isWide = window.matchMedia('(min-width: 1280px)');
function sheetIsOpen() { return !$('cartPanel').classList.contains('translate-y-full'); }
function setSheet(open) {
  if (isWide.matches) return;
  $('cartPanel').classList.toggle('translate-y-full', !open);
  $('cartBackdrop').classList.toggle('hidden', !open);
  document.body.style.overflow = open ? 'hidden' : '';
  $('jump').classList.toggle('hidden', open || !cart.length);
}
$('jump').addEventListener('click', () => setSheet(true));
$('sheetClose').addEventListener('click', () => setSheet(false));
$('cartBackdrop').addEventListener('click', () => setSheet(false));
document.addEventListener('keydown', e => { if (e.key === 'Escape' && sheetIsOpen()) setSheet(false); });
isWide.addEventListener('change', e => { if (e.matches) { $('cartBackdrop').classList.add('hidden'); document.body.style.overflow = ''; } });

/* ---------- Pelanggan ---------- */
const modal = $('custModal');
let timer;
async function searchCustomers() {
  const r = await fetch(URLS.customers + '?q=' + encodeURIComponent($('custSearch').value), { headers: { Accept: 'application/json' } });
  const list = await r.json();
  $('custResults').innerHTML = list.length ? list.map(c => `<button data-pick='${esc(JSON.stringify(c))}' class="w-full text-left px-3 py-2.5 rounded-2xl hover:bg-slate-50"><b>${esc(c.name)}</b> <span class="pill pill-slate !py-0 !text-[10px]">${esc(c.tier)}</span><div class="text-xs text-slate-500">${esc(c.phone || '-')} · ${esc(c.code)}</div></button>`).join('') : '<p class="text-sm text-slate-500 px-3 py-2">' + tr('no_results') + '</p>';
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
  if (!r.ok) { err.textContent = d.errors ? Object.values(d.errors)[0][0] : (d.message || tr('save_failed')); err.classList.remove('hidden'); return; }
  $('newName').value = $('newPhone').value = ''; pick(d);
});

/* ---------- Peringatan stok bahan baku saat isi keranjang berubah ---------- */
URLS.stockCheck = @json(route('pos.stock.check'));
let stockBlocked = false, stockSig = '', stockTimer = null, stockReq = 0;
(function () {
  const box = document.createElement('div');
  box.id = 'stockAlert';
  box.className = 'hidden text-xs font-semibold text-red-500 space-y-1';
  $('err').insertAdjacentElement('beforebegin', box);
})();
function showStock(messages) {
  const box = $('stockAlert');
  box.innerHTML = messages.map(m => `<p>${esc(m)}</p>`).join('');
  box.classList.toggle('hidden', !messages.length);
}
async function checkStock() {
  if (!cart.length) {
    const was = stockBlocked;
    stockSig = ''; stockBlocked = false; showStock([]);
    if (was) renderCart();
    return;
  }
  const items = cart.map(i => ({ product_id: i.product_id, qty: i.qty, variant_ids: i.variant_ids || [] }));
  const sig = JSON.stringify(items);
  if (sig === stockSig) return;
  stockSig = sig;
  const my = ++stockReq;
  let blocked = false, messages = [];
  try {
    const r = await fetch(URLS.stockCheck, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': CSRF },
      body: JSON.stringify({ items }),
    });
    if (r.ok) {
      const d = await r.json();
      blocked = !d.ok; messages = d.messages || [];
    }
    // gagal jaringan / validasi: jangan blokir kasir; server tetap mengecek lagi saat Bayar
  } catch (e) { /* abaikan */ }
  if (my !== stockReq) return;   // ada perubahan keranjang yang lebih baru
  stockBlocked = blocked;
  showStock(messages);
  renderCart();
}
const _renderCart = renderCart;
renderCart = function () {
  _renderCart();
  if (stockBlocked && cart.length) $('charge').disabled = true;
  clearTimeout(stockTimer);
  stockTimer = setTimeout(checkStock, 250);
};

/* ---------- Tampilan stok bahan baku: kartu merah + notifikasi bubble ---------- */
function stockStyle(p) {
  const a = p.stock_alert;
  return '';
}
function stockBadge(p) {
  const a = p.stock_alert;
  if (!a) return '';
  const red = a.level === 'habis';
  return `<span class="pill ${red ? 'pill-red' : 'pill-amber'} !text-[10px] shadow-sm" style="position:absolute;bottom:.375rem;left:.375rem;display:inline-flex;align-items:center;gap:.25rem"><span class="material-symbols-outlined" style="font-size:13px;text-transform:none;letter-spacing:normal">${red ? 'error' : 'warning'}</span><span class="hidden sm:block">${red ? tr('ing_out') : tr('ing_low')}</span></span>`;
}
(function () {
  const host = document.createElement('div');
  host.id = 'stockToasts';
  host.setAttribute('aria-live', 'polite');
  host.style.cssText = 'position:fixed;top:calc(1rem + env(safe-area-inset-top,0px));right:1rem;z-index:70;display:flex;flex-direction:column;gap:.5rem;width:min(20rem,calc(100vw - 2rem));pointer-events:none';
  document.body.appendChild(host);
})();
function toast(level, title, text) {
  const host = $('stockToasts');
  const key = level + '|' + title + '|' + text;
  if ([...host.children].some(el => el.dataset.key === key)) return;
  while (host.children.length >= 3) host.firstElementChild.remove();
  const red = level === 'habis';
  const el = document.createElement('div');
  el.dataset.key = key;
  el.setAttribute('role', 'alert');
  el.style.cssText = 'pointer-events:auto;cursor:pointer;display:flex;gap:.625rem;align-items:flex-start;padding:.75rem .875rem;border-radius:1rem;color:#fff;box-shadow:0 10px 30px rgba(0,0,0,.35);transition:opacity .25s,transform .25s;opacity:0;transform:translateX(1rem);background:' + (red ? '#b91c1c' : '#b45309');
  el.innerHTML = `<span class="material-symbols-outlined" style="font-size:20px;flex:none">${red ? 'error' : 'warning'}</span><div style="min-width:0"><div style="font-weight:800;font-size:.8125rem;line-height:1.2">${esc(title)}</div><div style="font-size:.75rem;opacity:.92;margin-top:2px">${esc(text)}</div></div>`;
  host.appendChild(el);
  requestAnimationFrame(() => { el.style.opacity = '1'; el.style.transform = 'none'; });
  const close = () => { el.style.opacity = '0'; el.style.transform = 'translateX(1rem)'; setTimeout(() => el.remove(), 250); };
  el.addEventListener('click', close);
  setTimeout(close, 5000);
}
// Toast saat produk bermasalah ditambahkan ke keranjang
let prevQty = {}, qtyReady = false;
function trackStockToasts() {
  const now = {};
  cart.forEach(i => { now[i.product_id] = (now[i.product_id] || 0) + i.qty; });
  if (qtyReady) {
    for (const id in now) {
      if (now[id] > (prevQty[id] || 0)) {
        const p = byId[id], a = p && p.stock_alert;
        if (a) toast(a.level, a.level === 'habis' ? tr('toast_cant', { name: p.name }) : tr('toast_low', { name: p.name }), a.text);
      }
    }
  }
  qtyReady = true; prevQty = now;
}
const _renderCart2 = renderCart;
renderCart = function () { _renderCart2(); trackStockToasts(); };
// Toast saat jumlah di keranjang melebihi stok bahan (hasil cek server)
let lastShortage = '';
function toastShortage(messages) {
  const first = messages && messages[0] ? messages[0] : '';
  if (first && first !== lastShortage) {
    const informed = cart.some(i => byId[i.product_id]?.stock_alert?.level === 'habis');
    if (!informed) toast('habis', tr('stock_short'), first);
  }
  lastShortage = first;
}
const _showStock = showStock;
showStock = function (m) { _showStock(m); toastShortage(m); };

/* ---------- Kartu varian merah bila bahan resep varian habis ---------- */
function varAlert(v, p) {
  const own = v.stock_alert, base = p.stock_alert;
  if (own && own.level === 'habis') return own;
  if (base && base.level === 'habis') return base;   // bahan dasar produk habis: semua varian ikut tidak bisa dibuat
  return own || null;
}
function varStyle(v, p) {
  const a = varAlert(v, p);
  if (!a) return '';
  return a.level === 'habis' ? 'border-color:#ef4444;background:rgba(239,68,68,.12)' : 'border-color:#f59e0b';
}
function varNote(v, p) {
  const a = varAlert(v, p);
  if (!a) return '';
  return `<span style="display:block;margin-top:2px;font-size:.6875rem;font-weight:700;color:${a.level === 'habis' ? '#f87171' : '#fbbf24'}">${esc(a.text)}</span>`;
}
const _selectVar = selectVar;
selectVar = function (groupName, variantId) {
  _selectVar(groupName, variantId);
  if (!modalProduct || selectedVariants[groupName] !== variantId) return;
  const v = (modalProduct.variants || []).find(x => x.id === variantId);
  const a = v && varAlert(v, modalProduct);
  if (a) toast(a.level, a.level === 'habis' ? tr('toast_cant_var', { name: modalProduct.name, variant: v.name }) : tr('toast_low_var', { name: modalProduct.name, variant: v.name }), a.text);
};

/* ---------- Sembunyikan tombol voice & bantuan saat modal varian terbuka ---------- */
(function () {
  const modal = $('variantModal');
  if (!modal) return;
  const sync = () => {
    const open = !modal.classList.contains('hidden');
    ['voiceHelpBtn', 'voiceMic'].forEach(id => {
      const el = $(id);
      if (el) el.style.display = open ? 'none' : '';
    });
  };
  new MutationObserver(sync).observe(modal, { attributes: true, attributeFilter: ['class'] });
  sync();
})();

/* Modal sukses: satu kali per invoice (cegah popup ganda dari poll QRIS yang tumpang tindih / klik ganda). */
let lastDoneInv = null;
function showDone(o, changeText) {
  if (lastDoneInv === o.invoice_no) return false;
  lastDoneInv = o.invoice_no;
  $('doneInv').textContent = o.invoice_no; $('doneTotal').textContent = rp(o.total); $('doneChange').textContent = changeText || '';
  $('doneView').href = o.receipt_url; $('doneDl').href = o.download_url;
  thermalUrl = RECEIPT_PRINT ? (o.thermal_url || null) : null;
  $('doneThermal').style.display = thermalUrl ? '' : 'none';
  $('doneModal').classList.remove('hidden');
  if (thermalUrl && RECEIPT_AUTO) printThermal(thermalUrl);   // satu kali per invoice (dijaga guard lastDoneInv di atas)
  return true;
}

/* Cetak struk thermal: struk dimuat di iframe tersembunyi (?print=1 memanggil dialog cetak browser).
   Untuk cetak tanpa dialog, jalankan Chrome/Edge dengan --kiosk-printing dan set printer thermal sebagai default. */
let thermalUrl = null;
function printThermal(url) {
  if (!url) return;
  let f = $('thermalFrame');
  if (!f) {
    f = document.createElement('iframe');
    f.id = 'thermalFrame'; f.setAttribute('aria-hidden', 'true'); f.tabIndex = -1;
    f.style.cssText = 'position:fixed;right:0;bottom:0;width:0;height:0;border:0;visibility:hidden';
    document.body.appendChild(f);
  }
  f.src = 'about:blank';                       // paksa muat ulang walau URL sama (cetak ulang)
  setTimeout(() => { f.src = url; }, 50);
}
$('doneThermal').addEventListener('click', () => printThermal(thermalUrl));

/* ---------- Checkout ---------- */
$('charge').addEventListener('click', () => {
  if (method === 'qris' && QRIS_DYNAMIC) return startQris();
  if (method === 'qris' && QRIS_MANUAL_URL) return openManualQris();
  return runCheckout(needsRef() ? $('ref').value.trim() : null, $('err'), $('charge'));
});

async function runCheckout(refValue, err, btn) {
  if (btn.disabled) return;   // sedang diproses: abaikan klik/perintah suara ganda
  err.classList.add('hidden');
  const label = btn.textContent;
  btn.disabled = true; btn.textContent = tr('processing');
  const t = totals();
  try {
    const r = await fetch(URLS.checkout, {
      method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': CSRF },
      body: JSON.stringify({
        idempotency_key: (idemKey ??= uuid()),
        items: cart.map(item => ({
          product_id: item.product_id,
          qty: item.qty,
          variant_ids: item.variant_ids || [],
        })),
        customer_id: customer?.id ?? null, order_type: orderType, table_no: $('table') ? ($('table').value || null) : null,
        discount: t.disc, payment_method: method, paid_amount: method === 'cash' ? (+$('paid').value || 0) : t.total,
        payment_ref: refValue || null,
      }),
    });
    const d = await r.json();
    if (!r.ok) { idemKey = null; }   // server menolak -> percobaan berikutnya adalah transaksi baru
    if (!r.ok) throw new Error(d.errors ? Object.values(d.errors)[0][0] : (d.message || tr('tx_failed')));
    idemKey = null; $('ref').value = ''; $('mqRef').value = '';
    $('qrisManualModal').classList.add('hidden'); setSheet(false);
    showDone(d, d.change > 0 ? tr('change_text', { amount: rp(d.change) }) : '');
  } catch (e) { err.textContent = e.message; err.classList.remove('hidden'); }
  btn.textContent = label; btn.disabled = false;
  renderCart();
}

/* ---------- QRIS manual (gambar QRIS toko) ---------- */
function openManualQris() {
  $('mqTotal').textContent = rp(totals().total);
  if ($('mqImg').getAttribute('src') !== QRIS_MANUAL_URL) $('mqImg').src = QRIS_MANUAL_URL;
  $('mqOpen').href = QRIS_MANUAL_URL;
  $('mqRefBox').classList.toggle('hidden', !QRIS_NEEDS_REF);
  $('mqRef').value = ''; $('mqErr').classList.add('hidden');
  $('qrisManualModal').classList.remove('hidden');
  if (QRIS_NEEDS_REF) $('mqRef').focus();
}
$('mqConfirm').addEventListener('click', () => {
  const ref = $('mqRef').value.trim(), err = $('mqErr');
  if (QRIS_NEEDS_REF && !ref) { err.textContent = tr('enter_code'); err.classList.remove('hidden'); return; }
  runCheckout(QRIS_NEEDS_REF ? ref : null, err, $('mqConfirm'));
});
$('mqCancel').addEventListener('click', () => { if (!$('mqConfirm').disabled) $('qrisManualModal').classList.add('hidden'); });
/* ---------- QRIS dinamis (Midtrans) ---------- */
let qris = null, qrisPoll = null, qrisTick = null, qrisLeft = 0, qrisImgFor = null;
const JSON_HEADERS = { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': CSRF };
const stopQris = () => { clearInterval(qrisPoll); clearInterval(qrisTick); qrisPoll = qrisTick = null; };
const qrisClock = s => String(Math.floor(s / 60)).padStart(2, '0') + ':' + String(s % 60).padStart(2, '0');

async function startQris() {
  const btn = $('charge'), err = $('err'); err.classList.add('hidden');
  btn.disabled = true; btn.textContent = tr('making_qris');
  const t = totals();
  try {
    const r = await fetch(URLS.qris, { method: 'POST', headers: JSON_HEADERS, body: JSON.stringify({
      idempotency_key: (idemKey ??= uuid()),
      items: cart.map(item => ({
        product_id: item.product_id,
        qty: item.qty,
        variant_ids: item.variant_ids || [],
      })),
      customer_id: customer?.id ?? null, order_type: orderType, table_no: $('table') ? ($('table').value || null) : null, discount: t.disc,
    }) });
    const d = await r.json();
    if (!r.ok) { idemKey = null; throw new Error(d.errors ? Object.values(d.errors)[0][0] : (d.message || tr('qris_failed'))); }
    renderQris(d);
  } catch (e) { err.textContent = e.message; err.classList.remove('hidden'); }
  renderCart();
}

function renderQris(d) {
  qris = d;
  if (d.status === 'settled' && d.order) { finishQris(d.order); return; }
  const st = d.status, pending = st === 'pending';
  $('qrisModal').classList.remove('hidden');
  $('qrisTotal').textContent = rp(d.total);
  if (d.qr && qrisImgFor !== d.id) { $('qrisImg').src = d.qr; qrisImgFor = d.id; }

  const BADGE = { pending: ['pill-amber', tr('waiting')], expired: ['pill-red', tr('st_expired')], cancelled: ['pill-slate', tr('st_cancelled')], failed: ['pill-red', tr('st_failed')], review: ['pill-red', tr('st_review')] }[st] || ['pill-slate', st];
  $('qrisBadge').className = 'pill ' + BADGE[0]; $('qrisBadge').textContent = BADGE[1];

  qrisLeft = d.seconds_left;
  const over = $('qrisOverlay');
  over.classList.toggle('hidden', pending && qrisLeft > 0);
  over.textContent = { expired: tr('qr_expired'), cancelled: tr('st_cancelled'), failed: tr('pay_failed'), review: tr('st_review') }[st] || (pending ? tr('qr_expired') : '');
  $('qrisMsg').textContent = st === 'review' ? (d.message || tr('review_msg')) + ' ' + tr('review_tail')
    : st === 'failed' ? (d.message || tr('rejected')) : st === 'cancelled' ? tr('pay_cancelled') : st === 'expired' ? tr('time_up')
    : tr('qris_scan_msg');

  const expiredNow = pending && qrisLeft <= 0;
  $('qrisRegen').classList.toggle('hidden', !(st === 'expired' || st === 'failed' || expiredNow));
  $('qrisCancel').classList.toggle('hidden', !pending);
  $('qrisClose').classList.toggle('hidden', pending);
  $('qrisSandbox').classList.toggle('hidden', !(d.sandbox && d.gateway_qr_url && pending && qrisLeft > 0));
  $('qrisGwUrl').textContent = d.gateway_qr_url || '';

  if (!pending) { stopQris(); $('qrisTimer').textContent = ''; return; }
  const paintClock = () => { $('qrisTimer').textContent = qrisLeft > 0 ? tr('valid_for', { time: qrisClock(qrisLeft) }) : tr('waiting_gw'); };
  paintClock();
  if (!qrisTick) qrisTick = setInterval(() => { if (qrisLeft > 0) { qrisLeft--; paintClock(); if (qrisLeft === 0) renderQris({ ...qris, seconds_left: 0 }); } }, 1000);
  if (!qrisPoll) qrisPoll = setInterval(pollQris, 3000);
}

async function pollQris() {
  if (!qris) return;
  const id = qris.id;
  try {
    const r = await fetch(qris.urls.show, { headers: { Accept: 'application/json' } });
    if (!r.ok) return;
    const d = await r.json();
    if (!qris || qris.id !== id) return;   // respons telat: QRIS sudah selesai/diganti, abaikan
    renderQris(d);
  } catch (e) { /* jaringan putus sesaat: coba lagi di putaran berikutnya */ }
}

function finishQris(o) {
  stopQris(); qris = null; idemKey = null; qrisImgFor = null;
  $('qrisModal').classList.add('hidden');
  if (!showDone(o, '')) return;   // sudah pernah ditampilkan (respons poll ganda/telat)
  if (window.Voice) Voice.say('Pembayaran QRIS berhasil');
}

$('qrisCancel').addEventListener('click', async () => {
  if (!qris) return;
  $('qrisCancel').disabled = true;
  try {
    const r = await fetch(qris.urls.cancel, { method: 'POST', headers: JSON_HEADERS });
    const d = await r.json();
    if (r.ok) { renderQris(d); if (d.status === 'cancelled') { stopQris(); $('qrisModal').classList.add('hidden'); } }
  } catch (e) { /* biarkan modal terbuka; kasir bisa mencoba lagi */ }
  $('qrisCancel').disabled = false;
});
$('qrisRegen').addEventListener('click', () => { stopQris(); $('qrisModal').classList.add('hidden'); startQris(); });
$('qrisClose').addEventListener('click', () => { stopQris(); qris = null; $('qrisModal').classList.add('hidden'); });
$('qrisCopy').addEventListener('click', () => { navigator.clipboard?.writeText($('qrisGwUrl').textContent).then(() => { $('qrisCopy').textContent = tr('copied'); setTimeout(() => $('qrisCopy').textContent = tr('copy'), 1500); }); });

/* Pesanan baru TANPA reload: kosongkan form, lalu ambil stok & nomor invoice segar di latar belakang. */
async function newOrder() {
  $('doneModal').classList.add('hidden');
  cart = []; idemKey = null; customer = null;
  orderType = IS_RETAIL ? 'takeaway' : 'dine_in'; method = 'cash';
  $('discount').value = 0; $('paid').value = ''; $('ref').value = '';
  if ($('table')) $('table').value = '';
  $('err').classList.add('hidden');
  setSheet(false);
  refresh();
  try {
    const r = await fetch(URLS.state, { headers: { Accept: 'application/json' } });
    if (!r.ok) throw new Error();
    const d = await r.json();
    PRODUCTS.splice(0, PRODUCTS.length, ...d.products);
    Object.keys(byId).forEach(k => delete byId[k]);
    PRODUCTS.forEach(p => { byId[p.id] = p; });
    $('invNo').textContent = d.next_invoice;
    renderGrid();
  } catch (e) { /* jaringan putus: form sudah bersih; stok/invoice menyegar di transaksi berikutnya atau saat halaman dimuat ulang */ }
}
$('doneNew').addEventListener('click', newOrder);

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

  /* ---- Perintah keranjang: keranjang berbentuk array baris + varian + stok bahan baku ---- */
  const stockNote = (p, vars = []) => {
    const list = [p.stock_alert, ...vars.map(v => v.stock_alert)].filter(Boolean);
    const bad = list.find(a => a.level === 'habis') || list[0];
    return bad ? bad.text : '';
  };
  // cocokkan nama varian yang ikut disebut, misalnya "roti medium dua" (satu varian per grup)
  const pickVariants = (p, seg) => {
    const chosen = {};
    for (const v of (p.variants || [])) {
      const vt = V.tokens(v.name);
      if (vt.length && vt.every(x => seg.some(s => V.tokMatch(x, s)))) chosen[v.group] = v;
    }
    return Object.values(chosen);
  };
  const lineLabel = (p, vars) => p.name + (vars.length ? ' ' + vars.map(v => v.name).join(' ') : '');
  const addLine = (p, vars, n) => {
    const vIds = vars.map(v => v.id), key = makeCartKey(p.id, vIds);
    const line = cart.find(i => i.key === key);
    if (line) line.qty += n;
    else cart.push({ key, product_id: p.id, variant_ids: vIds, variants_summary: vars.map(v => v.name).join(', '), qty: n, unit_price: p.price + vars.reduce((s, v) => s + v.price, 0) });
  };
  const dropLines = (p, key) => { for (let i = cart.length - 1; i >= 0; i--) if (cart[i].product_id === p.id && (!key || cart[i].key === key)) cart.splice(i, 1); };

  // "cek stok roti" Â· "stok menipis" Â· "bahan apa yang habis"
  function stockQuery(t) {
    const listAsk = (/\b(menipis|tipis|habis|abis)\b/.test(t) && /\b(stok|bahan)\b/.test(t)) || /^(?:cek |lihat )?(?:sisa )?stok(?: bahan(?: baku)?)?$/.test(t);
    const qm = t.match(/^(?:cek |lihat )?(?:sisa )?stok\s+(.+)$/);
    if (!listAsk && !qm) return false;
    if (listAsk) {
      const seen = new Set(), items = [];
      const push = txt => { if (txt && !seen.has(txt)) { seen.add(txt); items.push(txt); } };
      PRODUCTS.forEach(p => {
        push(p.stock_alert && p.stock_alert.text);
        (p.variants || []).forEach(v => push(v.stock_alert && v.stock_alert.text));
        if (!p.has_recipe && p.stock <= 0) push('Stok ' + p.name + ' habis');
      });
      if (!items.length) { V.say('Semua bahan baku dan stok aman'); return true; }
      V.say('Perlu perhatian: ' + items.slice(0, 5).join(', ') + (items.length > 5 ? ', dan ' + (items.length - 5) + ' lainnya' : ''));
      return true;
    }
    const m = V.matchProduct(V.tokens(qm[1]), PRODUCTS);
    if (!m) { V.say('Produk tidak ditemukan'); return true; }
    if (m.ambiguous) { V.say('Maksudnya ' + m.ambiguous.map(p => p.name).join(' atau ') + '?'); return true; }
    const p = m.product;
    if (p.has_recipe) {
      const notes = [...new Set([p.stock_alert, ...(p.variants || []).map(v => v.stock_alert)].filter(Boolean).map(a => a.text))];
      V.say(p.name + ': ' + (notes.length ? notes.join(', ') : 'bahan cukup'));
    } else V.say('Stok ' + p.name + ' ' + p.stock);
    return true;
  }

  function itemCommandV2(t) {
    if (stockQuery(t)) return true;
    const removing = /\b(hapus|buang|batalkan|batal|kurangi|kurangin|kurang|keluarkan|cancel)\b/.test(t);
    const setting = /\b(ubah|ganti|jadikan)\b/.test(t) && /\bjadi\b/.test(t);
    const segs = t.split(/\b(?:dan|sama|lalu|terus|kemudian|juga)\b/).map(x => x.trim()).filter(Boolean);
    const done = [], problems = [], notes = []; let matched = 0;
    for (const seg of segs) {
      const toks = V.tokens(seg), runs = V.numbers(toks);
      const rest = toks.filter((_, i) => !runs.some(r => i >= r.start && i < r.end) && !FILL.has(toks[i]));
      if (!rest.length) continue;
      const m = V.matchProduct(rest, PRODUCTS);
      if (!m) continue;
      matched++;
      if (m.ambiguous) { problems.push('Maksudnya ' + m.ambiguous.map(p => p.name).join(' atau ') + '?'); continue; }
      const p = m.product, vars = pickVariants(p, rest), key = makeCartKey(p.id, vars.map(v => v.id));
      const lines = cart.filter(i => i.product_id === p.id);
      const target = cart.find(i => i.key === key) || (vars.length ? null : lines[lines.length - 1]);
      const qty = runs.length ? Math.min(Math.max(runs[runs.length - (setting ? 1 : runs.length)].value, 0), 99) : 1;
      const label = lineLabel(p, vars);

      if (setting) {
        if (!target) { problems.push(label + ' belum ada di keranjang'); continue; }
        if (qty <= 0) { dropLines(p, target.key); done.push(label + ' dihapus'); continue; }
        const others = getProductTotalQty(p.id) - target.qty;
        if (!p.has_recipe && others + qty > p.stock) {
          target.qty = p.stock - others;
          if (target.qty <= 0) dropLines(p, target.key);
          problems.push('Stok ' + p.name + ' hanya ' + p.stock);
        } else { target.qty = qty; done.push(label + ' jadi ' + qty); }
      } else if (removing) {
        if (!target) { problems.push(label + ' belum ada di keranjang'); continue; }
        const hapusSemua = /\b(hapus|buang|batalkan|batal|keluarkan|cancel)\b/.test(t) && !runs.length;
        const left = hapusSemua ? 0 : target.qty - qty;
        if (left <= 0) dropLines(p, hapusSemua && !vars.length ? null : target.key); else target.qty = left;
        done.push(label + (left <= 0 ? ' dihapus' : ' dikurangi'));
      } else {
        const n = qty || 1, room = p.has_recipe ? n : p.stock - getProductTotalQty(p.id);
        if (room <= 0) { problems.push(p.stock <= 0 ? p.name + ' habis' : 'Stok ' + p.name + ' sudah maksimal'); continue; }
        const add = Math.min(n, room);
        addLine(p, vars, add); done.push(add + ' ' + label);
        if (add < n) problems.push('Stok ' + p.name + ' hanya cukup ' + add);
        const note = stockNote(p, vars);
        if (note) notes.push(p.name + ': ' + note);
      }
    }
    if (!matched) return false;
    touch();
    const tot = sayTotal();
    const extra = [...problems, ...notes.map(n => 'Perhatian, ' + n)];
    const lines = [];
    if (done.length) lines.push((removing || setting ? 'Diubah: ' : 'Ditambahkan: ') + done.join(', ') + '. ' + tot.show);
    if (extra.length) lines.push(extra.join('. '));
    V.say(lines.join(' Â· '), (done.length ? (removing || setting ? 'Diubah. ' : 'Ditambahkan. ') + done.join(', ') + '. ' + tot.say + '. ' : '') + extra.join('. '));
    return true;
  }

  V.register((t) => {
    // Setelah pembayaran berhasil
    if (!$('doneModal').classList.contains('hidden')) {
      if (/\b(pesanan baru|order baru|transaksi baru|lanjut|tutup)\b/.test(t)) { V.say('Pesanan baru'); $('doneNew').click(); return true; }
      if (/\b(struk|cetak)\b/.test(t)) { (thermalUrl ? $('doneThermal') : $('doneView')).click(); return true; }
      return false;
    }
    if ($('custModal') && !$('custModal').classList.contains('hidden')) return false;
    if (!$('qrisModal').classList.contains('hidden')) return false;
    if (!$('qrisManualModal').classList.contains('hidden')) return false;

    if (/\b(kosongkan|bersihkan|reset)\b|\b(hapus|batalkan|batal) (semua|keranjang|pesanan)\b/.test(t)) {
      cart = []; $('discount').value = 0; $('paid').value = ''; touch(); V.say('Keranjang dikosongkan'); return true;
    }
    if (/\b(takeaway|take away|bawa pulang|dibungkus)\b/.test(t)) { orderType = 'takeaway'; renderMeta(); V.say('Takeaway'); return true; }
    if (/\b(dine in|dinein|makan (di )?sini|makan (di )?tempat)\b/.test(t)) {
      if (IS_RETAIL) { V.say('Toko ritel tidak memakai dine in'); return true; }
      orderType = 'dine_in'; renderMeta(); V.say('Dine in'); return true;
    }
    const mejaM = t.match(/\bmeja(?: nomor)?\s+(.+)$/);
    if (mejaM) {
      if (IS_RETAIL) { V.say('Toko ritel tidak memakai nomor meja'); return true; }
      const tk = V.tokens(mejaM[1]), nr = V.numbers(tk);
      const val = nr.length && nr[0].start === 0 ? String(nr[0].value) : (tk[0] || '').toUpperCase();
      if (val) { orderType = 'dine_in'; if ($('table')) $('table').value = val; renderMeta(); V.say('Meja ' + val); return true; }
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
          } else if (!$('qrisModal').classList.contains('hidden')) V.say('Kode QRIS tampil. Minta pelanggan untuk scan.');
          else V.say($('err').textContent || 'Pembayaran belum berhasil');
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
    return itemCommandV2(t);
  });
}

renderCats(); refresh();
</script>
@endpush
@endsection
