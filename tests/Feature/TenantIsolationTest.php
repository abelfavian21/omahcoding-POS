<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\Category;
use App\Models\Concerns\BelongsToBusiness;
use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use LogicException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    /** Model yang punya kolom business_id tetapi memang BUKAN data tenant (catatan platform). */
    private const BUKAN_TENANT = [\App\Models\PlatformAuditLog::class];

    /** Satu-satunya berkas yang boleh melewati isolasi. Menambah baris di sini = keputusan yang harus di-review. */
    private const BOLEH_BYPASS = [
        'Auth/TenantUserProvider.php',
        'Http/Controllers/AuthController.php',                    // loginPin: cari kasir di bisnis dari kode toko, sebelum ada konteks
        'Http/Controllers/MidtransWebhookController.php',
        'Http/Controllers/Platform/BusinessController.php',
        'Http/Controllers/Platform/DashboardController.php',
        'Models/Concerns/BelongsToBusiness.php',
        'Services/QrisPaymentService.php',
        'Support/TenantContext.php',
    ];

    private function business(string $code): Business
    {
        $cat = BusinessCategory::first() ?? BusinessCategory::create([
            'slug' => 'cafe', 'name' => 'Cafe', 'icon' => 'x', 'color' => 'blue', 'tagline' => 't',
            'features' => 'x', 'badge' => null, 'preset' => [], 'is_active' => true, 'sort_order' => 1,
        ]);

        return Business::create([
            'business_category_id' => $cat->id, 'code' => $code, 'name' => 'Toko ' . $code, 'owner_name' => 'Owner',
            'email' => strtolower($code) . '@t.test', 'status' => 'active', 'trial_ends_at' => null,
        ]);
    }

    private function ctx(): TenantContext
    {
        return app(TenantContext::class);
    }

    public function test_setiap_model_ber_business_id_memakai_trait(): void
    {
        $tanpaTrait = [];

        foreach (glob(app_path('Models/*.php')) as $file) {
            $class = 'App\\Models\\' . basename($file, '.php');
            if (! class_exists($class) || ! is_subclass_of($class, Model::class) || in_array($class, self::BUKAN_TENANT, true)) {
                continue;
            }
            if (Schema::hasColumn((new $class)->getTable(), 'business_id')
                && ! in_array(BelongsToBusiness::class, class_uses_recursive($class), true)) {
                $tanpaTrait[] = $class;
            }
        }

        $this->assertSame([], $tanpaTrait, 'Model ber-business_id tanpa BelongsToBusiness (data bisa bocor lintas toko).');
    }

    public function test_tanpa_konteks_tidak_ada_data_yang_terlihat(): void
    {
        [$a, $b] = [$this->business('CS-AAA-1111'), $this->business('CS-BBB-2222')];
        $this->ctx()->run($a->id, fn () => Category::create(['name' => 'Kopi', 'emoji' => 'x', 'sort_order' => 0]));
        $this->ctx()->run($b->id, fn () => Category::create(['name' => 'Teh', 'emoji' => 'x', 'sort_order' => 0]));

        $this->ctx()->set(null);
        $this->assertSame(0, Category::count(), 'konteks kosong harus fail-closed');
        $this->assertSame(['Kopi'], $this->ctx()->run($a->id, fn () => Category::pluck('name')->all()));
        $this->assertSame(['Teh'], $this->ctx()->run($b->id, fn () => Category::pluck('name')->all()));
        $this->assertSame(2, $this->ctx()->withoutTenant(fn () => Category::count()));
    }

    public function test_konteks_dipulihkan_setelah_run_dan_saat_exception(): void
    {
        $a = $this->business('CS-AAA-1111');

        try {
            $this->ctx()->run($a->id, fn () => throw new \RuntimeException('x'));
        } catch (\RuntimeException) {
        }

        $this->assertNull($this->ctx()->id());
        $this->assertFalse($this->ctx()->isUnscoped());
    }

    public function test_create_tanpa_konteks_dan_tanpa_business_id_ditolak(): void
    {
        $this->ctx()->set(null);
        $this->expectException(LogicException::class);
        Category::create(['name' => 'Yatim', 'emoji' => 'x', 'sort_order' => 0]);
    }

    public function test_tidak_bisa_menulis_ke_bisnis_lain_saat_ada_konteks(): void
    {
        [$a, $b] = [$this->business('CS-AAA-1111'), $this->business('CS-BBB-2222')];
        // business_id sengaja tidak fillable (create([... 'business_id' => ..]) diam-diam diabaikan), jadi isi lewat atribut.
        $cat = new Category(['name' => 'X', 'emoji' => 'x', 'sort_order' => 0]);
        $cat->business_id = $b->id;

        $this->expectException(LogicException::class);
        $this->ctx()->run($a->id, fn () => $cat->save());
    }

    public function test_bypass_isolasi_hanya_di_berkas_yang_diizinkan(): void
    {
        $base = str_replace('\\', '/', app_path()) . '/';
        $melanggar = [];

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(app_path(), RecursiveDirectoryIterator::SKIP_DOTS)) as $f) {
            if ($f->getExtension() !== 'php') {
                continue;
            }
            $rel = substr(str_replace('\\', '/', $f->getPathname()), strlen($base));
            if (preg_match('/withoutGlobalScopes?\s*\(|withoutTenant\s*\(/', file_get_contents($f->getPathname()))
                && ! in_array($rel, self::BOLEH_BYPASS, true)) {
                $melanggar[] = $rel;
            }
        }

        $this->assertSame([], $melanggar, 'Berkas ini melewati isolasi tenant tanpa terdaftar di BOLEH_BYPASS.');
    }
}
