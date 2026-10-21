<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // QRIS dinamis (Midtrans Core API). Server Key TIDAK di sini: tiap bisnis menyimpan key-nya sendiri
    // (terenkripsi) lewat menu Pengaturan. Bisnis tanpa key otomatis memakai QRIS manual.
    'midtrans' => [
        'qris_acquirer' => env('MIDTRANS_QRIS_ACQUIRER', 'gopay'),
        'notify_url' => env('MIDTRANS_NOTIFY_URL'),   // opsional (override URL notifikasi per transaksi)
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
