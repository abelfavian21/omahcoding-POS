<?php

return [
    'store_name' => env('POS_STORE_NAME', 'Orely Cafe'),
    'store_address' => env('POS_STORE_ADDRESS', 'Jl. Slamet Riyadi No. 1, Surakarta'),
    'store_phone' => env('POS_STORE_PHONE', '0271-000000'),
    'tax_rate' => (float) env('POS_TAX_RATE', 0.10),   // PB1 10%
    'tax_label' => env('POS_TAX_LABEL', 'Pajak (PB1)'),
    'receipt_footer' => env('POS_RECEIPT_FOOTER', 'Terima kasih, sampai jumpa lagi!'),
];
