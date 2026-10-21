<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\CashShift;
use App\Models\QrisPayment;
use App\Services\MidtransClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class SettingsController extends Controller
{
    public function edit()
    {
        return view('settings.edit', ['business' => auth()->user()->business]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'location' => ['nullable', 'string', 'max:120'],
            'tax_rate_pct' => ['required', 'numeric', 'min:0', 'max:100'],
            'tax_label' => ['required', 'string', 'max:40'],
            'receipt_footer' => ['nullable', 'string', 'max:255'],
        ]);

        $request->user()->business->update([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'location' => $data['location'],
            'tax_rate' => round($data['tax_rate_pct'] / 100, 4),
            'tax_label' => $data['tax_label'],
            'receipt_footer' => $data['receipt_footer'],
        ]);

        AuditLog::record('settings.updated', null, ['tax_rate_pct' => (float) $data['tax_rate_pct'], 'tax_label' => $data['tax_label']]);

        return redirect()->route('settings.edit')->with('success', 'Pengaturan toko disimpan. Pajak dan struk memakai data ini.');
    }

    /** Aktif / nonaktifkan shift kasir. Menonaktifkan ditolak selama masih ada shift terbuka agar hitungan kas tidak menggantung. */
    public function updateShift(Request $request)
    {
        $enable = $request->boolean('shift_enabled');
        $business = $request->user()->business;

        if (! $enable && CashShift::open()->exists()) {
            return redirect()->route('settings.edit')->withErrors(['shift_enabled' => 'Masih ada shift yang terbuka. Tutup semua shift dulu sebelum menonaktifkan fitur ini.']);
        }

        $business->forceFill(['shift_enabled' => $enable])->save();
        AuditLog::record('settings.shift_' . ($enable ? 'enabled' : 'disabled'));

        return redirect()->route('settings.edit')->with('success', $enable
            ? 'Shift kasir diaktifkan. Kasir wajib membuka shift sebelum menerima pembayaran.'
            : 'Shift kasir dinonaktifkan. Kasir bisa langsung menerima pembayaran tanpa buka shift.');
    }

    /**
     * QRIS manual: unggah / ganti / hapus gambar QRIS statis bisnis dan atur apakah nomor referensi wajib.
     * Gambar disimpan di disk privat dan hanya disajikan lewat route terautentikasi milik bisnis ini.
     */
    public function updateQris(Request $request)
    {
        $request->validate([
            'qris_image' => ['nullable', 'file', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048', 'dimensions:min_width=200,min_height=200'],
        ], [
            'qris_image.image' => 'File harus berupa gambar (PNG, JPG, atau WEBP).',
            'qris_image.mimes' => 'Format gambar harus PNG, JPG, atau WEBP.',
            'qris_image.max' => 'Ukuran gambar maksimal 2 MB.',
            'qris_image.dimensions' => 'Gambar terlalu kecil (minimal 200x200 piksel) sehingga QR sulit dipindai.',
        ]);

        $business = $request->user()->business;
        $old = $business->qris_image;

        if ($request->boolean('remove_image')) {
            if ($old) {
                Storage::disk('local')->delete($old);
            }
            $business->forceFill(['qris_image' => null])->save();
            AuditLog::record('settings.qris_image_removed');

            return redirect()->route('settings.edit')->with('success', 'Gambar QRIS dihapus. Kasir kembali wajib mengisi nomor referensi untuk QRIS manual.');
        }

        $changes = ['qris_require_ref' => $request->boolean('qris_require_ref')];

        if ($request->hasFile('qris_image')) {
            $file = $request->file('qris_image');
            $name = 'qris/' . $business->id . '-' . Str::lower(Str::random(16)) . '.' . $file->extension();
            Storage::disk('local')->putFileAs('qris', $file, basename($name));
            $changes['qris_image'] = $name;
        }

        $business->forceFill($changes)->save();

        if (isset($changes['qris_image']) && $old && $old !== $changes['qris_image']) {
            Storage::disk('local')->delete($old);
        }
        AuditLog::record('settings.qris_updated', null, [
            'image_changed' => isset($changes['qris_image']), 'require_ref' => $changes['qris_require_ref'],
        ]);

        return redirect()->route('settings.edit')->with('success', 'Pengaturan QRIS manual disimpan.');
    }

    /**
     * Simpan / hapus Midtrans Server Key bisnis. Key diverifikasi ke Midtrans dulu, disimpan terenkripsi,
     * tidak pernah ditampilkan ulang (hanya 4 karakter terakhir) dan tidak dimasukkan ke flash input / audit log.
     */
    public function updateMidtrans(Request $request)
    {
        $business = $request->user()->business;
        $fail = fn (string $msg) => back()->withErrors(['midtrans_server_key' => $msg]);   // sengaja tanpa withInput()

        // Mengganti/menghapus key saat ada QR aktif akan membuat pembayaran itu tidak bisa diverifikasi lagi.
        if (QrisPayment::where('status', QrisPayment::PENDING)->where('expires_at', '>', now())->exists()) {
            return $fail('Masih ada pembayaran QRIS yang menunggu. Tunggu selesai atau batalkan dulu sebelum mengubah key.');
        }

        if ($request->boolean('remove')) {
            $business->forceFill(['midtrans_server_key' => null])->save();
            AuditLog::record('settings.midtrans_removed');

            return redirect()->route('settings.edit')->with('success', 'Midtrans dinonaktifkan. Kasir kembali memakai QRIS manual.');
        }

        $key = trim((string) $request->input('midtrans_server_key'));
        if ($key === '' || strlen($key) > 200) {
            return $fail('Server Key wajib diisi.');
        }
        if (! MidtransClient::validKeyFormat($key)) {
            return $fail('Format key tidak dikenali. Contoh: SB-Mid-server-xxxx (sandbox) atau Mid-server-xxxx (produksi).');
        }

        $client = MidtransClient::forKey($key);
        try {
            $client->verifyKey();
        } catch (RuntimeException $e) {
            return $fail($e->getMessage());
        }

        $business->forceFill(['midtrans_server_key' => $key])->save();
        AuditLog::record('settings.midtrans_set', null, ['mode' => $client->isProduction() ? 'production' : 'sandbox', 'key_tail' => substr($key, -4)]);

        return redirect()->route('settings.edit')->with('success', 'Midtrans terhubung (' . ($client->isProduction() ? 'produksi' : 'sandbox') . '). QRIS dinamis aktif di kasir.');
    }
}
