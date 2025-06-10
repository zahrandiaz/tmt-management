<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Karung\Http\Controllers\DashboardController;
use App\Modules\Karung\Http\Controllers\ProductCategoryController;
use App\Modules\Karung\Http\Controllers\ProductTypeController;
use App\Modules\Karung\Http\Controllers\SupplierController;
use App\Modules\Karung\Http\Controllers\CustomerController;
use App\Modules\Karung\Http\Controllers\ProductController;
use App\Modules\Karung\Http\Controllers\PurchaseTransactionController;
use App\Modules\Karung\Http\Controllers\SalesTransactionController;
use App\Modules\Karung\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Web Routes for Karung Module
|--------------------------------------------------------------------------
|
| Semua rute di file ini secara otomatis akan memiliki:
| - Prefix URL: '/tmt/karung'
| - Prefix Nama Rute: 'karung.'
| - Middleware: 'web', 'auth', 'verified'
| Ini semua diatur terpusat di KarungServiceProvider.php
|
*/

// Rute untuk Dashboard Modul Karung
Route::get('/dashboard-modul', [DashboardController::class, 'index'])->name('dashboard')->middleware('permission:karung.access_module');

// --- Rute Master Data ---
Route::resource('product-categories', ProductCategoryController::class)->middleware('permission:karung.manage_categories');
Route::resource('product-types', ProductTypeController::class)->middleware('permission:karung.manage_types');
Route::resource('suppliers', SupplierController::class)->middleware('permission:karung.manage_suppliers');
Route::resource('customers', CustomerController::class)->middleware('permission:karung.manage_customers');
Route::resource('products', ProductController::class)->middleware('permission:karung.manage_products');

// --- Rute Transaksi ---
// Menggunakan 'permission:A|B' berarti pengguna harus punya izin A ATAU B
Route::resource('purchases', PurchaseTransactionController::class)
    ->except(['edit', 'update', 'destroy']) // Sesuai kesepakatan V1
    ->middleware(['permission:karung.view_purchases|karung.create_purchases']);

// Rute khusus untuk membatalkan transaksi pembelian
Route::post('purchases/{purchase}/cancel', [PurchaseTransactionController::class, 'cancel'])
    ->name('purchases.cancel')
    ->middleware('permission:karung.cancel_purchases'); // Pastikan izin ini sesuai

Route::resource('sales', SalesTransactionController::class)
    ->except(['edit', 'update', 'destroy']) // Sesuai kesepakatan V1
    ->middleware(['permission:karung.view_sales|karung.create_sales']);

// Rute khusus untuk membatalkan transaksi penjualan
Route::post('sales/{sale}/cancel', [SalesTransactionController::class, 'cancel'])
    ->name('sales.cancel')
    ->middleware('permission:karung.cancel_sales'); // Pastikan izin ini sesuai

// --- Rute Laporan ---
Route::middleware(['permission:karung.view_reports'])->prefix('reports')->name('reports.')->group(function() {
    Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
    Route::get('/purchases', [ReportController::class, 'purchases'])->name('purchases');
    Route::get('/stock', [ReportController::class, 'stockReport'])->name('stock');
    Route::get('/profit-loss', [ReportController::class, 'profitAndLoss'])->name('profit_and_loss');
});