<?php

/*
 * Di balik Nginx / Cloudflare / load balancer, IP asli pengunjung dan skema https hanya terbaca
 * bila proxy-nya dipercaya. Tanpa ini semua rate limiter (login, PIN, register) memakai IP proxy
 * yang sama untuk semua orang.
 *
 * TRUSTED_PROXIES di .env: daftar IP/CIDR dipisah koma (mis. "10.0.0.0/8,172.16.0.0/12"),
 * atau "*" bila aplikasi HANYA bisa dijangkau lewat proxy (kalau terbuka langsung ke internet,
 * "*" membuat header X-Forwarded-For bisa dipalsukan untuk menghindari rate limit).
 * Kosong = tidak ada proxy yang dipercaya (cocok untuk lokal tanpa proxy).
 * Dibaca otomatis oleh middleware TrustProxies bawaan Laravel.
 */
return [
    'proxies' => env('TRUSTED_PROXIES') ?: null,
];
