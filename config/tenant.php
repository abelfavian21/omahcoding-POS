<?php

return [
    // true  = tanpa konteks bisnis, query tenant kosong dan create ditolak (WAJIB di produksi).
    // false = hanya mencatat log "tenant.unscoped_query"; pakai sementara untuk menemukan kode yang belum diberi konteks.
    'strict' => filter_var(env('TENANT_STRICT', true), FILTER_VALIDATE_BOOL),
];
