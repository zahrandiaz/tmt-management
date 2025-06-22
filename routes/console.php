<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule; // <-- Pastikan use statement ini ada

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


// [MODIFIKASI] Tambahkan penjadwalan untuk queue worker di sini
Schedule::command('queue:work --stop-when-empty')
         ->everyMinute()
         ->withoutOverlapping();