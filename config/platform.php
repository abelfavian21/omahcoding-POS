<?php

return [
    // Daftar IP / CIDR yang boleh membuka /platform (pisahkan koma). Kosong = semua IP boleh.
    // Contoh: PLATFORM_ALLOWED_IPS=203.0.113.7,198.51.100.0/24
    // Penting: di belakang Nginx/Cloudflare set juga TRUSTED_PROXIES supaya IP asli terbaca.
    'allowed_ips' => array_values(array_filter(array_map('trim', explode(',', (string) env('PLATFORM_ALLOWED_IPS', ''))))),
];
