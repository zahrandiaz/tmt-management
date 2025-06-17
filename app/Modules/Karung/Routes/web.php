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
Route::resource('purchases', PurchaseTransactionController::class)->middleware('permission:karung.access_module');
Route::post('purchases/{purchase}/cancel', [PurchaseTransactionController::class, 'cancel'])->name('purchases.cancel');
// [BARU] Route untuk restore pembelian
Route::post('purchases/{purchase}/restore', [PurchaseTransactionController::class, 'restore'])->name('purchases.restore');

// Rute Transaksi Penjualan
Route::resource('sales', SalesTransactionController::class)->middleware('permission:karung.access_module');
Route::post('sales/{sale}/cancel', [SalesTransactionController::class, 'cancel'])->name('sales.cancel');
// [BARU] Route untuk restore penjualan
Route::post('sales/{sale}/restore', [SalesTransactionController::class, 'restore'])->name('sales.restore');


// Rute Laporan
Route::middleware(['permission:karung.view_reports'])->prefix('reports')->name('reports.')->group(function() {
    Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
    Route::get('/sales/export', [ReportController::class, 'exportSales'])->name('sales.export');
    Route::get('/sales/export-pdf', [ReportController::class, 'exportSalesPdf'])->name('sales.export.pdf');
    Route::get('/purchases', [ReportController::class, 'purchases'])->name('purchases');
    Route::get('/purchases/export', [ReportController::class, 'exportPurchases'])->name('purchases.export');
    Route::get('/purchases/export-pdf', [ReportController::class, 'exportPurchasesPdf'])->name('purchases.export.pdf');
    Route::get('/stock', [ReportController::class, 'stockReport'])->name('stock');
    Route::get('/stock/export', [ReportController::class, 'exportStock'])->name('stock.export');
    Route::get('/stock/export-pdf', [ReportController::class, 'exportStockPdf'])->name('stock.export.pdf');
    Route::get('/profit-loss', [ReportController::class, 'profitAndLoss'])->name('profit_and_loss');
    Route::get('/profit-loss/export', [ReportController::class, 'exportProfitLoss'])->name('profit_loss.export');
    Route::get('/profit-loss/export-pdf', [ReportController::class, 'exportProfitLossPdf'])->name('profit_loss.export.pdf');
});