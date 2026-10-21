# QRIS Dinamis (Midtrans) — satu Server Key per bisnis

Tiap bisnis memakai akun Midtrans-nya sendiri, jadi uang QRIS masuk langsung ke rekening bisnis itu.
Server Key disimpan **terenkripsi** di database (kolom `businesses.midtrans_server_key`), bukan di `.env`.

## 1. Pasang file & migrasi
```
php artisan migrate
php artisan config:clear
```
(Kunci enkripsi memakai `APP_KEY`. Jangan ganti `APP_KEY` setelah key tersimpan, atau key harus diisi ulang.)

## 2. Hapus key lama di .env (bila ada)
Versi sebelumnya membaca `MIDTRANS_SERVER_KEY` dari `.env`. Sekarang tidak lagi dipakai; hapus baris itu.
Yang masih opsional di `.env`:
```
MIDTRANS_QRIS_ACQUIRER=gopay           # default gopay, boleh dihapus
# MIDTRANS_NOTIFY_URL=https://...      # override URL webhook per transaksi (opsional)
```

## 3. Isi key per bisnis (dilakukan admin tiap bisnis)
1. Daftar / masuk di Midtrans (sandbox: https://dashboard.sandbox.midtrans.com).
2. **Settings → Access Keys** → salin **Server Key**.
   - `SB-Mid-server-…` = sandbox, `Mid-server-…` = produksi. Mode terdeteksi otomatis dari awalan.
3. Di aplikasi: **Pengaturan → QRIS dinamis (Midtrans)** → tempel key → **Simpan & verifikasi**.
   Key diuji ke Midtrans dulu; bila ditolak, tidak disimpan.
4. Bisnis tanpa key otomatis memakai QRIS manual seperti sebelumnya.

## 4. Webhook (opsional, mempercepat konfirmasi)
Tanpa webhook, aplikasi tetap mengecek status ke Midtrans tiap beberapa detik selagi QR tampil.
Untuk konfirmasi instan, tiap bisnis menempel URL yang tampil di halaman Pengaturan ke
Midtrans Dashboard → **Settings → Configuration → Payment Notification URL**
(`https://<domain>/payments/midtrans/notify`, wajib HTTPS & publik; lokal: pakai ngrok dan set `APP_URL`).

## 5. Uji (sandbox)
1. Buka shift → Order Line → tambah menu → **QRIS** → **Bayar**.
2. Buka https://simulator.sandbox.midtrans.com/qris/index , tempel **URL QR** dari kotak kuning, klik **Pay**.
3. Modal berganti "Pembayaran Berhasil"; order muncul di Laporan.

## Keamanan & operasional
- Webhook: pembayaran dicari lewat `order_id` (memuat id bisnis), signature diverifikasi dengan **key bisnis pemilik pembayaran itu**
  (notifikasi bisnis A tidak bisa memicu pembayaran bisnis B), lalu status dikonfirmasi ulang ke API Midtrans dan nominal dicocokkan.
- Key tidak pernah dikirim ke browser, tidak masuk audit log (hanya 4 karakter terakhir), dan tidak di-flash ke sesi.
- Mengganti / menghapus key ditolak selama ada QR yang masih menunggu.
- Uang masuk tetapi order gagal dibuat → status `review` + **Audit Log → Pembayaran QRIS**; refund lewat dashboard Midtrans.
- Void tidak mengembalikan uang QRIS otomatis (refund manual di Midtrans).
- Menutup shift diblokir selama ada QRIS menunggu.
- Penambahan produksi: pakai HTTPS, `APP_DEBUG=false`, dan jangan membagikan key via chat/Git.
