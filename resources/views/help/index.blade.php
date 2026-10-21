@extends('layouts.app')
@section('title', 'Panduan Penggunaan')
@section('content')
@php
  // Daftar isi: [id, ikon, judul, khusus admin?]
  $toc = [
    ['mulai',      'rocket_launch', 'Mulai cepat',                 false],
    ['peran',      'badge',         'Login & peran',               false],
    ['kasir',      'receipt_long',  'Cara transaksi (Order Line)', false],
    ['bayar',      'payments',      'Metode pembayaran',           false],
    ['shift',      'schedule',      'Shift kasir',                 false],
    ['suara',      'mic',           'Perintah suara',              false],
    ['produk',     'lunch_dining',  'Produk & menu',               false],
    ['pelanggan',  'group',         'Pelanggan & poin',            false],
    ['stok',       'warehouse',     'Stok barang',                 true],
    ['laporan',    'query_stats',   'Laporan & void',              true],
    ['dashboard',  'grid_view',     'Dashboard',                   true],
    ['staff',      'manage_accounts','Manajemen staff',            true],
    ['audit',      'policy',        'Audit log',                   true],
    ['konfigurasi','settings',      'Konfigurasi toko',            true],
    ['midtrans',   'qr_code_2',     'Setup QRIS Midtrans',         true],
    ['env',        'terminal',      'Konfigurasi server (.env)',   true],
    ['masalah',    'build',         'Pemecahan masalah',           false],
  ];
  $toc = collect($toc)->reject(fn ($t) => $t[3] && ! $isAdmin)->values();
  $h2 = 'text-xl font-extrabold tracking-tight flex items-center gap-2.5';
  $icoWrap = 'w-9 h-9 rounded-xl bg-lime-subtle text-slate-900 dark:bg-lime-accent/10 dark:text-lime-accent grid place-items-center shrink-0';
  $muted = 'text-sm text-slate-500 dark:text-slate-400';
  $ol = 'list-decimal pl-5 space-y-1.5 text-sm';
  $ul = 'list-disc pl-5 space-y-1.5 text-sm';
  $code = 'px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-white/10 text-[12px] font-mono';
@endphp

<div class="mb-6">
  <div class="text-xs font-semibold text-slate-500 mb-1">Dashboard › <span class="text-slate-900 dark:text-white">Panduan</span></div>
  <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Panduan Penggunaan</h1>
  <p class="{{ $muted }} mt-1">Dari cara login, transaksi, sampai konfigurasi toko. @unless ($isAdmin) Bagian konfigurasi khusus admin tidak ditampilkan untuk akun kasir. @endunless</p>
</div>

<div class="xl:grid xl:grid-cols-[250px_minmax(0,1fr)] xl:gap-8 items-start">

  {{-- ===== Daftar isi + pencarian ===== --}}
  <aside class="xl:sticky xl:top-6 mb-6 xl:mb-0 space-y-3">
    <div class="relative">
      <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" style="font-size:19px">search</span>
      <input id="helpSearch" type="search" class="input input-pill" placeholder="Cari panduan (mis. diskon, QRIS, void)…" autocomplete="off" aria-label="Cari panduan">
    </div>
    <p id="helpCount" class="hidden text-xs font-semibold text-slate-500 px-1"></p>
    <nav class="card p-2 max-h-[60vh] overflow-y-auto no-scrollbar" aria-label="Daftar isi panduan">
      @foreach ($toc as [$id, $icon, $title, $adm])
        <a href="#{{ $id }}" data-toc="{{ $id }}" class="flex items-center gap-2.5 px-3 h-10 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5 transition">
          <span class="material-symbols-outlined text-[19px]">{{ $icon }}</span>
          <span class="truncate">{{ $title }}</span>
          @if ($adm)<span class="ml-auto text-[10px] font-bold uppercase tracking-wide text-slate-400">admin</span>@endif
        </a>
      @endforeach
    </nav>
  </aside>

  <div class="space-y-5 min-w-0">
    <p id="helpEmpty" class="hidden card p-6 text-sm text-slate-500">Tidak ada bagian yang cocok. Coba kata lain, misalnya "diskon" atau "shift".</p>

    {{-- ===== 1. Mulai cepat ===== --}}
    <section id="mulai" data-help class="card p-5 sm:p-6 scroll-mt-6">
      <h2 class="{{ $h2 }}"><span class="{{ $icoWrap }}"><span class="material-symbols-outlined text-[20px]">rocket_launch</span></span> Mulai cepat</h2>
      @if ($isAdmin)
        <p class="{{ $muted }} mt-2 mb-3">Urutan yang disarankan untuk toko baru:</p>
        <ol class="{{ $ol }}">
          <li><b>Lengkapi data toko</b> di <b>Pengaturan</b>: nama, telepon, lokasi, tarif pajak, dan teks bawah struk.</li>
          <li><b>Tambah produk</b> di <b>Produk</b> → <i>Tambah Produk</i> (nama, SKU, harga jual, harga modal, stok, foto).</li>
          <li><b>Tambah staff kasir</b> di <b>Staff</b> dan buat PIN 6 digit untuk login cepat.</li>
          <li><b>Pilih metode QRIS</b>: unggah gambar QRIS toko (manual) atau pasang Server Key Midtrans (dinamis).</li>
          <li><b>Putuskan pakai shift atau tidak</b> di Pengaturan → Shift kasir.</li>
          <li><b>Coba satu transaksi</b> di <b>Order Line</b>, lalu cek hasilnya di <b>Laporan</b> dan <b>Stok</b>.</li>
        </ol>
      @else
        <p class="{{ $muted }} mt-2 mb-3">Alur kerja harian kasir:</p>
        <ol class="{{ $ol }}">
          <li>Login memakai <b>Kode Toko + PIN</b> (tab <i>PIN Kasir</i>).</li>
          @if ($shiftOn)<li>Buka <b>Shift Kasir</b> dan isi modal awal di laci.</li>@endif
          <li>Buka <b>Order Line</b>, ketuk menu, pilih jenis pesanan dan metode bayar, lalu tekan <b>Bayar</b>.</li>
          <li>Berikan struk ke pelanggan (cetak atau unduh PDF).</li>
          @if ($shiftOn)<li>Di akhir jaga, hitung uang di laci lalu <b>Tutup Shift</b>.</li>@endif
        </ol>
      @endif
    </section>

    {{-- ===== 2. Login & peran ===== --}}
    <section id="peran" data-help class="card p-5 sm:p-6 scroll-mt-6">
      <h2 class="{{ $h2 }}"><span class="{{ $icoWrap }}"><span class="material-symbols-outlined text-[20px]">badge</span></span> Login & peran</h2>
      <div class="grid md:grid-cols-2 gap-4 mt-3">
        <div>
          <h3 class="font-bold text-sm mb-1.5">Admin / Manager</h3>
          <p class="{{ $muted }}">Login di tab <b>Admin / Manager</b> dengan email dan password. Akses ke semua menu.</p>
        </div>
        <div>
          <h3 class="font-bold text-sm mb-1.5">Kasir</h3>
          <p class="{{ $muted }}">Login cepat di tab <b>PIN Kasir</b> memakai <b>Kode Toko</b> + PIN 6 digit.@if ($storeCode) Kode toko ini: <b>{{ $storeCode }}</b>.@endif Kasir juga bisa login dengan email dan password.</p>
        </div>
      </div>
      <div class="overflow-x-auto mt-4">
        <table class="w-full text-sm">
          <thead><tr class="text-left text-xs uppercase tracking-wide text-slate-400"><th class="py-2 pr-4">Menu</th><th class="py-2 px-3 text-center">Admin</th><th class="py-2 px-3 text-center">Kasir</th></tr></thead>
          <tbody class="divide-y divide-card-border">
            @foreach ([
              ['Order Line, Shift Kasir, Kantor Virtual, Panduan', '✅', '✅'],
              ['Lihat Produk · Tambah/ubah Pelanggan', '✅', '✅'],
              ['Dashboard, Laporan, Stok, Staff, Pengaturan, Audit Log', '✅', '—'],
              ['Tambah/ubah/hapus produk · Hapus pelanggan · Void transaksi', '✅', '—'],
              ['Diskon manual', 'Tanpa batas', 'Maks. '.rtrim(rtrim(number_format($cashierMaxDiscount, 2), '0'), '.').'% dari subtotal'],
            ] as [$m, $a, $k])
              <tr><td class="py-2.5 pr-4">{{ $m }}</td><td class="py-2.5 px-3 text-center">{{ $a }}</td><td class="py-2.5 px-3 text-center">{{ $k }}</td></tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <p class="{{ $muted }} mt-3">Login dibatasi percobaannya. Bila salah terlalu sering, tunggu sebentar sebelum mencoba lagi.</p>
    </section>

    {{-- ===== 3. Transaksi ===== --}}
    <section id="kasir" data-help class="card p-5 sm:p-6 scroll-mt-6">
      <h2 class="{{ $h2 }}"><span class="{{ $icoWrap }}"><span class="material-symbols-outlined text-[20px]">receipt_long</span></span> Cara transaksi (Order Line)</h2>
      <ol class="{{ $ol }} mt-3">
        <li>Buka menu <b>Order Line</b>. @if ($shiftOn)Bila muncul peringatan <i>Shift kasir belum dibuka</i>, tekan <b>Buka shift</b> dulu.@endif</li>
        <li><b>Pilih menu</b>: ketuk kartu menu untuk menambah. Gunakan kolom <i>Cari menu…</i> atau chip kategori untuk menyaring. Menu bertanda <b>Habis</b> tidak bisa dipilih.</li>
        <li><b>Atur jumlah</b> dengan tombol + / − di keranjang. Kurangi sampai 0 untuk menghapus item.</li>
        <li><b>Pilih jenis pesanan</b>: <i>Dine-in</i> (isi nomor meja) atau <i>Takeaway</i>.</li>
        <li><b>Pilih pelanggan</b> (opsional) lewat tombol <i>Pelanggan</i>: cari berdasarkan nama, telepon, atau kode. Pelanggan baru bisa ditambah langsung. Tanpa pilihan, transaksi dicatat sebagai <i>Walk-in</i>.</li>
        <li><b>Isi diskon</b> (opsional, dalam Rupiah). @unless ($isAdmin) Batas diskon kasir {{ rtrim(rtrim(number_format($cashierMaxDiscount, 2), '0'), '.') }}% dari subtotal.@endunless</li>
        <li>Periksa <b>Ringkasan Pembayaran</b>: subtotal − diskon + pajak ({{ $taxPct }}%) = total.</li>
        <li>Pilih <b>metode bayar</b> (lihat bagian berikutnya), lalu tekan <b>Bayar</b>.</li>
        <li>Setelah muncul <i>Pembayaran Berhasil</i>: pilih <b>Lihat / Cetak Struk</b>, <b>Unduh Struk PDF</b>, atau <b>Pesanan Baru</b>.</li>
      </ol>
      <div class="mt-4 rounded-2xl bg-lime-subtle dark:bg-lime-accent/10 px-4 py-3 text-sm">
        <b>Catatan:</b> harga selalu diambil dari database, bukan dari browser. Klik berulang pada tombol Bayar tidak membuat transaksi ganda. Bila stok tidak cukup, pembayaran ditolak.
      </div>
      <p class="{{ $muted }} mt-3">Di HP, ketuk bar keranjang di bawah layar untuk membuka keranjang.</p>
    </section>

    {{-- ===== 4. Metode bayar ===== --}}
    <section id="bayar" data-help class="card p-5 sm:p-6 scroll-mt-6">
      <h2 class="{{ $h2 }}"><span class="{{ $icoWrap }}"><span class="material-symbols-outlined text-[20px]">payments</span></span> Metode pembayaran</h2>
      <div class="grid md:grid-cols-2 gap-4 mt-3 text-sm">
        <div class="rounded-2xl border border-card-border p-4">
          <h3 class="font-bold mb-1.5">Tunai</h3>
          <p class="{{ $muted }}">Isi <i>Uang diterima</i>; kembalian dihitung otomatis. Ucapkan "uang pas" bila memakai perintah suara.</p>
        </div>
        <div class="rounded-2xl border border-card-border p-4">
          <h3 class="font-bold mb-1.5">Kartu</h3>
          <p class="{{ $muted }}">Proses di mesin EDC, lalu isi <b>No. referensi / approval code</b> dari bukti. Kolom ini selalu wajib untuk kartu.</p>
        </div>
        <div class="rounded-2xl border border-card-border p-4">
          <h3 class="font-bold mb-1.5">QRIS dinamis (Midtrans)</h3>
          <p class="{{ $muted }}">Bila admin sudah memasang Server Key, tekan <b>Bayar</b> untuk membuat QR dengan nominal terkunci dan masa berlaku 15 menit. Minta pelanggan scan; jendela berubah otomatis saat lunas. Tersedia <i>Buat ulang QR</i> dan <i>Batalkan pembayaran</i>. Pesanan baru tercatat setelah pembayaran terkonfirmasi.</p>
        </div>
        <div class="rounded-2xl border border-card-border p-4">
          <h3 class="font-bold mb-1.5">QRIS manual</h3>
          <p class="{{ $muted }}">Dipakai bila Midtrans belum aktif. Gambar QRIS toko tampil di jendela pembayaran. Setelah uang masuk, tekan <b>Konfirmasi Pembayaran</b>. Kode konfirmasi wajib diisi bila admin mengaktifkannya, atau bila toko belum mengunggah gambar QRIS.</p>
        </div>
      </div>
    </section>

    {{-- ===== 5. Shift ===== --}}
    <section id="shift" data-help class="card p-5 sm:p-6 scroll-mt-6">
      <h2 class="{{ $h2 }}"><span class="{{ $icoWrap }}"><span class="material-symbols-outlined text-[20px]">schedule</span></span> Shift kasir</h2>
      @if (! $shiftOn)
        <p class="{{ $muted }} mt-2">Fitur shift sedang <b>nonaktif</b> di toko ini: kasir langsung bisa bertransaksi dan menu Shift disembunyikan. @if ($isAdmin) Aktifkan lewat Pengaturan → Shift kasir. @endif Bagian di bawah berlaku bila fitur diaktifkan.</p>
      @else
        <p class="{{ $muted }} mt-2">Shift mencatat modal awal dan menghitung selisih kas saat kasir selesai bertugas. Pembayaran ditolak selama shift belum dibuka.</p>
      @endif
      <div class="grid md:grid-cols-2 gap-4 mt-3">
        <div>
          <h3 class="font-bold text-sm mb-1.5">Membuka shift</h3>
          <ol class="{{ $ol }}">
            <li>Buka menu <b>Shift Kasir</b>.</li>
            <li>Isi <i>Modal awal di laci (Rp)</i> dan catatan bila perlu.</li>
            <li>Tekan <b>Buka Shift</b>. Hanya satu shift terbuka per kasir.</li>
          </ol>
        </div>
        <div>
          <h3 class="font-bold text-sm mb-1.5">Menutup shift</h3>
          <ol class="{{ $ol }}">
            <li>Hitung uang fisik di laci.</li>
            <li>Isi <i>Uang di laci (hitung fisik)</i>, lalu tekan <b>Tutup Shift</b> dan konfirmasi.</li>
            <li>Sistem mencatat <b>selisih</b> antara uang yang dihitung dan yang seharusnya. Riwayat ada di tabel <i>Riwayat shift</i>.</li>
          </ol>
        </div>
      </div>
      <p class="{{ $muted }} mt-3">Shift tidak bisa ditutup selama masih ada pembayaran QRIS yang menunggu. Kasir hanya melihat shift miliknya; admin melihat semua.</p>
    </section>

    {{-- ===== 6. Suara ===== --}}
    <section id="suara" data-help class="card p-5 sm:p-6 scroll-mt-6">
      <h2 class="{{ $h2 }}"><span class="{{ $icoWrap }}"><span class="material-symbols-outlined text-[20px]">mic</span></span> Perintah suara</h2>
      <p class="{{ $muted }} mt-2">Tekan tombol mikrofon di pojok kanan bawah, lalu bicara biasa. Gunakan <b>Chrome atau Edge</b> dengan internet aktif. Tombol <b>?</b> di atasnya menampilkan daftar lengkap perintah.</p>
      <div class="grid md:grid-cols-2 gap-4 mt-3 text-sm">
        <div>
          <h3 class="font-bold mb-1.5">Di halaman kasir</h3>
          <ul class="{{ $ul }}">
            <li>"dua kopi susu dan satu croissant"</li>
            <li>"hapus kopi susu" · "ubah kopi susu jadi tiga"</li>
            <li>"dine in" · "takeaway" · "meja lima"</li>
            <li>"pelanggan Budi" · "tanpa pelanggan"</li>
            <li>"diskon lima ribu"</li>
            <li>"bayar tunai" · "bayar QRIS" · "bayar kartu"</li>
            <li>"uang diterima lima puluh ribu" · "uang pas"</li>
            <li>"bayar", lalu jawab "ya" atau "batal"</li>
            <li>"pesanan baru" (setelah berhasil)</li>
          </ul>
        </div>
        <div>
          <h3 class="font-bold mb-1.5">Di semua halaman</h3>
          <ul class="{{ $ul }}">
            <li>"buka dashboard / kasir / produk / stok / pelanggan / laporan / staff / kantor"</li>
            <li>"mode gelap" · "mode terang"</li>
            <li>"bantuan" · "berhenti mendengarkan"</li>
          </ul>
        </div>
      </div>
    </section>

    {{-- ===== 7. Produk ===== --}}
    <section id="produk" data-help class="card p-5 sm:p-6 scroll-mt-6">
      <h2 class="{{ $h2 }}"><span class="{{ $icoWrap }}"><span class="material-symbols-outlined text-[20px]">lunch_dining</span></span> Produk & menu</h2>
      <p class="{{ $muted }} mt-2">Menu <b>Produk</b> menampilkan daftar menu, dengan pencarian nama/SKU dan filter kategori. Kategori awal dibuat otomatis dari jenis usaha saat pendaftaran.</p>
      @if ($isAdmin)
        <h3 class="font-bold text-sm mt-4 mb-1.5">Menambah produk</h3>
        <ol class="{{ $ol }}">
          <li>Tekan <b>Tambah Produk</b>.</li>
          <li>Isi <b>Nama</b>, <b>SKU</b> (kode unik, mis. MNU-001), dan <b>Kategori</b>.</li>
          <li>Isi <b>Harga jual</b> dan <b>Harga modal (HPP)</b>. HPP dipakai untuk menghitung laba kotor di laporan.</li>
          <li>Isi <b>Stok awal</b> dan <b>Batas stok menipis</b>; produk muncul di daftar "Stok menipis" saat stok mencapai batas ini.</li>
          <li>Pilih <b>ikon</b> atau unggah <b>foto</b> (maks. 2 MB); ikon dipakai bila tidak ada foto.</li>
          <li>Tekan <b>Simpan</b>.</li>
        </ol>
        <p class="{{ $muted }} mt-3">Ikon di kolom Aksi: atur stok, edit, dan hapus. Produk nonaktif tidak tampil di kasir.</p>
      @else
        <p class="{{ $muted }} mt-2">Akun kasir hanya dapat melihat produk. Perubahan menu dan harga dilakukan admin.</p>
      @endif
    </section>

    {{-- ===== 8. Pelanggan ===== --}}
    <section id="pelanggan" data-help class="card p-5 sm:p-6 scroll-mt-6">
      <h2 class="{{ $h2 }}"><span class="{{ $icoWrap }}"><span class="material-symbols-outlined text-[20px]">group</span></span> Pelanggan & poin</h2>
      <ol class="{{ $ol }} mt-3">
        <li>Buka <b>Pelanggan</b> → <b>Tambah Pelanggan</b>, isi nama (wajib), telepon/WhatsApp, email, tanggal lahir, dan tier.</li>
        <li>Isi <b>Alergi</b> bila ada; ini menjadi peringatan untuk dapur. Kolom <b>Catatan</b> untuk preferensi pelanggan.</li>
        <li>Pilih pelanggan di kasir agar belanjanya tercatat dan poinnya bertambah.</li>
      </ol>
      <div class="mt-4 text-sm space-y-1.5">
        <p><b>Poin:</b> 1 poin untuk setiap belanja Rp {{ number_format($pointPer, 0, ',', '.') }}.</p>
        <p><b>Tier naik otomatis</b> berdasarkan total belanja (tidak turun otomatis):
          @foreach (collect($tiers)->sort() as $t => $min) <span class="chip mr-1">{{ strtoupper($t) }} ≥ Rp {{ number_format($min, 0, ',', '.') }}</span>@endforeach</p>
      </div>
      @unless ($isAdmin)<p class="{{ $muted }} mt-3">Kasir dapat menambah dan mengubah pelanggan. Menghapus pelanggan hanya bisa dilakukan admin.</p>@endunless
    </section>

    @if ($isAdmin)
    {{-- ===== 9. Stok ===== --}}
    <section id="stok" data-help class="card p-5 sm:p-6 scroll-mt-6">
      <h2 class="{{ $h2 }}"><span class="{{ $icoWrap }}"><span class="material-symbols-outlined text-[20px]">warehouse</span></span> Stok barang</h2>
      <p class="{{ $muted }} mt-2">Stok berkurang otomatis setiap penjualan. Ubah manual lewat <b>Stok Barang</b> → <i>Ubah Stok</i>:</p>
      <ul class="{{ $ul }} mt-2">
        <li><b>Barang masuk (+)</b>: tambah stok, mis. belanja dari supplier.</li>
        <li><b>Barang keluar / rusak (−)</b>: kurangi stok untuk barang pecah atau kedaluwarsa.</li>
        <li><b>Stok opname</b>: isi jumlah sebenarnya hasil hitung fisik; sistem menyesuaikan selisihnya.</li>
      </ul>
      <p class="{{ $muted }} mt-3">Isi <i>Catatan</i> agar riwayat mudah ditelusuri. Tabel riwayat bisa disaring per produk dan jenis, dan mencatat siapa yang mengubah.</p>
    </section>

    {{-- ===== 10. Laporan ===== --}}
    <section id="laporan" data-help class="card p-5 sm:p-6 scroll-mt-6">
      <h2 class="{{ $h2 }}"><span class="{{ $icoWrap }}"><span class="material-symbols-outlined text-[20px]">query_stats</span></span> Laporan & void</h2>
      <ol class="{{ $ol }} mt-3">
        <li>Buka <b>Laporan Penjualan</b>, pilih rentang <i>Dari</i> – <i>Sampai</i> dan metode pembayaran, lalu <b>Terapkan</b>.</li>
        <li>Laporan memuat pendapatan harian, metode bayar, produk terlaris, laba kotor, dan daftar transaksi.</li>
        <li>Ekspor ke <b>PDF</b> atau <b>Excel</b> dari tombol ekspor di halaman laporan.</li>
        <li>Ikon struk pada baris transaksi membuka struk PDF.</li>
      </ol>
      <h3 class="font-bold text-sm mt-4 mb-1.5">Void (membatalkan transaksi)</h3>
      <ol class="{{ $ol }}">
        <li>Pada baris transaksi, tekan ikon <b>Void</b> dan isi alasannya.</li>
        <li>Stok dikembalikan, dan transaksi pindah ke daftar <i>Transaksi dibatalkan (void)</i>.</li>
      </ol>
      <div class="mt-4 rounded-2xl bg-amber-50 dark:bg-amber-400/10 text-amber-900 dark:text-amber-200 px-4 py-3 text-sm">
        <b>Penting:</b> void <b>tidak</b> mengembalikan uang QRIS otomatis. Refund dilakukan manual lewat dashboard Midtrans.
      </div>
    </section>

    {{-- ===== 11. Dashboard ===== --}}
    <section id="dashboard" data-help class="card p-5 sm:p-6 scroll-mt-6">
      <h2 class="{{ $h2 }}"><span class="{{ $icoWrap }}"><span class="material-symbols-outlined text-[20px]">grid_view</span></span> Dashboard</h2>
      <p class="{{ $muted }} mt-2">Ringkasan cepat kondisi toko: pendapatan, jumlah pesanan, rata-rata transaksi, grafik pendapatan per jam, menu terlaris, pesanan terbaru, daftar <b>stok menipis</b>, dan staff yang bertugas. Cek bagian stok menipis setiap hari agar tidak kehabisan menu.</p>
    </section>

    {{-- ===== 12. Staff ===== --}}
    <section id="staff" data-help class="card p-5 sm:p-6 scroll-mt-6">
      <h2 class="{{ $h2 }}"><span class="{{ $icoWrap }}"><span class="material-symbols-outlined text-[20px]">manage_accounts</span></span> Manajemen staff</h2>
      <ol class="{{ $ol }} mt-3">
        <li>Buka <b>Staff</b> → tambah staff baru.</li>
        <li>Isi nama, email, <b>peran</b> (Kasir / Admin), telepon, dan jam shift (mis. 07:00 - 15:00).</li>
        <li>Buat <b>password</b> yang kuat. Untuk kasir, buat juga <b>PIN 6 digit</b> untuk login cepat.</li>
        <li>Bagikan <b>Kode Toko</b> (ada di Pengaturan) dan PIN kepada kasir.</li>
      </ol>
      <p class="{{ $muted }} mt-3">PIN ditolak bila terlalu mudah ditebak (mis. 123456 atau 111111). Login PIN hanya berlaku untuk kasir. Kosongkan kolom password/PIN saat edit bila tidak ingin mengubahnya.</p>
    </section>

    {{-- ===== 13. Audit ===== --}}
    <section id="audit" data-help class="card p-5 sm:p-6 scroll-mt-6">
      <h2 class="{{ $h2 }}"><span class="{{ $icoWrap }}"><span class="material-symbols-outlined text-[20px]">policy</span></span> Audit log</h2>
      <p class="{{ $muted }} mt-2">Mencatat aktivitas penting: login, void, diskon, shift, perubahan stok, ekspor, perubahan pengaturan, dan pembayaran QRIS. Saring berdasarkan jenis aktivitas; arahkan kursor ke kolom Detail untuk melihat rinciannya. Catatan tidak bisa diubah atau dihapus dari aplikasi. Bila ada uang QRIS masuk tetapi pesanan gagal dibuat, statusnya <i>review</i> dan terlihat di sini.</p>
    </section>

    {{-- ===== 14. Konfigurasi ===== --}}
    <section id="konfigurasi" data-help class="card p-5 sm:p-6 scroll-mt-6">
      <h2 class="{{ $h2 }}"><span class="{{ $icoWrap }}"><span class="material-symbols-outlined text-[20px]">settings</span></span> Konfigurasi toko</h2>
      <p class="{{ $muted }} mt-2">Semua ada di <a href="{{ route('settings.edit') }}" class="font-bold underline">Pengaturan</a>. Gunakan kolom pencarian di sana (atau tekan <kbd class="{{ $code }}">/</kbd>) untuk menemukan pengaturan.</p>
      <div class="space-y-4 mt-4 text-sm">
        <div>
          <h3 class="font-bold mb-1.5">Info toko, pajak, dan struk</h3>
          <ul class="{{ $ul }}">
            <li>Isi <i>Nama toko</i>, <i>Telepon</i>, dan <i>Lokasi</i>; data ini tampil di struk.</li>
            <li><i>Tarif pajak (%)</i> dan <i>Nama pajak di struk</i> (mis. Pajak (PB1)). Saat ini tarif: <b>{{ $taxPct }}%</b>. Isi 0 bila toko tidak memungut pajak.</li>
            <li><i>Teks bawah struk</i> untuk ucapan terima kasih atau info promo.</li>
            <li>Tekan <b>Simpan</b>. Perubahan berlaku untuk transaksi berikutnya.</li>
          </ul>
        </div>
        <div>
          <h3 class="font-bold mb-1.5">Shift kasir</h3>
          <p class="{{ $muted }}">Centang <i>Wajib buka shift sebelum menerima pembayaran</i> untuk mengaktifkan. Bila dimatikan, kasir langsung bisa bertransaksi dan menu Shift disembunyikan. Tidak bisa dinonaktifkan selama masih ada shift terbuka.</p>
        </div>
        <div>
          <h3 class="font-bold mb-1.5">QRIS manual</h3>
          <ol class="{{ $ol }}">
            <li>Unggah gambar QRIS resmi toko (PNG/JPG/WEBP, maks. 2 MB, minimal 200×200 piksel).</li>
            <li>Centang <i>Minta kode konfirmasi</i> bila ingin setiap pembayaran QRIS disertai kode bukti.</li>
            <li>Tekan <b>Simpan QRIS manual</b>.</li>
          </ol>
        </div>
      </div>
    </section>

    {{-- ===== 15. Midtrans ===== --}}
    <section id="midtrans" data-help class="card p-5 sm:p-6 scroll-mt-6">
      <h2 class="{{ $h2 }}"><span class="{{ $icoWrap }}"><span class="material-symbols-outlined text-[20px]">qr_code_2</span></span> Setup QRIS dinamis (Midtrans)</h2>
      <p class="{{ $muted }} mt-2">Tiap bisnis memakai akun Midtrans sendiri, jadi uang masuk langsung ke rekening bisnis itu. Bisnis tanpa key otomatis memakai QRIS manual.</p>
      <ol class="{{ $ol }} mt-3">
        <li>Daftar atau masuk di Midtrans (uji coba: dashboard sandbox).</li>
        <li>Buka <b>Settings → Access Keys</b> dan salin <b>Server Key</b> (bukan Client Key). Awalan <span class="{{ $code }}">SB-Mid-server-</span> = sandbox; tanpa <span class="{{ $code }}">SB-</span> = produksi.</li>
        <li>Di aplikasi, buka <b>Pengaturan → QRIS dinamis (Midtrans)</b>, tempel key, lalu tekan <b>Simpan &amp; verifikasi</b>. Key diuji dulu ke Midtrans; bila ditolak, tidak disimpan.</li>
        <li><i>(Opsional)</i> Tempel <b>URL notifikasi</b> yang tampil di halaman Pengaturan ke Midtrans → Settings → Configuration → <b>Payment Notification URL</b> agar konfirmasi instan. Wajib HTTPS dan publik. Tanpa ini, status tetap dicek otomatis saat QR tampil.</li>
        <li><b>Uji sandbox:</b> buat transaksi QRIS di kasir, lalu bayar lewat simulator QRIS Midtrans memakai URL QR yang tampil di jendela pembayaran.</li>
      </ol>
      <ul class="{{ $ul }} mt-3">
        <li>Key disimpan terenkripsi dan tidak pernah ditampilkan lagi (hanya 4 karakter terakhir).</li>
        <li>Mengganti atau menghapus key ditolak selama ada QR yang masih menunggu pembayaran.</li>
        <li>Jangan bagikan Server Key lewat chat atau Git.</li>
      </ul>
    </section>

    {{-- ===== 16. .env ===== --}}
    <section id="env" data-help class="card p-5 sm:p-6 scroll-mt-6">
      <h2 class="{{ $h2 }}"><span class="{{ $icoWrap }}"><span class="material-symbols-outlined text-[20px]">terminal</span></span> Konfigurasi server (.env)</h2>
      <p class="{{ $muted }} mt-2">Untuk pengelola server. Setelah mengubah <span class="{{ $code }}">.env</span>, jalankan <span class="{{ $code }}">php artisan config:clear</span>.</p>
      <div class="overflow-x-auto mt-3">
        <table class="w-full text-sm">
          <thead><tr class="text-left text-xs uppercase tracking-wide text-slate-400"><th class="py-2 pr-4">Variabel</th><th class="py-2 pr-4">Default</th><th class="py-2">Fungsi</th></tr></thead>
          <tbody class="divide-y divide-card-border align-top">
            @foreach ([
              ['POS_STORE_NAME / _ADDRESS / _PHONE', 'contoh', 'Data toko default (bisnis baru memakai nama sendiri)'],
              ['POS_TAX_RATE', '0.10', 'Tarif pajak default (10%); bisa diubah per bisnis di Pengaturan'],
              ['POS_TAX_LABEL', 'Pajak (PB1)', 'Nama pajak di struk'],
              ['POS_CASHIER_MAX_DISCOUNT_PCT', '10', 'Batas diskon manual kasir (% subtotal); admin tanpa batas'],
              ['POS_POINT_VALUE', '100', 'Nilai Rp per 1 poin saat ditukar'],
              ['POS_ENFORCE_TRIAL', 'false', 'Kunci akun bisnis saat masa trial (14 hari) habis'],
              ['POS_CSP_MODE', 'report', 'report, enforce, atau off untuk Content-Security-Policy'],
              ['POS_RECEIPT_FOOTER', 'Terima kasih…', 'Teks bawah struk default'],
              ['MIDTRANS_QRIS_ACQUIRER', 'gopay', 'Acquirer QRIS Midtrans (opsional)'],
              ['MIDTRANS_NOTIFY_URL', 'kosong', 'Override URL webhook per transaksi (opsional)'],
            ] as [$v, $d, $f])
              <tr><td class="py-2.5 pr-4"><span class="{{ $code }}">{{ $v }}</span></td><td class="py-2.5 pr-4">{{ $d }}</td><td class="py-2.5">{{ $f }}</td></tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <p class="{{ $muted }} mt-3">Server Key Midtrans <b>tidak</b> diisi di <span class="{{ $code }}">.env</span>; tiap bisnis mengisinya di Pengaturan. Jangan mengganti <span class="{{ $code }}">APP_KEY</span> setelah key tersimpan, karena key terenkripsi memakainya. Untuk produksi: pakai HTTPS dan <span class="{{ $code }}">APP_DEBUG=false</span>.</p>
    </section>
    @endif

    {{-- ===== 17. Masalah ===== --}}
    <section id="masalah" data-help class="card p-5 sm:p-6 scroll-mt-6">
      <h2 class="{{ $h2 }}"><span class="{{ $icoWrap }}"><span class="material-symbols-outlined text-[20px]">build</span></span> Pemecahan masalah</h2>
      <div class="divide-y divide-card-border mt-2 text-sm">
        @foreach (array_filter([
          ['Pembayaran ditolak / tombol Bayar tidak jalan', ($shiftOn ? 'Pastikan shift sudah dibuka. ' : '') . 'Cek juga stok menu cukup dan, untuk kartu, nomor referensi sudah diisi.'],
          ['Diskon ditolak', $isAdmin ? 'Diskon tidak boleh melebihi subtotal.' : 'Diskon kasir dibatasi ' . rtrim(rtrim(number_format($cashierMaxDiscount, 2), '0'), '.') . '% dari subtotal. Minta admin bila perlu diskon lebih besar.'],
          ['Menu tidak muncul di kasir', 'Menu mungkin nonaktif atau berada di kategori lain. Cek filter kategori dan pencarian.'],
          ['QRIS dinamis tidak muncul', 'Server Key bisnis belum diisi. Tanpa key, kasir memakai QRIS manual.'],
          $isAdmin ? ['Key Midtrans ditolak saat disimpan', 'Pastikan yang disalin adalah Server Key (bukan Client Key) dan akunnya sesuai mode sandbox/produksi.'] : null,
          $isAdmin ? ['Key Midtrans hilang', 'Terjadi bila APP_KEY diganti. Isi ulang key di Pengaturan.'] : null,
          ['Perintah suara tidak merespons', 'Pakai Chrome atau Edge, izinkan akses mikrofon, dan pastikan internet aktif.'],
          ['Tidak bisa login dengan PIN', 'Pastikan Kode Toko benar dan akun berperan kasir. Percobaan berulang yang salah akan dibatasi sementara.'],
        ]) as [$q, $a])
          <details class="py-3 group">
            <summary class="cursor-pointer font-bold flex items-center justify-between gap-3 list-none">{{ $q }}<span class="material-symbols-outlined text-slate-400 group-open:rotate-180 transition">expand_more</span></summary>
            <p class="{{ $muted }} mt-2">{{ $a }}</p>
          </details>
        @endforeach
      </div>
    </section>

    <div class="card p-5 sm:p-6 text-sm flex items-start gap-3" data-help-static>
      <span class="material-symbols-outlined text-slate-400">tips_and_updates</span>
      <div class="{{ $muted }}">
        <b class="text-slate-900 dark:text-white">Tips:</b> tombol <i>Mode Gelap</i> ada di sidebar. Menu sidebar bisa dilipat per grup. Di layar kecil, buka menu lewat ikon ☰ atau tombol <i>Menu</i> di bawah.
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
(() => {
  const input = document.getElementById('helpSearch');
  const secs = [...document.querySelectorAll('[data-help]')];
  const count = document.getElementById('helpCount'), empty = document.getElementById('helpEmpty');
  const norm = (t) => (t || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  const text = new Map(secs.map((s) => [s, norm(s.textContent)]));
  const tocLinks = [...document.querySelectorAll('[data-toc]')];

  function filter() {
    const terms = norm(input.value).split(/\s+/).filter(Boolean);
    let shown = 0;
    secs.forEach((s) => {
      const ok = terms.every((t) => text.get(s).includes(t));
      s.classList.toggle('hidden', !ok);
      if (ok) shown++;
    });
    tocLinks.forEach((a) => a.classList.toggle('hidden', document.getElementById(a.dataset.toc)?.classList.contains('hidden')));
    empty.classList.toggle('hidden', shown > 0);
    count.classList.toggle('hidden', terms.length === 0 || shown === 0);
    count.textContent = shown + ' bagian ditemukan';
  }
  input.addEventListener('input', filter);
  document.addEventListener('keydown', (e) => {
    if (e.key === '/' && !/input|textarea|select/i.test(document.activeElement?.tagName || '')) { e.preventDefault(); input.focus(); }
  });

  // tandai bagian yang sedang dibaca di daftar isi
  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach((en) => {
        if (!en.isIntersecting) return;
        tocLinks.forEach((a) => a.classList.toggle('bg-lime-subtle', a.dataset.toc === en.target.id));
      });
    }, { rootMargin: '-20% 0px -70% 0px' });
    secs.forEach((s) => io.observe(s));
  }
})();
</script>
@endpush
@endsection
