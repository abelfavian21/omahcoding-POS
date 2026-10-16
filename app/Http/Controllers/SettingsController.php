<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

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
}
