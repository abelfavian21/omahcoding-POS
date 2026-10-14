<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    public const OUTLETS = ['1 Outlet', '2 - 5 Outlet', '6 - 20 Outlet', 'Lebih dari 20 Outlet'];

    /** Registrasi bisnis: pemilik + usaha + kategori -> akun aktif -> masuk dashboard */
    public function store(Request $request)
    {
        $data = $request->validate([
            'owner_name' => ['required', 'string', 'max:120'],
            'business_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'min:8', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'max:100'],
            'category' => ['required', Rule::exists('business_categories', 'slug')->where('is_active', true)],
            'outlet_count' => ['required', Rule::in(self::OUTLETS)],
            'location' => ['required', 'string', 'max:120'],
            'agree' => ['accepted'],
        ], [
            'email.unique' => 'Email ini sudah terdaftar. Silakan masuk atau pakai email lain.',
            'category.required' => 'Pilih kategori usaha terlebih dahulu.',
            'category.exists' => 'Kategori usaha ini belum tersedia.',
            'agree.accepted' => 'Centang persetujuan ketentuan layanan terlebih dahulu.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
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
                'status' => 'trial',
                'trial_ends_at' => now()->addDays(14),
            ]);

            $owner = User::create([
                'business_id' => $business->id,
                'name' => $data['owner_name'],
                'email' => $data['email'],
                'password' => $data['password'],   // di-hash oleh cast model
                'role' => 'admin',
                'phone' => $data['phone'],
                'is_active' => true,
            ]);

            // set user sementara supaya kategori produk bawaan otomatis milik bisnis ini
            Auth::setUser($owner);
            foreach (($category->preset ?? []) as $i => [$name, $emoji]) {
                Category::create(['name' => $name, 'emoji' => $emoji, 'sort_order' => $i]);
            }

            return $owner;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('landing')->with('registered', [
            'name' => $user->business->name,
            'category' => $category->name,
            'icon' => $category->icon,
            'code' => $user->business->code,
        ]);
    }
}
