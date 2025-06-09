<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tmt\UserController;
use App\Modules\Karung\Http\Controllers\DashboardController; // Biarkan ini jika Anda masih ingin menggunakan Dashboard Modul Karung
use App\Modules\Karung\Http\Controllers\ProductCategoryController; // <-- TAMBAHKAN USE STATEMENT INI
use App\Modules\Karung\Http\Controllers\ProductTypeController; // <-- TAMBAHKAN USE STATEMENT INI
use App\Modules\Karung\Http\Controllers\SupplierController; // <-- TAMBAHKAN USE STATEMENT INI
use App\Modules\Karung\Http\Controllers\CustomerController; // <-- TAMBAHKAN USE STATEMENT INI
use App\Modules\Karung\Http\Controllers\ProductController; // <-- PASTIKAN USE STATEMENT INI ADA
use App\Modules\Karung\Http\Controllers\PurchaseTransactionController; // <-- TAMBAHKAN USE STATEMENT INI
use App\Modules\Karung\Http\Controllers\SalesTransactionController; // <-- TAMBAHKAN USE STATEMENT INI
use App\Modules\Karung\Http\Controllers\ReportController; // <-- TAMBAHKAN USE STATEMENT INI


// Rute untuk Dashboard Modul Karung (jika masih dipakai)
//Route::get('/dashboard-modul', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard-modul', [DashboardController::class, 'index'])->name('dashboard')->middleware(['permission:karung.access_module']);
// Saya ganti URLnya menjadi /dashboard-modul agar tidak bentrok dengan /dashboard TMT
// dan nama rutenya tetap 'dashboard' (yang akan menjadi 'karung.dashboard')

// Grup untuk master data (anggap saja butuh izin 'manage_products' untuk kelola semua master data) // after fixing bug
Route::middleware(['permission:karung.manage_products'])->group(function () {
    Route::resource('product-categories', ProductCategoryController::class);
    Route::resource('product-types', ProductTypeController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::resource('customers', CustomerController::class);
    Route::resource('products', ProductController::class); 
});

// Grup untuk transaksi pembelian (anggap butuh izin 'manage_products' juga)
//Route::resource('purchases', PurchaseTransactionController::class); // v.normal
//Route::resource('sales', SalesTransactionController::class); // <-- v.normal
//Route::resource('purchases', PurchaseTransactionController::class)->except(['edit', 'update', 'destroy']); // <-- v.1 (disable delete and edit)
//Route::resource('sales', SalesTransactionController::class)->except(['edit', 'update', 'destroy']); // <-- v.1 (disable delete and edit)
//Route::resource('purchases', PurchaseTransactionController::class)->except(['edit', 'update', 'destroy'])->middleware(['permission:karung.manage_products']); // <-- v.1 (disable delete and edit) & after fixing bug
//Route::resource('sales', SalesTransactionController::class)->except(['edit', 'update', 'destroy'])->middleware(['permission:karung.create_sales|karung.manage_products']); // <-- v.1 (disable delete and edit) & after fixing bug
Route::resource('purchases', PurchaseTransactionController::class)->middleware(['permission:karung.manage_products']); // after fixing bug
Route::resource('sales', SalesTransactionController::class)->middleware(['permission:karung.create_sales|karung.manage_products']); // after fixing bug

// Grup untuk laporan
Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales')->middleware(['permission:karung.view_reports']); // after fixing bug

// --- TAMBAHKAN GRUP RUTE UNTUK TMT ADMIN DI SINI ---
Route::middleware(['auth', 'verified', 'role:Super Admin TMT'])->prefix('tmt-admin')->name('tmt.admin.')->group(function () {
    // Rute untuk Manajemen Pengguna
    Route::resource('users', UserController::class);

    // Nanti rute untuk manajemen Peran, Izin, dan Pengaturan TMT bisa diletakkan di sini juga
});

// Anda bisa menambahkan resource route lain di bawah ini nanti untuk Produk, Supplier, dll.