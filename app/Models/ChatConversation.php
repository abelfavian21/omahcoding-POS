<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

/**
 * Percakapan chat landing page. Data level PLATFORM: BUKAN data bisnis, tidak memakai trait BelongsToBusiness.
 * Status: ai (AI membalas) | human (admin platform menangani, AI diam) | closed.
 */
class ChatConversation extends Model
{
    public const AI = 'ai';
    public const HUMAN = 'human';
    public const CLOSED = 'closed';

    // Hanya diisi dari kode server; JANGAN pernah mass-assign dari input request.
    protected $fillable = ['token', 'status', 'needs_admin', 'handled_by', 'ip', 'user_agent', 'last_active_at'];

    protected function casts(): array
    {
        return ['needs_admin' => 'boolean', 'last_active_at' => 'datetime'];
    }

    public static function start(?string $ip, ?string $userAgent): self
    {
        return static::create([
            'token' => Str::random(40),
            'status' => self::AI,
            'ip' => $ip,
            'user_agent' => mb_substr((string) $userAgent, 0, 255),
            'last_active_at' => now(),
        ]);
    }

    public static function findByToken(?string $token): ?self
    {
        if (! $token || ! preg_match('/^[A-Za-z0-9]{40}$/', $token)) {
            return null;
        }

        return static::where('token', $token)->first();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(ChatMessage::class)->latestOfMany();
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(PlatformAdmin::class, 'handled_by');
    }

    public function addMessage(string $role, string $body, ?int $adminId = null): ChatMessage
    {
        $message = $this->messages()->create(['role' => $role, 'body' => $body, 'platform_admin_id' => $adminId]);
        $this->forceFill(['last_active_at' => now()])->save();

        return $message;
    }
}
