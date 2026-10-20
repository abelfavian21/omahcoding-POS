<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\QrisPayment;
use App\Services\MidtransClient;
use Illuminate\Http\Request;
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
