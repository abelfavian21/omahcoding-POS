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

  /* ---------- Board: the open row moves down the list ---------- */
  const rows = [...document.querySelectorAll('.trow')];
  if (rows.length && !reduce) {
    let cur = rows.findIndex(r => r.classList.contains('open'));
    setInterval(() => {
      if (document.hidden) return;
      rows[cur].classList.remove('open');
      do { cur = (cur + 1) % rows.length; } while (rows[cur].classList.contains('void'));
      rows[cur].classList.add('open');
    }, 2600);
  }

  /* ---------- QRIS countdown (illustration of the ~15 minute expiry) ---------- */
  const qt = $('qr-time');
  if (qt && !reduce) {
    let left = 899;
    setInterval(() => {
      if (document.hidden) return;
      left = left <= 0 ? 899 : left - 1;
      qt.textContent = String(Math.floor(left / 60)).padStart(2, '0') + ':' + String(left % 60).padStart(2, '0');
    }, 1000);
  }

  /* ---------- Tutup kas: the live calculation ---------- */
  const inp = $('kas-count');
  if (inp) {
    const expected = parseInt(inp.dataset.expected, 10), out = $('kas-result');
    const paint = () => {
      const digits = inp.value.replace(/\D/g, '');
      inp.value = digits ? parseInt(digits, 10).toLocaleString('id-ID') : '';
      if (!digits) { out.className = 'pill dim'; out.textContent = 'Isi uang di laci'; return; }
      const d = parseInt(digits, 10) - expected;
      if (d === 0) { out.className = 'pill'; out.textContent = 'Kas sesuai.'; }
      else if (d > 0) { out.className = 'pill info'; out.textContent = 'Kas lebih ' + rp(d); }
      else { out.className = 'pill warn'; out.textContent = 'Kas kurang ' + rp(d); }
    };
    inp.addEventListener('input', paint);
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
