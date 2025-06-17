<?php

namespace App\Modules\Karung\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class KarungServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $moduleName = 'Karung';
        
        $this->loadViewsFrom(__DIR__.'/../Resources/views', strtolower($moduleName));
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        // Ini adalah cara pendaftaran rute yang paling benar dan stabil
        Route::middleware(['web', 'auth', 'verified'])
             ->prefix('tmt/karung')
             ->name('karung.')
             ->group(__DIR__.'/../Routes/web.php');
    }
}