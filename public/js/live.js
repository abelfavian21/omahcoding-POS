/* Pembaruan halaman tanpa muat ulang (mirip Livewire, tanpa pustaka).
 * Tandai bagian halaman dengan data-live-region="nama". Di dalam bagian itu:
 *   - tautan ke halaman yang sama (mis. ?page=2, filter) dan form GET jadi "live": hanya bagian itu yang diganti
 *   - <select onchange="this.form.submit()"> ikut live
 *   - kolom pencarian (name="q" / type="search") mengirim otomatis setelah berhenti mengetik
 * Tautan/form lain (halaman berbeda, POST, target=_blank, download, data-live="off") tetap bekerja normal.
 * Kalau ada masalah (sesi habis, bagian tidak ditemukan, error jaringan) otomatis pindah halaman biasa. */
(function () {
  'use strict';
  if (window.__liveInit) return;
  window.__liveInit = true;

  var ctrl = null, seq = 0, bar = null, timer = null;

  function regionOf(el) { return el && el.closest ? el.closest('[data-live-region]') : null; }
  function samePage(u) { return u.origin === location.origin && u.pathname === location.pathname; }
  function isGetForm(f) { return f && f.tagName === 'FORM' && (f.method || 'get').toLowerCase() === 'get' && f.getAttribute('data-live') !== 'off'; }

  function busy(regions, on) {
    regions.forEach(function (r) { r.classList.toggle('live-busy', on); });
    if (!bar) { bar = document.createElement('div'); bar.className = 'live-bar'; document.body.appendChild(bar); }
    bar.classList.toggle('on', on);
  }

  function snapshot(region) {
    var a = document.activeElement;
    if (!a || !region.contains(a) || !a.name || !/^(INPUT|TEXTAREA|SELECT)$/.test(a.tagName)) return null;
    var s = { name: a.name, value: a.value, start: null, end: null };
    try { s.start = a.selectionStart; s.end = a.selectionEnd; } catch (e) {}
    return s;
  }
  function restore(region, s) {
    if (!s) return;
    var el = region.querySelector('[name="' + s.name.replace(/"/g, '\\"') + '"]');
    if (!el) return;
    if (el.tagName !== 'SELECT' && el.type !== 'checkbox' && el.type !== 'radio') el.value = s.value;   // pertahankan ketikan terbaru
    el.focus();
    try { if (s.start != null) el.setSelectionRange(s.start, s.end); } catch (e) {}
  }

  function go(url, opts) {
    var targets = opts.all ? [].slice.call(document.querySelectorAll('[data-live-region]')) : [opts.region];
    if (!targets.length) { location.href = url; return; }
    if (ctrl) ctrl.abort();
    ctrl = new AbortController();
    var my = ++seq, want = new URL(url, location.href).pathname;
    busy(targets, true);
    fetch(url, { credentials: 'same-origin', signal: ctrl.signal, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } })
      .then(function (res) {
        if (!res.ok || new URL(res.url).pathname !== want) throw new Error('full');   // error / dialihkan (mis. sesi habis)
        return res.text();
      })
      .then(function (html) {
        if (my !== seq) return;
        var doc = new DOMParser().parseFromString(html, 'text/html'), pairs = [];
        targets.forEach(function (reg) {
          var fresh = doc.querySelector('[data-live-region="' + reg.getAttribute('data-live-region') + '"]');
          if (!fresh) throw new Error('full');
          pairs.push([reg, fresh]);
        });
        pairs.forEach(function (p) {
          var snap = snapshot(p[0]);
          p[0].innerHTML = p[1].innerHTML;
          restore(p[0], snap);
          document.dispatchEvent(new CustomEvent('live:swapped', { detail: { region: p[0] } }));
        });
        if (doc.title) document.title = doc.title;
        if (opts.push) history.pushState({ live: 1 }, '', url);
      })
      .catch(function (e) { if (e && e.name === 'AbortError') return; location.href = url; })
      .then(function () { if (my === seq) busy(targets, false); });
  }

  function submit(form, submitter, region) {
    var u = new URL(form.getAttribute('action') || location.href, location.href), fd, qs = new URLSearchParams();
    try { fd = new FormData(form, submitter || undefined); } catch (e) { fd = new FormData(form); }
    fd.forEach(function (v, k) { if (typeof v === 'string') qs.append(k, v); });
    u.search = qs.toString();
    go(u.href, { region: region, push: true });
  }

  document.addEventListener('click', function (e) {
    if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
    var a = e.target.closest && e.target.closest('a[href]');
    var region = regionOf(a);
    if (!a || !region || a.getAttribute('data-live') === 'off' || a.target || a.hasAttribute('download')) return;
    var h = a.getAttribute('href');
    if (!h || h.charAt(0) === '#' || /^(javascript|mailto|tel):/i.test(h)) return;
    var u = new URL(a.href, location.href);
    if (!samePage(u)) return;
    e.preventDefault();
    go(u.href, { region: region, push: true });
  });

  document.addEventListener('submit', function (e) {
    var f = e.target, region = regionOf(f);
    if (!region || !isGetForm(f)) return;
    if (!samePage(new URL(f.getAttribute('action') || location.href, location.href))) return;
    e.preventDefault();
    submit(f, e.submitter, region);
  });

  // <select onchange="this.form.submit()">: form.submit() tidak memicu event submit, jadi dicegat lebih awal (capture)
  document.addEventListener('change', function (e) {
    var t = e.target, f = t && t.form, region = regionOf(f);
    if (!region || !isGetForm(f) || (t.getAttribute('onchange') || '').indexOf('submit') < 0) return;
    if (!samePage(new URL(f.getAttribute('action') || location.href, location.href))) return;
    e.stopImmediatePropagation();
    submit(f, null, region);
  }, true);

  // pencarian: kirim otomatis setelah berhenti mengetik
  document.addEventListener('input', function (e) {
    var t = e.target, f = t && t.form, region = regionOf(f);
    if (!region || !isGetForm(f) || !(t.name === 'q' || t.type === 'search')) return;
    if (!samePage(new URL(f.getAttribute('action') || location.href, location.href))) return;
    clearTimeout(timer);
    timer = setTimeout(function () { submit(f, null, region); }, 400);
  });

  window.addEventListener('popstate', function () {
    if (document.querySelector('[data-live-region]')) go(location.href, { all: true, push: false });
  });
})();
