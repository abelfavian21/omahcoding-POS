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


    // Chat customer service di landing (AI). Format OpenAI-compatible (/chat/completions).
    // Key kosong = tidak ada panggilan HTTP, selalu balasan fallback.
    'ai' => [
        'base_url' => env('AI_BASE_URL'),
        'api_key' => env('AI_API_KEY'),
        'model' => env('AI_MODEL'),
        'fallback' => [
            'base_url' => env('AI_FALLBACK_BASE_URL'),
            'api_key' => env('AI_FALLBACK_API_KEY'),
            'model' => env('AI_FALLBACK_MODEL'),
        ],
        'timeout' => (int) env('AI_TIMEOUT', 8),             // detik per panggilan; dibatasi maks 10
        'max_tokens' => (int) env('AI_MAX_TOKENS', 800),   // model "thinking" ikut memakai jatah ini; terlalu kecil = balasan kosong
        'context_messages' => (int) env('AI_CONTEXT_MESSAGES', 10),
        'daily_limit' => (int) env('AI_DAILY_LIMIT', 200),   // panggilan HTTP ke provider per hari, global
        'reasoning_effort' => env('AI_REASONING_EFFORT'),    // opsional, mis. "low"
    ],
];
