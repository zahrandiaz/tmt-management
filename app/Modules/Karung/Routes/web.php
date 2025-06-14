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

// Rute Dashboard
Route::get('/dashboard-modul', [DashboardController::class, 'index'])->name('dashboard')->middleware('permission:karung.access_module');

// Rute Master Data
Route::resource('product-categories', ProductCategoryController::class)->middleware('permission:karung.manage_categories');
Route::resource('product-types', ProductTypeController::class)->middleware('permission:karung.manage_types');
Route::get('suppliers/{supplier}/history', [SupplierController::class, 'history'])->name('suppliers.history')->middleware('permission:karung.view_purchases');
Route::resource('suppliers', SupplierController::class)->middleware('permission:karung.manage_suppliers');
Route::get('customers/{customer}/history', [CustomerController::class, 'history'])->name('customers.history')->middleware('permission:karung.view_sales');
Route::resource('customers', CustomerController::class)->middleware('permission:karung.manage_customers');
Route::resource('products', ProductController::class)->middleware('permission:karung.manage_products');

// Rute Transaksi Pembelian
Route::resource('purchases', PurchaseTransactionController::class)
    ->except(['destroy'])
    ->middleware(['permission:karung.view_purchases|karung.create_purchases|karung.edit_purchases']);

Route::post('purchases/{purchase}/cancel', [PurchaseTransactionController::class, 'cancel'])
    ->name('purchases.cancel')
    ->middleware('permission:karung.cancel_purchases');

// [MODIFIKASI] Rute Transaksi Penjualan
Route::resource('sales', SalesTransactionController::class)
    ->except(['destroy']) // <-- Hanya 'destroy' yang dikecualikan
    ->middleware(['permission:karung.view_sales|karung.create_sales|karung.edit_sales']); // <-- Tambahkan izin edit

Route::post('sales/{sale}/cancel', [SalesTransactionController::class, 'cancel'])
    ->name('sales.cancel')
    ->middleware('permission:karung.cancel_sales');

// Rute Laporan
Route::middleware(['permission:karung.view_reports'])->prefix('reports')->name('reports.')->group(function() {
    Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
    Route::get('/purchases', [ReportController::class, 'purchases'])->name('purchases');
    Route::get('/stock', [ReportController::class, 'stockReport'])->name('stock');
    Route::get('/profit-loss', [ReportController::class, 'profitAndLoss'])->name('profit_and_loss');
});
