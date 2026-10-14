<?php

namespace App\Http\Controllers;

use App\Models\User;
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
        User::create($data);

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

        $staff->update($data);

        return redirect()->route('staff.index')->with('success', 'Data staff diperbarui.');
    }

    public function destroy(Request $request, User $staff)
    {
        if ($staff->id === $request->user()->id) {
            return back()->withErrors(['staff' => 'Tidak bisa menghapus akun yang sedang dipakai.']);
        }

        // punya riwayat transaksi -> cukup nonaktifkan agar laporan tetap utuh
        if ($staff->orders()->exists()) {
            $staff->update(['is_active' => false]);
            return back()->with('success', "{$staff->name} dinonaktifkan (punya riwayat transaksi).");
        }

        $staff->delete();

        return back()->with('success', 'Staff dihapus.');
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'role' => ['required', 'in:admin,kasir'],
            'phone' => ['nullable', 'string', 'max:30'],
            'shift' => ['nullable', 'string', 'max:40'],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8'],
            'pin' => ['nullable', 'digits:4', function ($attr, $value, $fail) use ($user) {
                $clash = User::whereNotNull('pin')
                    ->when($user, fn ($q) => $q->where('id', '!=', $user->id))
                    ->get()->first(fn (User $u) => Hash::check($value, $u->pin));
                if ($clash) $fail('PIN sudah dipakai staff lain.');
            }],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        if (empty($data['password'])) unset($data['password']);
        if (empty($data['pin'])) unset($data['pin']);

        return $data;
    }
}
