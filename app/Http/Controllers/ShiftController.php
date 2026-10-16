<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\CashShift;
use Illuminate\Http\Request;
class ShiftController extends Controller
{
    public function index()
    {
        return view('shifts.index', [
            'current' => CashShift::currentFor(auth()->user()),
            // kasir hanya melihat shift miliknya sendiri
            'shifts' => CashShift::with('cashier')
                ->when(! auth()->user()->isAdmin(), fn ($q) => $q->where('user_id', auth()->id()))
                ->latest('opened_at')->paginate(12),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'opening_cash' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        if (CashShift::currentFor($request->user())) {
            return back()->withErrors(['opening_cash' => 'Masih ada shift yang terbuka. Tutup dulu sebelum buka yang baru.']);
        }

        $opened = CashShift::create([
            'user_id' => $request->user()->id,
            'opened_at' => now(),
            'opening_cash' => $data['opening_cash'],
            'note' => $data['note'] ?? null,
        ]);
        AuditLog::record('shift.opened', (string) $opened->id, ['opening_cash' => (float) $data['opening_cash']]);

        return redirect()->route('shifts.index')->with('success', 'Shift dibuka. Kasir siap menerima transaksi.');
    }

    public function close(Request $request, CashShift $shift)
    {
        $data = $request->validate([
            'closing_cash' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        if ($shift->user_id !== $request->user()->id && ! $request->user()->isAdmin()) {
            abort(403);
        }
        if (! $shift->isOpen()) {
            return back()->withErrors(['closing_cash' => 'Shift ini sudah ditutup.']);
        }

        $expected = $shift->expectedAtClose();
        $closing = (float) $data['closing_cash'];

        $shift->update([
            'closed_at' => now(),
            'closing_cash' => $closing,
            'expected_cash' => $expected,
            'difference' => $closing - $expected,
            'note' => trim(($shift->note ? $shift->note.' | ' : '').($data['note'] ?? '')),
        ]);

        $diff = $closing - $expected;
        AuditLog::record('shift.closed', (string) $shift->id, ['expected' => $expected, 'counted' => $closing, 'difference' => $diff]);
        $msg = 'Shift ditutup. '.($diff == 0.0
            ? 'Kas sesuai.'
            : ($diff > 0 ? 'Kas lebih ' : 'Kas kurang ').'Rp '.number_format(abs($diff), 0, ',', '.'));

        return redirect()->route('shifts.index')->with('success', $msg);
    }
}
