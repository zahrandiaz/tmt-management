<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tmt\UserController;
use App\Http\Controllers\Tmt\RoleController; 
use App\Http\Controllers\Tmt\SettingController;
use App\Http\Controllers\Tmt\ActivityLogController;
use App\Http\Controllers\ReceiptVerificationController;

Route::get('/', function () {
    return view('welcome');
});

// [BARU v1.27] Rute publik untuk verifikasi struk
Route::get('/receipt/verify/{uuid}', [ReceiptVerificationController::class, 'verify'])->name('receipt.verify');


Route::get('/dashboard', function () {
    return view('tmt_dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    if (class_exists(App\Http\Controllers\ProfileController::class)) {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    }
});

Route::middleware(['auth', 'verified', 'role:Super Admin TMT'])->prefix('tmt-admin')->name('tmt.admin.')->group(function () {
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
    Route::get('activity-log', [ActivityLogController::class, 'index'])->name('activity_log.index')->middleware('permission:view system logs');
});

require __DIR__.'/auth.php';