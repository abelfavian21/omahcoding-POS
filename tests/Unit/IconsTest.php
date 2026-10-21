<?php

namespace Tests\Unit;

use App\Support\Icons;
use PHPUnit\Framework\TestCase;

class IconsTest extends TestCase
{
    public function test_emoji_lama_diterjemahkan_ke_nama_ikon(): void
    {
        $this->assertSame('coffee', Icons::name('☕'));
        $this->assertSame('restaurant', Icons::name("🍽\u{FE0F}"));   // dengan variation selector
        $this->assertSame('rice_bowl', Icons::name('🍛'));
    }

    public function test_nama_ikon_dipertahankan_dan_kosong_memakai_fallback(): void
    {
        $this->assertSame('lunch_dining', Icons::name('lunch_dining'));
        $this->assertSame('restaurant', Icons::name(null));
        $this->assertSame('storefront', Icons::name('', 'storefront'));
    }

    public function test_emoji_tak_dikenal_dan_input_berbahaya_memakai_fallback(): void
    {
        $this->assertSame('restaurant', Icons::name('🦄'));
        $this->assertSame('restaurant', Icons::name('<script>alert(1)</script>'));
        $this->assertSame('restaurant', Icons::name('Nama Ikon Salah'));
    }

    public function test_semua_ikon_di_pemilih_valid(): void
    {
        foreach (array_keys(Icons::picker()) as $name) {
            $this->assertMatchesRegularExpression('/^[a-z][a-z0-9_]{1,40}$/', $name);
        }
    }
}
