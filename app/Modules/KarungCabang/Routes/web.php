<?php

use Illuminate\Support\Facades\Route;
use App\Modules\KarungCabang\Http\Controllers\DashboardController;

Route::prefix('tmt/karung-cabang')
    ->middleware(['auth', 'verified'])
    ->as('karungcabang.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });