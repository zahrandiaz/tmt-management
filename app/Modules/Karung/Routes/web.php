<?php

use Illuminate\Support\Facades\Route;
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
Route::get('/dashboard-modul', [DashboardController::class, 'index'])->name('dashboard');
// Saya ganti URLnya menjadi /dashboard-modul agar tidak bentrok dengan /dashboard TMT
// dan nama rutenya tetap 'dashboard' (yang akan menjadi 'karung.dashboard')

// Rute untuk CRUD Kategori Produk
Route::resource('product-categories', ProductCategoryController::class); // <-- TAMBAHKAN BARIS INI
Route::resource('product-types', ProductTypeController::class); // <-- TAMBAHKAN BARIS INI
Route::resource('suppliers', SupplierController::class); // <-- TAMBAHKAN BARIS INI
Route::resource('customers', CustomerController::class); // <-- TAMBAHKAN BARIS INI
Route::resource('products', ProductController::class); // <-- TAMBAHKAN BARIS INI

// Rute Transaksi
Route::resource('purchases', PurchaseTransactionController::class); // v.normal
Route::resource('sales', SalesTransactionController::class); // <-- v.normal
//Route::resource('purchases', PurchaseTransactionController::class)->except(['edit', 'update', 'destroy']); // <-- v.1 (disable delete and edit)
//Route::resource('sales', SalesTransactionController::class)->except(['edit', 'update', 'destroy']); // <-- v.1 (disable delete and edit)

// Rute Laporan
Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales'); // <-- TAMBAHKAN BARIS INI


// Anda bisa menambahkan resource route lain di bawah ini nanti untuk Produk, Supplier, dll.