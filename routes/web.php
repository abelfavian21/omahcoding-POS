<?php

use App\Http\Controllers\AuditController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\QrisController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Route;

/* ---------- Publik: landing page / company profile ---------- */
Route::get('/', [LandingController::class, 'index'])->name('landing');

/* ---------- Webhook Midtrans (publik, tanpa CSRF; diverifikasi lewat signature_key) ---------- */
Route::post('/payments/midtrans/notify', [MidtransWebhookController::class, 'notify'])->middleware('throttle:300,1')->name('midtrans.notify');

/* ---------- Guest (limiter didefinisikan di AppServiceProvider) ---------- */
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login.password');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:register')->name('register');
    Route::post('/login/pin', [AuthController::class, 'loginPin'])->middleware('throttle:pin')->name('login.pin');
});

/* ---------- Auth (admin & kasir); "account" = cek akun/bisnis masih aktif di tiap request ---------- */
Route::middleware(['auth', 'account'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/office', [OfficeController::class, 'index'])->name('office');

    // Kasir / POS
    Route::get('/pos', [PosController::class, 'index'])->name('pos');
    Route::get('/pos/customers', [PosController::class, 'customers'])->middleware('throttle:120,1')->name('pos.customers');
    Route::post('/pos/customers', [PosController::class, 'quickCustomer'])->middleware('throttle:30,1')->name('pos.customers.store');
    Route::post('/pos/checkout', [PosController::class, 'checkout'])->middleware('throttle:60,1')->name('pos.checkout');
    // QRIS manual: gambar QRIS statis milik bisnis (disajikan hanya ke user bisnis tsb)
    Route::get('/pos/qris-manual', [PosController::class, 'manualQris'])->middleware('throttle:120,1')->name('pos.qris.manual');
    // QRIS dinamis (Midtrans)
    Route::post('/pos/qris', [QrisController::class, 'store'])->middleware('throttle:30,1')->name('pos.qris.store');
    Route::get('/pos/qris/{payment}', [QrisController::class, 'show'])->middleware('throttle:90,1')->name('pos.qris.show');
    Route::get('/pos/qris/{payment}/qr', [QrisController::class, 'qr'])->middleware('throttle:60,1')->name('pos.qris.qr');
    Route::post('/pos/qris/{payment}/cancel', [QrisController::class, 'cancel'])->middleware('throttle:30,1')->name('pos.qris.cancel');
    Route::get('/orders/{order}/receipt', [OrderController::class, 'receipt'])->name('orders.receipt');

    // Shift kas (kasir: milik sendiri; admin: semua)
    Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts.index');
    Route::post('/shifts', [ShiftController::class, 'store'])->name('shifts.store');
    Route::post('/shifts/{shift}/close', [ShiftController::class, 'close'])->name('shifts.close');

    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::resource('customers', CustomerController::class)->except(['show', 'destroy']);

    /* ---------- Khusus admin ---------- */
    Route::middleware('role:admin')->group(function () {
        // dashboard memuat omzet & daftar staff -> bukan untuk kasir
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('products', ProductController::class)->except(['index', 'show']);
        Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
        Route::post('/stock', [StockController::class, 'store'])->name('stock.store');
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/pdf', [ReportController::class, 'pdf'])->middleware('throttle:10,1')->name('reports.pdf');
        Route::get('/reports/excel', [ReportController::class, 'excel'])->middleware('throttle:10,1')->name('reports.excel');
        Route::post('/orders/{order}/void', [OrderController::class, 'void'])->middleware('throttle:20,1')->name('orders.void');

        Route::resource('staff', StaffController::class)->except(['show']);

        Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::put('/settings/qris', [SettingsController::class, 'updateQris'])->middleware('throttle:20,1')->name('settings.qris');
        Route::put('/settings/midtrans', [SettingsController::class, 'updateMidtrans'])->middleware('throttle:10,1')->name('settings.midtrans');
        Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
    });
});
