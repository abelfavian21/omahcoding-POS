# Review Keamanan & Perbaikan — CashierStore POS

> **Status pengujian:** semua perubahan ditulis tanpa bisa menjalankan PHP (tidak tersedia di lingkungan
> penyusunan). Saya hanya melakukan pengecekan struktur (kurung/direktif Blade). **Jalankan migrasi dan
> `php artisan test` sebelum dipakai**, lalu lakukan uji manual alur: login → buka shift → transaksi → void → tutup shift.

## Cara menerapkan
```bash
# 1. timpa proyek Anda dengan isi zip ini (database.sqlite SENGAJA tidak disertakan)
php artisan migrate                 # menjalankan 2026_10_15 (shift/pajak) DAN 2026_10_16 (audit, idempotency, void)
php artisan test --filter=SecurityTest
# 2. salin .env.example -> .env, isi APP_KEY (php artisan key:generate), lalu atur nilai production
```
**Perhatian zona waktu:** `config/app.php` sekarang `Asia/Jakarta` (sebelumnya `UTC`, sehingga grafik per jam
dan batas "hari ini" bergeser 7 jam). Data lama di DB tersimpan dalam UTC; untuk DB yang sudah berisi data
nyata, set `APP_TIMEZONE=UTC` dulu atau geser timestamp lama +7 jam sebelum beralih.

---

## A. Kelemahan keamanan yang ditemukan → status

 # | Tingkat | Temuan | Perbaikan |
|---|---|---|---|
| 1 | **Kritis** | **Isolasi tenant gagal-terbuka.** Global scope dilewati bila `business_id` user NULL → user itu melihat data *semua* toko. Kolom `business_id` nullable di semua tabel. | `BelongsToBusiness` kini fail-closed: user tanpa bisnis tidak melihat apa pun & tidak bisa membuat data; `business_id` tidak bisa dipindah. Akun tanpa bisnis ditolak middleware. |
| 2 | **Kritis** | **Login PIN lemah:** 4 digit (10.000 kemungkinan), throttle hanya per-IP, kode toko mudah ditebak (`CS-NAMA-1234`), **admin boleh punya PIN** (= admin bisa diambil alih lewat PIN), seeder membuat admin PIN `0000`. Juga loop bcrypt ke semua user (celah DoS CPU). | PIN hanya untuk **kasir**; PIN baru 6 digit + tolak pola mudah; limiter per (toko+IP), per IP, dan **per kode toko** (30/jam); waktu respons disamakan; PIN admin dihapus. |
| 3 | **Tinggi** | **Kasir bisa memberi diskon sebesar apa pun** — angka diskon dipercaya dari browser (hingga 100% subtotal). | Batas server-side `POS_CASHIER_MAX_DISCOUNT_PCT` (default 10%); admin bebas; setiap diskon masuk audit log. |
| 4 | **Tinggi** | **Akun yang dinonaktifkan tetap bisa bekerja** (`is_active` hanya dicek saat login; sesi 2 jam, "ingat saya" bertahun). Ganti password tidak mematikan sesi lain. | `EnsureAccountActive` dicek **tiap request** (user nonaktif / bisnis suspended); `authenticateSessions()`; remember-token diputar saat password/PIN/peran berubah atau akun dinonaktifkan. |
| 5 | **Tinggi** | **Transaksi tidak tahan retry:** klik ganda / jaringan putus → penjualan ganda & stok terpotong dua kali; nomor invoice balapan (urut *string*, rusak setelah 9999). | `idempotency_key` (UUID dari browser, unik per bisnis); retry otomatis bila nomor invoice bertabrakan; nomor invoice dihitung numerik. |
| 6 | **Tinggi** | **Void tidak aman:** tanpa lock (dua admin = stok kembali 2×), bisa void order `held` (stok bertambah padahal tak pernah berkurang), alasan opsional, poin yang ditukar tidak dikembalikan, pembatal hanya tersirat di kolom catatan. | Baris dikunci & status dicek ulang di transaksi; hanya order `paid`; alasan wajib (min 3 huruf); `voided_by/at/reason` terstruktur; poin dikembalikan akurat; masuk audit log. |
| 7 | **Sedang** | **Formula injection di Excel:** nama pelanggan/produk `=HYPERLINK(...)` (kasir bisa membuat pelanggan) dieksekusi saat pemilik membuka laporan. | `TableSheet` menyimpan teks berawalan `=` sebagai string, kecuali baris TOTAL milik sistem. |
| 8 | **Sedang** | **Password lemah:** hanya min 8 karakter. Tidak ada throttle per-akun (hanya per-IP → credential stuffing lolos). Email tidak dinormalisasi (`A@x.com` ≠ `a@x.com` di SQLite → akun ganda). | `StrongPassword` (≥10, huruf+angka, bukan umum/email/nama); limiter per akun+IP, per IP, per akun/jam; email di-lowercase. |
| 9 | **Sedang** | **Hak akses terlalu longgar:** kasir bisa membuka Dashboard (omzet, daftar staff), struk semua kasir, mengubah *tier* pelanggan (benefit finansial), melihat semua shift. | Dashboard admin-only; kasir hanya struk & shift miliknya; tier hanya admin. |
| 10 | **Sedang** | Tidak ada header keamanan; Tailwind dari CDN tanpa SRI/CSP; cookie `secure`/enkripsi sesi tidak diatur. | `SecurityHeaders` (XFO, nosniff, Referrer-Policy, Permissions-Policy, HSTS, COOP, CSP); `.env.example` produksi (`SESSION_SECURE_COOKIE`, `SESSION_ENCRYPT`, `APP_DEBUG=false`). |
| 11 | **Sedang** | **Zip membawa `database.sqlite` berisi email & hash password pengguna sungguhan**, tanpa `.gitignore`. Seeder membuat akun `password`. | DB dikeluarkan dari paket + `.gitignore`; seeder menolak jalan di production. **Anggap akun di DB itu sudah terpapar: minta mereka ganti password.** |
| 12 | Rendah | Admin terakhir bisa dinonaktifkan/dihapus oleh admin lain → toko terkunci. Laporan tanpa batas rentang → ekspor memakan memori. Tanggal ngawur → error 500. | Guard "admin aktif terakhir"; rentang laporan maks. 366 hari; tanggal tidak valid pakai default. ||

## B. Bug fungsional yang ikut diperbaiki
* **`CheckoutService` (fitur shift, poin, pajak per toko) tidak pernah dipakai** — route checkout memakai kode lama yang menghitung pajak dari config global dan tanpa shift. Sekarang **satu-satunya jalur** pembayaran. Di dalamnya juga ada bug laten: `applyPurchase()` tidak mengembalikan nilai (→ `points_earned = NULL` melanggar NOT NULL) dan `Order::$fillable` tidak memuat kolom baru (diabaikan diam-diam); `Order::held()`, `Business::taxRate()`, `Customer::pointValue()` belum ada. Semua dilengkapi.
* Shift, Pengaturan toko (pajak & footer struk), dan Audit Log kini punya route + tampilan. Struk memakai pajak/footer toko, bukan config global.
* Zona waktu (lihat atas).

## C. Fitur baru
1. **Audit log append-only** + halaman admin (`/audit`): login berhasil/gagal, void, diskon manual, shift, stok, staff, pengaturan, ekspor laporan — lengkap dengan IP.
2. **Shift kas** dengan *blind close*: kasir menghitung uang fisik dulu, nilai "seharusnya" hanya terlihat admin; selisih tercatat.
3. **Pengaturan toko**: tarif pajak, nama pajak, teks struk, kode toko.
4. **Referensi pembayaran** opsional (`payment_ref`) untuk QRIS/kartu — dasar rekonsiliasi.
5. Jejak login terakhir (`last_login_at/ip`) per user.
6. Tes otomatis `tests/Feature/SecurityTest.php` (isolasi tenant, PIN, rate limit, hak akses, diskon, shift, idempotency, void ganda).

## D. Belum dikerjakan (perlu keputusan / infrastruktur Anda)
* **Verifikasi email & reset password.** Sekarang siapa pun bisa mendaftar dengan email orang lain dan langsung jadi admin; pemilik yang lupa password tidak punya jalur pulih. Butuh konfigurasi SMTP.
* **2FA untuk admin** (mis. TOTP) — langkah berikutnya yang paling berharga setelah verifikasi email.
* **Pembayaran QRIS/kartu masih berbasis kepercayaan** (kasir menekan "QRIS" = dianggap lunas). Perbaikan sebenarnya: integrasi payment gateway + webhook.
* **Build Tailwind lewat Vite** (file `resources/css|js` sudah ada) agar CDN dan `'unsafe-inline'` bisa dibuang dari CSP. CSP default **report-only** supaya UI tidak rusak: buka konsol browser, pastikan tidak ada pelanggaran, lalu set `POS_CSP_MODE=enforce`.
* **Di belakang proxy (Nginx/Cloudflare):** daftarkan proxy tepercaya (`$middleware->trustProxies(at: [...])`) agar rate limit memakai IP asli, bukan IP proxy — kalau tidak, semua pengguna berbagi satu kuota.
* **Produksi sebaiknya MySQL/PostgreSQL**, bukan SQLite; `lockForUpdate()` hanya efektif di keduanya. Backup terenkripsi terjadwal.
* **Void tanpa persetujuan dua orang** dan void setelah shift ditutup hanya tercatat di audit (tidak diblok).
* `KitchenController` dan `CategoryController` belum punya route/tampilan; saya tidak menyentuhnya agar tidak mengubah produk tanpa arahan Anda.
* Voice command memakai Web Speech API milik browser (di Chrome audio diproses server Google) — beri tahu pengguna.
