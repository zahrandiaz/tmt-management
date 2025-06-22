<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


// Menjalankan queue worker setiap menit
Schedule::command('queue:work --stop-when-empty')
         ->everyMinute()
         ->withoutOverlapping();

// [MODIFIKASI] Tambahkan penjadwalan untuk membersihkan laporan lama
Schedule::command('reports:prune')
         ->dailyAt('02:00'); // Berjalan setiap hari pukul 2 pagi