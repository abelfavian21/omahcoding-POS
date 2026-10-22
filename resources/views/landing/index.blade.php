<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CashierStore | Sistem kasir untuk kafe, restoran, dan bakery</title>
  <meta name="description" content="Kasir cloud untuk kafe, restoran, dan bakery: tutup kas dengan selisih yang jelas, QRIS, stok, member, dan audit log. Coba gratis 14 hari.">
  <meta name="theme-color" content="#181829">
  <link rel="preload" href="{{ asset('fonts/PlusJakartaSans-variable.woff2') }}" as="font" type="font/woff2" crossorigin>
  <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
  <script>document.documentElement.classList.add('js')</script>
</head>
<body>
@php
  $reg = $registered;
  $firstActive = $categories->firstWhere('is_active', true);
  $selectedSlug = old('category', $firstActive?->slug);
  $loginMode = session('login_mode', 'email');
  $initialTab = ($reg || old('_form') === 'register') ? 'register' : ((session('login_mode') || $errors->login->any()) ? 'login' : 'register');
  $initialStep = $reg ? 'success' : ($errors->has('agree') ? 3 : ($errors->hasAny(['category', 'outlet_count', 'location']) ? 2 : 1));
  $dest = auth()->check() ? (auth()->user()->isAdmin() ? route('dashboard') : route('pos')) : '#';

  // Contoh data (fiktif) untuk tampilan; angka saling cocok: kas awal + penjualan tunai = kas seharusnya
  $board = [
    ['0018', 'Rina', 'Meja 4', 'Tunai', 'Lunas', 110000],
    ['0017', 'Dimas', 'Meja 2', 'QRIS', 'Lunas', 57200],
    ['0016', 'Rina', 'Bawa pulang', 'Kartu', 'Lunas', 70400],
    ['0015', 'Rina', 'Meja 7', 'QRIS', 'Lunas', 105600],
    ['0014', 'Dimas', 'Meja 1', 'Tunai', 'Lunas', 49500],
    ['0013', 'Dimas', 'Bawa pulang', 'Tunai', 'Void', 72600],
    ['0012', 'Rina', 'Meja 5', 'QRIS', 'Lunas', 68200],
    ['0011', 'Dimas', 'Meja 3', 'Tunai', 'Lunas', 68200],
  ];
  $qr = [];
  $n = 17;
  for ($y = 0; $y < $n; $y++) {
    for ($x = 0; $x < $n; $x++) {
      $inF = fn ($fx, $fy) => $x >= $fx && $x < $fx + 7 && $y >= $fy && $y < $fy + 7;
      $f = null;
      foreach ([[0, 0], [$n - 7, 0], [0, $n - 7]] as [$fx, $fy]) {
        if ($inF($fx, $fy)) { $dx = $x - $fx; $dy = $y - $fy; $f = ($dx === 0 || $dx === 6 || $dy === 0 || $dy === 6 || ($dx >= 2 && $dx <= 4 && $dy >= 2 && $dy <= 4)) ? 1 : 0; }
      }
      $qr[] = $f ?? ((($x * 37 + $y * 91 + $x * $y * 7 + 13) % 11) < 5 ? 1 : 0);
    }
  }
  $word = ['C','A','S','H','I','E','R','S','T','O','R','E'];
  $hs = [78, 100, 64, 92, 56, 84, 100, 70, 94, 60, 88, 76];
  $faq = [
    ['Perangkat apa yang dibutuhkan?', 'Cukup browser di laptop, tablet, atau HP. Perintah suara berbahasa Indonesia hanya berjalan di Chrome dan Edge.'],
    ['Bagaimana kasir masuk?', 'Pemilik masuk dengan email dan kata sandi. Kasir masuk dengan kode toko dan PIN 6 digit, jadi tidak perlu mengingat email.'],
    ['Pembayaran apa saja yang bisa dicatat?', 'Tunai, QRIS, dan kartu. QRIS dinamis lewat Midtrans berlaku sekitar 15 menit sejak dibuat.'],
    ['Berapa lama uji coba, dan perlu kartu?', 'Uji cobanya 14 hari. Formulir pendaftaran tidak meminta data kartu.'],
    ['Apakah bisa dipakai tanpa internet?', 'Belum. CashierStore berjalan di cloud, jadi perangkat perlu terhubung ke internet. Mode offline belum tersedia.'],
    ['Siapa yang boleh membatalkan transaksi?', 'Hanya admin, dan alasannya wajib diisi. Stok dan poin member kembali. Setiap aksi masuk ke audit log yang tidak bisa diedit dari aplikasi.'],
    ['Apakah data usaha saya terpisah dari usaha lain?', 'Ya. Data setiap usaha dipisah, dan pengguna dari usaha lain tidak bisa melihatnya.'],
    ['Untuk jenis usaha apa?', 'Saat ini untuk kafe, restoran, bakery, dan gerai cepat saji. Retail dan salon segera hadir.'],
  ];
@endphp

<main class="page"><div class="wrap">

  {{-- ============ HERO ============ --}}
  <header class="hero">
    <div class="band rv" style="--i:0">
      <h1>Cashier<br class="brk">Store<span class="sr">, sistem kasir untuk kafe, restoran, dan bakery</span></h1>
    </div>

    <nav class="links rv" style="--i:1" aria-label="Utama">
      <a href="#fitur">Fitur <span aria-hidden="true">↓</span></a>
      <a href="#usaha">Usaha <span aria-hidden="true">↓</span></a>
      <a href="#faq">Tanya jawab <span aria-hidden="true">↓</span></a>
      @auth
        <a class="me" href="{{ $dest }}">Buka dashboard</a>
      @else
        <a class="me" href="#masuk" onclick="switchAuthTab('login')">Masuk</a>
      @endauth
    </nav>

    <div class="rail-l">
      <div class="receipt rv" style="--i:2" data-ui>
        <h3>Kopi Nusantara</h3>
        <p class="sub">#0018 · Rina · Meja 4</p>
        <div class="ln"><div>Nasi Goreng Kampung</div><div class="row mut"><span>2 × 35.000</span><span class="num">70.000</span></div></div>
        <div class="ln"><div>Es Teh Lemon</div><div class="row mut"><span>2 × 15.000</span><span class="num">30.000</span></div></div>
        <div class="ln row"><span>Pajak (PB1) 10%</span><span class="num">10.000</span></div>
        <div class="tot row"><span>Total</span><span class="num">110.000</span></div>
        <p class="foot">Struk 80 mm, PDF</p>
      </div>
      <ul class="facts">
        <li class="rv" style="--i:3">Tunai, QRIS, kartu</li>
        <li class="rv" style="--i:4">Shift dan selisih kas</li>
        <li class="rv" style="--i:5">Stok dan peringatan</li>
        <li class="rv" style="--i:6">Member dan poin</li>
        <li class="rv" style="--i:7">Laporan PDF dan Excel</li>
      </ul>
    </div>

    <div class="centre">
      <div class="tile night rv" style="--i:3;padding:14px" data-ui>
        <div class="shift on-dark">
          <div class="card">
            <h2>Tutup kas</h2>
            <p class="note" style="margin-top:2px">Shift Rina · hari ini</p>
            <div class="calc">
              <div class="row"><span class="mut">Kas awal</span><span class="num">Rp 200.000</span></div>
              <div class="row"><span class="mut">Penjualan tunai</span><span class="num">Rp 227.700</span></div>
              <div class="row"><strong>Kas seharusnya</strong><strong class="big num">Rp 427.700</strong></div>
              <div>
                <label for="kas-count">Uang di laci</label>
                <input id="kas-count" inputmode="numeric" autocomplete="off" value="427.700" data-expected="427700">
              </div>
              <div class="res" aria-live="polite"><span id="kas-result" class="pill">Kas sesuai.</span></div>
            </div>
          </div>
          <div class="card">
            <h2>Penjualan tunai</h2>
            <ul class="cashlist" style="margin-top:10px">
              <li><span>#0018 · Meja 4</span><span class="num">110.000</span></li>
              <li><span>#0014 · Meja 1</span><span class="num">49.500</span></li>
              <li><span>#0011 · Meja 3</span><span class="num">68.200</span></li>
              <li class="mut"><span>#0013 · Void</span><span class="num" style="text-decoration:line-through">72.600</span></li>
            </ul>
            <p class="note" style="margin-top:10px">Ketik jumlah uang di laci. Selisih langsung terlihat. Data contoh.</p>
          </div>
        </div>
      </div>
      <div class="tile statement rv" style="--i:4">
        <p>Kasir kafe dan resto yang kasnya selalu cocok.</p>
      </div>
    </div>

    <div class="rail-r">
      <div class="tile c1 rv" style="--i:4">Uji coba 14 hari</div>
      <div class="tile c2 rv" style="--i:5">Kasir masuk dengan PIN 6 digit</div>
      <div class="tile c3 rv" style="--i:6">Data tiap usaha terpisah</div>
      <div class="tile c4 rv" style="--i:7">Perintah suara berbahasa Indonesia</div>
      <div class="tile cta rv" style="--i:8">
        @auth
          <a class="btn" href="{{ $dest }}">Buka dashboard</a>
        @else
          <a class="btn" href="#auth-section" data-open-register>Coba gratis 14 hari</a>
        @endauth
      </div>
    </div>
  </header>

  {{-- ============ PAPAN TRANSAKSI ============ --}}
  <section class="sec board" aria-labelledby="t-board" data-ui>
    <div class="hd rv">
      <h2 id="t-board">Transaksi hari ini</h2>
      <p>Satu papan untuk semua shift. Data contoh.</p>
    </div>
    @foreach ($board as $i => [$no, $kasir, $meja, $metode, $status, $total])
      <div class="trow rv {{ $status === 'Void' ? 'void' : '' }} {{ $no === '0015' ? 'open' : '' }}" style="--i:{{ $i % 4 }}">
        <span class="id">#{{ $no }}</span>
        <span class="who">{{ $meja }}<small>{{ $kasir }}</small></span>
        <span class="meth">{{ $metode }}</span>
        <span class="st {{ $status === 'Void' ? 'void' : '' }}">{{ $status }}</span>
        <span class="amt">Rp {{ number_format($total, 0, ',', '.') }}</span>
      </div>
    @endforeach
  </section>

  <div class="marquee" aria-hidden="true"><div class="trk">
    @for ($k = 0; $k < 8; $k++)<span>Kas toko yang selalu cocok</span>@endfor
  </div></div>

  {{-- ============ FITUR ============ --}}
  <section class="sec" id="fitur" aria-labelledby="t-fitur">
    <div class="mosaic">
      <div class="m head rv"><h2 class="big" id="t-fitur">Yang dijaga di balik kasir</h2></div>

      <div class="m qris rv" style="--i:1" data-ui>
        <div class="qr" aria-hidden="true">@foreach ($qr as $b)<i class="{{ $b ? 'k' : '' }}"></i>@endforeach</div>
        <div class="z num" style="text-align:center" id="qr-time" aria-hidden="true">14:59</div>
        <p class="cap"><b>QRIS dinamis</b>Dibuat per transaksi lewat Midtrans, berlaku sekitar 15 menit.</p>
      </div>

      <div class="m member rv" style="--i:2" data-ui>
        <div class="tiers" aria-hidden="true"><i style="height:30%"></i><i style="height:52%"></i><i style="height:76%"></i><i style="height:100%"></i></div>
        <p class="cap"><b>Poin dan tingkat member</b>1 poin tiap Rp 10.000. Bronze, Silver, Gold, VIP.</p>
      </div>

      <div class="m disc rv" style="--i:3" data-ui>
        <div class="z">10%</div>
        <p class="cap"><b>Batas diskon kasir</b>Admin tanpa batas.</p>
      </div>

      <div class="m void rv" style="--i:4" data-ui>
        <div class="z">Alasan wajib</div>
        <p class="cap"><b>Void hanya admin</b>Stok dan poin kembali.</p>
      </div>

      <div class="m voice rv" style="--i:5" data-ui>
        <div class="z" style="font-size:22px;line-height:1.15">“dua kopi susu”</div>
        <p class="cap"><b>Perintah suara</b>Bahasa Indonesia, di Chrome dan Edge.</p>
      </div>

      <div class="m audit rv" style="--i:6" data-ui>
        <div>
          <div class="logline"><time>08:02</time><span>Rina membuka shift, kas awal Rp 200.000</span></div>
          <div class="logline"><time>11:47</time><span>Admin membatalkan #0013: salah input</span></div>
          <div class="logline"><time>21:30</time><span>Rina menutup shift: Kas sesuai.</span></div>
        </div>
        <p class="cap"><b>Audit log</b>Hanya bertambah. Tidak bisa diedit dari aplikasi.</p>
      </div>

      <div class="m stock rv" style="--i:7" data-ui>
        <div>
          <div class="stk"><strong>Croissant Butter</strong><span class="pill warn">Sisa 3</span></div>
          <div class="bar" style="--w:30%;margin-top:10px"></div>
        </div>
        <p class="cap"><b>Stok menipis</b>Peringatan saat di bawah batas.</p>
      </div>

      <div class="m report rv" style="--i:8" data-ui>
        <div class="z">PDF · Excel</div>
        <p class="cap"><b>Laporan penjualan</b>Laba kotor dan produk terlaris.</p>
      </div>
    </div>
  </section>

  {{-- ============ USAHA ============ --}}
  <section class="sec list" id="usaha" aria-labelledby="t-usaha">
    <div class="hd"><h2 id="t-usaha">Usaha</h2></div>
    @foreach ($categories as $c)
      <details class="rv" style="--i:{{ $loop->index }}" {{ $loop->first ? 'open' : '' }}>
        <summary><span>{{ $c->name }}</span><span class="st {{ $c->is_active ? '' : 'soon' }}">{{ $c->is_active ? 'Tersedia' : 'Segera hadir' }}</span></summary>
        <div class="body">
          @if ($c->is_active && $c->preset)
            Kategori produk bawaan saat Anda mendaftar:
            <ul>@foreach ($c->preset as $p)<li>{{ is_array($p) ? $p[0] : $p }}</li>@endforeach</ul>
          @else
            Belum tersedia. Saat ini CashierStore dibuka untuk usaha makanan dan minuman.
          @endif
        </div>
      </details>
    @endforeach
  </section>

  {{-- ============ TANYA JAWAB ============ --}}
  <section class="sec faq" id="faq" aria-labelledby="t-faq">
    <div class="hd rv"><h2 class="big" id="t-faq">Tanya jawab</h2></div>
    @foreach ($faq as $i => [$q, $a])
      <div class="q rv"><h3>{{ $q }}</h3><p>{{ $a }}</p></div>
    @endforeach
  </section>

  {{-- ============ MASUK / DAFTAR ============ --}}
  @if (auth()->check() && ! $reg)
  <section class="sec close" id="auth-section" aria-labelledby="t-auth">
    <div class="pitch solo"><h2 id="t-auth">Kamu sedang masuk</h2></div>
    <div class="panel" id="masuk">
      <h3>{{ auth()->user()->name }}</h3>
      <p class="lead">{{ auth()->user()->business?->name ?? config('pos.store_name') }}. Untuk mendaftarkan usaha lain atau masuk dengan akun berbeda, keluar dulu.</p>
      <div class="nav">
        <a href="{{ $dest }}" class="btn">Buka dashboard</a>
        <form method="POST" action="{{ route('logout') }}">@csrf
          <button type="submit" class="btn soft">Keluar &amp; daftar usaha lain</button>
        </form>
      </div>
    </div>
  </section>
  @else
  <section class="sec close" id="auth-section" aria-labelledby="t-auth" data-initial-tab="{{ $initialTab }}" data-initial-step="{{ $initialStep }}" data-login-mode="{{ $loginMode }}">
    <div class="pitch">
      <h2 id="t-auth">Coba gratis 14 hari</h2>
      <ul>
        <li>Kasir, stok, member, dan laporan langsung aktif.</li>
        <li>Tidak ada isian kartu saat daftar.</li>
        <li>Kasir masuk dengan kode toko dan PIN 6 digit.</li>
      </ul>
    </div>

    <div class="panel" id="masuk">
      <div class="tabs" role="tablist">
        <button type="button" role="tab" id="tab-login-btn" onclick="switchAuthTab('login')" aria-selected="false">Masuk</button>
        <button type="button" role="tab" id="tab-register-btn" onclick="switchAuthTab('register')" aria-selected="true">Daftar usaha</button>
      </div>

      {{-- ========== LOGIN ========== --}}
      <div id="view-login" class="hidden">
        <h3>Selamat datang kembali</h3>
        <p class="lead">Buka dashboard, terminal kasir, dan pengaturan toko.</p>
        <div class="seg" role="tablist">
          <button type="button" role="tab" id="mode-email" onclick="switchLoginMode('email')" aria-selected="true">Pemilik / Admin</button>
          <button type="button" role="tab" id="mode-pin" onclick="switchLoginMode('pin')" aria-selected="false">Kasir (PIN)</button>
        </div>

        <form id="form-email" method="POST" action="{{ route('login.password') }}">
          @csrf
          <label class="fld"><span>Email</span>
            <input class="inp" type="email" name="email" required autocomplete="username" value="{{ old('email') }}" placeholder="owner@tokomu.id">
            @error('email', 'login')<p class="err">{{ $message }}</p>@enderror
          </label>
          <label class="fld"><span>Kata sandi</span>
            <input class="inp" type="password" name="password" required autocomplete="current-password" placeholder="••••••••••••">
          </label>
          <label class="check"><input type="checkbox" name="remember" value="1"><span>Ingat akun di perangkat ini</span></label>
          <div class="nav"><button type="submit" class="btn block">Masuk ke dashboard</button></div>
        </form>

        <form id="form-pin" method="POST" action="{{ route('login.pin') }}" class="hidden">
          @csrf
          <label class="fld"><span>Kode toko</span>
            <input class="inp" type="text" name="store_code" required value="{{ old('store_code') }}" placeholder="CS-ORELY-0001" autocapitalize="characters" style="text-transform:uppercase">
            <small>Kode toko ada di layar konfirmasi saat registrasi. Tanyakan ke pemilik jika lupa.</small>
          </label>
          <label class="fld"><span>PIN kasir</span>
            <input class="inp pin" type="password" name="pin" required inputmode="numeric" maxlength="6" pattern="\d{4,6}" placeholder="••••••" autocomplete="off">
            @error('pin', 'login')<p class="err">{{ $message }}</p>@enderror
            @error('store_code', 'login')<p class="err">{{ $message }}</p>@enderror
          </label>
          <div class="nav"><button type="submit" class="btn block">Masuk ke terminal kasir</button></div>
        </form>

              </div>

      {{-- ========== REGISTRASI 3 LANGKAH ========== --}}
      <div id="view-register">
        <div class="steps" data-ui aria-label="Langkah pendaftaran">
          @foreach (['Data pemilik', 'Kategori usaha', 'Konfirmasi'] as $i => $lbl)
            <div id="step-badge-{{ $i + 1 }}"><b id="step-num-{{ $i + 1 }}">{{ $i + 1 }}</b>{{ $lbl }}</div>
          @endforeach
        </div>

        @if (old('_form') === 'register' && $errors->any())
          <div class="errs"><ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        <form id="register-form" method="POST" action="{{ route('register') }}" novalidate>
          @csrf
          <input type="hidden" name="_form" value="register">
          <input type="hidden" name="category" id="reg-category" value="{{ $selectedSlug }}">

          <div id="step-1-content" class="hidden">
            <h3>Akun pemilik</h3>
            <p class="lead">Identitas utama yang mengelola toko dan staf.</p>
            <label class="fld"><span>Nama lengkap pemilik</span><input class="inp" id="reg-owner-name" name="owner_name" required maxlength="120" value="{{ old('owner_name') }}" placeholder="Contoh: Budi Santoso"></label>
            <label class="fld"><span>Nama usaha / brand</span><input class="inp" id="reg-business-name" name="business_name" required maxlength="120" value="{{ old('business_name') }}" placeholder="Contoh: Kopi Nusantara"></label>
            <div class="two">
              <label class="fld"><span>Email bisnis</span><input class="inp" id="reg-email" type="email" name="email" required value="{{ old('email') }}" placeholder="owner@tokomu.id"></label>
              <label class="fld"><span>Nomor WhatsApp aktif</span><input class="inp" id="reg-phone" type="tel" name="phone" required minlength="8" value="{{ old('phone') }}" placeholder="0812-xxxx-xxxx"></label>
            </div>
            <label class="fld"><span>Kata sandi akun</span><input class="inp" id="reg-pass" type="password" name="password" required minlength="8" autocomplete="new-password" placeholder="Minimal 8 karakter"></label>
            <div class="nav"><button type="button" onclick="nextStep(1)" class="btn block">Lanjut: pilih kategori</button></div>
          </div>

          <div id="step-2-content" class="hidden">
            <h3>Kategori usaha</h3>
            <p class="lead">Menentukan kategori produk bawaan di toko Anda. Saat ini tersedia untuk usaha makanan dan minuman.</p>
            <div class="cats" role="radiogroup" aria-label="Kategori usaha">
              @foreach ($categories as $c)
                <div class="cat-card" role="radio" tabindex="{{ $c->is_active ? 0 : -1 }}" aria-checked="false"
                     data-slug="{{ $c->slug }}" data-name="{{ $c->name }}" data-active="{{ $c->is_active ? 1 : 0 }}">
                  <strong>{{ $c->name }}</strong>
                  <em>{{ $c->is_active ? 'Tersedia' : 'Segera hadir' }}</em>
                </div>
              @endforeach
            </div>
            <div class="two">
              <label class="fld"><span>Jumlah outlet saat ini</span>
                <select class="sel" id="reg-outlet-count" name="outlet_count">
                  @foreach (\App\Http\Controllers\RegisterController::OUTLETS as $o)
                    <option value="{{ $o }}" @selected(old('outlet_count', '1 Outlet') === $o)>{{ $o }}</option>
                  @endforeach
                </select></label>
              <label class="fld"><span>Kota / lokasi operasional</span><input class="inp" id="reg-location" name="location" maxlength="120" value="{{ old('location') }}" placeholder="Contoh: Surakarta"></label>
            </div>
            <p id="step2-error" class="err hidden" role="alert"></p>
            <div class="nav">
              <button type="button" onclick="goToStep(1)" class="btn soft">Kembali</button>
              <button type="button" onclick="nextStep(2)" class="btn">Lanjut: konfirmasi</button>
            </div>
          </div>

          <div id="step-3-content" class="hidden">
            <h3>Periksa data usaha</h3>
            <p class="lead">Ringkasan sebelum akun CashierStore Anda dibuat.</p>
            <dl class="review">
              <div><dt>Nama bisnis &amp; kategori</dt><dd><span id="review-business-name"></span> · <span id="review-category"></span></dd></div>
              <div class="grid2">
                <div><dt>Nama pemilik</dt><dd id="review-owner"></dd></div>
                <div><dt>Email login</dt><dd id="review-email"></dd></div>
                <div><dt>No. WhatsApp</dt><dd id="review-phone"></dd></div>
                <div><dt>Skala &amp; lokasi</dt><dd id="review-scale"></dd></div>
              </div>
              <div><dt>Langsung aktif</dt>
                <ul class="mods">@foreach (['Kasir / POS', 'Manajemen stok', 'Member dan poin', 'Dashboard penjualan', 'Laporan PDF & Excel', 'Login PIN kasir'] as $m)<li>{{ $m }}</li>@endforeach</ul>
              </div>
            </dl>
            <label class="check"><input id="agree-terms" type="checkbox" name="agree" value="1" @checked(old('agree'))><span>Saya menyatakan data usaha di atas valid dan menyetujui Ketentuan Layanan &amp; Kebijakan Privasi CashierStore.</span></label>
            <p id="step3-error" class="err hidden" role="alert"></p>
            <div class="nav">
              <button type="button" onclick="goToStep(2)" class="btn soft">Ubah data</button>
              <button type="submit" id="btn-submit" class="btn">Konfirmasi &amp; buat toko</button>
            </div>
          </div>
        </form>

        <div id="step-success" class="hidden">
          <h3>Usaha berhasil didaftarkan</h3>
          <p class="lead">Akun untuk <strong>{{ $reg['name'] ?? '' }}</strong> sudah aktif dengan masa uji coba 14 hari.</p>
          <div class="ok">
            <p>Kategori usaha: <strong>{{ $reg['category'] ?? '' }}</strong></p>
            <p style="margin-top:10px">Kode toko: <code>{{ $reg['code'] ?? '' }}</code></p>
            <p style="margin-top:10px;font-size:15px">Simpan kode toko ini. Kasir memakainya bersama PIN untuk login cepat.</p>
          </div>
          <div class="nav"><a href="{{ $dest }}" class="btn">Buka dashboard kasir</a></div>
        </div>

      </div>
    </div>
  </section>
  @endif

  {{-- ============ FOOTER ============ --}}
  <footer class="foot">
    <div class="wm" aria-hidden="true">
      @foreach ($word as $i => $l)<span data-l="{{ $l }}" style="height:{{ $hs[$i] }}%"></span>@endforeach
    </div>
    <div class="fcols">
      <div class="tile about"><h2>CashierStore</h2><p>Sistem kasir cloud untuk kafe, restoran, dan bakery.</p></div>
      <div class="tile"><h2>Halaman</h2><ul>
        <li><a href="#fitur">Fitur</a></li><li><a href="#usaha">Usaha</a></li><li><a href="#faq">Tanya jawab</a></li></ul></div>
      <div class="tile"><h2>Akun</h2><ul>
        @auth<li><a href="{{ $dest }}">Buka dashboard</a></li>@else<li><a href="#masuk" onclick="switchAuthTab('login')">Masuk</a></li><li><a href="#auth-section" data-open-register>Daftar usaha</a></li>@endauth</ul></div>
      <div class="tile"><h2>Kontak</h2><ul>
        <li>help@cashierstore.com</li><li>+62 21 8890 1200</li></ul></div>
    </div>
    <div class="last">
      <span>© {{ date('Y') }} CashierStore</span>
      @guest<a class="btn lime" href="#auth-section" data-open-register>Coba gratis 14 hari</a>@endguest
    </div>
  </footer>

</div></main>

<script src="{{ asset('js/landing.js') }}" defer></script>
</body>
</html>
