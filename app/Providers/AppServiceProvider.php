<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Pemakaian di Blade: @rupiah($angka)  =>  Rp 25.000
        Blade::directive('rupiah', fn ($e) => "<?php echo 'Rp ' . number_format((float) ($e), 0, ',', '.'); ?>");
    }
}
