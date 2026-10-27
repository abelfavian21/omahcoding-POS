<?php

return [
    // Daftar IP / CIDR yang boleh membuka /platform (pisahkan koma).
    // Contoh: PLATFORM_ALLOWED_IPS=203.0.113.7,198.51.100.0/24
    // Penting: di belakang Nginx/Cloudflare set juga TRUSTED_PROXIES (IP proxy spesifik, BUKAN "*"
    // bila app bisa dijangkau langsung), kalau tidak IP asli salah terbaca / bisa dipalsukan.
    'allowed_ips' => array_values(array_filter(array_map('trim', explode(',', (string) env('PLATFORM_ALLOWED_IPS', ''))))),

    // Allowlist kosong + opsi ini true => /platform dijawab 404 untuk semua orang (fail-closed).
    // Default: aktif di produksi. Isi PLATFORM_ALLOWED_IPS sebelum deploy, atau set false secara sadar.
    'require_allowlist' => filter_var(env('PLATFORM_REQUIRE_ALLOWLIST', env('APP_ENV') === 'production'), FILTER_VALIDATE_BOOL),

    // Wajib 2FA (TOTP) untuk semua admin platform. Admin tanpa 2FA dipaksa mendaftar saat login.
    'require_2fa' => filter_var(env('PLATFORM_REQUIRE_2FA', true), FILTER_VALIDATE_BOOL),

    // Sesi platform habis bila tidak aktif selama N menit (0 = nonaktif).
    'idle_timeout_minutes' => (int) env('PLATFORM_IDLE_TIMEOUT', 30),

    // Nama yang tampil di aplikasi authenticator.
    'issuer' => env('PLATFORM_2FA_ISSUER', 'CashierStore Platform'),
];
