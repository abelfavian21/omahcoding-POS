<?php

return [
    'store_name' => env('POS_STORE_NAME', 'Orely Cafe'),
    'store_address' => env('POS_STORE_ADDRESS', 'Jl. Slamet Riyadi No. 1, Surakarta'),
    'store_phone' => env('POS_STORE_PHONE', '0271-000000'),
    'tax_rate' => (float) env('POS_TAX_RATE', 0.10),   // PB1 10%
    'tax_label' => env('POS_TAX_LABEL', 'Pajak (PB1)'),
    // ---- keamanan & kontrol kas ----
    'cashier_max_discount_pct' => (float) env('POS_CASHIER_MAX_DISCOUNT_PCT', 10),  // diskon manual kasir, % dari subtotal (admin tanpa batas)
    'require_shift' => (bool) env('POS_REQUIRE_SHIFT', true),                        // wajib buka shift sebelum menerima pembayaran
    'point_value' => (int) env('POS_POINT_VALUE', 100),                              // Rp per 1 poin saat ditukar
    'enforce_trial' => (bool) env('POS_ENFORCE_TRIAL', false),                       // kunci akun saat masa trial habis
    'csp_mode' => env('POS_CSP_MODE', 'report'),                                     // report | enforce | off
    'receipt_footer' => env('POS_RECEIPT_FOOTER', 'Terima kasih, sampai jumpa lagi!'),
];
