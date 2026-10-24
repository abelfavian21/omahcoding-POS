(() => {
  const $ = id => document.getElementById(id);
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const rp = n => 'Rp ' + Math.abs(n).toLocaleString('id-ID');

  /* ---------- Entrances ---------- */
  const rv = [...document.querySelectorAll('.rv')];
  if (!('IntersectionObserver' in window) || reduce) {
    rv.forEach(e => e.classList.add('in'));
  } else {
    const io = new IntersectionObserver(es => es.forEach(e => {
      if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); }
    }), { threshold: .12, rootMargin: '0px 0px -4% 0px' });
    rv.forEach(e => io.observe(e));
    // safety net: never leave content hidden
    setTimeout(() => rv.forEach(e => e.classList.add('in')), 2500);
  }

  /* ---------- Order demo: tap menu, pick money, see change ---------- */
  const demo = $('demo');
  if (demo) {
    const qty = {}, prices = {};
    let cash = 0;
    const lines = $('o-lines'), tot = $('o-total'), chg = $('o-change');
    const paint = () => {
      const names = Object.keys(qty).filter(n => qty[n] > 0);
      lines.innerHTML = '';
      if (!names.length) { const li = document.createElement('li'); li.className = 'empty'; li.textContent = 'Belum ada pesanan.'; lines.appendChild(li); }
      names.forEach(n => {
        const li = document.createElement('li');
        const a = document.createElement('span'), b = document.createElement('span');
        a.textContent = qty[n] + ' × ' + n; b.textContent = rp(qty[n] * prices[n]);
        li.append(a, b); lines.appendChild(li);
      });
      const total = names.reduce((s, n) => s + qty[n] * prices[n], 0);
      tot.textContent = rp(total);
      chg.className = 'chg';
      if (!total) chg.textContent = 'Pilih menu dulu';
      else if (!cash) chg.textContent = 'Pilih uang diterima';
      else if (cash < total) { chg.classList.add('low'); chg.textContent = 'Uang kurang ' + rp(total - cash); }
      else { chg.classList.add('ok'); chg.textContent = 'Kembalian ' + rp(cash - total); }
      demo.querySelectorAll('[data-cash]').forEach(b => b.classList.toggle('on', +b.dataset.cash === cash));
    };
    demo.querySelectorAll('.menu button').forEach(b => b.addEventListener('click', () => {
      prices[b.dataset.name] = +b.dataset.price; qty[b.dataset.name] = (qty[b.dataset.name] || 0) + 1; paint();
    }));
    demo.querySelectorAll('[data-cash]').forEach(b => b.addEventListener('click', () => { cash = +b.dataset.cash; paint(); }));
    $('o-clear').addEventListener('click', () => { Object.keys(qty).forEach(n => delete qty[n]); cash = 0; paint(); });
    paint();
  }

  /* ---------- Auth ---------- */
  const auth = $('auth-section');
  // user sudah login: form registrasi tidak dirender (hanya kartu "Kamu sedang masuk")
  if (!auth || !auth.dataset.initialTab) { window.switchAuthTab = () => {}; return; }

  window.switchAuthTab = tab => {
    const login = tab === 'login';
    $('view-login').classList.toggle('hidden', !login);
    $('view-register').classList.toggle('hidden', login);
    $('tab-login-btn').setAttribute('aria-selected', login);
    $('tab-register-btn').setAttribute('aria-selected', !login);
  };
  window.switchLoginMode = mode => {
    const pin = mode === 'pin';
    $('form-email').classList.toggle('hidden', pin);
    $('form-pin').classList.toggle('hidden', !pin);
    $('mode-email').setAttribute('aria-selected', !pin);
    $('mode-pin').setAttribute('aria-selected', pin);
  };

  const badge = (i, state) => {
    const b = $('step-badge-' + i);
    b.className = state === 'done' ? 'done' : state === 'active' ? 'act' : '';
    $('step-num-' + i).textContent = i;
  };
  window.goToStep = step => {
    ['1', '2', '3'].forEach(n => $('step-' + n + '-content').classList.add('hidden'));
    $('register-form').classList.remove('hidden');
    $('step-success').classList.add('hidden');
    if (step === 'success') {
      $('register-form').classList.add('hidden');
      $('step-success').classList.remove('hidden');
      [1, 2, 3].forEach(i => badge(i, 'done'));
      return;
    }
    if (step === 3) fillReview();
    $('step-' + step + '-content').classList.remove('hidden');
    [1, 2, 3].forEach(i => badge(i, i < step ? 'done' : i === step ? 'active' : 'idle'));
  };
  const fail = (id, msg) => { const e = $(id); e.textContent = msg; e.classList.remove('hidden'); };
  window.nextStep = from => {
    if (from === 1) {
      for (const id of ['reg-owner-name', 'reg-business-name', 'reg-email', 'reg-phone', 'reg-pass']) {
        const el = $(id);
        if (!el.value.trim() || !el.checkValidity()) { el.reportValidity(); el.focus(); return; }
      }
      goToStep(2);
    } else if (from === 2) {
      $('step2-error').classList.add('hidden');
      if (!$('reg-category').value) return fail('step2-error', 'Pilih salah satu kategori usaha.');
      if (!$('reg-location').value.trim()) { $('reg-location').focus(); return fail('step2-error', 'Isi kota / lokasi operasional.'); }
      goToStep(3);
    }
  };

  const cards = [...document.querySelectorAll('.cat-card')];
  const paintCards = () => {
    const cur = $('reg-category').value;
    cards.forEach(c => {
      const sel = c.dataset.slug === cur && c.dataset.active === '1';
      c.classList.toggle('sel', sel);
      c.setAttribute('aria-checked', sel);
    });
  };
  const pick = c => { if (c.dataset.active !== '1') return; $('reg-category').value = c.dataset.slug; paintCards(); };
  cards.forEach(c => {
    c.addEventListener('click', () => pick(c));
    c.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); pick(c); } });
  });

  function fillReview() {
    const cur = cards.find(c => c.dataset.slug === $('reg-category').value);
    $('review-business-name').textContent = $('reg-business-name').value;
    $('review-category').textContent = cur ? cur.dataset.name : '-';
    $('review-owner').textContent = $('reg-owner-name').value;
    $('review-email').textContent = $('reg-email').value;
    $('review-phone').textContent = $('reg-phone').value;
    $('review-scale').textContent = $('reg-outlet-count').value + ' • ' + $('reg-location').value;
  }

  $('register-form').addEventListener('submit', e => {
    if (!$('agree-terms').checked) { e.preventDefault(); return fail('step3-error', 'Centang persetujuan ketentuan layanan terlebih dahulu.'); }
    const b = $('btn-submit'); b.disabled = true; b.textContent = 'Membuat toko…';
  });

  // every "Coba gratis" link opens the register tab
  document.querySelectorAll('[data-open-register]').forEach(a => a.addEventListener('click', () => switchAuthTab('register')));

  paintCards();
  let step = auth.dataset.initialStep; step = step === 'success' ? step : parseInt(step, 10);
  goToStep(step);
  switchLoginMode(auth.dataset.loginMode);
  switchAuthTab(location.hash === '#masuk' ? 'login' : auth.dataset.initialTab);
})();
