<?php

return [

    // Saklar darurat: false = widget tidak tampil dan endpoint publik 404.
    'enabled' => (bool) env('CHAT_ENABLED', true),

    'message_max_length' => 500,          // karakter, pesan pengunjung
    'admin_message_max_length' => 1000,   // karakter, balasan admin
    'max_visitor_messages' => 30,         // total pesan pengunjung per percakapan

    // Semua dihitung per IP kecuali yang tertulis per percakapan.
    'limits' => [
        'ip_per_minute' => 10,
        'ip_per_hour' => 60,
        'new_conversations_per_hour' => 5,
        'conversation_per_minute' => 6,   // per percakapan
        'poll_per_minute' => 60,
    ],

    // Isi system prompt AI. Komentar HTML (<!-- ... -->) di file dibuang sebelum dikirim ke AI.
    'system_prompt_path' => resource_path('prompts/customer-service.md'),

    // Dikirim ke pengunjung saat AI tidak bisa menjawab (tanpa key, kuota habis, atau semua provider gagal).
    'fallback_message' => 'Maaf, asisten kami sedang tidak bisa menjawab. Pesan Anda sudah kami terima dan tim kami akan segera membalas di chat ini.',

];
