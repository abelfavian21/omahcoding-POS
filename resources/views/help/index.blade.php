@extends('layouts.app')
@section('title', 'Panduan Penggunaan')
@section('content')
@php
  // [id, ikon, judul, deskripsi singkat, khusus admin?, tampil?]
  $all = [
    ['mulai',       'rocket_launch',   'Mulai di sini',        'Langkah pertama',          false, true],
    ['peran',       'badge',           'Login & hak akses',    'Siapa bisa apa',           false, true],
    ['kasir',       'receipt_long',    'Melayani pelanggan',   'Catat pesanan & bayar',    false, true],
    ['bayar',       'payments',        'Cara pembayaran',      'Tunai, kartu, QRIS',       false, true],
    ['thermal',     'print',           'Cetak struk thermal',  'Printer 58 / 80 mm',       false, true],
    ['shift',       'schedule',        'Buka & tutup shift',   'Catat uang di laci',       false, $shiftOn || $isAdmin],
    ['suara',       'mic',             'Perintah suara',       'Pesan tanpa mengetik',     false, true],
    ['produk',      'lunch_dining',    'Produk & menu',        'Daftar menu yang dijual',  false, true],
    ['pelanggan',   'group',           'Pelanggan & poin',     'Member dan poin',          false, true],
    ['stok',        'warehouse',       'Stok barang',          'Tambah & hitung stok',     true,  true],
    ['laporan',     'query_stats',     'Laporan & void',       'Omzet dan pembatalan',     true,  true],
    ['dashboard',   'grid_view',       'Dashboard',            'Ringkasan hari ini',       true,  true],
    ['staff',       'manage_accounts', 'Staff & PIN',          'Akun kasir',               true,  true],
    ['audit',       'policy',          'Catatan aktivitas',    'Siapa melakukan apa',      true,  true],
    ['konfigurasi', 'settings',        'Pengaturan toko',      'Pajak, struk, QRIS',       true,  true],
    ['midtrans',    'qr_code_2',       'QRIS otomatis',        'Hubungkan Midtrans',       true,  true],
    ['masalah',     'build',           'Ada masalah?',         'Jawaban cepat',            false, true],
  ];
  $toc = collect($all)->filter(fn ($s) => $s[5] && (! $s[4] || $isAdmin))->values();
  $by  = $toc->keyBy(0);

  // topik yang paling sering dicari (kartu pintasan di atas)
  $featIds = $isAdmin ? ['kasir', 'produk', 'stok', 'laporan', 'midtrans', 'masalah'] : ['kasir', 'bayar', 'shift', 'pelanggan', 'suara', 'masalah'];
  $feat = collect($featIds)->filter(fn ($id) => $by->has($id))->map(fn ($id) => $by[$id])->values();

  $pct  = rtrim(rtrim(number_format($cashierMaxDiscount, 2), '0'), '.');
  $muted = 'text-sm text-slate-500';
  $body  = 'text-[15px] leading-relaxed';
  $sec   = 'card p-5 sm:p-7 scroll-mt-28 md:scroll-mt-20 xl:scroll-mt-6';
  $sub   = 'font-bold text-sm mb-2.5';

  // judul bagian
  $head = fn (string $icon, string $title, string $about) =>
    '<header class="flex items-start gap-3.5 mb-5"><span class="w-11 h-11 rounded-2xl bg-lime-accent text-slate-950 grid place-items-center shrink-0"><span class="material-symbols-outlined fill-1">' . $icon . '</span></span>'
    . '<div class="min-w-0 pt-0.5"><h2 class="text-lg sm:text-xl font-extrabold tracking-tight leading-tight">' . e($title) . '</h2><p class="text-sm text-slate-500 mt-0.5">' . e($about) . '</p></div></header>';

  // kotak catatan: tip / warn / info  (isi = HTML statis dari template ini)
  $note = function (string $kind, string $html) {
    [$cls, $icon] = [
      'tip'  => ['bg-lime-subtle dark:bg-lime-accent/10', 'lightbulb'],
      'warn' => ['bg-amber-50 text-amber-800', 'warning'],
      'info' => ['bg-slate-100', 'info'],
    ][$kind];
    return '<div class="flex gap-3 rounded-2xl px-4 py-3 text-sm leading-relaxed ' . $cls . '"><span class="material-symbols-outlined fill-1 shrink-0 text-[20px]">' . $icon . '</span><div class="min-w-0">' . $html . '</div></div>';
  };
@endphp

<style>
  html { scroll-behavior: smooth; }
  @media (prefers-reduced-motion: reduce) { html { scroll-behavior: auto; } }
  .hl-steps { counter-reset: s; display: grid; gap: .85rem; list-style: none; margin: 0; padding: 0; }
  .hl-steps > li { counter-increment: s; position: relative; padding-left: 2.6rem; font-size: 15px; line-height: 1.6; }
  .hl-steps > li::before { content: counter(s); position: absolute; left: 0; top: .05rem; width: 1.8rem; height: 1.8rem; border-radius: 9999px;
    background: #181829; color: #D2F839; font-weight: 800; font-size: .8rem; display: grid; place-items: center; }
  .dark .hl-steps > li::before { background: #D2F839; color: #181829; }
  .hl-faq summary::-webkit-details-marker { display: none; }
  .hl-faq[open] .hl-chev { transform: rotate(180deg); }
</style>

{{-- ===== Judul + pencarian ===== --}}
<div class="mb-5 sm:mb-6">
  <div class="text-xs font-semibold text-slate-500 mb-1">Dashboard › <span class="text-slate-900 dark:text-white">Panduan</span></div>
  <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Panduan Penggunaan</h1>
  <p class="{{ $muted }} mt-1 max-w-2xl">Langkah demi langkah, dari login sampai laporan. Pilih topik di bawah atau cari dengan kata kunci.@unless ($isAdmin) Bagian khusus admin tidak ditampilkan di akun kasir.@endunless</p>

  <div class="relative mt-4 max-w-xl">
    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" style="font-size:20px">search</span>
    <input id="helpSearch" type="search" class="input input-pill !h-12 !text-[15px]" placeholder="Cari, misalnya: diskon, shift, QRIS, void…" autocomplete="off" aria-label="Cari panduan">
  </div>
  <p id="helpCount" class="hidden text-xs font-semibold text-slate-500 mt-2 px-1"></p>
</div>

{{-- ===== Pintasan topik ===== --}}
<div id="helpTiles" class="mb-6">
  <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Kamu ingin…</h2>
  <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
    @foreach ($feat as [$id, $icon, $title, $about])
      <a href="#{{ $id }}" class="card p-4 flex flex-col gap-2.5 hover:shadow-lg hover:-translate-y-0.5 transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#B1D501]">
        <span class="w-10 h-10 rounded-xl bg-lime-accent text-slate-950 grid place-items-center"><span class="material-symbols-outlined fill-1">{{ $icon }}</span></span>
        <span><span class="block font-extrabold text-sm leading-tight">{{ $title }}</span><span class="block text-xs text-slate-500 mt-0.5">{{ $about }}</span></span>
      </a>
    @endforeach
  </div>
</div>

{{-- ===== Navigasi topik: chip (HP & tablet) ===== --}}
<nav id="helpChips" aria-label="Daftar topik" class="xl:hidden sticky top-14 md:top-0 z-20 -mx-4 sm:-mx-6 px-4 sm:px-6 py-2.5 mb-5 flex gap-2 overflow-x-auto no-scrollbar backdrop-blur" style="background: color-mix(in srgb, var(--canvas) 88%, transparent)">
  @foreach ($toc as [$id, $icon, $title])
    <a href="#{{ $id }}" data-toc="{{ $id }}" class="chip shrink-0 gap-1.5"><span class="material-symbols-outlined" style="font-size:18px">{{ $icon }}</span>{{ $title }}</a>
  @endforeach
</nav>

<div class="xl:grid xl:grid-cols-[230px_minmax(0,1fr)] xl:gap-8 items-start">

  {{-- ===== Daftar isi (desktop) ===== --}}
  <aside class="hidden xl:block xl:sticky xl:top-6">
    <nav class="card p-2 max-h-[calc(100vh-3rem)] overflow-y-auto no-scrollbar" aria-label="Daftar isi">
      @foreach ($toc as [$id, $icon, $title, $about, $adm])
        <a href="#{{ $id }}" data-toc="{{ $id }}" class="flex items-center gap-2.5 px-3 h-10 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-100 transition">
          <span class="material-symbols-outlined text-[19px]">{{ $icon }}</span><span class="truncate">{{ $title }}</span>
          @if ($adm)<span class="material-symbols-outlined ml-auto text-[15px] text-slate-400" title="Khusus admin">lock</span>@endif
        </a>
      @endforeach
    </nav>
  </aside>

  <div class="space-y-5 min-w-0 max-w-3xl">
    <p id="helpEmpty" class="hidden card p-6 text-sm text-slate-500">Tidak ada bagian yang cocok. Coba kata lain, misalnya "diskon", "shift", atau "struk".</p>

    <div data-group-head="all" class="flex items-center gap-3"><h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Untuk semua pengguna</h2><span class="h-px flex-1 bg-card-border"></span></div>

    {{-- ===== Mulai ===== --}}
    <section id="mulai" data-help data-group="all" data-kw="awal baru pertama langkah urutan" class="{{ $sec }}">
      {!! $head('rocket_launch', 'Mulai di sini', $isAdmin ? 'Urutan yang disarankan untuk toko baru.' : 'Alur kerja kasir sehari-hari.') !!}
      @if ($isAdmin)
        <ol class="hl-steps">
          <li><b>Lengkapi data toko</b> di menu <b>Pengaturan</b>: nama toko, telepon, alamat, dan pajak.</li>
          <li><b>Masukkan menu</b> di <b>Produk</b> → <b>Tambah Produk</b>: nama, harga, dan stok.</li>
          <li><b>Tambah kasir</b> di menu <b>Staff</b>, lalu buat PIN 6 angka supaya mereka bisa login cepat.</li>
          <li><b>Pilih cara menerima QRIS</b>: unggah gambar QRIS toko, atau sambungkan QRIS otomatis (Midtrans).</li>
          <li><b>Tentukan pakai shift atau tidak</b> di Pengaturan <i>(opsional)</i>.</li>
          <li><b>Coba satu transaksi</b> di <b>Order Line</b>, lalu cek hasilnya di <b>Laporan</b> dan <b>Stok</b>.</li>
        </ol>
      @else
        <ol class="hl-steps">
          <li>Login dengan <b>Kode Toko + PIN</b> di tab <b>PIN Kasir</b>.</li>
          @if ($shiftOn)<li>Buka menu <b>Shift Kasir</b> dan isi uang modal yang ada di laci.</li>@endif
          <li>Buka <b>Order Line</b>, ketuk menu yang dipesan, lalu tekan <b>Bayar</b>.</li>
          <li>Berikan struk ke pelanggan (cetak thermal, atau unduh PDF).</li>
          @if ($shiftOn)<li>Saat selesai bertugas, hitung uang di laci lalu <b>Tutup Shift</b>.</li>@endif
        </ol>
      @endif
    </section>

    {{-- ===== Login & hak akses ===== --}}
    <section id="peran" data-help data-group="all" data-kw="login masuk akun admin kasir pin password kode toko akses izin diskon" class="{{ $sec }}">
      {!! $head('badge', 'Login & hak akses', 'Siapa bisa melakukan apa.') !!}
      <div class="grid sm:grid-cols-2 gap-4">
        <div class="rounded-2xl border border-card-border p-4">
          <div class="flex items-center gap-2 font-extrabold mb-1.5"><span class="material-symbols-outlined">admin_panel_settings</span> Admin / Manager</div>
          <p class="{{ $muted }}">Login di tab <b>Admin / Manager</b> memakai email dan password. Bisa membuka <b>semua menu</b> dan memberi diskon tanpa batas.</p>
        </div>
        <div class="rounded-2xl border border-card-border p-4">
          <div class="flex items-center gap-2 font-extrabold mb-1.5"><span class="material-symbols-outlined">point_of_sale</span> Kasir</div>
          <p class="{{ $muted }}">Login cepat di tab <b>PIN Kasir</b> memakai <b>Kode Toko</b> + PIN 6 angka.@if ($storeCode) Kode toko ini: <b class="tracking-wider text-slate-900 dark:text-white">{{ $storeCode }}</b>.@endif</p>
        </div>
      </div>
      <div class="mt-4">
        <h3 class="{{ $sub }}">Yang tidak bisa dilakukan kasir</h3>
        <ul class="grid sm:grid-cols-2 gap-x-4 gap-y-1.5 text-sm text-slate-600">
          @foreach (['Membuka Dashboard, Laporan, Stok, Staff, Pengaturan, dan Catatan aktivitas', 'Menambah, mengubah, atau menghapus produk', 'Membatalkan transaksi (void) dan menghapus pelanggan', 'Memberi diskon lebih dari ' . $pct . '% dari subtotal'] as $x)
            <li class="flex gap-2"><span class="material-symbols-outlined text-[18px] text-slate-400 shrink-0 mt-0.5">block</span>{{ $x }}</li>
          @endforeach
        </ul>
      </div>
      <div class="mt-4">{!! $note('info', 'Salah login berkali-kali akan dikunci sementara. Tunggu beberapa menit lalu coba lagi.') !!}</div>
    </section>

    {{-- ===== Melayani pelanggan ===== --}}
    <section id="kasir" data-help data-group="all" data-kw="transaksi order pesan pesanan jual struk diskon meja dine in takeaway keranjang walk-in" class="{{ $sec }}">
      {!! $head('receipt_long', 'Melayani pelanggan', 'Cara mencatat pesanan di menu Order Line.') !!}
      <ol class="hl-steps">
        <li>Buka menu <b>Order Line</b>.@if ($shiftOn) Jika muncul peringatan <i>Shift kasir belum dibuka</i>, tekan <b>Buka shift</b> dulu.@endif</li>
        <li><b>Ketuk menu</b> yang dipesan. Pakai kolom <i>Cari menu…</i> atau tombol kategori agar lebih cepat. Menu berlabel <b>Habis</b> tidak bisa dipilih.</li>
        <li>Atur jumlah dengan tombol <b>+</b> dan <b>−</b>. Kurangi sampai 0 untuk menghapus item.</li>
        <li>Pilih <b>Dine-in</b> (isi nomor meja) atau <b>Takeaway</b>.</li>
        <li><i>(Opsional)</i> Pilih <b>Pelanggan</b> agar belanjanya tercatat dan mendapat poin. Kalau dikosongkan, tercatat sebagai <i>Walk-in</i>.</li>
        <li><i>(Opsional)</i> Isi <b>Diskon</b> dalam rupiah.@unless ($isAdmin) Batas diskon kasir: {{ $pct }}% dari subtotal.@endunless</li>
        <li>Periksa <b>Ringkasan Pembayaran</b>: subtotal − diskon + pajak ({{ $taxPct }}%) = total.</li>
        <li>Pilih <b>cara bayar</b>, lalu tekan <b>Bayar</b>.</li>
        <li>Setelah muncul <i>Pembayaran Berhasil</i>, pilih <b>Cetak Struk (Thermal)</b> bila fitur itu aktif, <b>Lihat Struk PDF</b>, <b>Unduh Struk PDF</b>, atau <b>Pesanan Baru</b>.</li>
      </ol>
      <div class="mt-5 space-y-3">
        {!! $note('tip', 'Aman kalau tombol <b>Bayar</b> tertekan dua kali: transaksi tidak akan tercatat ganda. Jika stok tidak cukup, pembayaran ditolak dan akan ada pesannya.') !!}
        {!! $note('info', 'Di HP, ketuk bar <b>Lihat keranjang</b> di bagian bawah layar untuk membuka keranjang.') !!}
      </div>
    </section>

    {{-- ===== Cara pembayaran ===== --}}
    <section id="bayar" data-help data-group="all" data-kw="tunai cash kartu edc qris qr kembalian uang pas referensi konfirmasi bayar" class="{{ $sec }}">
      {!! $head('payments', 'Cara pembayaran', 'Pilih sesuai cara pelanggan membayar.') !!}
      <div class="grid sm:grid-cols-2 gap-4">
        <div class="rounded-2xl border border-card-border p-4">
          <div class="flex items-center gap-2 font-extrabold mb-1.5"><span class="material-symbols-outlined">payments</span> Tunai</div>
          <p class="{{ $muted }}">Isi <b>Uang diterima</b>. Kembalian dihitung otomatis. Kalau pelanggan membayar pas, cukup tekan <b>Uang pas</b>.</p>
        </div>
        <div class="rounded-2xl border border-card-border p-4">
          <div class="flex items-center gap-2 font-extrabold mb-1.5"><span class="material-symbols-outlined">credit_card</span> Kartu</div>
          <p class="{{ $muted }}">Proses dulu di mesin EDC, lalu isi <b>No. referensi / approval code</b> dari struk mesin. Kolom ini wajib diisi.</p>
        </div>
        <div class="rounded-2xl border border-card-border p-4">
          <div class="flex items-center gap-2 font-extrabold mb-1.5"><span class="material-symbols-outlined">qr_code_2</span> QRIS otomatis</div>
          <p class="{{ $muted }}">Tersedia bila admin sudah mengaktifkannya. Tekan <b>Bayar</b>, lalu muncul kode QR dengan nominal pas yang berlaku <b>15 menit</b>. Minta pelanggan scan; layar berubah sendiri saat pembayaran masuk. Waktu habis? Tekan <b>Buat ulang QR</b>. Salah pesanan? Tekan <b>Batalkan pembayaran</b>.</p>
        </div>
        <div class="rounded-2xl border border-card-border p-4">
          <div class="flex items-center gap-2 font-extrabold mb-1.5"><span class="material-symbols-outlined">qr_code</span> QRIS manual</div>
          <p class="{{ $muted }}">Dipakai bila QRIS otomatis belum aktif. Gambar QRIS toko tampil di layar. Setelah uang masuk ke rekening, tekan <b>Konfirmasi Pembayaran</b>. Kode konfirmasi perlu diisi bila admin mengaktifkannya.</p>
        </div>
      </div>
    </section>

    {{-- ===== Cetak struk thermal ===== --}}
    <section id="thermal" data-help data-group="all" data-kw="cetak struk thermal printer print kertas 58 80 mm otomatis nota usb bluetooth driver kiosk dialog pdf" class="{{ $sec }}">
      {!! $head('print', 'Cetak struk thermal', 'Cetak struk ke printer kasir 58 / 80 mm setelah pembayaran.') !!}
      <p class="{{ $body }} mb-5">Status di toko ini:
        @if ($thermalOn)
          <b>aktif</b>, kertas <b>{{ $paperMm }} mm</b>, @if ($thermalAuto) struk <b>otomatis dicetak</b> setelah pembayaran berhasil. @else kasir mencetak lewat tombol <b>Cetak Struk (Thermal)</b>. @endif
        @else
          <b>nonaktif</b>. Struk hanya bisa dibuka atau diunduh sebagai PDF.@if ($isAdmin) Aktifkan di langkah "Menyiapkan" di bawah.@else Minta admin mengaktifkannya.@endif
        @endif
      </p>

      <div class="space-y-6">
        @if ($isAdmin)
        <div>
          <h3 class="{{ $sub }}">Menyiapkan (admin, sekali saja)</h3>
          <ol class="hl-steps">
            <li>Pasang printer thermal di komputer atau HP kasir (USB atau Bluetooth). Pasang <b>driver</b>-nya sampai printer muncul di daftar printer perangkat.</li>
            <li>Buka <a href="{{ route('settings.edit') }}" class="font-bold underline">Pengaturan</a>, cari kartu <b>Cetak struk (printer thermal)</b>.</li>
            <li>Centang <b>Aktifkan cetak struk thermal</b>.</li>
            <li>Pilih <b>Lebar kertas</b> sesuai gulungan di printer: 58 mm atau 80 mm.</li>
            <li>Centang <b>Cetak otomatis setelah pembayaran berhasil</b> bila dialog cetak harus terbuka sendiri. Kosongkan bila kasir lebih suka menekan tombol.</li>
            <li>Tekan <b>Simpan pengaturan cetak</b>. Hilangkan centang aktif lalu simpan untuk mematikan fitur ini; struk PDF tetap bisa dipakai.</li>
          </ol>
        </div>
        @endif
        <div>
          <h3 class="{{ $sub }}">Mencetak struk (kasir)</h3>
          <ol class="hl-steps">
            <li>Selesaikan pembayaran seperti biasa sampai muncul <i>Pembayaran Berhasil</i>.</li>
            <li>Dialog cetak browser terbuka otomatis. Bila tidak, tekan <b>Cetak Struk (Thermal)</b>. Tombol ini juga dipakai untuk cetak ulang.</li>
            <li>Di dialog, pilih <b>printer thermal</b> pada kolom <i>Destination / Printer</i>.</li>
            <li>Pilih <b>Paper size</b> yang sama dengan kertas printer (80 mm atau 58 mm) dan set <b>Margins</b> ke <i>None</i>.</li>
            <li>Tekan <b>Print</b>. Struk keluar dan terpotong otomatis bila printer punya pemotong.</li>
          </ol>
        </div>
        <div>
          <h3 class="{{ $sub }}">Cetak tanpa dialog (opsional)</h3>
          <p class="{{ $body }}">Jadikan printer thermal sebagai <b>printer default</b> di sistem, lalu buka Chrome atau Edge dengan opsi <code class="px-1.5 py-0.5 rounded-md bg-slate-100 text-[12px] font-mono">--kiosk-printing</code>. Struk langsung tercetak tanpa memilih printer. Opsi ini diatur di shortcut browser kasir, bukan di aplikasi.</p>
        </div>
        <div>
          <h3 class="{{ $sub }}">Mencoba tanpa printer</h3>
          <p class="{{ $body }}">Di dialog cetak, pilih <b>Save as PDF</b> (Windows: <b>Microsoft Print to PDF</b>) sebagai printer. Struk tersimpan sebagai file PDF. Karena <i>Paper size</i> biasanya A4, struk tampil kecil di pojok kiri atas. Itu normal; di printer thermal asli ukurannya mengikuti kertas.</p>
        </div>
      </div>
      <div class="mt-5 space-y-3">
        {!! $note('tip', 'Struk thermal memuat data yang sama dengan struk PDF: nama toko, telepon, lokasi, pajak, dan teks bawah struk dari Pengaturan.') !!}
        {!! $note('warn', 'Dialog cetak tidak muncul? Browser mungkin memblokir. Izinkan jendela pop-up atau cetak untuk situs ini, lalu tekan <b>Cetak Struk (Thermal)</b> lagi.') !!}
      </div>
    </section>

    {{-- ===== Shift ===== --}}
    @if ($shiftOn || $isAdmin)
    <section id="shift" data-help data-group="all" data-kw="shift laci modal buka tutup selisih kas uang" class="{{ $sec }}">
      {!! $head('schedule', 'Buka & tutup shift', 'Mencatat uang di laci supaya selisih kas ketahuan.') !!}
      @if (! $shiftOn)
        <div class="mb-5">{!! $note('info', 'Fitur shift sedang <b>nonaktif</b> di toko ini, jadi kasir bisa langsung bertransaksi.' . ($isAdmin ? ' Aktifkan lewat <b>Pengaturan → Shift kasir</b>.' : '') . ' Langkah di bawah berlaku setelah fitur diaktifkan.') !!}</div>
      @else
        <p class="{{ $body }} mb-5">Selama shift belum dibuka, pembayaran tidak bisa diproses.</p>
      @endif
      <div class="grid sm:grid-cols-2 gap-6">
        <div>
          <h3 class="{{ $sub }}">Membuka shift</h3>
          <ol class="hl-steps">
            <li>Buka menu <b>Shift Kasir</b>.</li>
            <li>Isi <b>Modal awal di laci (Rp)</b>, catatan bila perlu.</li>
            <li>Tekan <b>Buka Shift</b>.</li>
          </ol>
        </div>
        <div>
          <h3 class="{{ $sub }}">Menutup shift</h3>
          <ol class="hl-steps">
            <li>Hitung uang yang ada di laci.</li>
            <li>Isi <b>Uang di laci (hitung fisik)</b>, lalu tekan <b>Tutup Shift</b>.</li>
            <li>Sistem mencatat <b>selisihnya</b> otomatis di <i>Riwayat shift</i>.</li>
          </ol>
        </div>
      </div>
      <div class="mt-5 space-y-3">
        {!! $note('info', 'Shift tidak bisa ditutup selama masih ada pembayaran QRIS yang menunggu. Kasir hanya melihat shift miliknya; admin melihat semuanya.') !!}
      </div>
    </section>
    @endif

    {{-- ===== Suara ===== --}}
    <section id="suara" data-help data-group="all" data-kw="suara mikrofon voice bicara perintah" class="{{ $sec }}">
      {!! $head('mic', 'Perintah suara', 'Pesan tanpa mengetik, cukup bicara.') !!}
      <ol class="hl-steps">
        <li>Pakai <b>Chrome atau Edge</b> dan pastikan internet aktif.</li>
        <li>Tekan tombol <b>mikrofon</b> di pojok kanan bawah, lalu izinkan akses mikrofon.</li>
        <li>Bicara seperti biasa. Tombol <b>?</b> di atasnya menampilkan daftar perintah lengkap.</li>
      </ol>
      @php
        $says = [
          'Di halaman kasir' => ['dua kopi susu dan satu croissant', 'hapus kopi susu', 'ubah kopi susu jadi tiga', 'dine in', 'takeaway', 'meja lima', 'pelanggan Budi', 'tanpa pelanggan', 'diskon lima ribu', 'bayar tunai', 'bayar QRIS', 'bayar kartu', 'uang pas', 'uang diterima lima puluh ribu', 'bayar', 'pesanan baru'],
          'Di semua halaman' => ['buka dashboard', 'buka kasir', 'buka produk', 'buka stok', 'buka pelanggan', 'buka laporan', 'mode gelap', 'mode terang', 'bantuan', 'berhenti mendengarkan'],
        ];
      @endphp
      <div class="mt-5 space-y-4">
        @foreach ($says as $title => $items)
          <div>
            <h3 class="{{ $sub }}">{{ $title }}</h3>
            <div class="flex flex-wrap gap-2">
              @foreach ($items as $i)<span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1.5 text-[13px] font-medium">“{{ $i }}”</span>@endforeach
            </div>
          </div>
        @endforeach
        <p class="{{ $muted }}">Setelah mengucap “bayar”, jawab <b>“ya”</b> untuk melanjutkan atau <b>“batal”</b> untuk membatalkan.</p>
      </div>
    </section>

    {{-- ===== Produk ===== --}}
    <section id="produk" data-help data-group="all" data-kw="produk menu harga foto kategori sku kode modal hpp stok menipis" class="{{ $sec }}">
      {!! $head('lunch_dining', 'Produk & menu', 'Daftar menu yang dijual di toko.') !!}
      <p class="{{ $body }} mb-4">Menu <b>Produk</b> menampilkan semua menu. Cari dengan nama atau kode produk, atau saring berdasarkan kategori. Kategori awal dibuat otomatis sesuai jenis usaha saat mendaftar.</p>
      @if ($isAdmin)
        <h3 class="{{ $sub }}">Menambah produk</h3>
        <ol class="hl-steps">
          <li>Tekan <b>Tambah Produk</b>.</li>
          <li>Isi <b>Nama</b>, <b>SKU</b> (kode unik produk, contoh MNU-001), dan <b>Kategori</b>.</li>
          <li>Isi <b>Harga jual</b> dan <b>Harga modal</b>. Harga modal dipakai untuk menghitung untung di laporan.</li>
          <li>Isi <b>Stok awal</b> dan <b>Batas stok menipis</b>. Saat stok mencapai angka ini, produk masuk daftar “Stok menipis”.</li>
          <li>Pilih <b>ikon</b> atau unggah <b>foto</b> (maksimal 2 MB).</li>
          <li>Tekan <b>Simpan</b>.</li>
        </ol>
        <div class="mt-5">{!! $note('tip', 'Di kolom <b>Aksi</b> ada tombol untuk mengatur stok, mengedit, dan menghapus. Produk yang <b>dinonaktifkan</b> tidak tampil di kasir.') !!}</div>
      @else
        {!! $note('info', 'Akun kasir hanya bisa <b>melihat</b> produk. Perubahan menu dan harga dilakukan oleh admin.') !!}
      @endif
    </section>

    {{-- ===== Pelanggan ===== --}}
    <section id="pelanggan" data-help data-group="all" data-kw="pelanggan member poin tier alergi catatan loyalty vip gold silver bronze" class="{{ $sec }}">
      {!! $head('group', 'Pelanggan & poin', 'Member, riwayat belanja, dan poin.') !!}
      <ol class="hl-steps">
        <li>Buka <b>Pelanggan</b> → <b>Tambah Pelanggan</b>. Isi nama (wajib), telepon/WhatsApp, email, dan tanggal lahir.</li>
        <li>Isi <b>Alergi</b> bila ada; ini menjadi pengingat untuk dapur. Kolom <b>Catatan</b> untuk kebiasaan atau preferensi pelanggan.</li>
        <li>Saat transaksi, pilih pelanggan tersebut di kasir.</li>
      </ol>
      <div class="mt-5 rounded-2xl bg-slate-100 p-4 space-y-2.5 text-sm">
        <p><b>Poin:</b> setiap belanja Rp {{ number_format($pointPer, 0, ',', '.') }} mendapat 1 poin.</p>
        <div><b>Tingkat member</b> naik otomatis sesuai total belanja (tidak turun otomatis):
          <div class="flex flex-wrap gap-2 mt-2">
            @foreach (collect($tiers)->sort() as $t => $min)<span class="pill pill-blue">{{ $t }} · mulai Rp {{ number_format($min, 0, ',', '.') }}</span>@endforeach
          </div>
        </div>
      </div>
      @unless ($isAdmin)<p class="{{ $muted }} mt-4">Kasir boleh menambah dan mengubah pelanggan. Menghapus pelanggan hanya bisa dilakukan admin.</p>@endunless
    </section>

    @if ($isAdmin)
    <div data-group-head="admin" class="flex items-center gap-3 pt-3"><h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5"><span class="material-symbols-outlined text-[16px]">lock</span> Khusus admin</h2><span class="h-px flex-1 bg-card-border"></span></div>

    {{-- ===== Stok ===== --}}
    <section id="stok" data-help data-group="admin" data-kw="stok barang masuk keluar rusak opname hitung supplier" class="{{ $sec }}">
      {!! $head('warehouse', 'Stok barang', 'Stok berkurang sendiri setiap ada penjualan.') !!}
      <p class="{{ $body }} mb-4">Untuk mengubah stok secara manual, buka <b>Stok Barang</b> → <b>Ubah Stok</b>, lalu pilih jenisnya:</p>
      <div class="grid sm:grid-cols-3 gap-3">
        <div class="rounded-2xl border border-card-border p-4"><div class="font-extrabold mb-1">Barang masuk (+)</div><p class="{{ $muted }}">Menambah stok, misalnya setelah belanja ke supplier.</p></div>
        <div class="rounded-2xl border border-card-border p-4"><div class="font-extrabold mb-1">Barang keluar / rusak (−)</div><p class="{{ $muted }}">Mengurangi stok untuk barang pecah atau kedaluwarsa.</p></div>
        <div class="rounded-2xl border border-card-border p-4"><div class="font-extrabold mb-1">Stok opname</div><p class="{{ $muted }}">Isi jumlah <b>sebenarnya</b> hasil hitung langsung; sistem menyesuaikan selisihnya.</p></div>
      </div>
      <div class="mt-5">{!! $note('tip', 'Isi kolom <b>Catatan</b> agar riwayat mudah ditelusuri. Riwayat juga mencatat siapa yang mengubah stok.') !!}</div>
    </section>

    {{-- ===== Laporan ===== --}}
    <section id="laporan" data-help data-group="admin" data-kw="laporan penjualan omzet untung laba export excel pdf void batal batalkan refund struk" class="{{ $sec }}">
      {!! $head('query_stats', 'Laporan & void', 'Lihat omzet, untung, dan batalkan transaksi bila perlu.') !!}
      <h3 class="{{ $sub }}">Melihat laporan</h3>
      <ol class="hl-steps">
        <li>Buka <b>Laporan Penjualan</b>.</li>
        <li>Pilih tanggal <b>Dari</b> dan <b>Sampai</b> (atau tombol <b>Hari ini</b>, <b>7 hari</b>, <b>Bulan ini</b>), lalu tekan <b>Terapkan</b>.</li>
        <li>Lihat ringkasan: pendapatan, jumlah transaksi, item terjual, laba kotor (untung sebelum biaya lain), grafik harian, dan produk terlaris.</li>
        <li>Simpan laporan lewat <b>Export PDF</b> atau <b>Export Excel</b>. Ikon struk di tiap transaksi membuka struk PDF-nya.</li>
      </ol>
      <h3 class="{{ $sub }} mt-6">Membatalkan transaksi (void)</h3>
      <ol class="hl-steps">
        <li>Di baris transaksi, tekan tombol <b>Void</b>.</li>
        <li>Tulis alasan pembatalan.</li>
        <li>Stok kembali otomatis dan transaksi pindah ke daftar <i>Transaksi dibatalkan</i>.</li>
      </ol>
      <div class="mt-5">{!! $note('warn', '<b>Penting:</b> void <b>tidak</b> mengembalikan uang pelanggan. Untuk pembayaran QRIS otomatis, pengembalian dana dilakukan sendiri di akun Midtrans toko.') !!}</div>
    </section>

    {{-- ===== Dashboard ===== --}}
    <section id="dashboard" data-help data-group="admin" data-kw="dashboard ringkasan grafik terlaris stok menipis staff bertugas pendapatan" class="{{ $sec }}">
      {!! $head('grid_view', 'Dashboard', 'Ringkasan kondisi toko hari ini.') !!}
      <ul class="grid sm:grid-cols-2 gap-x-6 gap-y-2 text-[15px]">
        @foreach (['Total pendapatan dan perbandingan dengan kemarin', 'Jumlah pesanan dan rata-rata belanja', 'Grafik pendapatan per jam', 'Menu terlaris', 'Pesanan terbaru', 'Daftar stok menipis', 'Staff yang sedang bertugas'] as $x)
          <li class="flex gap-2"><span class="material-symbols-outlined text-[18px] text-lime-700 dark:text-lime-accent shrink-0 mt-0.5">check_circle</span>{{ $x }}</li>
        @endforeach
      </ul>
      <div class="mt-5">{!! $note('tip', 'Cek bagian <b>Stok menipis</b> setiap pagi supaya tidak kehabisan menu saat ramai.') !!}</div>
    </section>

    {{-- ===== Staff ===== --}}
    <section id="staff" data-help data-group="admin" data-kw="staff karyawan kasir pin password akun tambah" class="{{ $sec }}">
      {!! $head('manage_accounts', 'Staff & PIN', 'Menambah akun admin atau kasir.') !!}
      <ol class="hl-steps">
        <li>Buka menu <b>Staff</b> dan tambah staff baru.</li>
        <li>Isi nama, email, <b>peran</b> (Kasir atau Admin), telepon, dan jam shift (contoh: 07:00 - 15:00).</li>
        <li>Buat <b>password</b> yang kuat. Untuk kasir, buat juga <b>PIN 6 angka</b> untuk login cepat.</li>
        <li>Beri tahu kasir <b>Kode Toko</b> (ada di Pengaturan) dan PIN-nya.</li>
      </ol>
      <div class="mt-5 space-y-3">
        {!! $note('info', 'PIN yang terlalu mudah ditebak (misalnya 123456 atau 111111) akan ditolak. PIN hanya berlaku untuk kasir.') !!}
        {!! $note('tip', 'Saat mengedit staff, kosongkan kolom password atau PIN kalau tidak ingin mengubahnya.') !!}
      </div>
    </section>

    {{-- ===== Audit ===== --}}
    <section id="audit" data-help data-group="admin" data-kw="audit log catatan aktivitas riwayat jejak keamanan" class="{{ $sec }}">
      {!! $head('policy', 'Catatan aktivitas', 'Rekam jejak: siapa melakukan apa dan kapan.') !!}
      <p class="{{ $body }}">Menu <b>Audit Log</b> mencatat kejadian penting seperti login, void, diskon, shift, perubahan stok, ekspor laporan, perubahan pengaturan, dan pembayaran QRIS. Gunakan pilihan di atas tabel untuk menyaring jenis aktivitas. Catatan ini <b>tidak bisa diubah atau dihapus</b>.</p>
      <div class="mt-4">{!! $note('warn', 'Bila ada uang QRIS yang sudah masuk tetapi pesanan gagal dibuat, statusnya <b>review</b> dan muncul di sini. Periksa kasus itu dan kembalikan dana lewat akun Midtrans bila perlu.') !!}</div>
    </section>

    {{-- ===== Konfigurasi ===== --}}
    <section id="konfigurasi" data-help data-group="admin" data-kw="pengaturan konfigurasi pajak struk cetak printer thermal nama toko alamat telepon shift qris manual gambar" class="{{ $sec }}">
      {!! $head('settings', 'Pengaturan toko', 'Semua ada di menu Pengaturan.') !!}
      <p class="{{ $muted }} mb-5">Buka <a href="{{ route('settings.edit') }}" class="font-bold underline">Pengaturan</a>. Tekan <kbd class="px-1.5 py-0.5 rounded-md bg-slate-100 text-[12px] font-mono">/</kbd> untuk langsung mencari pengaturan.</p>
      <div class="space-y-6">
        <div>
          <h3 class="{{ $sub }}">Data toko, pajak, dan struk</h3>
          <ol class="hl-steps">
            <li>Isi <b>Nama toko</b>, <b>Telepon</b>, dan <b>Lokasi</b>. Data ini tampil di struk.</li>
            <li>Atur <b>Tarif pajak (%)</b> dan <b>Nama pajak di struk</b>. Tarif saat ini: <b>{{ $taxPct }}%</b>. Isi 0 bila toko tidak memungut pajak.</li>
            <li>Tulis <b>Teks bawah struk</b> untuk ucapan terima kasih atau info promo.</li>
            <li>Tekan <b>Simpan</b>. Perubahan berlaku untuk transaksi berikutnya.</li>
          </ol>
          <h3 class="{{ $sub }}">Cetak struk thermal</h3>
          <p class="{{ $body }}">Aktif/nonaktif, cetak otomatis, dan lebar kertas diatur di kartu <b>Cetak struk (printer thermal)</b>. Langkah lengkap ada di bagian <a href="#thermal" class="font-bold underline">Cetak struk thermal</a>.</p>
        </div>
        <div>
          <h3 class="{{ $sub }}">Shift kasir</h3>
          <p class="{{ $body }}">Centang <i>Wajib buka shift sebelum menerima pembayaran</i> untuk mengaktifkan. Bila dimatikan, kasir langsung bisa bertransaksi dan menu Shift disembunyikan. Pengaturan ini tidak bisa dimatikan selama masih ada shift yang terbuka.</p>
        </div>
        <div>
          <h3 class="{{ $sub }}">QRIS manual</h3>
          <ol class="hl-steps">
            <li>Unggah gambar QRIS resmi toko (PNG, JPG, atau WEBP; maksimal 2 MB; minimal 200×200 piksel).</li>
            <li>Centang <b>Minta kode konfirmasi</b> bila setiap pembayaran QRIS harus disertai kode bukti.</li>
            <li>Tekan <b>Simpan QRIS manual</b>.</li>
          </ol>
        </div>
      </div>
    </section>

    {{-- ===== Midtrans ===== --}}
    <section id="midtrans" data-help data-group="admin" data-kw="midtrans qris otomatis dinamis server key akun gateway sandbox uji coba" class="{{ $sec }}">
      {!! $head('qr_code_2', 'QRIS otomatis (Midtrans)', 'Pembayaran terkonfirmasi sendiri; uang masuk ke akun Midtrans toko.') !!}
      <p class="{{ $body }} mb-4">Tiap toko memakai akun Midtrans sendiri. Kalau belum diatur, kasir memakai QRIS manual.</p>
      <ol class="hl-steps">
        <li>Buat akun di <b>Midtrans</b> lalu masuk ke dashboard-nya.</li>
        <li>Buka <b>Settings → Access Keys</b> dan salin <b>Server Key</b> (bukan Client Key).</li>
        <li>Di aplikasi ini, buka <b>Pengaturan → QRIS dinamis (Midtrans)</b>, tempel key tadi, lalu tekan <b>Simpan &amp; verifikasi</b>. Jika key salah, key tidak akan disimpan.</li>
        <li>Selesai. Saat kasir memilih QRIS, kode QR muncul otomatis.</li>
      </ol>
      <div class="mt-5 space-y-3">
        {!! $note('tip', 'Key yang diawali <b>SB-</b> adalah mode <b>uji coba</b> (tidak ada uang sungguhan). Cocok untuk latihan sebelum dipakai betulan.') !!}
        {!! $note('warn', '<b>Server Key itu rahasia.</b> Jangan dibagikan lewat chat atau media sosial. Key tidak bisa diganti atau dihapus selama masih ada QR yang menunggu dibayar.') !!}
      </div>
      <details class="hl-faq mt-4 rounded-2xl border border-card-border px-4 py-3">
        <summary class="cursor-pointer list-none flex items-center justify-between gap-3 font-bold text-sm">Pengaturan lanjutan (opsional)<span class="hl-chev material-symbols-outlined text-slate-400 transition">expand_more</span></summary>
        <p class="{{ $muted }} mt-2">Agar konfirmasi pembayaran lebih cepat, tempel <b>URL notifikasi</b> yang tampil di halaman Pengaturan ke akun Midtrans (menu Settings → Configuration → Payment Notification URL). Minta bantuan teknisi bila perlu. Tanpa ini pembayaran tetap terkonfirmasi otomatis, hanya beberapa detik lebih lambat.</p>
      </details>
    </section>
    @endif

    <div data-group-head="help" class="flex items-center gap-3 pt-3"><h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Bantuan</h2><span class="h-px flex-1 bg-card-border"></span></div>

    {{-- ===== Masalah ===== --}}
    <section id="masalah" data-help data-group="help" data-kw="masalah error gagal tidak bisa ditolak bantuan kendala" class="{{ $sec }}">
      {!! $head('build', 'Ada masalah?', 'Jawaban cepat untuk kendala yang sering terjadi.') !!}
      <div class="divide-y divide-card-border">
        @foreach (array_filter([
          ['Tombol Bayar tidak jalan atau pembayaran ditolak', 'Periksa: ' . ($shiftOn ? 'shift sudah dibuka, ' : '') . 'stok menu cukup, dan untuk pembayaran kartu, nomor referensi sudah diisi.'],
          ['Diskon ditolak', $isAdmin ? 'Diskon tidak boleh lebih besar dari subtotal.' : 'Diskon kasir dibatasi ' . $pct . '% dari subtotal. Minta admin bila perlu diskon lebih besar.'],
          ['Menu tidak muncul di kasir', 'Menu mungkin dinonaktifkan atau ada di kategori lain. Periksa tombol kategori dan kolom pencarian.'],
          ['QRIS otomatis tidak muncul', 'Toko belum memasang key Midtrans. ' . ($isAdmin ? 'Pasang di Pengaturan.' : 'Minta admin memasangnya.') . ' Sementara itu pakai QRIS manual.'],
          $isAdmin ? ['Key Midtrans ditolak saat disimpan', 'Pastikan yang disalin adalah Server Key (bukan Client Key) dan berasal dari akun Midtrans toko ini.'] : null,
          $isAdmin ? ['Key Midtrans tiba-tiba hilang', 'Isi ulang di Pengaturan. Hal ini bisa terjadi setelah ada perubahan pada server, jadi beri tahu teknisi yang mengelola aplikasi.'] : null,
          $shiftOn ? ['Shift tidak bisa ditutup', 'Masih ada pembayaran QRIS yang menunggu. Selesaikan atau batalkan dulu, lalu coba lagi.'] : null,
          ['Tombol Cetak Struk (Thermal) tidak ada', 'Fitur cetak thermal belum aktif. ' . ($isAdmin ? 'Aktifkan di Pengaturan.' : 'Minta admin mengaktifkannya di Pengaturan.') . ' Sementara itu pakai struk PDF.'],
          ['Struk tidak tercetak di printer thermal', 'Pastikan printer menyala, terhubung, dan dipilih di dialog cetak. Cek kertas tidak habis, lalu tekan Cetak Struk (Thermal) untuk mencoba lagi.'],
          ['Struk terpotong atau terlalu kecil saat dicetak', 'Samakan Paper size di dialog cetak dengan kertas printer, set Margins ke None, dan pastikan lebar kertas di Pengaturan sesuai (58 atau 80 mm).'],
          ['Perintah suara tidak merespons', 'Pakai Chrome atau Edge, izinkan akses mikrofon, dan pastikan internet aktif.'],
          ['Tidak bisa login dengan PIN', 'Pastikan Kode Toko benar dan akunnya berperan kasir. Terlalu sering salah akan dikunci sementara; tunggu beberapa menit.'],
          ['Masalah lain', 'Hubungi admin toko atau teknisi yang mengelola aplikasi.'],
        ]) as [$q, $a])
          <details class="hl-faq py-3.5">
            <summary class="cursor-pointer list-none flex items-center justify-between gap-3 font-bold text-[15px]">{{ $q }}<span class="hl-chev material-symbols-outlined text-slate-400 transition shrink-0">expand_more</span></summary>
            <p class="{{ $muted }} mt-2 leading-relaxed">{{ $a }}</p>
          </details>
        @endforeach
      </div>
    </section>

    <div data-help-static class="card p-5 text-sm flex items-start gap-3">
      <span class="material-symbols-outlined fill-1 text-slate-400">tips_and_updates</span>
      <p class="{{ $muted }}"><b class="text-slate-900 dark:text-white">Tips:</b> tombol <b>Mode Gelap</b> ada di sidebar. Di HP, buka menu lewat ikon ☰ atau tombol <b>Menu</b> di bagian bawah layar.</p>
    </div>
  </div>
</div>

@push('scripts')
<script>
(() => {
  const input = document.getElementById('helpSearch');
  const secs = [...document.querySelectorAll('[data-help]')];
  const heads = [...document.querySelectorAll('[data-group-head]')];
  const count = document.getElementById('helpCount'), empty = document.getElementById('helpEmpty'), tiles = document.getElementById('helpTiles');
  const links = [...document.querySelectorAll('[data-toc]')];
  const chipBar = document.getElementById('helpChips');
  const norm = (t) => (t || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  const text = new Map(secs.map((s) => [s, norm(s.textContent + ' ' + (s.dataset.kw || ''))]));

  function filter() {
    const terms = norm(input.value).split(/\s+/).filter(Boolean);
    let shown = 0;
    secs.forEach((s) => {
      const ok = terms.every((t) => text.get(s).includes(t));
      s.classList.toggle('hidden', !ok);
      if (ok) shown++;
    });
    heads.forEach((h) => {
      const g = h.dataset.groupHead;
      h.classList.toggle('hidden', !secs.some((s) => s.dataset.group === g && !s.classList.contains('hidden')));
    });
    links.forEach((a) => a.classList.toggle('hidden', document.getElementById(a.dataset.toc)?.classList.contains('hidden')));
    tiles.classList.toggle('hidden', terms.length > 0);
    empty.classList.toggle('hidden', shown > 0);
    count.classList.toggle('hidden', terms.length === 0 || shown === 0);
    count.textContent = shown + ' bagian ditemukan';
  }
  input.addEventListener('input', filter);
  input.addEventListener('keydown', (e) => { if (e.key === 'Escape') { input.value = ''; filter(); input.blur(); } });
  document.addEventListener('keydown', (e) => {
    if (e.key === '/' && !/input|textarea|select/i.test(document.activeElement?.tagName || '')) { e.preventDefault(); input.focus(); }
  });

  // tandai topik yang sedang dibaca (chip di HP/tablet, daftar isi di desktop)
  const ON = ['bg-lime-accent', 'text-slate-950', 'font-bold'];
  function mark(id) {
    links.forEach((a) => {
      const on = a.dataset.toc === id;
      if (a.classList.contains('chip')) {
        a.classList.toggle('chip-on', on);
        if (on && chipBar && chipBar.offsetParent !== null) chipBar.scrollTo({ left: a.offsetLeft - chipBar.clientWidth / 2 + a.clientWidth / 2, behavior: 'smooth' });
      } else {
        ON.forEach((c) => a.classList.toggle(c, on));
        a.classList.toggle('text-slate-600', !on);
      }
    });
  }
  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach((en) => { if (en.isIntersecting) mark(en.target.id); });
    }, { rootMargin: '-25% 0px -65% 0px' });
    secs.forEach((s) => io.observe(s));
  }
})();
</script>
@endpush
@endsection
