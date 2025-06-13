<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tmt\UserController;
use App\Http\Controllers\Tmt\RoleController;
use App\Http\Controllers\Tmt\SettingController; // <-- TAMBAHKAN INI
use App\Http\Controllers\Tmt\ActivityLogController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Ini adalah rute utama setelah login, yang menampilkan dashboard TMT
Route::get('/dashboard', function () {
    return view('tmt_dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


// Rute untuk profil pengguna yang dibuat oleh Breeze
Route::middleware('auth')->group(function () {
    if (class_exists(App\Http\Controllers\ProfileController::class)) {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    }
});


// --- GRUP RUTE UNTUK AREA ADMIN TMT ---
Route::middleware(['auth', 'verified', 'role:Super Admin TMT'])->prefix('tmt-admin')->name('tmt.admin.')->group(function () {
    
    // Rute untuk Manajemen Pengguna
    Route::resource('users', UserController::class);

    // Rute untuk Manajemen Peran
    Route::resource('roles', RoleController::class);
    
    // --- Rute untuk Pengaturan ---
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingController::class, 'update'])->name('settings.update');

    // [BARU] Rute untuk Log Aktivitas
    Route::get('activity-log', [ActivityLogController::class, 'index'])->name('activity_log.index')->middleware('permission:view system logs');


});


// Ini memuat semua rute otentikasi (login, register, logout, dll.)
require __DIR__.'/auth.php';

