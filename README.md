# CashierStore POS

Aplikasi kasir (Point of Sale) multi-bisnis berbasis **Laravel**, dengan manajemen produk, stok, pelanggan loyalty, shift kasir, laporan penjualan, audit log, dan pembayaran **QRIS dinamis (Midtrans)**. Tampilan memakai desain *Luminescent Fintech* (sidebar gelap, aksen lime, mode terang/gelap) dan responsif untuk desktop, tablet, dan mobile.

## Fitur

**Kasir**
- Order Line: pilih menu, dine-in / takeaway, nomor meja, pelanggan member, diskon.
- Metode bayar: tunai, QRIS, kartu. QRIS dinamis otomatis terkonfirmasi lewat Midtrans.
- Struk PDF, perintah suara (bahasa Indonesia) untuk menambah menu dan membayar.
- Shift kasir: modal awal, hitung uang di laci saat tutup, selisih kas tercatat otomatis.

**Manajemen**
- Produk dan kategori, stok masuk/keluar/opname dengan riwayat lengkap.
- Pelanggan: poin danriwayat kunjungan.
- Laporan penjualan per periode: grafik harian, metode bayar, produk terlaris, laba kotor. Ekspor PDF dan Excel.
- Void transaksi (stok dikembalikan), manajemen staff, pengaturan toko (pajak, teks struk).
- Audit log aktivitas penting (login, void, diskon, shift, stok, ekspor, QRIS).
- Dashboard: pendapatan, pesanan, rata-rata transaksi, grafik per jam, menu terlaris, stok menipis.

**Multi-bisnis (multi-tenant)**
- Satu instalasi untuk banyak bisnis; data tiap bisnis terisolasi otomatis.
- Tiap bisnis punya kode toko dan Midtrans Server Key sendiri.

## Teknologi

| Bagian | Yang dipakai |
|---|---|
| Backend | PHP 8.2+, Laravel 11 atau lebih baru |
| Database | SQLite (default) · MySQL/PostgreSQL didukung lewat `.env` |
| Tampilan | Blade, Tailwind CSS (CDN), Material Symbols, font Plus Jakarta Sans |
| PDF | `barryvdh/laravel-dompdf` |
| Excel | `maatwebsite/excel` |
| Pembayaran | Midtrans Core API (QRIS) |

> Tailwind dimuat lewat CDN supaya tanpa proses build. Untuk produksi berskala besar, pertimbangkan mengompilasi Tailwind (dan sesuaikan CSP).

## Tips Instalasi

```bash
git clone <url-repo> cashierstore
cd cashierstore

composer install
cp .env.example .env
php artisan key:generate

# database (default SQLite)
touch database/database.sqlite
php artisan migrate

# data demo (HANYA lokal / development)
php artisan db:seed

php artisan serve
```

Buka `http://localhost:8000`. Login dan pendaftaran ada di landing page.

> **Seeder membuat akun demo dengan password lemah dan menolak berjalan di `production`.** Detail akun demo ada di `database/seeders/DatabaseSeeder.php`. Jangan dipakai di server sungguhan.

## Konfigurasi `.env`

Pengaturan toko dan keamanan dibaca dari `config/pos.php`:

| Variabel | Default | Fungsi |
|---|---|---|
| `POS_STORE_NAME` | `Omah Cafe` | Nama toko default (bisnis baru memakai nama sendiri) |
| `POS_STORE_ADDRESS` / `POS_STORE_PHONE` | contoh | Alamat dan telepon default |
| `POS_TAX_RATE` | `0.10` | Tarif pajak default (10%); bisa diubah per bisnis di Pengaturan |
| `POS_TAX_LABEL` | `Pajak (PB1)` | Nama pajak di struk |
| `POS_CASHIER_MAX_DISCOUNT_PCT` | `10` | Batas diskon manual kasir (% dari subtotal). Admin tanpa batas |
| `POS_REQUIRE_SHIFT` | `true` | Wajib buka shift sebelum menerima pembayaran |
| `POS_POINT_VALUE` | `100` | Nilai Rp per 1 poin saat ditukar |
| `POS_ENFORCE_TRIAL` | `false` | Kunci akun bisnis saat masa trial habis |
| `POS_CSP_MODE` | `report` | `report`, `enforce`, atau `off` untuk Content-Security-Policy |
| `POS_RECEIPT_FOOTER` | `Terima kasih…` | Teks bawah struk default |
| `MIDTRANS_QRIS_ACQUIRER` | `gopay` | Acquirer QRIS Midtrans (opsional) |
| `MIDTRANS_NOTIFY_URL` | kosong | Override URL webhook per transaksi (opsional) |

Untuk pembayaran QRIS, **Server Key Midtrans tidak diisi di `.env`.** Tiap bisnis mengisinya sendiri di menu Pengaturan (lihat bagian QRIS di bawah).

## Peran dan akses

| Menu | Admin | Kasir |
|---|:-:|:-:|
| Order Line (kasir), Shift Kasir, Kantor Virtual | ✅ | ✅ |
| Produk (lihat), Pelanggan (tambah/ubah) | ✅ | ✅ |
| Dashboard, Laporan, Stok, Staff, Pengaturan, Audit Log | ✅ | ❌ |
| Tambah/ubah/hapus produk, hapus pelanggan, void transaksi | ✅ | ❌ |
| Login dengan email + password | ✅ | ✅ |
| Login cepat Kode Toko + PIN | ❌ | ✅ |

## QRIS dinamis (Midtrans)

Pembayaran QRIS dibuat per transaksi, dengan nominal terkunci dan masa berlaku 15 menit. Pesanan baru tercatat **setelah** Midtrans mengonfirmasi lunas.

1. Tiap bisnis mendaftar akun Midtrans sendiri (uang masuk langsung ke rekening bisnis itu).
2. Admin bisnis membuka **Pengaturan → QRIS dinamis (Midtrans)**, menempel Server Key, lalu menekan *Simpan & verifikasi*. Key diuji ke Midtrans dan disimpan **terenkripsi**.
3. Mode sandbox atau produksi terdeteksi dari awalan key (`SB-Mid-server-…` = sandbox).
4. (Opsional) Pasang URL webhook `https://<domain>/payments/midtrans/notify` di dashboard Midtrans. Tanpa webhook, status tetap dicek otomatis saat QR tampil.

Bisnis tanpa key otomatis memakai QRIS manual. Panduan lengkap dan cara uji sandbox: [`MIDTRANS-SETUP.md`](MIDTRANS-SETUP.md).

## Keamanan

- **Isolasi data per bisnis** lewat trait `BelongsToBusiness`; user tanpa bisnis tidak melihat data apa pun.
- **Pembatasan percobaan login** (limiter `login`, `pin`, `register`) berdasarkan IP, akun, dan kode toko.
- **Password dan PIN kuat** (`StrongPassword`, `StrongPin` 6 digit, menolak pola mudah). Login PIN hanya untuk kasir.
- **Harga selalu dari database**, bukan dari browser. Diskon kasir dibatasi, shift kas wajib.
- **Checkout idempoten** (kunci unik per keranjang) mencegah transaksi ganda saat klik berulang.
- **Audit log** untuk aktivitas sensitif; tidak bisa diubah/dihapus dari aplikasi.
- **Header keamanan** (CSP, dan lainnya) lewat middleware `SecurityHeaders`. Mulai dari `POS_CSP_MODE=report`, lalu naikkan ke `enforce` setelah tidak ada pelanggaran.
- **Webhook Midtrans**: signature SHA-512 diverifikasi dengan key bisnis pemilik transaksi, status dikonfirmasi ulang ke API Midtrans, nominal dicocokkan, pemrosesan idempoten.
- Server Key Midtrans terenkripsi di database, tidak pernah dikirim ke browser atau dicatat di log.

## Struktur proyek

```
app/
  Http/Controllers/    # Pos, Qris, MidtransWebhook, Report, Stock, Customer, Shift, Staff, Settings, Audit, ...
  Http/Middleware/     # SecurityHeaders, RoleMiddleware, EnsureAccountActive
  Models/              # Business, User, Order, Product, Customer, CashShift, QrisPayment, AuditLog, ...
  Rules/               # StrongPassword, StrongPin
  Services/            # CheckoutService, SalesReport, MidtransClient, QrisPaymentService
config/pos.php         # pengaturan toko dan keamanan
database/migrations/   # skema (termasuk qris_payments dan kunci Midtrans per bisnis)
resources/views/
  layouts/app.blade.php        # sidebar, top bar mobile, bottom navigation
  partials/head.blade.php      # design tokens, komponen UI, mode gelap
  pos/, products/, customers/, stock/, reports/, shifts/, staff/, settings/, audit/, office/
routes/web.php
```

## Desain dan tampilan

- Design system *Luminescent Fintech*: sidebar `#151624`, aksen lime `#D2F839`, kanvas `#F1F3F6`, font Plus Jakarta Sans.
- Semua token warna dan komponen (`.card`, `.btn-primary`, `.input`, `.chip`, `.pill`, …) ada di `resources/views/partials/head.blade.php`.
- Responsif: sidebar penuh di ≥1280px, rail ikon di tablet, menu geser + bottom navigation di mobile. Tabel otomatis menjadi daftar kartu di layar kecil.
- Mode terang/gelap tersimpan di browser.

## Pemecahan masalah

- **QRIS dinamis tidak muncul di kasir:** Server Key bisnis belum diisi di Pengaturan.
- **Key Midtrans ditolak saat disimpan:** pastikan yang disalin adalah *Server Key* (bukan Client Key) dan akunnya sesuai mode.
- **Key hilang setelah `APP_KEY` diganti:** key terenkripsi memakai `APP_KEY`; isi ulang key di Pengaturan.
- **Perubahan `.env` tidak terbaca:** jalankan `php artisan config:clear`.
- **Webhook tidak masuk saat lokal:** pakai tunnel (mis. ngrok), set `APP_URL` ke URL publik, dan tempel URL notifikasi di dashboard Midtrans.

## Pengembangan lanjutan (ide)

Modifier produk, simpan pesanan (hold), tukar poin di kasir, cetak struk thermal, notifikasi stok menipis, resep dan bahan baku, refund parsial QRIS, kitchen display, mode offline (PWA).

## Lisensi

Tentukan lisensi sebelum dipublikasikan (mis. MIT, atau *proprietary* bila untuk produk komersial).
