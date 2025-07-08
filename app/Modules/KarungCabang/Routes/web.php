<?php

use Illuminate\Support\Facades\Route;
use App\Modules\KarungCabang\Http\Controllers\DashboardController;
use App\Modules\KarungCabang\Http\Controllers\ProductController;
use App\Modules\KarungCabang\Http\Controllers\SalesTransactionController; // <-- Tambahkan ini
use App\Modules\KarungCabang\Http\Controllers\ReturnController;

Route::prefix('tmt/karung-cabang')
    ->middleware(['auth', 'verified'])
    ->as('karungcabang.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // --- ROUTE PRODUK ---
        Route::get('/products/gallery-api', [ProductController::class, 'getProductGallery'])
        ->name('products.gallery.api')
        ->middleware('permission:karung.manage_products');

        Route::get('products/bulk-price-edit', [ProductController::class, 'bulkPriceEdit'])->name('products.bulk-price.edit')->middleware('permission:karung.manage_products');
        Route::post('products/bulk-price-update', [ProductController::class, 'bulkPriceUpdate'])->name('products.bulk-price.update')->middleware('permission:karung.manage_products');
        
        Route::resource('products', ProductController::class)->middleware('permission:karung.manage_products'); 

        // Rute Transaksi Penjualan
        Route::resource('sales', SalesTransactionController::class)->middleware('permission:karung.access_module');
        // Anda bisa menambahkan route cancel, restore, print, dll di sini sesuai kebutuhan.
    
        // (BARU) Tambahkan route kustom di bawah ini
        Route::post('sales/{sale}/cancel', [SalesTransactionController::class, 'cancel'])->name('sales.cancel');
        Route::post('sales/{sale}/restore', [SalesTransactionController::class, 'restore'])->name('sales.restore');
        Route::get('sales/{sale}/print-thermal', [SalesTransactionController::class, 'printThermal'])->name('sales.print.thermal')->middleware('permission:karung.view_sales');
        Route::get('sales/{sale}/download-pdf', [SalesTransactionController::class, 'downloadPdf'])->name('sales.download.pdf')->middleware('permission:karung.view_sales');
        
        // Rute untuk membuat retur
        Route::get('/sales/{salesTransaction}/returns/create', [ReturnController::class, 'createSalesReturn'])->name('sales.returns.create');
        Route::post('/sales/{salesTransaction}/returns', [ReturnController::class, 'storeSalesReturn'])->name('sales.returns.store');

        // Rute untuk riwayat retur
        Route::get('/returns/sales', [ReturnController::class, 'salesReturnIndex'])->name('returns.sales.index');
        Route::get('/returns/sales/{salesReturn}', [ReturnController::class, 'showSalesReturn'])->name('returns.sales.show');
        Route::get('/returns/sales/{salesReturn}/credit-note', [ReturnController::class, 'downloadCreditNotePdf'])->name('returns.sales.credit_note.pdf');
    });