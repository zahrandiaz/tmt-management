<?php

use App\Http\Controllers\ProfileController; // Pastikan ProfileController ada atau akan dibuat Breeze
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tmt\UserController;
use App\Http\Controllers\Tmt\RoleController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('tmt_dashboard'); // Ini adalah dashboard bawaan Breeze
})->middleware(['auth', 'verified'])->name('dashboard');

// --- TAMBAHKAN RUTE BARU KITA DI SINI ---
Route::get('/tmt-dashboard', function () {
    return view('tmt_dashboard'); // Ini view tmt_dashboard.blade.php yang kita buat
})->middleware(['auth', 'verified'])->name('tmt.dashboard');
// --- AKHIR DARI RUTE BARU KITA ---

Route::middleware('auth')->group(function () {
    // Pastikan ProfileController benar-benar ada di App\Http\Controllers\ProfileController
    // Jika Anda menggunakan Breeze, ini seharusnya sudah dibuatkan.
    if (class_exists(App\Http\Controllers\ProfileController::class)) {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    }
});

// --- TAMBAHKAN GRUP RUTE UNTUK TMT ADMIN DI SINI ---
Route::middleware(['auth', 'verified', 'role:Super Admin TMT'])->prefix('tmt-admin')->name('tmt.admin.')->group(function () {
    // Rute untuk Manajemen Pengguna
    Route::resource('users', UserController::class);

    // Rute untuk Manajemen Peran
    Route::resource('roles', RoleController::class); // <-- TAMBAHKAN BARIS INI

    // Nanti rute untuk manajemen Peran, Izin, dan Pengaturan TMT bisa diletakkan di sini juga
});

require __DIR__.'/auth.php';