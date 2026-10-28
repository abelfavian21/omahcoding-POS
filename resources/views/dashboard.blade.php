@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
@php
  $hour = now()->hour;
  $greet = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 19 ? 'Selamat sore' : 'Selamat malam'));
  $first = explode(' ', auth()->user()->name)[0];

  // jam tersibuk (hanya kalau memang ada pesanan)
  $peak = $chart->sortByDesc(fn ($c) => $c['dine'] + $c['take'])->first();
  $peak = ($peak && $peak['count'] > 0) ? $peak : null;
  $topMax = max(1, (int) $topItems->max('sold'));

  // komposisi dine-in vs takeaway (dihitung dari angka yang sudah ada)
  $mixTotal = (float) $dineTotal + (float) $takeTotal;
  $dinePct = $mixTotal > 0 ? round($dineTotal / $mixTotal * 100) : 0;

  $trendPill = fn ($v) => $v >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600';
  $arrow = fn ($v) => $v >= 0 ? 'arrow_upward' : 'arrow_downward';
  $focus = 'focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#B1D501]';
  $label = 'text-xs font-bold text-slate-400 tracking-wide uppercase';
@endphp

{{-- Header --}}
<div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4 mb-6">
  <div class="min-w-0">
    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">{{ $greet }}, {{ $first }} <x-ico v="waving_hand" class="text-lime-600 !align-middle" style="font-size:28px" /></h1>
    <p class="text-sm font-medium text-slate-500 mt-1">Ringkasan penjualan hari ini, dibandingkan dengan kemarin.</p>
  </div>
  <div class="flex flex-wrap items-center gap-2.5">
    <span class="h-11 px-4 inline-flex items-center gap-2 rounded-full bg-white border border-card-border text-xs font-bold text-slate-700 shadow-sm">
      <span class="relative flex w-2 h-2"><span class="absolute inline-flex h-full w-full rounded-full bg-lime-500 opacity-75 animate-ping motion-reduce:animate-none"></span><span class="relative inline-flex w-2 h-2 rounded-full bg-lime-500"></span></span>
      {{ now()->translatedFormat('l, d F Y') }}
    </span>
    <div class="inline-flex h-11 items-center gap-1 rounded-full bg-white border border-card-border p-1 shadow-sm" role="group" aria-label="Tema tampilan">
      @foreach (['light' => ['Mode terang', 'light_mode'], 'dark' => ['Mode gelap', 'dark_mode']] as $mode => [$lbl, $icon])
        <button type="button" data-theme-set="{{ $mode }}" aria-pressed="false" aria-label="{{ $lbl }}" title="{{ $lbl }}"
                class="grid place-items-center w-9 h-9 rounded-full transition text-slate-400 hover:text-slate-700 aria-pressed:bg-[#181829] aria-pressed:text-lime-accent {{ $focus }}">
          <span class="material-symbols-outlined">{{ $icon }}</span>
        </button>
      @endforeach
    </div>
    @if (auth()->user()->isAdmin())
      <a href="{{ route('reports.index') }}" class="btn bg-white border border-card-border text-slate-700 hover:bg-slate-50 shadow-sm {{ $focus }}">
        <span class="material-symbols-outlined">download</span> <span class="hidden xs:inline">Export laporan</span><span class="xs:hidden">Export</span>
      </a>
    @endif
    <a href="{{ route('pos') }}" class="btn-dark text-lime-accent shadow-sm {{ $focus }}">
      <span class="material-symbols-outlined">add_circle</span> Pesanan baru
    </a>
  </div>
</div>

{{-- KPI --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-5 mb-5">
  {{-- Hero gelap --}}
  <div class="card-dark p-5 sm:p-6 flex flex-col justify-between">
    <div class="flex items-start justify-between">
      <div>
        <span class="text-xs font-bold text-slate-400 tracking-wide uppercase">Total pendapatan</span>
        <div class="mt-2 text-[28px] leading-[34px] font-extrabold tracking-[-0.03em] break-all"><span class="text-sm font-bold text-slate-400 align-top mr-1">Rp</span>{{ number_format($revenue, 0, ',', '.') }}</div>
      </div>
      <div class="w-9 h-9 rounded-xl bg-lime-accent text-slate-950 grid place-items-center shrink-0"><span class="material-symbols-outlined fill-1">payments</span></div>
    </div>
    <div class="mt-5 flex flex-wrap items-center gap-2">
      <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-lime-accent/15 text-lime-accent font-bold text-xs"><span class="material-symbols-outlined" style="font-size:14px">{{ $arrow($revenueTrend) }}</span>{{ $revenueTrend >= 0 ? '+' : '' }}{{ $revenueTrend }}%</span>
      <span class="text-xs font-medium text-slate-400">vs kemarin (Rp {{ number_format($revenueY, 0, ',', '.') }})</span>
    </div>
    <span aria-hidden="true" class="material-symbols-outlined absolute -right-3 -bottom-4 text-white/5 pointer-events-none" style="font-size:96px">receipt</span>
  </div>

  <div class="card p-5 sm:p-6 flex flex-col justify-between">
    <div class="flex items-start justify-between">
      <div>
        <span class="{{ $label }}">Total pesanan</span>
        <div class="mt-2 flex items-baseline gap-1.5"><span class="text-[28px] leading-[34px] font-extrabold tracking-[-0.03em] text-slate-900">{{ $orders }}</span><span class="text-[11px] font-bold text-slate-400">PESANAN</span></div>
      </div>
      <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-600 grid place-items-center shrink-0"><span class="material-symbols-outlined">receipt_long</span></div>
    </div>
    <div class="mt-5 flex flex-wrap items-center gap-2">
      <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-bold text-xs {{ $trendPill($ordersTrend) }}"><span class="material-symbols-outlined" style="font-size:14px">{{ $arrow($ordersTrend) }}</span>{{ $ordersTrend >= 0 ? '+' : '' }}{{ $ordersTrend }}%</span>
      <span class="text-xs font-medium text-slate-400">selesai hari ini</span>
    </div>
  </div>

  <div class="card p-5 sm:p-6 flex flex-col justify-between">
    <div class="flex items-start justify-between">
      <div>
        <span class="{{ $label }}">Rata-rata transaksi</span>
        <div class="mt-2 text-[28px] leading-[34px] font-extrabold tracking-[-0.03em] text-slate-900 break-all"><span class="text-sm font-bold text-slate-400 align-top mr-1">Rp</span>{{ number_format($avg, 0, ',', '.') }}</div>
      </div>
      <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-600 grid place-items-center shrink-0"><span class="material-symbols-outlined">sell</span></div>
    </div>
    <div class="mt-5 flex flex-wrap items-center gap-2">
      <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-bold text-xs {{ $trendPill($avgTrend) }}"><span class="material-symbols-outlined" style="font-size:14px">{{ $arrow($avgTrend) }}</span>{{ $avgTrend >= 0 ? '+' : '' }}{{ $avgTrend }}%</span>
      <span class="text-xs font-medium text-slate-400">per pesanan</span>
    </div>
  </div>

  <div class="card p-5 sm:p-6 flex flex-col justify-between">
    <div class="flex items-start justify-between">
      <div>
        <span class="{{ $label }}">Dine-in vs takeaway</span>
        <div class="mt-2 flex items-baseline gap-1.5"><span class="text-[28px] leading-[34px] font-extrabold tracking-[-0.03em] text-slate-900">{{ $dinePct }}<span class="text-xl text-slate-400 font-semibold">%</span></span><span class="text-[11px] font-bold text-slate-400">DINE-IN</span></div>
      </div>
      <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-600 grid place-items-center shrink-0"><span class="material-symbols-outlined">restaurant</span></div>
    </div>
    <div class="mt-5">
      <div class="h-2 rounded-full overflow-hidden flex bg-slate-100"><div class="h-full bg-lime-accent" style="width: {{ $dinePct }}%"></div><div class="h-full hatch" style="width: {{ $mixTotal > 0 ? 100 - $dinePct : 0 }}%"></div></div>
      <div class="mt-2 text-xs font-medium text-slate-400">Takeaway {{ $mixTotal > 0 ? 100 - $dinePct : 0 }}%</div>
    </div>
  </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-12 gap-5">
  {{-- Grafik per jam --}}
  <section class="card p-5 sm:p-6 xl:col-span-8 min-w-0 hc-card">
    <div class="flex flex-wrap items-start justify-between gap-x-6 gap-y-4 mb-4">
      <div class="min-w-0">
        <h2 class="text-base font-extrabold text-slate-900">Pendapatan per jam</h2>
        <p class="text-xs text-slate-400 font-medium">Hari ini, dine-in dan takeaway.@if ($peak) <span class="text-slate-700 font-bold">Tersibuk {{ $peak['label'] }} &middot; {{ $peak['count'] }} pesanan.</span>@endif</p>
        <div class="hc-total text-slate-900">@rupiah($mixTotal)</div>
      </div>
      <dl class="flex gap-3">
        <div class="hc-chip"><dt><span class="hc-dot-leg" style="background:var(--hc-dine)"></span>Dine-in</dt><dd>@rupiah($dineTotal)</dd></div>
        <div class="hc-chip"><dt><span class="hc-dot-leg" style="background:var(--hc-take)"></span>Takeaway</dt><dd>@rupiah($takeTotal)</dd></div>
      </dl>
    </div>

    <div id="hourChart" class="hc" tabindex="0" role="img" data-now="{{ now()->hour }}"
         aria-label="Grafik pendapatan per jam hari ini. Gunakan panah kiri dan kanan untuk melihat tiap jam."
         data-points="{{ json_encode($chart->values()) }}">
      <div class="hc-plot"></div>
      <div class="hc-x"></div>
    </div>

    <style>
      .hc-card { --hc-dine:#9ec400; --hc-dine-glow:rgba(158,196,0,.35); --hc-take:#0ea5e9; --hc-grid:rgba(100,116,139,.20); --hc-axis:#94a3b8; --hc-card:#ffffff; --hc-chip:#f1f5f9; --hc-now-bg:#181829; --hc-now-fg:#ffffff; }
      .dark .hc-card { --hc-dine:#D2F839; --hc-dine-glow:rgba(210,248,57,.40); --hc-take:#7dd3fc; --hc-grid:rgba(148,163,184,.16); --hc-axis:#7c86a2; --hc-card:#181829; --hc-chip:rgba(255,255,255,.05); --hc-now-bg:#D2F839; --hc-now-fg:#10101c; }
      .hc-total { margin-top:.65rem; font-size:1.9rem; line-height:1; font-weight:800; letter-spacing:-.02em; }
      .hc-chip { background:var(--hc-chip); border-radius:.9rem; padding:.5rem .8rem; min-width:7rem; }
      .hc-chip dt { display:flex; align-items:center; gap:.45rem; font-size:.72rem; font-weight:600; color:var(--hc-axis); }
      .hc-chip dd { margin:.15rem 0 0; font-size:.88rem; font-weight:800; }
      .hc-dot-leg { width:.6rem; height:.6rem; border-radius:9999px; display:inline-block; }
      .hc { outline:none; border-radius:1rem; }
      .hc:focus-visible { box-shadow:0 0 0 2px var(--hc-dine); }
      .hc-plot { position:relative; height:236px; touch-action:pan-y; cursor:crosshair; }
      .hc-plot svg { display:block; overflow:visible; }
      .hc-grid { stroke:var(--hc-grid); stroke-width:1; }
      .hc-grid-0 { stroke:var(--hc-axis); stroke-opacity:.45; }
      .hc-yl { fill:var(--hc-axis); font-size:10px; font-weight:700; }
      .hc-line { fill:none; stroke-linecap:round; stroke-linejoin:round; }
      .hc-l-dine { stroke:var(--hc-dine); stroke-width:3; filter:drop-shadow(0 6px 10px var(--hc-dine-glow)); }
      .hc-l-take { stroke:var(--hc-take); stroke-width:2.25; stroke-dasharray:6 5; }
      .hc-guide { stroke:var(--hc-axis); stroke-width:1; stroke-dasharray:3 4; opacity:0; transition:opacity .12s; }
      .hc-dot { opacity:0; stroke:var(--hc-card); stroke-width:3; transition:opacity .12s; }
      .hc-dot-d { fill:var(--hc-dine); }
      .hc-dot-t { fill:var(--hc-take); }
      .hc-pdot { fill:var(--hc-dine); stroke:var(--hc-card); stroke-width:3; }
      .hc-pulse { fill:var(--hc-dine); opacity:.35; transform-box:fill-box; transform-origin:center; animation:hc-pulse 2s ease-out infinite; }
      .hc-peaklab { position:absolute; transform:translateX(-50%); pointer-events:none; background:var(--hc-dine); color:#10101c; font-size:10.5px; font-weight:800; padding:.2rem .55rem; border-radius:9999px; white-space:nowrap; box-shadow:0 6px 16px var(--hc-dine-glow); transition:opacity .12s; }
      .hc-peaklab::after { content:""; position:absolute; left:50%; bottom:-4px; width:8px; height:8px; background:inherit; transform:translateX(-50%) rotate(45deg); border-radius:2px; }
      .hc-tip { position:absolute; top:6px; opacity:0; pointer-events:none; background:#181829; color:#fff; border:1px solid rgba(255,255,255,.08); border-radius:.9rem; padding:.55rem .75rem; min-width:9.5rem; box-shadow:0 12px 28px rgba(0,0,0,.35); transition:opacity .12s; font-size:11px; }
      .hc-tip-h { font-weight:800; font-size:11.5px; margin-bottom:.35rem; }
      .hc-tip-r { display:flex; align-items:center; gap:.4rem; margin-top:.2rem; color:#cbd5e1; font-weight:600; }
      .hc-tip-r i { width:.5rem; height:.5rem; border-radius:9999px; display:inline-block; }
      .hc-tip-r b { margin-left:auto; padding-left:.8rem; color:#fff; font-weight:800; }
      .hc-empty { position:absolute; inset:0; display:grid; place-items:center; pointer-events:none; font-size:12px; font-weight:700; color:var(--hc-axis); }
      .hc-x { display:flex; margin-top:.45rem; padding-left:46px; padding-right:10px; }
      .hc-xl { flex:1; min-width:0; text-align:center; font-size:10px; font-weight:700; color:var(--hc-axis); }
      .hc-xl span { display:inline-block; padding:.12rem .42rem; border-radius:9999px; }
      .hc-xl.fut { opacity:.55; }
      .hc-xl.pk { color:inherit; font-weight:800; }
      .hc-xl.on span { background:var(--hc-now-bg); color:var(--hc-now-fg); }
      .hc-xl.act span { box-shadow:0 0 0 2px var(--hc-dine); }
      .hc-sparse .hc-xl:nth-child(even):not(.on):not(.pk) span { visibility:hidden; }
      .hc-sparse .hc-xl span { padding:.12rem .3rem; }
      @keyframes hc-pulse { from { transform:scale(1); opacity:.4; } to { transform:scale(2.6); opacity:0; } }
      @keyframes hc-draw { from { stroke-dashoffset:1; } to { stroke-dashoffset:0; } }
      @keyframes hc-fade { from { opacity:0; } to { opacity:1; } }
      .hc-anim .hc-l-dine { stroke-dasharray:1; stroke-dashoffset:1; animation:hc-draw 1.1s .1s ease-out forwards; }
      .hc-anim .hc-area, .hc-anim .hc-l-take { animation:hc-fade .9s .35s ease-out backwards; }
      @media (prefers-reduced-motion: reduce) { .hc-pulse { animation:none; opacity:0; } }
    </style>
  </section>

  @push('scripts')
  <script>
  (function () {
    var root = document.getElementById('hourChart');
    if (!root) return;
    var data = [];
    try { data = JSON.parse(root.dataset.points || '[]'); } catch (e) { console.error('Grafik: data tidak terbaca', e); }
    if (!data.length) return;

    var nowH = parseInt(root.dataset.now, 10);
    var rows = data.map(function (r) {
      return { label: String(r.label), h: parseInt(r.label, 10), dine: +r.dine || 0, take: +r.take || 0, count: +r.count || 0 };
    });
    var plot = root.querySelector('.hc-plot'), xr = root.querySelector('.hc-x');
    var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
    var H = 236, PL = 46, PR = 10, PT = 30, PB = 6, n = rows.length;

    var fmtRp = function (v) { return 'Rp ' + Math.round(v).toLocaleString('id-ID'); };
    var fmtShort = function (v) {
      if (v >= 1e6) return (Math.round(v / 1e5) / 10).toString().replace('.', ',') + ' jt';
      if (v >= 1e3) return Math.round(v / 1e3) + ' rb';
      return String(Math.round(v));
    };
    var niceStep = function (v) {
      if (v <= 0) return 25000;
      var p = Math.pow(10, Math.floor(Math.log10(v))), m = v / p;
      return (m <= 1 ? 1 : m <= 2 ? 2 : m <= 2.5 ? 2.5 : m <= 5 ? 5 : 10) * p;
    };
    var maxVal = Math.max.apply(null, [0].concat(rows.map(function (r) { return Math.max(r.dine, r.take); })));
    var step = niceStep(maxVal / 4), top = step * 4;
    var empty = maxVal === 0;

    var peak = -1, pv = 0;
    rows.forEach(function (r, i) { var t = r.dine + r.take; if (r.count > 0 && t >= pv) { pv = t; peak = i; } });

    // Kurva halus yang tidak melewati nilai (tidak "turun" di bawah nol): monotone cubic
    function curve(p) {
      var k = p.length;
      if (k < 2) return '';
      var dx = [], m = [], t = [], i;
      for (i = 0; i < k - 1; i++) { dx[i] = p[i + 1].x - p[i].x; m[i] = (p[i + 1].y - p[i].y) / dx[i]; }
      t[0] = m[0]; t[k - 1] = m[k - 2];
      for (i = 1; i < k - 1; i++) t[i] = m[i - 1] * m[i] <= 0 ? 0 : (m[i - 1] + m[i]) / 2;
      for (i = 0; i < k - 1; i++) {
        if (m[i] === 0) { t[i] = 0; t[i + 1] = 0; continue; }
        var a = t[i] / m[i], b = t[i + 1] / m[i], s = a * a + b * b;
        if (s > 9) { var q = 3 / Math.sqrt(s); t[i] = q * a * m[i]; t[i + 1] = q * b * m[i]; }
      }
      var d = 'M' + p[0].x + ',' + p[0].y;
      for (i = 0; i < k - 1; i++) {
        var h = dx[i] / 3;
        d += ' C' + (p[i].x + h) + ',' + (p[i].y + t[i] * h) + ' ' + (p[i + 1].x - h) + ',' + (p[i + 1].y - t[i + 1] * h) + ' ' + p[i + 1].x + ',' + p[i + 1].y;
      }
      return d;
    }

    // Label jam di bawah grafik
    xr.innerHTML = rows.map(function (r, i) {
      var c = 'hc-xl' + (r.h === nowH ? ' on' : '') + (i === peak ? ' pk' : '') + (r.h > nowH ? ' fut' : '');
      return '<div class="' + c + '" data-i="' + i + '"><span>' + r.label.slice(0, 2) + '</span></div>';
    }).join('');

    var ctx = null, active = null, first = true;

    function draw() {
      var W = Math.max(280, plot.clientWidth), ph = H - PT - PB, base = PT + ph;
      var slot = (W - PL - PR) / n;
      var X = function (i) { return PL + (i + 0.5) * slot; };
      var Y = function (v) { return PT + ph - (v / top) * ph; };
      var pd = rows.map(function (r, i) { return { x: X(i), y: Y(r.dine) }; });
      var pt = rows.map(function (r, i) { return { x: X(i), y: Y(r.take) }; });
      var dl = curve(pd), tl = curve(pt);
      var close = function (line, p) { return line + ' L' + p[p.length - 1].x + ',' + base + ' L' + p[0].x + ',' + base + ' Z'; };

      var grid = '';
      for (var g = 0; g <= 4; g++) {
        var gy = Y(step * g);
        grid += '<line class="hc-grid' + (g === 0 ? ' hc-grid-0' : '') + '" x1="' + PL + '" x2="' + (W - PR) + '" y1="' + gy + '" y2="' + gy + '"' + (g === 0 ? '' : ' stroke-dasharray="2 5"') + '/>' +
                '<text class="hc-yl" x="' + (PL - 10) + '" y="' + (gy + 3.5) + '" text-anchor="end">' + fmtShort(step * g) + '</text>';
      }

      var pk = '';
      if (peak >= 0) {
        var pyv = Math.max(rows[peak].dine, rows[peak].take);
        pk = '<g transform="translate(' + X(peak) + ',' + Y(pyv) + ')"><circle class="hc-pulse" r="6"/><circle class="hc-pdot" r="5.5"/></g>';
      }

      var svg = '<svg width="' + W + '" height="' + H + '" viewBox="0 0 ' + W + ' ' + H + '" aria-hidden="true">' +
        '<defs>' +
        '<linearGradient id="hcGd" x1="0" y1="0" x2="0" y2="1"><stop offset="0" style="stop-color:var(--hc-dine);stop-opacity:.42"/><stop offset="1" style="stop-color:var(--hc-dine);stop-opacity:0"/></linearGradient>' +
        '<linearGradient id="hcGt" x1="0" y1="0" x2="0" y2="1"><stop offset="0" style="stop-color:var(--hc-take);stop-opacity:.22"/><stop offset="1" style="stop-color:var(--hc-take);stop-opacity:0"/></linearGradient>' +
        '</defs>' + grid +
        '<path class="hc-area" fill="url(#hcGt)" d="' + close(tl, pt) + '"/>' +
        '<path class="hc-line hc-l-take" d="' + tl + '"/>' +
        '<path class="hc-area" fill="url(#hcGd)" d="' + close(dl, pd) + '"/>' +
        '<path class="hc-line hc-l-dine" pathLength="1" d="' + dl + '"/>' +
        '<line class="hc-guide" x1="0" x2="0" y1="' + PT + '" y2="' + base + '"/>' +
        pk +
        '<circle class="hc-dot hc-dot-t" r="5"/><circle class="hc-dot hc-dot-d" r="6"/>' +
        '</svg>' +
        (peak >= 0 ? '<div class="hc-peaklab" style="left:' + Math.min(Math.max(X(peak), 44), W - 44) + 'px;top:' + (Y(Math.max(rows[peak].dine, rows[peak].take)) - 32) + 'px">Tersibuk ' + rows[peak].label + '</div>' : '') +
        (empty ? '<div class="hc-empty">Belum ada penjualan hari ini</div>' : '') +
        '<div class="hc-tip"></div>';

      plot.innerHTML = svg;
      xr.classList.toggle('hc-sparse', W < 520);
      ctx = { W: W, slot: slot, X: X, Y: Y };
      if (first && !reduce) root.classList.add('hc-anim');
      first = false;
      if (active !== null) show(active);
    }

    function show(i) {
      if (!ctx || i === null) return;
      active = i;
      var r = rows[i], x = ctx.X(i);
      var guide = plot.querySelector('.hc-guide'), dd = plot.querySelector('.hc-dot-d'), dt = plot.querySelector('.hc-dot-t');
      var tip = plot.querySelector('.hc-tip'), lab = plot.querySelector('.hc-peaklab');
      guide.setAttribute('x1', x); guide.setAttribute('x2', x); guide.style.opacity = 1;
      dd.setAttribute('cx', x); dd.setAttribute('cy', ctx.Y(r.dine)); dd.style.opacity = 1;
      dt.setAttribute('cx', x); dt.setAttribute('cy', ctx.Y(r.take)); dt.style.opacity = 1;
      if (lab) lab.style.opacity = 0;
      tip.innerHTML = '<div class="hc-tip-h">' + r.label + ' &middot; ' + r.count + ' pesanan</div>' +
        '<div class="hc-tip-r"><i style="background:var(--hc-dine)"></i>Dine-in<b>' + fmtRp(r.dine) + '</b></div>' +
        '<div class="hc-tip-r"><i style="background:var(--hc-take)"></i>Takeaway<b>' + fmtRp(r.take) + '</b></div>';
      var w = tip.offsetWidth, left = x + 14;
      if (left + w > ctx.W - 4) left = x - w - 14;
      tip.style.left = Math.max(4, left) + 'px'; tip.style.opacity = 1;
      xr.querySelectorAll('.hc-xl').forEach(function (el, k) { el.classList.toggle('act', k === i); });
    }

    function hide() {
      active = null;
      var q = function (s) { return plot.querySelector(s); };
      ['.hc-guide', '.hc-dot-d', '.hc-dot-t', '.hc-tip'].forEach(function (s) { if (q(s)) q(s).style.opacity = 0; });
      if (q('.hc-peaklab')) q('.hc-peaklab').style.opacity = 1;
      xr.querySelectorAll('.hc-xl').forEach(function (el) { el.classList.remove('act'); });
    }

    var at = function (e) {
      var rect = plot.getBoundingClientRect();
      return Math.min(n - 1, Math.max(0, Math.floor((e.clientX - rect.left - PL) / ctx.slot)));
    };
    plot.addEventListener('pointermove', function (e) { if (ctx) show(at(e)); });
    plot.addEventListener('pointerdown', function (e) { if (ctx) show(at(e)); });
    plot.addEventListener('pointerleave', hide);
    xr.addEventListener('pointerover', function (e) { var c = e.target.closest('.hc-xl'); if (c) show(+c.dataset.i); });
    xr.addEventListener('pointerleave', hide);
    root.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
        e.preventDefault();
        var i = active === null ? (peak >= 0 ? peak : 0) : active + (e.key === 'ArrowRight' ? 1 : -1);
        show(Math.min(n - 1, Math.max(0, i)));
      } else if (e.key === 'Escape') hide();
    });
    root.addEventListener('blur', hide);

    draw();
    if (window.ResizeObserver) new ResizeObserver(function () { draw(); }).observe(plot);
    else window.addEventListener('resize', draw);
  })();
  </script>
  @endpush
  {{-- Menu terlaris --}}
  <section class="card p-5 sm:p-6 xl:col-span-4 min-w-0">
    <div class="flex justify-between items-baseline mb-5">
      <h2 class="text-base font-extrabold text-slate-900">Menu terlaris</h2>
      <span class="text-xs font-semibold text-slate-400">7 hari terakhir</span>
    </div>
    <ul class="space-y-4">
      @forelse ($topItems as $i)
        <li class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-2xl bg-slate-100 grid place-items-center text-2xl overflow-hidden shrink-0">
            @if ($i->product?->image_url)<img src="{{ $i->product->image_url }}" class="w-full h-full object-cover" alt="">@else<x-ico :v="$i->product?->emoji" style="font-size:22px" />@endif
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-baseline justify-between gap-2">
              <span class="font-bold text-sm truncate">{{ $i->product_name }}</span>
              <span class="text-sm font-extrabold shrink-0">@rupiah($i->revenue)</span>
            </div>
            <div class="mt-1.5 h-1.5 rounded-full bg-slate-100 overflow-hidden"><div class="h-full rounded-full bg-lime-accent" style="width: {{ round($i->sold / $topMax * 100) }}%"></div></div>
            <div class="mt-1 text-xs text-slate-400 font-medium">{{ $i->sold }} terjual</div>
          </div>
        </li>
      @empty
        <li class="text-sm text-slate-500">Belum ada data penjualan.</li>
      @endforelse
    </ul>
  </section>

  {{-- Pesanan terbaru --}}
  <section class="card xl:col-span-8 min-w-0 overflow-hidden">
    <div class="flex justify-between items-center px-5 sm:px-6 pt-5 sm:pt-6 pb-3">
      <div class="flex items-center gap-2"><h2 class="text-base font-extrabold text-slate-900">Pesanan terbaru</h2><span class="w-2 h-2 rounded-full bg-lime-500 animate-pulse motion-reduce:animate-none"></span></div>
      @if (auth()->user()->isAdmin())
        <a href="{{ route('reports.index') }}" class="text-sm font-bold text-brand-600 hover:underline rounded {{ $focus }}">Lihat semua</a>
      @endif
    </div>

    {{-- tabel (tablet+) --}}
    <div class="hidden md:block overflow-x-auto px-2 pb-3">
      <table class="w-full">
        <thead><tr class="border-b border-slate-100"><th class="th">Order</th><th class="th">Pelanggan</th><th class="th">Item</th><th class="th">Bayar</th><th class="th text-right">Total</th></tr></thead>
        <tbody class="divide-y divide-slate-50">
          @forelse ($recent as $o)
            <tr class="hover:bg-slate-50/70 transition-colors">
              <td class="td">
                <div class="flex items-center gap-2.5">
                  <span class="w-9 h-9 rounded-xl bg-[#181829] text-lime-accent grid place-items-center shrink-0"><span class="material-symbols-outlined" style="font-size:18px">receipt</span></span>
                  <div><div class="font-bold text-slate-900">{{ $o->invoice_no }}</div><div class="text-[11px] text-slate-400">{{ $o->paid_at?->format('H:i') }}</div></div>
                </div>
              </td>
              <td class="td"><div class="font-bold">{{ $o->customer->name ?? 'Walk-in' }}</div>
                <div class="text-[11px] text-slate-400">{{ $o->order_type === 'dine_in' ? 'Dine-in' . ($o->table_no ? ' · Meja ' . $o->table_no : '') : 'Takeaway' }}</div></td>
              <td class="td text-slate-500 max-w-[200px] truncate">{{ $o->items->map(fn ($i) => $i->qty . '× ' . $i->product_name)->implode(', ') }}</td>
              <td class="td"><span class="pill pill-slate">{{ \App\Models\Order::PAYMENTS[$o->payment_method] ?? $o->payment_method }}</span></td>
              <td class="td text-right font-extrabold text-slate-900">@rupiah($o->total)</td>
            </tr>
          @empty
            <tr><td colspan="5" class="px-6 py-12 text-center">
              <span class="mx-auto mb-3 w-12 h-12 rounded-2xl bg-slate-100 text-slate-500 grid place-items-center"><span class="material-symbols-outlined">receipt_long</span></span>
              <p class="text-sm font-semibold">Belum ada pesanan.</p>
              <a href="{{ route('pos') }}" class="inline-block mt-1 text-sm font-bold text-brand-600 hover:underline">Buat pesanan pertama</a>
            </td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- daftar kartu (mobile) --}}
    <ul class="md:hidden divide-y divide-slate-50 px-5 pb-3">
      @forelse ($recent as $o)
        <li class="py-3.5 flex items-start gap-3">
          <span class="w-10 h-10 rounded-xl bg-[#181829] text-lime-accent grid place-items-center shrink-0"><span class="material-symbols-outlined" style="font-size:18px">receipt</span></span>
          <div class="min-w-0 flex-1">
            <div class="flex justify-between gap-2"><span class="font-bold text-sm truncate">{{ $o->customer->name ?? 'Walk-in' }}</span><span class="font-extrabold text-sm shrink-0">@rupiah($o->total)</span></div>
            <div class="text-[11px] text-slate-400">{{ $o->invoice_no }} · {{ $o->paid_at?->format('H:i') }} · {{ $o->order_type === 'dine_in' ? 'Dine-in' . ($o->table_no ? ' T' . $o->table_no : '') : 'Takeaway' }}</div>
            <div class="text-xs text-slate-500 truncate mt-0.5">{{ $o->items->map(fn ($i) => $i->qty . '× ' . $i->product_name)->implode(', ') }}</div>
            <span class="pill pill-slate mt-1.5">{{ \App\Models\Order::PAYMENTS[$o->payment_method] ?? $o->payment_method }}</span>
          </div>
        </li>
      @empty
        <li class="py-10 text-center text-sm text-slate-500">Belum ada pesanan. <a href="{{ route('pos') }}" class="font-bold text-brand-600">Buat pesanan pertama</a></li>
      @endforelse
    </ul>
  </section>

  {{-- Kolom kanan bawah --}}
  <div class="xl:col-span-4 space-y-5 min-w-0">
    <section class="card p-5 sm:p-6">
      <div class="flex justify-between items-baseline mb-4">
        <h2 class="text-base font-extrabold text-slate-900">Stok menipis</h2>
        <a href="{{ route('products.index', ['stock' => 'low']) }}" class="text-sm font-bold text-brand-600 hover:underline rounded {{ $focus }}">Lihat semua</a>
      </div>
      <ul class="space-y-1">
        @forelse ($lowStock as $p)
          <li class="flex items-center justify-between gap-3 p-3 -mx-3 rounded-2xl hover:bg-slate-50 transition-colors">
            <div class="min-w-0">
              <div class="font-semibold text-sm truncate"><x-ico :v="$p->emoji" class="text-slate-400 mr-1" style="font-size:18px" />{{ $p->name }}</div>
              <div class="text-xs text-slate-400">Minimum {{ $p->min_stock }}</div>
            </div>
            <span class="pill {{ $p->stock <= 0 ? 'pill-red' : 'pill-amber' }}">{{ $p->stock <= 0 ? 'Habis' : 'Sisa ' . $p->stock }}</span>
          </li>
        @empty
          <li class="flex items-center gap-2 text-sm text-slate-500"><span class="material-symbols-outlined text-emerald-600">check_circle</span> Semua stok aman.</li>
        @endforelse
      </ul>
    </section>

    <section class="card p-5 sm:p-6">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-base font-extrabold text-slate-900">Staff bertugas</h2>
        <span class="pill pill-blue">● Aktif</span>
      </div>
      <ul class="space-y-3.5">
        @foreach ($staff as $s)
          <li class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-[#181829] text-lime-accent grid place-items-center text-sm font-bold shrink-0 ring-2 ring-lime-accent/40">{{ $s->initials }}</div>
            <div class="flex-1 min-w-0"><div class="font-bold text-sm truncate">{{ $s->name }}</div><div class="text-xs text-slate-400 capitalize">{{ $s->role }}</div></div>
            <span class="text-xs font-semibold text-slate-400">{{ $s->shift }}</span>
          </li>
        @endforeach
      </ul>
    </section>
  </div>
</div>
@endsection

@push('scripts')
<script>
  (function () {
    if (!window.setTheme) {
      window.setTheme = function (mode) {
        document.documentElement.classList.toggle('dark', mode === 'dark');
        try { localStorage.setItem('theme', mode); } catch (e) {}
      };
    }
    var btns = document.querySelectorAll('[data-theme-set]');
    function sync() {
      var dark = document.documentElement.classList.contains('dark');
      btns.forEach(function (b) { b.setAttribute('aria-pressed', String((b.dataset.themeSet === 'dark') === dark)); });
    }
    btns.forEach(function (b) { b.addEventListener('click', function () { window.setTheme(b.dataset.themeSet); sync(); }); });
    new MutationObserver(sync).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    sync();
  })();
</script>
@endpush
