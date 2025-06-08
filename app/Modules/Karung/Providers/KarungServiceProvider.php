<?php

namespace App\Modules\Karung\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route; // <-- PASTIKAN 'use Route;' INI DITAMBAHKAN

class KarungServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Untuk sekarang, biarkan kosong.
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Definisikan path ke file rute modul Karung
        $moduleRoutesPath = __DIR__.'/../Routes/web.php';

        // Muat file rute dengan prefix URL, prefix nama, dan middleware
        Route::middleware(['web', 'auth', 'verified']) // Melindungi semua rute modul
             ->prefix('tmt/karung')                   // URL akan menjadi: domain.com/tmt/karung/...
             ->name('karung.')                       // Nama rute akan menjadi: karung.nama_rute
             ->group($moduleRoutesPath);

        // Muat file migrasi untuk modul Karung
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        // Muat file view untuk modul Karung dengan namespace 'karung'
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'karung');
    }
}