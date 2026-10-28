<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\PlatformAdmin;
use App\Models\PlatformAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    public function index()
    {
        return view('platform.admins.index', ['admins' => PlatformAdmin::orderByDesc('is_active')->orderBy('name')->get()]);
    }

    /** Admin baru diberi password sementara; wajib menggantinya saat login pertama. */
    public function store(Request $request)
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:platform_admins,email'],
            'password' => ['required', 'string', 'max:200', Password::min(14)->letters()->mixedCase()->numbers()->symbols()],
        ], ['email.unique' => 'Email ini sudah terdaftar sebagai admin platform.']);

        $admin = PlatformAdmin::create($data + ['is_active' => true, 'must_change_password' => true]);
        PlatformAuditLog::record('admin.created', $admin->email, ['name' => $admin->name]);

        return back()->with('success', "Admin {$admin->name} dibuat. Beri tahu password sementaranya lewat jalur aman; ia wajib menggantinya saat login pertama.");
    }

    /** Nonaktifkan / aktifkan admin. Tidak boleh diri sendiri, dan tidak boleh menyisakan nol admin aktif. */
    public function toggle(Request $request, PlatformAdmin $admin)
    {
        $me = Auth::guard('platform')->user();

        if ($admin->is(($me))) {
            return back()->withErrors(['admin' => 'Anda tidak bisa menonaktifkan akun Anda sendiri.']);
        }

        if ($admin->is_active && PlatformAdmin::where('is_active', true)->count() <= 1) {
            return back()->withErrors(['admin' => 'Harus tersisa minimal satu admin aktif.']);
        }

        $admin->forceFill(['is_active' => ! $admin->is_active])->save();
        PlatformAuditLog::record($admin->is_active ? 'admin.activated' : 'admin.deactivated', $admin->email);

        return back()->with('success', $admin->is_active ? 'Admin diaktifkan.' : 'Admin dinonaktifkan. Sesinya langsung berakhir.');
    }
}
