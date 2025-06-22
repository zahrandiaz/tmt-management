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
use App\Modules\Karung\Http\Controllers\StockAdjustmentController;
use App\Modules\Karung\Http\Controllers\NotificationController;



// Rute Dashboard
Route::get('/dashboard-modul', [DashboardController::class, 'index'])->name('dashboard')->middleware('permission:karung.access_module');

// Rute Master Data
Route::resource('product-categories', ProductCategoryController::class)->middleware('permission:karung.manage_categories');
Route::resource('product-types', ProductTypeController::class)->middleware('permission:karung.manage_types');
Route::get('suppliers/{supplier}/history', [SupplierController::class, 'history'])->name('suppliers.history')->middleware('permission:karung.view_purchases');
Route::resource('suppliers', SupplierController::class)->middleware('permission:karung.manage_suppliers');
Route::get('customers/{customer}/history', [CustomerController::class, 'history'])->name('customers.history')->middleware('permission:karung.view_sales');
Route::resource('customers', CustomerController::class)->middleware('permission:karung.manage_customers');
Route::get('products/bulk-price-edit', [ProductController::class, 'bulkPriceEdit'])->name('products.bulk-price.edit')->middleware('permission:karung.manage_products');
Route::post('products/bulk-price-update', [ProductController::class, 'bulkPriceUpdate'])->name('products.bulk-price.update')->middleware('permission:karung.manage_products');

// [PERBAIKAN] Definisikan route API untuk galeri SEBELUM resource controller produk.
Route::get('/products/gallery-api', [ProductController::class, 'getProductGallery'])
     ->name('products.gallery.api')
     ->middleware('permission:karung.manage_products'); // Pastikan permission sesuai

Route::resource('products', ProductController::class)->middleware('permission:karung.manage_products');

// Rute untuk Biaya Operasional
Route::resource('operational-expenses', \App\Modules\Karung\Http\Controllers\OperationalExpenseController::class)
    ->middleware('permission:karung.manage_expenses');

// [BARU] Rute untuk Penyesuaian Stok
Route::resource('stock-adjustments', StockAdjustmentController::class)
    ->only(['index', 'create', 'store']) // Kita hanya butuh 3 method ini untuk sekarang
    ->middleware('permission:karung.manage_stock_adjustments');

// Rute Transaksi Pembelian
Route::resource('purchases', PurchaseTransactionController::class)->middleware('permission:karung.access_module');
Route::post('purchases/{purchase}/cancel', [PurchaseTransactionController::class, 'cancel'])->name('purchases.cancel');
Route::post('purchases/{purchase}/restore', [PurchaseTransactionController::class, 'restore'])->name('purchases.restore');
Route::post('purchases/{purchase}/pay', [PurchaseTransactionController::class, 'updatePayment'])->name('purchases.update_payment');

// Rute Transaksi Penjualan
Route::resource('sales', SalesTransactionController::class)->middleware('permission:karung.access_module');
Route::post('sales/{sale}/cancel', [SalesTransactionController::class, 'cancel'])->name('sales.cancel');
Route::post('sales/{sale}/restore', [SalesTransactionController::class, 'restore'])->name('sales.restore');
Route::post('sales/{sale}/pay', [SalesTransactionController::class, 'updatePayment'])->name('sales.update_payment');
Route::get('sales/{sale}/print-thermal', [SalesTransactionController::class, 'printThermal'])->name('sales.print.thermal')->middleware('permission:karung.view_sales');
Route::get('sales/{sale}/download-pdf', [SalesTransactionController::class, 'downloadPdf'])->name('sales.download.pdf')->middleware('permission:karung.view_sales');


// Rute Laporan
Route::middleware(['permission:karung.view_reports'])->prefix('reports')->name('reports.')->group(function() {
    Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
    Route::get('/sales/export', [ReportController::class, 'exportSales'])->name('sales.export');
    Route::get('/sales/export-pdf', [ReportController::class, 'exportSalesPdf'])->name('sales.export.pdf');
    Route::get('/purchases', [ReportController::class, 'purchases'])->name('purchases');
    Route::get('/purchases/export', [ReportController::class, 'exportPurchases'])->name('purchases.export');
    Route::get('/purchases/export-pdf', [ReportController::class, 'exportPurchasesPdf'])->name('purchases.export.pdf');
    Route::get('/stock', [ReportController::class, 'stockReport'])->name('stock');
    Route::get('/stock/{product}/history', [ReportController::class, 'stockHistory'])->name('stock.history');
    Route::get('/stock/export', [ReportController::class, 'exportStock'])->name('stock.export');
    Route::get('/stock/export-pdf', [ReportController::class, 'exportStockPdf'])->name('stock.export.pdf');
    Route::get('/profit-loss', [ReportController::class, 'profitAndLoss'])->name('profit_and_loss');
    Route::get('/profit-loss/export', [ReportController::class, 'exportProfitLoss'])->name('profit_loss.export');
    Route::get('/profit-loss/export-pdf', [ReportController::class, 'exportProfitLossPdf'])->name('profit_loss.export.pdf');
    Route::get('/customer-performance', [ReportController::class, 'customerPerformance'])->name('customer_performance');
    Route::get('/product-performance', [ReportController::class, 'productPerformance'])->name('product_performance');
    Route::get('/cash-flow', [ReportController::class, 'cashFlow'])->name('cash_flow');
    Route::get('/download/{filename}', [ReportController::class, 'downloadExportedReport'])->name('download');
    Route::get('/pusat-unduhan', [ReportController::class, 'downloadCenter'])->name('download_center');
});

// [MODIFIKASI] Tambahkan rute ini untuk aksi hapus
Route::delete('/reports/download-center/{report}', [ReportController::class, 'destroyExportedReport'])
    ->name('reports.download_center.destroy')
    ->middleware('role:Super Admin TMT');

Route::get('/notifications/latest', [NotificationController::class, 'getLatest'])->name('notifications.latest')->middleware('auth');
Route::post('/notifications/{notificationId}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notification.markAsRead')->middleware('auth');