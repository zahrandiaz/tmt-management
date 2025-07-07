<?php

namespace App\Modules\KarungCabang;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class KarungCabangServiceProvider extends ServiceProvider
{
    /**
     * Nama namespace untuk controller modul ini.
     *
     * @var string
     */
    protected $namespace = 'App\Modules\KarungCabang\Http\Controllers';

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Mendaftarkan file views modul
        $this->loadViewsFrom(__DIR__.'/Resources/views', 'karungcabang');

        // Mendaftarkan file routes modul
        $this->mapWebRoutes();
    }

    /**
     * Mendaftarkan routes web untuk modul.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
             ->namespace($this->namespace)
             ->group(__DIR__.'/Routes/web.php');
    }
}