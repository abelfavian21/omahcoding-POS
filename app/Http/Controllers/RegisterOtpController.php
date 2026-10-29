<?php

namespace App\Http\Controllers;

use App\Mail\RegistrationOtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterOtpController extends Controller
{
    public function send(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = Str::lower(trim($data['email']));
        $key = 'register-otp-send:' . sha1($email);

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json([
                'message' => 'Terlalu banyak permintaan OTP. Coba lagi beberapa saat.',
            ], 429);
        }

        if (User::where('email', $email)->exists()) {
            return response()->json([
                'message' => 'Email ini sudah terdaftar. Silakan gunakan email lain.',
            ], 422);
        }

        $currentEmail = $request->session()->get('registration_otp.email');

        if ($currentEmail === $email) {
            $lastSent = $request->session()->get('registration_otp.sent_at', 0);

            if (time() - $lastSent < 60) {
                return response()->json([
                    'message' => 'Tunggu 60 detik sebelum meminta OTP baru.',
                ], 429);
            }
        }

        $otp = (string) random_int(100000, 999999);

        $request->session()->put('registration_otp', [
            'email' => $email,
            'hash' => Hash::make($otp),
            'expires_at' => now()->addMinutes(5)->timestamp,
            'sent_at' => time(),
            'attempts' => 0,
            'verified' => false,
        ]);

        RateLimiter::hit($key, 3600);

        try {
            Mail::to($email)->send(new RegistrationOtpMail($otp));
        } catch (\Throwable $e) {
            $request->session()->forget('registration_otp');

            report($e);

            return response()->json([
                'message' => 'Email OTP gagal dikirim. Silakan coba lagi.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Kode OTP telah dikirim ke email Anda.',
        ]);
    }

    public function verify(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'otp' => ['required', 'digits:6'],
        ]);

        $email = Str::lower(trim($data['email']));
        $otpData = $request->session()->get('registration_otp');

        if (! $otpData || $otpData['email'] !== $email) {
            return response()->json([
                'message' => 'Silakan minta OTP baru untuk email ini.',
            ], 422);
        }

        if ($otpData['verified'] ?? false) {
            return response()->json([
                'success' => true,
                'message' => 'Email sudah terverifikasi.',
            ]);
        }

        if (now()->timestamp > $otpData['expires_at']) {
            $request->session()->forget('registration_otp');

            return response()->json([
                'message' => 'Kode OTP sudah kedaluwarsa. Silakan minta kode baru.',
            ], 422);
        }

        if ($otpData['attempts'] >= 5) {
            $request->session()->forget('registration_otp');

            return response()->json([
                'message' => 'Batas percobaan tercapai. Silakan minta OTP baru.',
            ], 429);
        }

        if (! Hash::check($data['otp'], $otpData['hash'])) {
            $otpData['attempts']++;
            $request->session()->put('registration_otp', $otpData);

            return response()->json([
                'message' => 'Kode OTP salah.',
            ], 422);
        }

        $otpData['verified'] = true;
        $request->session()->put('registration_otp', $otpData);

        return response()->json([
            'success' => true,
            'message' => 'Email berhasil diverifikasi.',
        ]);
    }
}
