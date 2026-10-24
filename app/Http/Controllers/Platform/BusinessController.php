<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Order;
use App\Models\PlatformAuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class BusinessController extends Controller
{
    public const STATUSES = ['active' => 'Aktif', 'suspended' => 'Ditangguhkan'];

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $status = $request->query('status');

        $businesses = Business::with('category')
            ->withCount(['users' => fn ($u) => $u->withoutGlobalScope('business')])
            ->when($q !== '', function ($w) use ($q) {
                $like = '%' . addcslashes($q, '%_\\') . '%';
                $w->where(fn ($x) => $x->where('name', 'like', $like)->orWhere('code', 'like', $like)
                    ->orWhere('email', 'like', $like)->orWhere('owner_name', 'like', $like));
            })
            ->when(array_key_exists((string) $status, self::STATUSES), fn ($w) => $w->where('status', $status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('platform.businesses.index', ['businesses' => $businesses, 'statuses' => self::STATUSES, 'q' => $q, 'status' => $status]);
    }

    public function show(Business $business)
    {
        $business->load('category');

        $users = User::withoutGlobalScope('business')->where('business_id', $business->id)->orderBy('role')->orderBy('name')->get();
        $paid = fn () => Order::withoutGlobalScope('business')->where('business_id', $business->id)->where('status', 'paid');

        return view('platform.businesses.show', [
            'business' => $business,
            'users' => $users,
            'orderCount' => $paid()->count(),
            'revenueAll' => (float) $paid()->sum('total'),
            'revenue30' => (float) $paid()->where('paid_at', '>=', now()->subDays(30))->sum('total'),
            'lastOrderAt' => $paid()->max('paid_at'),
            'log' => PlatformAuditLog::with('admin')->where('business_id', $business->id)->orderByDesc('id')->limit(15)->get(),
            'statuses' => self::STATUSES,
        ]);
    }

    /** Tangguhkan (alasan wajib) atau aktifkan. Efeknya langsung: EnsureAccountActive mengunci toko di request berikutnya. */
    public function status(Request $request, Business $business)
    {
        $data = $request->validate([
            'action' => ['required', 'in:suspend,activate'],
            'reason' => ['required_if:action,suspend', 'nullable', 'string', 'min:5', 'max:200'],
        ], ['reason.required_if' => 'Alasan penangguhan wajib diisi (minimal 5 karakter).', 'reason.min' => 'Alasan penangguhan minimal 5 karakter.']);

        $to = $data['action'] === 'suspend' ? 'suspended' : 'active';
        $from = $business->status;

        if ($from === $to) {
            return back()->with('success', 'Status toko sudah ' . self::STATUSES[$to] . '.');
        }

        $business->forceFill(['status' => $to])->save();
        PlatformAuditLog::record('business.' . $data['action'], $business->code, ['from' => $from, 'to' => $to, 'reason' => $data['reason'] ?? null], $business->id);

        return back()->with('success', $to === 'suspended' ? 'Toko ditangguhkan. Pengguna toko langsung terkunci.' : 'Toko diaktifkan.');
    }
}
