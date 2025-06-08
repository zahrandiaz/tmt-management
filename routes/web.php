<?php

use App\Http\Controllers\ProfileController; // Pastikan ProfileController ada atau akan dibuat Breeze
use Illuminate\Support\Facades\Route;

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

require __DIR__.'/auth.php';