<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /* ---- Kategori usaha + bisnis demo ---- */
        $this->call(BusinessCategorySeeder::class);
        $business = Business::create([
            'business_category_id' => BusinessCategory::where('slug', 'cafe')->value('id'),
            'code' => 'CS-ORELY-0001', 'name' => config('pos.store_name'), 'owner_name' => 'Orely Studio',
            'email' => 'admin@cashierstore.test', 'phone' => '0812-0000-0001', 'location' => 'Surakarta',
            'outlet_count' => '1 Outlet', 'status' => 'active',
        ]);

        /* ---- Staff ---- */
        $admin = User::create([
            'business_id' => $business->id,
            'name' => 'Orely Studio', 'email' => 'admin@cashierstore.test', 'password' => 'password',
            'role' => 'admin', 'pin' => '0000', 'shift' => '08:00 - 16:00', 'phone' => '0812-0000-0001',
        ]);
        Auth::setUser($admin);   // data berikutnya otomatis milik bisnis demo
        User::create([
            'business_id' => $business->id,
            'name' => 'Clara Lucas', 'email' => 'kasir@cashierstore.test', 'password' => 'password',
            'role' => 'kasir', 'pin' => '1234', 'shift' => '07:00 - 15:30', 'phone' => '0812-0000-0002',
        ]);
        User::create([
            'business_id' => $business->id,
            'name' => 'Elena Morris', 'email' => 'elena@cashierstore.test', 'password' => 'password',
            'role' => 'kasir', 'pin' => '5678', 'shift' => '08:00 - 16:00', 'phone' => '0812-0000-0003',
        ]);

        /* ---- Kategori ---- */
        $cats = [];
        foreach ([['Main Course', '🍛'], ['Dessert', '🍰'], ['Drinks', '🥤'], ['Asian', '🍜'], ['Western', '🧇'], ['Sides', '🥟']] as $i => [$n, $e]) {
            $cats[$n] = Category::create(['name' => $n, 'emoji' => $e, 'sort_order' => $i]);
        }

        /* ---- Produk (mengikuti prototype), harga dalam Rupiah ---- */
        $menu = [
            ['Chicken Dimsum', 'Sides', 38000, 15000, '🥟', 40],
            ['Matcha Latte', 'Drinks', 32000, 11000, '🍵', 60],
            ['Fruit Parfait', 'Dessert', 35000, 14000, '🍨', 25],
            ['Grilled Salmon with Salad', 'Main Course', 85000, 42000, '🥗', 12],
            ['Strawberry Waffles', 'Western', 42000, 16000, '🧇', 30],
            ['Omurice', 'Asian', 55000, 21000, '🍛', 35],
            ['Almond Croissant', 'Sides', 28000, 10000, '🥐', 4],
            ['Truffle Omelette', 'Western', 62000, 25000, '🍳', 18],
            ['Iced Americano', 'Drinks', 24000, 6000, '🧊', 80],
            ['Cappuccino', 'Drinks', 30000, 9000, '☕', 70],
            ['Avocado Toast', 'Western', 45000, 18000, '🥑', 3],
            ['Chocolate Lava Cake', 'Dessert', 38000, 15000, '🍫', 0],
        ];
        $products = [];
        foreach ($menu as $i => [$name, $cat, $price, $cost, $emoji, $stock]) {
            $p = Product::create([
                'category_id' => $cats[$cat]->id, 'sku' => 'MNU-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'name' => $name, 'price' => $price, 'cost_price' => $cost, 'emoji' => $emoji,
                'stock' => 0, 'min_stock' => 5, 'is_active' => true,
            ]);
            if ($stock > 0) $p->adjustStock($stock, 'in', 'Stok awal', $admin->id);
            $products[] = $p;
        }

        /* ---- Pelanggan ---- */
        $customers = [];
        $rows = [
            ['Sarah Jenkins', 'sarah.j@outlook.com', '0812-4091-8842', 'gold', 'Suka meja 4 dekat jendela. Oat milk untuk semua minuman espresso.', 'Alergi kacang parah (beritahu dapur)'],
            ['Michael Tan', 'michael.tan@mail.com', '0811-9923-412', 'vip', 'Cold brew & takeaway.', null],
            ['Amanda Putri', 'amanda.p@studio.io', '0813-2200-1100', 'silver', 'Penyuka avocado toast.', null],
            ['David Lee', 'david.lee@mail.com', '0822-4801-993', 'bronze', 'Flat white di pagi hari.', null],
            ['Clara Wijaya', 'clara.wijaya@gmail.com', '0822-7788-1234', 'gold', 'Strawberry waffle di akhir pekan.', null],
            ['Budi Santoso', 'budi.s@mail.com', '0857-1111-2222', 'bronze', null, null],
            ['Siti Rahmawati', 'siti.r@mail.com', '0878-3333-4444', 'silver', 'Tidak suka terlalu manis.', null],
            ['Rizky Pratama', 'rizky.p@mail.com', '0896-5555-6666', 'bronze', null, null],
        ];
        foreach ($rows as $i => [$name, $email, $phone, $tier, $notes, $allergy]) {
            $customers[] = Customer::create([
                'name' => $name, 'email' => $email, 'phone' => $phone, 'tier' => $tier,
                'notes' => $notes, 'allergy' => $allergy,
                'birthday' => now()->subYears(25 + $i)->addDays($i * 3 - 2)->toDateString(),
                'created_at' => now()->subDays(rand(5, 300)),
            ]);
        }

        /* ---- Transaksi contoh (7 hari terakhir) supaya dashboard langsung terisi ---- */
        mt_srand(42);
        $seq = [];
        $taxRate = config('pos.tax_rate');
        $cashiers = User::where('role', 'kasir')->pluck('id')->all();

        for ($d = 6; $d >= 0; $d--) {
            $count = $d === 0 ? mt_rand(14, 20) : mt_rand(10, 18);
            for ($n = 0; $n < $count; $n++) {
                $when = now()->subDays($d)->setTime(8, 0)->addMinutes(mt_rand(0, 720));
                $key = $when->format('Ymd');
                $seq[$key] = ($seq[$key] ?? 0) + 1;

                $items = [];
                $subtotal = 0;
                foreach ((array) array_rand($products, mt_rand(1, 3)) as $idx) {
                    $p = $products[$idx];
                    $qty = mt_rand(1, 2);
                    $items[] = ['product_id' => $p->id, 'product_name' => $p->name, 'price' => $p->price, 'cost_price' => $p->cost_price, 'qty' => $qty, 'subtotal' => $p->price * $qty];
                    $subtotal += $p->price * $qty;
                }
                $tax = round($subtotal * $taxRate);
                $total = $subtotal + $tax;
                $customer = mt_rand(0, 100) < 55 ? $customers[array_rand($customers)] : null;
                $method = ['cash', 'qris', 'card'][mt_rand(0, 2)];

                $order = Order::create([
                    'invoice_no' => sprintf('INV-%s-%04d', $key, $seq[$key]),
                    'user_id' => $cashiers[array_rand($cashiers)],
                    'customer_id' => $customer?->id,
                    'order_type' => mt_rand(0, 100) < 65 ? 'dine_in' : 'takeaway',
                    'table_no' => mt_rand(1, 8),
                    'subtotal' => $subtotal, 'tax' => $tax, 'total' => $total,
                    'payment_method' => $method,
                    'paid_amount' => $method === 'cash' ? ceil($total / 10000) * 10000 : $total,
                    'change_amount' => $method === 'cash' ? ceil($total / 10000) * 10000 - $total : 0,
                    'status' => 'paid', 'paid_at' => $when,
                    'created_at' => $when, 'updated_at' => $when,
                ]);
                $order->items()->createMany($items);

                if ($customer) {
                    $customer->increment('visits');
                    $customer->increment('total_spent', $total);
                    $customer->increment('points', (int) floor($total / 10000));
                    $customer->update(['last_visit_at' => max($customer->last_visit_at ?? $when, $when)]);
                }
            }
        }
    }
}
