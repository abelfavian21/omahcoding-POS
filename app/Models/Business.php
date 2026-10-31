<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Business extends Model
{
    /** Bahasa tampilan yang tersedia di Pengaturan. */
    public const LANGUAGES = ['id', 'en'];

    protected $fillable = [
        'business_category_id', 'code', 'name', 'owner_name', 'email', 'phone',
        'location', 'outlet_count', 'status', 'mode', 'trial_ends_at',
        'tax_rate', 'tax_label', 'receipt_footer',
    ];

    /** Kunci pembayaran tidak boleh ikut ter-serialize ke JSON / log. */
    protected $hidden = ['midtrans_server_key'];

    protected function casts(): array
    {
        return ['trial_ends_at' => 'datetime', 'tax_rate' => 'float', 'midtrans_server_key' => 'encrypted', 'qris_require_ref' => 'boolean', 'shift_enabled' => 'boolean',
            'receipt_print_enabled' => 'boolean', 'receipt_auto_print' => 'boolean', 'receipt_paper' => 'integer'];
    }

    /** Server Key Midtrans bisnis ini (null bila belum diisi atau tidak bisa didekripsi, mis. APP_KEY berganti). */
    public function midtransKey(): ?string
    {
        try {
            $k = $this->midtrans_server_key;
        } catch (\Throwable $e) {
            return null;
        }

        return filled($k) ? $k : null;
    }

    /** Petunjuk aman untuk ditampilkan di UI: 4 karakter terakhir saja. */
    public function midtransKeyHint(): ?string
    {
        $k = $this->midtransKey();

        return $k ? '…' . substr($k, -4) : null;
    }

    public function category()
    {
        return $this->belongsTo(BusinessCategory::class, 'business_category_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    /** Wajib buka shift sebelum menerima pembayaran? Diatur admin di Pengaturan (default aktif). */
    public function requiresShift(): bool
    {
        return (bool) $this->shift_enabled;
    }

    /** Cetak struk ke printer thermal aktif? Diatur admin di Pengaturan (default nonaktif). */
    public function receiptPrintEnabled(): bool
    {
        return (bool) $this->receipt_print_enabled;
    }

    /** Cetak otomatis setelah bayar: hanya berlaku bila fitur cetak thermal aktif. */
    public function receiptAutoPrint(): bool
    {
        return $this->receiptPrintEnabled() && (bool) $this->receipt_auto_print;
    }

    /** Lebar kertas thermal (mm): 58 atau 80; nilai lain jatuh ke 80. */
    public function receiptPaperMm(): int
    {
        return (int) $this->receipt_paper === 58 ? 58 : 80;
    }

    /** Gambar QRIS statis milik bisnis ini ada di disk privat? */
    public function hasManualQris(): bool
    {
        return filled($this->qris_image) && Storage::disk('local')->exists($this->qris_image);
    }

    /**
     * Apakah kasir wajib mengisi nomor referensi untuk QRIS manual?
     * Mengikuti opsi admin di Pengaturan (qris_require_ref), default nonaktif untuk bisnis baru.
     */
    public function qrisManualNeedsRef(): bool
    {
        return (bool) $this->qris_require_ref;
    }

    /** Tarif pajak bisnis ini; fallback ke config bila belum diatur. */
    public function taxRate(): float
    {
        return (float) ($this->tax_rate ?? config('pos.tax_rate'));
    }

    public function isRetail(): bool
    {
        return $this->mode === 'retail';
    }

    public function isFnb(): bool
    {
        return ! $this->isRetail();
    }

    public function isUsable(): bool
    {
        return $this->status !== 'suspended';
    }

    public function isTrialExpired(): bool
    {
        return false;
    }

    public function unusableReason(): string
    {
        return 'Akun bisnis ini ditangguhkan. Hubungi dukungan CashierStore.';
    }

    /** ID Toko unik, contoh: CS-ORELY-8804 */
    public static function generateCode(string $name): string
    {
        $base = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', Str::ascii($name)), 0, 5)) ?: 'TOKO';
        do {
            $code = 'CS-' . $base . '-' . random_int(1000, 9999);
        } while (static::where('code', $code)->exists());

        return $code;
    }
}
