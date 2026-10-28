<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\PlatformAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    public function edit()
    {
        return view('platform.account', ['admin' => Auth::guard('platform')->user()]);
    }

    public function update(Request $request)
    {
        $admin = Auth::guard('platform')->user();

        $data = $request->validate([
            'current_password' => ['required', 'string', 'max:200'],
            'password' => ['required', 'string', 'max:200', 'confirmed', Password::min(14)->letters()->mixedCase()->numbers()->symbols(), 'different:current_password'],
        ], [
            'password.different' => 'Password baru harus berbeda dari password saat ini.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
        ]);

        if (! Hash::check($data['current_password'], $admin->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini salah.']);
        }

        $admin->forceFill(['password' => $data['password'], 'must_change_password' => false])->save();
        $request->session()->regenerate();
        PlatformAuditLog::record('account.password_changed', $admin->email);

        return redirect()->route('platform.dashboard')->with('success', 'Password diganti.');
    }
}
