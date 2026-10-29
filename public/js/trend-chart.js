/* Grafik tren tanpa pustaka (SVG). Dipakai:
 *   data-kind="hours"  -> dashboard, pendapatan per jam (dine-in vs takeaway)
 *   data-kind="daily"  -> laporan, pendapatan harian + rata-rata 7 hari
 * Data dibaca dari <script type="application/json" id="..."> yang ditunjuk data-src.
 * Tidak memakai gaya inline di HTML (aman untuk CSP ketat); posisi diatur lewat CSSOM. */
(function () {
  'use strict';

  var uid = 0;
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function fmtRp(v) { return 'Rp ' + Math.round(v).toLocaleString('id-ID'); }
  function fmtShort(v) {
    if (v >= 1e9) return (Math.round(v / 1e8) / 10).toString().replace('.', ',') + ' M';
    if (v >= 1e6) return (Math.round(v / 1e5) / 10).toString().replace('.', ',') + ' jt';
    if (v >= 1e3) return Math.round(v / 1e3) + ' rb';
    return String(Math.round(v));
  }
  function niceStep(v) {
    if (v <= 0) return 25000;
    var p = Math.pow(10, Math.floor(Math.log10(v))), m = v / p;
    return (m <= 1 ? 1 : m <= 2 ? 2 : m <= 2.5 ? 2.5 : m <= 5 ? 5 : 10) * p;
  }

  // Kurva halus yang tidak melewati nilai (tidak turun di bawah nol): monotone cubic
  function curve(p) {
    var k = p.length, i;
    if (k === 0) return '';
    if (k === 1) return 'M' + p[0].x + ',' + p[0].y;
    var dx = [], m = [], t = [];
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

  function esc(s) { return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }

  /* cfg: { rows:[{x,title,lines:[[name,value,key]],fut}], series:[{k,v,area,dashed}], now, peak, peakText, emptyText, labelW } */
  function mount(root, cfg) {
    var plot = root.querySelector('.hc-plot'), xr = root.querySelector('.hc-x');
    if (!plot || !xr) return;
    var id = ++uid, rows = cfg.rows, series = cfg.series, n = rows.length;
    var H = 236, PL = 46, PR = 10, PT = 30, PB = 6;
    var maxVal = 0;
    series.forEach(function (s) { s.v.forEach(function (x) { if (x > maxVal) maxVal = x; }); });
    var step = niceStep(maxVal / 4), top = step * 4, empty = maxVal === 0;
    var peak = cfg.peak == null ? -1 : cfg.peak;
    var ctx = null, active = -1, first = true;

    function labels(W) {
      var maxL = Math.max(2, Math.floor((W - PL - PR) / (cfg.labelW || 30)));
      var every = Math.max(1, Math.ceil(n / maxL));
      var specials = [];
      rows.forEach(function (r, i) { if (i === cfg.now || i === peak) specials.push(i); });
      xr.innerHTML = rows.map(function (r, i) {
        var special = specials.indexOf(i) >= 0;
        var show = special || (i % every === 0 && !specials.some(function (s) { return Math.abs(s - i) < every; }));
        var c = 'hc-xl' + (i === cfg.now ? ' on' : '') + (i === peak ? ' pk' : '') + (r.fut ? ' fut' : '') + (show ? '' : ' skip') + (i === active ? ' act' : '');
        return '<div class="' + c + '" data-i="' + i + '"><span>' + esc(r.x) + '</span></div>';
      }).join('');
    }

    function draw() {
      var W = Math.max(280, plot.clientWidth), ph = H - PT - PB, base = PT + ph, slot = (W - PL - PR) / n;
      var X = function (i) { return PL + (i + 0.5) * slot; };
      var Y = function (v) { return PT + ph - (v / top) * ph; };
      var o = [], g, si;
      o.push('<svg width="' + W + '" height="' + H + '" viewBox="0 0 ' + W + ' ' + H + '" aria-hidden="true"><defs>');
      series.forEach(function (s) {
        o.push('<linearGradient id="hcG' + id + s.k + '" x1="0" y1="0" x2="0" y2="1"><stop class="hc-s-' + s.k + '" offset="0" stop-opacity="' + (s.k === 'd' ? '.42' : '.22') + '"/><stop class="hc-s-' + s.k + '" offset="1" stop-opacity="0"/></linearGradient>');
      });
      o.push('</defs>');
      for (g = 0; g <= 4; g++) {
        var gy = Y(step * g);
        o.push('<line class="hc-grid' + (g === 0 ? ' hc-grid-0' : '') + '" x1="' + PL + '" x2="' + (W - PR) + '" y1="' + gy + '" y2="' + gy + '"/>' +
               '<text class="hc-yl" x="' + (PL - 10) + '" y="' + (gy + 3.5) + '" text-anchor="end">' + fmtShort(step * g) + '</text>');
      }
      for (si = series.length - 1; si >= 0; si--) {
        var s = series[si], pts = s.v.map(function (v, i) { return { x: X(i), y: Y(v) }; }), ln = curve(pts);
        if (s.area && n > 1) o.push('<path class="hc-area" fill="url(#hcG' + id + s.k + ')" d="' + ln + ' L' + pts[n - 1].x + ',' + base + ' L' + pts[0].x + ',' + base + ' Z"/>');
        if (n > 1) o.push('<path class="hc-line hc-l-' + s.k + (s.dashed ? ' hc-dashed' : '') + '"' + (s.k === 'd' ? ' pathLength="1"' : '') + ' d="' + ln + '"/>');
        else o.push('<circle class="hc-pdot" cx="' + pts[0].x + '" cy="' + pts[0].y + '" r="5"/>');
      }
      o.push('<line class="hc-guide" x1="0" x2="0" y1="' + PT + '" y2="' + base + '"/>');
      if (peak >= 0) {
        o.push('<g transform="translate(' + X(peak) + ',' + Y(series[0].v[peak]) + ')"><circle class="hc-pulse" r="6"/><circle class="hc-pdot" r="5.5"/></g>');
      }
      series.forEach(function (s) { o.push('<circle class="hc-dot hc-dot-' + s.k + '" r="' + (s.k === 'd' ? 6 : 5) + '"/>'); });
      o.push('</svg>');
      plot.innerHTML = o.join('');

      if (peak >= 0 && cfg.peakText) {
        var lab = document.createElement('div');
        lab.className = 'hc-peaklab'; lab.textContent = cfg.peakText;
        lab.style.left = Math.min(Math.max(X(peak), 48), W - 48) + 'px';
        lab.style.top = (Y(series[0].v[peak]) - 32) + 'px';
        plot.appendChild(lab);
      }
      if (empty) {
        var em = document.createElement('div');
        em.className = 'hc-empty'; em.textContent = cfg.emptyText || 'Belum ada data';
        plot.appendChild(em);
      }
      var tip = document.createElement('div'); tip.className = 'hc-tip'; plot.appendChild(tip);

      ctx = { W: W, slot: slot, X: X, Y: Y };
      labels(W);
      if (first && !reduce) root.classList.add('hc-anim');
      first = false;
      if (active >= 0) show(active);
    }

    function show(i) {
      if (!ctx || i < 0) return;
      active = i;
      var r = rows[i], x = ctx.X(i);
      var guide = plot.querySelector('.hc-guide'), tip = plot.querySelector('.hc-tip'), lab = plot.querySelector('.hc-peaklab');
      guide.setAttribute('x1', x); guide.setAttribute('x2', x); guide.style.opacity = 1;
      series.forEach(function (s) {
        var d = plot.querySelector('.hc-dot-' + s.k);
        d.setAttribute('cx', x); d.setAttribute('cy', ctx.Y(s.v[i])); d.style.opacity = 1;
      });
      if (lab) lab.style.opacity = 0;
      tip.innerHTML = '<div class="hc-tip-h">' + esc(r.title) + '</div>' + r.lines.map(function (l) {
        return '<div class="hc-tip-r"><i class="' + l[2] + '"></i>' + esc(l[0]) + '<b>' + esc(l[1]) + '</b></div>';
      }).join('');
      var w = tip.offsetWidth, left = x + 14;
      if (left + w > ctx.W - 4) left = x - w - 14;
      tip.style.left = Math.max(4, left) + 'px'; tip.style.opacity = 1;
      xr.querySelectorAll('.hc-xl').forEach(function (e, k) { e.classList.toggle('act', k === i); });
    }

    function hide() {
      active = -1;
      var q = function (s) { return plot.querySelector(s); };
      ['.hc-guide', '.hc-tip'].concat(series.map(function (s) { return '.hc-dot-' + s.k; })).forEach(function (s) { if (q(s)) q(s).style.opacity = 0; });
      if (q('.hc-peaklab')) q('.hc-peaklab').style.opacity = 1;
      xr.querySelectorAll('.hc-xl').forEach(function (e) { e.classList.remove('act'); });
    }

    var at = function (e) {
      var rect = plot.getBoundingClientRect();
      return Math.min(n - 1, Math.max(0, Math.floor((e.clientX - rect.left - PL) / ctx.slot)));
    };
    plot.addEventListener('pointermove', function (e) { if (ctx) show(at(e)); });
    plot.addEventListener('pointerdown', function (e) { if (ctx) show(at(e)); });
    plot.addEventListener('pointerleave', hide);
    xr.addEventListener('pointerover', function (e) { var c = e.target.closest && e.target.closest('.hc-xl'); if (c) show(+c.dataset.i); });
    xr.addEventListener('pointerleave', hide);
    root.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
        e.preventDefault();
        var i = active < 0 ? (peak >= 0 ? peak : 0) : active + (e.key === 'ArrowRight' ? 1 : -1);
        show(Math.min(n - 1, Math.max(0, i)));
      } else if (e.key === 'Escape') hide();
    });
    root.addEventListener('blur', hide);

    draw();
    if (window.ResizeObserver) new ResizeObserver(function () { draw(); }).observe(plot);
    else window.addEventListener('resize', draw);
  }

  /* ---- adaptor: dashboard per jam ---- */
  function hoursCfg(p) {
    var nowIdx = -1, peak = -1, pv = 0;
    var rows = p.rows.map(function (r, i) {
      var h = parseInt(r.label, 10);
      if (h === p.now) nowIdx = i;
      var t = r.dine + r.take;
      if (r.count > 0 && t >= pv) { pv = t; peak = i; }
      return { x: r.label.slice(0, 2), fut: h > p.now,
        title: r.label + ' Â· ' + r.count + ' pesanan',
        lines: [['Dine-in', fmtRp(r.dine), 'd'], ['Takeaway', fmtRp(r.take), 't']] };
    });
    return {
      rows: rows, now: nowIdx, peak: peak, labelW: 30,
      peakText: peak >= 0 ? 'Tersibuk ' + p.rows[peak].label : '',
      emptyText: 'Belum ada penjualan pada rentang jam ini',
      series: [
        { k: 'd', v: p.rows.map(function (r) { return +r.dine || 0; }), area: true },
        { k: 't', v: p.rows.map(function (r) { return +r.take || 0; }), area: true, dashed: true }
      ]
    };
  }

  /* ---- adaptor: laporan harian ---- */
  function dailyCfg(p) {
    var n = p.rows.length, long = n > 31;
    var tot = p.rows.map(function (r) { return +r.total || 0; });
    var ma = tot.map(function (_, i) {
      var a = Math.max(0, i - 6), s = 0, k;
      for (k = a; k <= i; k++) s += tot[k];
      return s / (i - a + 1);
    });
    var nowIdx = -1, peak = -1, pv = 0;
    var rows = p.rows.map(function (r, i) {
      if (r.date === p.today) nowIdx = i;
      if (tot[i] > 0 && tot[i] >= pv) { pv = tot[i]; peak = i; }
      return { x: long ? r.sm : r.j, fut: false,
        title: r.full + ' Â· ' + r.orders + ' transaksi',
        lines: [['Pendapatan', fmtRp(tot[i]), 'd'], ['Rata-rata 7 hari', fmtRp(ma[i]), 't']] };
    });
    return {
      rows: rows, now: nowIdx, peak: peak, labelW: long ? 46 : 26,
      peakText: peak >= 0 ? 'Tertinggi ' + p.rows[peak].sm : '',
      emptyText: 'Belum ada penjualan pada periode ini',
      series: [
        { k: 'd', v: tot, area: true },
        { k: 't', v: ma, area: false, dashed: true }
      ]
    };
  }

  // Pasang grafik pada semua .hc[data-kind] di dalam scope (belum terpasang saja). Dipanggil lagi setelah live-swap.
  function initIn(scope) {
    (scope || document).querySelectorAll('.hc[data-kind]').forEach(function (root) {
      if (root.getAttribute('data-hc-ready')) return;
      var src = document.getElementById(root.getAttribute('data-src'));
      var p = null;
      try { p = JSON.parse(src ? src.textContent : 'null'); } catch (e) { console.error('Grafik: data tidak terbaca', e); }
      if (!p || !p.rows || !p.rows.length) return;
      root.setAttribute('data-hc-ready', '1');
      var kind = root.getAttribute('data-kind');
      mount(root, kind === 'daily' ? dailyCfg(p) : hoursCfg(p));
    });
  }

  function init() {
    initIn(document);
    // tutup popover rentang jam saat klik di luar (dipasang sekali)
    document.addEventListener('click', function (e) {
      document.querySelectorAll('.hc-custom[open]').forEach(function (d) { if (!d.contains(e.target)) d.removeAttribute('open'); });
    });
    // halaman memperbarui sebagian tanpa muat ulang (live.js): pasang ulang grafik di bagian yang diganti
    document.addEventListener('live:swapped', function (e) { initIn(e.detail && e.detail.region); });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
