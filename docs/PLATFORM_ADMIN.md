# Panel Admin Platform CashierStore

Dokumen ini menjelaskan apa yang sudah dibuat, cara memasangnya, keputusan desainnya, dan rencana berikutnya.
Branch: `feature/platform-admin`.

## 1. Apa ini

Panel untuk **pemilik platform** (kamu dan partner), terpisah dari admin tiap toko. Admin toko mengelola satu
bisnis. Admin platform melihat dan mengatur **semua** bisnis: siapa yang daftar, trial siapa yang habis,
toko mana yang perlu ditangguhkan.

- Alamat: **`/platform/login`** (setelah login: `/platform`). Tidak ditautkan dari landing page publik.
- Akun admin platform ada di tabel sendiri (`platform_admins`), **bukan** tabel `users` milik toko.
  Akun toko tidak bisa masuk ke sini, dan akun platform tidak bisa membuka halaman toko.

### Soal "login sama seperti login toko"
Saya artikan: **tampilan dan caranya sama** (email + password, gaya yang sama dengan aplikasi), bukan
form yang sama di landing page. Alasannya keamanan: jika tab "Admin platform" ada di landing page, siapa pun
bisa melihat bahwa panel itu ada, dan satu kesalahan di form bersama bisa mencampur akun toko dengan akun platform.
Jadi login platform punya halaman sendiri dan dibatasi lebih ketat. Kalau kamu memang ingin tab ketiga di
landing page, itu bisa ditambahkan (lihat bagian 7).

## 2. Yang sudah dikerjakan (v1)

| Bagian | Isi |
| --- | --- |
| Login | Email + password, tanpa "ingat saya", batas percobaan (5 per menit per email+IP, 15 per menit per IP, 10 per jam per email), waktu respons disamakan untuk email yang ada / tidak ada |
| Ringkasan | Jumlah toko per status, pendaftar baru 7/30 hari, trial yang perlu ditindak, transaksi dan omzet bulan ini, toko terbesar, trial berakhir berikutnya, pendaftar terbaru |
| Toko | Daftar dengan pencarian (nama, kode, email, pemilik), filter status dan "trial ≤ 7 hari", halaman detail |
| Detail toko | Profil, jumlah transaksi, omzet 30 hari dan total, daftar pengguna toko (dengan login terakhir), riwayat tindakan platform |
| Tindakan toko | **Tangguhkan** (alasan wajib), **aktifkan kembali**, **jadikan Aktif**, **perpanjang trial** +7/14/30 hari. Penangguhan langsung berlaku: pengguna toko terlempar di request berikutnya |
| Admin platform | Daftar admin, tambah admin (password sementara, wajib diganti saat login pertama), nonaktifkan / aktifkan |
| Akun saya | Ganti password sendiri |
| Audit log | Semua login dan tindakan admin platform, tidak bisa diubah atau dihapus dari aplikasi |
| Keamanan tambahan | Daftar IP yang diizinkan (opsional), header `noindex`, admin yang dinonaktifkan langsung terlempar |

## 3. Cara memasang

```bash
git checkout feature/platform-admin          # atau terapkan patch (lihat bagian 8)
php artisan migrate                          # membuat platform_admins dan platform_audit_logs
php artisan platform:create-admin kamu@email.com --name="Nama Kamu"   # minta password dua kali
php artisan view:clear
```

Lalu buka `https://domain-kamu/platform/login`.

- Admin kedua (partner): buat dari menu **Admin platform → Tambah admin**, atau jalankan perintah yang sama.
- Lupa password: `php artisan platform:create-admin kamu@email.com --reset`.
- Password: minimal 12 karakter, huruf besar dan kecil, angka.

### Pengaturan `.env`
```
PLATFORM_ALLOWED_IPS=          # kosong = semua IP. Contoh: 203.0.113.7,198.51.100.0/24
POS_ENFORCE_TRIAL=false        # lihat catatan di bawah
```
- **`PLATFORM_ALLOWED_IPS`** hanya bekerja benar kalau IP asli terbaca. Di belakang Nginx / Cloudflare / load balancer
  set juga `TRUSTED_PROXIES` (perbaikan #2 dari review), kalau tidak semua orang terlihat berasal dari IP proxy.
- **`POS_ENFORCE_TRIAL`**: bawaannya `false`, artinya toko yang trial-nya habis **tetap bisa dipakai** sampai kamu
  menangguhkannya dari panel. Ringkasan platform menampilkan peringatan kuning selama ini `false`. Jika mau otomatis
  terkunci saat trial habis, set `true`.

## 4. Cara kerja dan keputusan desain

**Guard terpisah.** `config/auth.php` mendapat guard `platform` dengan provider `platform_admins`. Semua kode panel
memakai `Auth::guard('platform')` secara eksplisit dan **tidak pernah** `Auth::shouldUse('platform')`. Ini penting:
model toko (`User`, `Order`, dst.) memakai scope `BelongsToBusiness` yang membaca `Auth::user()->business_id`
dari guard default. Kalau guard default berubah menjadi platform, scope itu akan menutup semua data.

**Data semua toko.** Query lintas toko memakai `withoutGlobalScope('business')` secara eksplisit. Jadi hasilnya benar
walau di browser yang sama kamu juga sedang login sebagai user toko (ada tesnya).

**Penangguhan.** Memakai kolom `businesses.status` yang sudah ada. `EnsureAccountActive` (middleware toko) sudah
mengecek status di setiap request, jadi tidak ada logika baru di sisi toko.

**Audit terpisah.** Tindakan platform masuk ke `platform_audit_logs`, bukan `audit_logs` milik toko, supaya
toko tidak melihat jejak admin platform dan sebaliknya. Tindakan pada satu toko juga menyimpan `business_id`,
sehingga muncul di riwayat halaman detail toko.

**Tampilan.** Memakai layout, warna, font, dan komponen yang sama dengan aplikasi toko (sidebar gelap, aksen lime,
kartu putih), supaya terasa satu produk. Teks sekunder dan label tabel sedikit dibuat lebih gelap di layout platform
agar kontrasnya memenuhi 4.5:1.

**Tidak ada pendaftaran publik** untuk admin platform. Admin pertama hanya lewat artisan.

### File yang ditambah / diubah
```
database/migrations/2026_10_22_000001_create_platform_tables.php   (baru)
app/Models/PlatformAdmin.php, PlatformAuditLog.php                 (baru)
app/Http/Controllers/Platform/{Auth,Account,Dashboard,Business,Admin,Audit}Controller.php (baru)
app/Http/Middleware/PlatformIpAllowlist.php, EnsurePlatformAdmin.php (baru)
app/Console/Commands/PlatformCreateAdmin.php                       (baru)
config/platform.php                                                (baru)
resources/views/layouts/platform.blade.php, resources/views/platform/** (baru)
tests/Feature/PlatformAdminTest.php                                (baru)
docs/PLATFORM_ADMIN.md                                             (baru, file ini)
config/auth.php            (guard + provider platform)
bootstrap/app.php          (alias middleware; redirect tamu ke dashboard platform)
app/Providers/AppServiceProvider.php (limiter platform-login)
routes/web.php             (grup /platform)
.env.example               (PLATFORM_ALLOWED_IPS)
```

## 5. Status pengujian (jujur)

- Semua file PHP dan semua view Blade lolos pemeriksaan sintaks, dan semua layar berhasil dirender dengan Blade asli.
- Tampilan sudah diperiksa di lebar desktop dan HP lewat screenshot.
- **Tes otomatis (`PlatformAdminTest`, 17 tes) sudah ditulis tetapi BELUM dijalankan.** Di lingkungan saya,
  PHP lebih lama dari yang dibutuhkan versi Symfony yang terpasang, jadi aplikasi tidak bisa boot. Jalankan:
  `php artisan test --filter=PlatformAdminTest`. Jika ada yang gagal, kemungkinan selisih kecil (nama pesan,
  urutan sesi di tes); tolong kirimkan hasilnya.
- Pemeriksaan visual memakai data contoh; ikon Material Symbols tidak termuat di lingkungan preview (tidak ada
  internet), di aplikasi asli ikon tampil normal.

## 6. Batasan v1 (disengaja)

- Tidak ada **login sebagai pemilik toko** (impersonation). Berisiko dan perlu jejak audit khusus.
- Tidak ada **reset password pemilik toko** dari panel (butuh email). Admin platform hanya bisa mereset admin
  platform lewat artisan.
- Tidak ada **2FA**. Ini langkah keamanan berikutnya yang paling penting (bagian 7).
- Tidak ada **tagihan / paket langganan**. "Aktif" artinya "kamu yang menandainya berbayar".
- Penangguhan dan perpanjangan trial dilakukan manual.
- Nonaktifkan admin langsung menghentikan aksesnya di request berikutnya, tapi tidak ada tombol "keluarkan semua
  sesi" atau reset password admin lain dari panel.

## 7. Rencana berikutnya (urut prioritas)

1. **Jalankan dan rapikan tes** (di mesinmu), lalu merge.
2. **2FA (TOTP) untuk admin platform.** Panel ini bisa mengunci semua toko; password saja tidak cukup. Butuh
   satu paket TOTP dan kode pemulihan. Sebelum itu aktif, pakai `PLATFORM_ALLOWED_IPS` dan password panjang.
3. **Batas waktu sesi lebih pendek** untuk platform (misal 30 menit idle) dan tombol "keluar dari semua perangkat".
4. **Langganan dan tagihan**: paket (nama, harga, batas outlet/staff), tanggal berlaku, status bayar, pengingat
   trial otomatis lewat email. Perlu keputusan harga dan metode bayar darimu.
5. **Login sebagai pemilik toko (impersonation)** untuk dukungan: hanya admin platform, alasan wajib, banner
   merah di sisi toko, setiap aksi tercatat dengan penanda "oleh platform".
6. **Dukungan toko**: reset password pemilik, kirim ulang kode toko, ubah email pemilik, ekspor / hapus data
   toko (permintaan privasi).
7. **Pemantauan**: grafik pendaftar dan omzet harian, toko tidak aktif > 14 hari, transaksi gagal QRIS, error terbaru.
8. **Pengumuman** ke semua toko (banner di dasbor toko).
9. **Tab "Admin platform" di landing page** bila memang diinginkan: tambahkan segmen ketiga di panel masuk yang
   mengarah ke `platform.login`. Tidak disarankan (lihat bagian 1).

## 8. Cara mengambil perubahan

Patch: `git am fix-04-platform-admin.patch` (di branch baru dari commit dasar yang sama), atau salin file di
`fix-04-platform-files.zip` ke root proyek. File `routes/web.php`, `bootstrap/app.php`, `config/auth.php`,
`AppServiceProvider.php` dan `.env.example` diubah sedikit; jika kamu sudah menerapkan perbaikan #2 (trust proxies)
atau mengubah file itu, gabungkan manual (perubahannya pendek dan terpisah).

## 9. Pertanyaan yang menentukan langkah berikutnya

1. Apakah kamu dan partner setuju login platform di alamat sendiri (`/platform/login`) dan bukan tab di landing page?
2. Domain khusus untuk panel (misal `admin.cashierstore.id`) atau cukup `/platform`?
3. Model langganan: berapa paket, berapa harga, bayar lewat apa? (menentukan nomor 4 di rencana)
4. Apakah partner butuh peran lebih terbatas (hanya lihat, tidak bisa menangguhkan)? Sekarang semua admin sama.
