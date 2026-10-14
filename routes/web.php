<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Route;

/* ---------- Publik: landing page / company profile ---------- */
Route::get('/', [LandingController::class, 'index'])->name('landing');

/* ---------- Guest ---------- */
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.password');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:6,1')->name('register');
    Route::post('/login/pin', [AuthController::class, 'loginPin'])->middleware('throttle:10,1')->name('login.pin');
});

/* ---------- Auth (admin & kasir) ---------- */
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/office', [OfficeController::class, 'index'])->name('office');

    // Kasir / POS
    Route::get('/pos', [PosController::class, 'index'])->name('pos');
    Route::get('/pos/customers', [PosController::class, 'customers'])->name('pos.customers');
    Route::post('/pos/customers', [PosController::class, 'quickCustomer'])->name('pos.customers.store');
    Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
    Route::get('/orders/{order}/receipt', [OrderController::class, 'receipt'])->name('orders.receipt');

    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::resource('customers', CustomerController::class)->except(['show', 'destroy']);

    /* ---------- Khusus admin ---------- */
    Route::middleware('role:admin')->group(function () {
        Route::resource('products', ProductController::class)->except(['index', 'show']);
        Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
        Route::post('/stock', [StockController::class, 'store'])->name('stock.store');
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/pdf', [ReportController::class, 'pdf'])->name('reports.pdf');
        Route::get('/reports/excel', [ReportController::class, 'excel'])->name('reports.excel');
        Route::post('/orders/{order}/void', [OrderController::class, 'void'])->name('orders.void');

        Route::resource('staff', StaffController::class)->except(['show']);
    });
});
