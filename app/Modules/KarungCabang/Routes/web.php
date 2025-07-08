<?php

use Illuminate\Support\Facades\Route;
use App\Modules\KarungCabang\Http\Controllers\DashboardController;
use App\Modules\KarungCabang\Http\Controllers\ProductController;

Route::prefix('tmt/karung-cabang')
    ->middleware(['auth', 'verified'])
    ->as('karungcabang.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // --- ROUTE PRODUK ---
        // Tambahkan route ini sebelum resource agar tidak tertimpa
        Route::get('products/bulk-price-edit', [ProductController::class, 'bulkPriceEdit'])->name('products.bulk-price.edit')->middleware('permission:karung.manage_products');
        Route::post('products/bulk-price-update', [ProductController::class, 'bulkPriceUpdate'])->name('products.bulk-price.update')->middleware('permission:karung.manage_products');
        
        Route::resource('products', ProductController::class)->middleware('permission:karung.manage_products'); 
    });