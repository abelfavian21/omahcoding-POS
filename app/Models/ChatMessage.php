<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Satu pesan chat. role: visitor | ai | admin. Teks mentah; WAJIB di-escape saat ditampilkan. */
class ChatMessage extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['role', 'body', 'platform_admin_id'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'chat_conversation_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(PlatformAdmin::class, 'platform_admin_id');
    }

    /**
     * Bersihkan teks masukan: buang karakter kontrol (sisakan baris baru dan tab), karakter pengarah teks
     * (bidi) yang bisa menipu tampilan, rapikan baris kosong berlebih. HTML sengaja TIDAK dibuang:
     * keamanannya dari escape saat output (Blade {{ }} / textContent), supaya "a < b" tetap utuh.
     */
    public static function sanitize(string $text): string
    {
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F\x{200E}\x{200F}\x{202A}-\x{202E}\x{2066}-\x{2069}\x{FEFF}]/u', '', $text) ?? '';
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;

        return trim($text);
    }

    public function toWidget(): array
    {
        return ['id' => $this->id, 'role' => $this->role, 'body' => $this->body, 'at' => $this->created_at?->toIso8601String()];
    }
}
