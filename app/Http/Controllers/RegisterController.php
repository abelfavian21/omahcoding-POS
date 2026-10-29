<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Rules\StrongPassword;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    public const OUTLETS = ['1 Outlet', '2 - 5 Outlet', '6 - 20 Outlet', 'Lebih dari 20 Outlet'];

    /** Registrasi bisnis: pemilik + usaha + kategori -> akun aktif -> masuk dashboard */
    public function store(Request $request)
    {
        $email = Str::lower(trim((string) $request->input('email')));
        $request->merge(['email' => $email]);

        // Pastikan email sudah diverifikasi menggunakan OTP
        $otpData = $request->session()->get('registration_otp');

        if (
            ! $otpData ||
            ! ($otpData['verified'] ?? false) ||
            $otpData['email'] !== $email
        ) {
            $message = 'Verifikasi email terlebih dahulu sebelum menyelesaikan registrasi.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'errors' => [
                        'email' => [$message],
                    ],
                ], 422);
            }

            return back()
                ->withInput($request->except('password'))
                ->withErrors(['email' => $message]);
        }

        $data = $request->validate([
            'owner_name' => ['required', 'string', 'max:120'],
            'business_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'min:8', 'max:30'],
            'password' => ['required', 'string', 'max:100', new StrongPassword($request->input('email'), $request->input('owner_name'))],
            'category' => ['required', Rule::exists('business_categories', 'slug')->where('is_active', true)],
            'outlet_count' => ['required', Rule::in(self::OUTLETS)],
            'location' => ['required', 'string', 'max:120'],
            'agree' => ['accepted'],
        ], [
            'email.unique' => 'Email ini sudah terdaftar. Silakan masuk atau pakai email lain.',
            'category.required' => 'Pilih kategori usaha terlebih dahulu.',
            'category.exists' => 'Kategori usaha ini belum tersedia.',
            'agree.accepted' => 'Centang persetujuan ketentuan layanan terlebih dahulu.',
        ]);

        $category = BusinessCategory::where('slug', $data['category'])->firstOrFail();

        $user = DB::transaction(function () use ($data, $category) {
            $business = Business::create([
                'business_category_id' => $category->id,
                'code' => Business::generateCode($data['business_name']),
                'name' => $data['business_name'],
                'owner_name' => $data['owner_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'location' => $data['location'],
                'outlet_count' => $data['outlet_count'],
                'status' => 'active',
                'mode' => $category->default_mode ?? 'fnb',
                'trial_ends_at' => null,
            ]);

            $owner = User::create([
                'business_id' => $business->id,
                'name' => $data['owner_name'],
                'email' => $data['email'],
                'password' => $data['password'],   // di-hash oleh cast model
                'role' => 'admin',
                'phone' => $data['phone'],
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            // set user sementara supaya kategori produk bawaan otomatis milik bisnis ini
            Auth::setUser($owner);
            foreach (($category->preset ?? []) as $i => [$name, $emoji]) {
                Category::create(['name' => $name, 'emoji' => $emoji, 'sort_order' => $i]);
            }

            AuditLog::record('business.registered', $business->code, ['email' => $owner->email]);

            return $owner;
        });

        $request->session()->forget('registration_otp');

        Auth::login($user);
        $request->session()->regenerate();

        $registered = [
            'name' => $user->business->name,
            'category' => $category->name,
            'icon' => $category->icon,
            'code' => $user->business->code,
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Usaha berhasil didaftarkan.',
                'registered' => $registered,
                'redirect' => route('dashboard'),
            ]);
        }

        return redirect()->route('landing')->with('registered', $registered);
    }
}
