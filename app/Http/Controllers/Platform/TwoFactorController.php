<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsurePlatformAdmin;
use App\Models\PlatformAdmin;
use App\Models\PlatformAuditLog;
use App\Support\Totp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * 2FA (TOTP) admin platform: tantangan saat login, pendaftaran, kode pemulihan.
 * Rate limit: limiter "platform-2fa" dipasang di rute POST.
 * Tidak ada "matikan 2FA" lewat web (sengaja): lupa perangkat -> `php artisan platform:2fa-reset email`.
 */
class TwoFactorController extends Controller
{
    private const PENDING = 'platform_2fa_pending_secret';

    private function admin(): PlatformAdmin
    {
        return Auth::guard('platform')->user();
    }

    private function passed(Request $request, PlatformAdmin $admin): bool
    {
        return $request->session()->get(EnsurePlatformAdmin::TWO_FACTOR_OK) === $admin->id;
    }

    /* ---------- Tantangan saat login ---------- */

    public function challenge(Request $request)
    {
        $admin = $this->admin();

        if (! $admin->hasTwoFactor() || $this->passed($request, $admin)) {
            return redirect()->route('platform.dashboard');
        }

        return view('platform.2fa-challenge');
    }

    public function verify(Request $request)
    {
        $admin = $this->admin();
        abort_unless($admin->hasTwoFactor(), 403);

        $data = $request->validate(['code' => ['required', 'string', 'max:32']]);

        // dikunci per baris: dua request bersamaan tidak bisa memakai kode (atau kode pemulihan) yang sama
        $how = DB::transaction(function () use ($admin, $data) {
            $a = PlatformAdmin::whereKey($admin->id)->lockForUpdate()->firstOrFail();

            $step = Totp::verify($a->totp_secret, $data['code'], $a->totp_last_step);
            if ($step !== null) {
                $a->forceFill(['totp_last_step' => $step])->save();

                return 'totp';
            }

            return $a->consumeRecoveryCode($data['code']) ? 'recovery' : null;
        });

        if ($how === null) {
            PlatformAuditLog::record('2fa.failed', $admin->email, [], null, $admin->id);

            return back()->withErrors(['code' => 'Kode salah atau sudah dipakai.']);
        }

        $request->session()->regenerate();
        $request->session()->put(EnsurePlatformAdmin::TWO_FACTOR_OK, $admin->id);

        if ($how === 'recovery') {
            $left = $admin->refresh()->recoveryCodesLeft();
            PlatformAuditLog::record('2fa.recovery_used', $admin->email, ['left' => $left], null, $admin->id);

            return redirect()->route('platform.2fa.manage')
                ->with('warning', "Kode pemulihan dipakai. Sisa {$left}. Buat set baru bila hampir habis.");
        }

        PlatformAuditLog::record('2fa.passed', $admin->email, [], null, $admin->id);

        return redirect()->route('platform.dashboard');
    }

    /* ---------- Pendaftaran ---------- */

    public function setup(Request $request)
    {
        $admin = $this->admin();
        if ($admin->hasTwoFactor()) {
            return redirect()->route('platform.2fa.manage');
        }

        $enc = $request->session()->get(self::PENDING);
        $secret = $enc ? Crypt::decryptString($enc) : null;
        if (! $secret) {
            $secret = Totp::generateSecret();
            $request->session()->put(self::PENDING, Crypt::encryptString($secret));
        }

        return view('platform.2fa-setup', [
            'groups' => implode(' ', str_split($secret, 4)),
            'uri' => Totp::uri($secret, $admin->email, (string) config('platform.issuer')),
        ]);
    }

    public function confirm(Request $request)
    {
        $admin = $this->admin();
        abort_if($admin->hasTwoFactor(), 403);

        $data = $request->validate([
            'password' => ['required', 'string', 'max:200'],
            'code' => ['required', 'string', 'max:16'],
        ]);

        // sesi yang dicuri tidak bisa mendaftarkan perangkat penyerang tanpa password
        if (! Hash::check($data['password'], $admin->password)) {
            PlatformAuditLog::record('2fa.setup_failed', $admin->email, ['reason' => 'password'], null, $admin->id);

            return back()->withErrors(['password' => 'Password salah.']);
        }

        $enc = $request->session()->get(self::PENDING);
        if (! $enc) {
            return redirect()->route('platform.2fa.setup');
        }
        $secret = Crypt::decryptString($enc);

        $step = Totp::verify($secret, $data['code']);
        if ($step === null) {
            PlatformAuditLog::record('2fa.setup_failed', $admin->email, ['reason' => 'code'], null, $admin->id);

            return back()->withErrors(['code' => 'Kode salah. Cek jam di ponsel Anda sudah otomatis.']);
        }

        $codes = DB::transaction(function () use ($admin, $secret, $step) {
            $a = PlatformAdmin::whereKey($admin->id)->lockForUpdate()->firstOrFail();
            abort_if($a->hasTwoFactor(), 403);
            $a->forceFill(['totp_secret' => $secret, 'totp_confirmed_at' => now(), 'totp_last_step' => $step])->save();

            return $a->issueRecoveryCodes();
        });

        $request->session()->forget(self::PENDING);
        $request->session()->regenerate();
        $request->session()->put(EnsurePlatformAdmin::TWO_FACTOR_OK, $admin->id);
        PlatformAuditLog::record('2fa.enabled', $admin->email, [], null, $admin->id);

        return redirect()->route('platform.2fa.manage')->with('recovery_codes', $codes);
    }

    /* ---------- Kelola ---------- */

    public function manage()
    {
        $admin = $this->admin();
        if (! $admin->hasTwoFactor()) {
            return redirect()->route('platform.2fa.setup');
        }

        return view('platform.2fa-manage', ['admin' => $admin, 'left' => $admin->recoveryCodesLeft()]);
    }

    public function regenerate(Request $request)
    {
        $admin = $this->admin();
        abort_unless($admin->hasTwoFactor(), 403);

        $data = $request->validate([
            'password' => ['required', 'string', 'max:200'],
            'code' => ['required', 'string', 'max:16'],
        ]);

        if (! Hash::check($data['password'], $admin->password)) {
            return back()->withErrors(['password' => 'Password salah.']);
        }

        // harus kode TOTP dari aplikasi, bukan kode pemulihan
        $codes = DB::transaction(function () use ($admin, $data) {
            $a = PlatformAdmin::whereKey($admin->id)->lockForUpdate()->firstOrFail();
            $step = Totp::verify($a->totp_secret, $data['code'], $a->totp_last_step);
            if ($step === null) {
                return null;
            }
            $a->forceFill(['totp_last_step' => $step])->save();

            return $a->issueRecoveryCodes();
        });

        if ($codes === null) {
            PlatformAuditLog::record('2fa.failed', $admin->email, ['context' => 'regenerate'], null, $admin->id);

            return back()->withErrors(['code' => 'Kode salah atau sudah dipakai.']);
        }

        PlatformAuditLog::record('2fa.recovery_regenerated', $admin->email, [], null, $admin->id);

        return redirect()->route('platform.2fa.manage')->with('recovery_codes', $codes);
    }
}
