<?php

namespace Database\Seeders;

use App\Models\BusinessCategory;
use Illuminate\Database\Seeder;

class BusinessCategorySeeder extends Seeder
{
    /** Tahap sekarang: hanya F&B yang aktif. Retail & Salon tampil "Segera hadir". */
    public function run(): void
    {
        $rows = [
            ['cafe', 'Cafe & Coffee Shop', 'coffee', 'orange',
                'Spesialisasi racikan minuman, varian modifier, split bill meja, & printer bar.',
                'Meja Dine-in, Add-on Barista & Pilihan Susu', 'Preset Cafe POS', true, 'fnb',
                [['Kopi', 'coffee'], ['Non-Kopi', 'local_drink'], ['Makanan Berat', 'rice_bowl'], ['Snack & Pastry', 'bakery_dining']]],
            ['resto', 'Restoran & Dining', 'restaurant', 'rose',
                'Manajemen meja, urutan penyajian, dan menu makanan berkategori lengkap.',
                'Nomor Meja, Dine-in & Takeaway, Menu Berkategori', 'Dine-in & Takeaway', true, 'fnb',
                [['Makanan Utama', 'rice_bowl'], ['Appetizer', 'tapas'], ['Dessert', 'cake'], ['Minuman', 'local_drink']]],
            ['bakery', 'Bakery & Cake Shop', 'bakery_dining', 'amber',
                'Katalog roti & kue harian, pantau stok yang cepat habis, dan poin member.',
                'Stok Harian, Peringatan Stok Menipis, Member', 'Stok & Member', true, 'fnb',
                [['Roti', 'bakery_dining'], ['Kue', 'cake'], ['Pastry', 'bakery_dining'], ['Minuman', 'local_drink']]],
            ['quick-service', 'Quick Service & UMKM', 'lunch_dining', 'emerald',
                'Antarmuka gesit untuk gerai cepat saji, food court, stan bazar, dan pop-up booth.',
                'Kasir Cepat, Login PIN Kasir, Menu Ringkas', 'Kasir Cepat', true, 'fnb',
                [['Makanan', 'lunch_dining'], ['Minuman', 'local_drink'], ['Snack', 'fastfood']]],
            ['retail', 'Minimarket & Toko Kelontong', 'storefront', 'blue',
                'Kasir ritel praktis tanpa nomor meja. Cocok untuk toko kelontong, sembako, dan minimarket.',
                'Kasir Cepat, Barcode SKU, Tanpa Meja/Dine-in', 'Kasir Ritel', true, 'retail',
                [['Sembako', 'inventory_2'], ['Makanan & Snack', 'fastfood'], ['Minuman Ringan', 'local_drink'], ['Kebutuhan Rumah', 'cleaning_services'], ['Rokok & Tembakau', 'smoking_rooms'], ['Perawatan Diri', 'soap']]],
            ['salon', 'Barbershop & Salon', 'content_cut', 'purple',
                'Reservasi kapster, komisi staf per layanan, dan paket perawatan.',
                'Booking, Komisi Staf, Paket Layanan', 'Segera hadir', false, 'fnb', null],
        ];

        foreach ($rows as $i => [$slug, $name, $icon, $color, $tagline, $features, $badge, $active, $defaultMode, $preset]) {
            BusinessCategory::updateOrCreate(['slug' => $slug], [
                'name' => $name, 'icon' => $icon, 'color' => $color, 'tagline' => $tagline,
                'features' => $features, 'badge' => $badge, 'is_active' => $active,
                'default_mode' => $defaultMode,
                'preset' => $preset, 'sort_order' => $i,
            ]);
        }
    }
}
