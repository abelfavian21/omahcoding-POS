<?php

namespace App\Support;

/**
 * Ikon vektor (Material Symbols) menggantikan emoji.
 * Kolom lama (products.emoji, categories.emoji, business_categories.icon) boleh berisi emoji lama ATAU nama ikon;
 * keduanya diterjemahkan di sini saat ditampilkan, jadi data lama tidak perlu dimigrasi.
 */
class Icons
{
    /** Emoji lama -> nama ikon Material Symbols. */
    private const MAP = [
        '☕' => 'coffee',
        '🍵' => 'emoji_food_beverage',
        '🫖' => 'emoji_food_beverage',
        '🥤' => 'local_drink',
        '🧋' => 'local_drink',
        '🧃' => 'local_drink',
        '🥛' => 'local_drink',
        '🍹' => 'local_bar',
        '🍸' => 'local_bar',
        '🍷' => 'wine_bar',
        '🍺' => 'sports_bar',
        '🍻' => 'sports_bar',
        '🍶' => 'liquor',
        '💧' => 'water_drop',
        '🍛' => 'rice_bowl',
        '🍚' => 'rice_bowl',
        '🍙' => 'rice_bowl',
        '🍜' => 'ramen_dining',
        '🍝' => 'dinner_dining',
        '🍲' => 'soup_kitchen',
        '🥘' => 'soup_kitchen',
        '🥗' => 'nutrition',
        '🥟' => 'tapas',
        '🍤' => 'set_meal',
        '🐟' => 'set_meal',
        '🍣' => 'set_meal',
        '🍱' => 'bento',
        '🍔' => 'lunch_dining',
        '🥪' => 'lunch_dining',
        '🌮' => 'lunch_dining',
        '🌯' => 'lunch_dining',
        '🍟' => 'fastfood',
        '🌭' => 'fastfood',
        '🍿' => 'fastfood',
        '🍕' => 'local_pizza',
        '🍗' => 'kebab_dining',
        '🍖' => 'kebab_dining',
        '🥩' => 'kebab_dining',
        '🍢' => 'kebab_dining',
        '🥓' => 'breakfast_dining',
        '🍳' => 'egg_alt',
        '🥚' => 'egg_alt',
        '🧇' => 'breakfast_dining',
        '🥞' => 'breakfast_dining',
        '🍞' => 'bakery_dining',
        '🥐' => 'bakery_dining',
        '🥖' => 'bakery_dining',
        '🥨' => 'bakery_dining',
        '🥯' => 'bakery_dining',
        '🍰' => 'cake',
        '🎂' => 'cake',
        '🧁' => 'cake',
        '🍨' => 'icecream',
        '🍦' => 'icecream',
        '🍧' => 'icecream',
        '🍩' => 'donut_large',
        '🍪' => 'cookie',
        '🍫' => 'cookie',
        '🍬' => 'cookie',
        '🍎' => 'nutrition',
        '🍌' => 'nutrition',
        '🍓' => 'nutrition',
        '🍇' => 'nutrition',
        '🍉' => 'nutrition',
        '🍊' => 'nutrition',
        '🥑' => 'eco',
        '🥕' => 'eco',
        '🌽' => 'eco',
        '🥬' => 'eco',
        '🥦' => 'eco',
        '🍽' => 'restaurant',
        '🧊' => 'ac_unit',
        '🛒' => 'shopping_cart',
        '🛍' => 'shopping_bag',
        '📦' => 'inventory_2',
        '💈' => 'content_cut',
        '🎉' => 'celebration',
        '👤' => 'person',
        '⚠' => 'warning',
        '✓' => 'check',
        '★' => 'star',
    ];

    /** Pilihan ikon di form produk: nama ikon => label. */
    private const PICKER = [
        'coffee' => 'Kopi',
        'local_cafe' => 'Cangkir',
        'emoji_food_beverage' => 'Teh',
        'local_drink' => 'Minuman',
        'local_bar' => 'Koktail',
        'wine_bar' => 'Anggur',
        'sports_bar' => 'Bir',
        'water_drop' => 'Air',
        'rice_bowl' => 'Nasi',
        'ramen_dining' => 'Mi',
        'lunch_dining' => 'Burger',
        'fastfood' => 'Cepat saji',
        'local_pizza' => 'Pizza',
        'kebab_dining' => 'Sate',
        'set_meal' => 'Ikan',
        'egg_alt' => 'Telur',
        'breakfast_dining' => 'Sarapan',
        'bakery_dining' => 'Roti',
        'cake' => 'Kue',
        'icecream' => 'Es krim',
        'cookie' => 'Kukis',
        'donut_large' => 'Donat',
        'tapas' => 'Dimsum',
        'soup_kitchen' => 'Sup',
        'bento' => 'Bento',
        'dinner_dining' => 'Pasta',
        'nutrition' => 'Buah',
        'eco' => 'Sayur',
        'restaurant' => 'Makanan',
        'takeout_dining' => 'Bawa pulang',
        'shopping_basket' => 'Belanja',
        'inventory_2' => 'Paket',
    ];

    public static function map(): array
    {
        return self::MAP;
    }

    public static function picker(): array
    {
        return self::PICKER;
    }

    /** Nama ikon aman untuk ditampilkan; emoji tak dikenal / kosong jatuh ke $fallback. */
    public static function name(?string $value, string $fallback = 'restaurant'): string
    {
        $v = trim(str_replace(["\u{FE0F}", "\u{200D}"], '', (string) $value));
        if ($v === '') {
            return $fallback;
        }
        if (preg_match('/^[a-z][a-z0-9_]{1,40}$/', $v)) {
            return $v;   // sudah berupa nama ikon
        }

        return self::MAP[$v] ?? self::MAP[mb_substr($v, 0, 1)] ?? $fallback;
    }
}
