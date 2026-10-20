<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Rules\StrongPassword;
use App\Rules\StrongPin;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function index()
    {
        return view('staff.index', ['staff' => User::withCount('orders')->orderBy('role')->orderBy('name')->get()]);
    }

    public function create()
    {
        return view('staff.form', ['user' => new User(['role' => 'kasir', 'is_active' => true])]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $new = User::create($data);
        AuditLog::record('staff.created', $new->email, ['role' => $new->role]);

        return redirect()->route('staff.index')->with('success', 'Staff ditambahkan.');
    }

    public function edit(User $staff)
    {
        return view('staff.form', ['user' => $staff]);
    }

    public function update(Request $request, User $staff)
    {
        $data = $this->validated($request, $staff);

        // jangan sampai admin terakhir / diri sendiri dinonaktifkan atau diturunkan perannya
        if ($staff->id === $request->user()->id && (! $data['is_active'] || $data['role'] !== 'admin')) {
            return back()->withInput()->withErrors(['role' => 'Anda tidak bisa menonaktifkan atau menurunkan peran akun sendiri.']);
        }

        if ($staff->isAdmin() && ($data['role'] !== 'admin' || ! $data['is_active']) && $this->isLastActiveAdmin($staff)) {
            return back()->withInput()->withErrors(['role' => 'Harus ada minimal satu admin aktif. Jadikan staff lain admin dulu.']);
        }

        $credentialsChanged = isset($data['password']) || array_key_exists('pin', $data)
            || $data['role'] !== $staff->role || ! $data['is_active'];

        $staff->update($data);

        // paksa semua perangkat "ingat saya" milik staff ini login ulang
        if ($credentialsChanged) {
            $staff->forceFill(['remember_token' => Str::random(60)])->saveQuietly();
        }

        AuditLog::record('staff.updated', $staff->email, [
            'role' => $staff->role, 'active' => $staff->is_active,
            'password_changed' => isset($data['password']), 'pin_changed' => array_key_exists('pin', $data),
        ]);

        return redirect()->route('staff.index')->with('success', 'Data staff diperbarui.');
    }

    public function destroy(Request $request, User $staff)
    {
        if ($staff->id === $request->user()->id) {
            return back()->withErrors(['staff' => 'Tidak bisa menghapus akun yang sedang dipakai.']);
        }

        if ($staff->isAdmin() && $this->isLastActiveAdmin($staff)) {
            return back()->withErrors(['staff' => 'Tidak bisa menghapus admin aktif terakhir.']);
        }

        // punya riwayat transaksi -> cukup nonaktifkan agar laporan tetap utuh
        if ($staff->orders()->exists()) {
            $staff->update(['is_active' => false]);
            $staff->forceFill(['remember_token' => Str::random(60)])->saveQuietly();
            AuditLog::record('staff.deactivated', $staff->email);
            return back()->with('success', "{$staff->name} dinonaktifkan (punya riwayat transaksi).");
        }

        AuditLog::record('staff.deleted', $staff->email);
        $staff->delete();

        return back()->with('success', 'Staff dihapus.');
    }

    private function isLastActiveAdmin(User $u): bool
    {
        return ! User::where('role', 'admin')->where('is_active', true)->where('id', '!=', $u->id)->exists();
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'role' => ['required', 'in:admin,kasir'],
            'phone' => ['nullable', 'string', 'max:30'],
            'shift' => ['nullable', 'string', 'max:40'],
            'password' => [$user ? 'nullable' : 'required', 'string', 'max:100', new StrongPassword($request->input('email'), $request->input('name'))],
            'pin' => ['nullable', new StrongPin, function ($attr, $value, $fail) use ($user) {
                $clash = User::whereNotNull('pin')
                    ->when($user, fn ($q) => $q->where('id', '!=', $user->id))
                    ->get()->first(fn (User $u) => Hash::check($value, $u->pin));
                if ($clash) $fail('PIN sudah dipakai staff lain.');
            }],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        if (empty($data['password'])) unset($data['password']);
        if (empty($data['pin'])) unset($data['pin']);

        // admin masuk dengan email+password; PIN 4-6 digit terlalu lemah untuk akun yang memegang laporan & data semua staff
        if ($data['role'] === 'admin') {
            if ($user?->pin) {
                $data['pin'] = null;      // hapus PIN lama milik admin
            } else {
                unset($data['pin']);
            }
        }

        return $data;
    }
}
