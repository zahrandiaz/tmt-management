<?php

namespace App\Modules\KarungCabang\Http\Controllers;

use App\Http\Controllers\ModuleBaseController; // Menggunakan base controller kita

class DashboardController extends ModuleBaseController
{
    public function index()
    {
        // Kita menggunakan 'karungcabang::dashboard' untuk memanggil view
        // dari namespace yang sudah kita daftarkan di ServiceProvider
        return view('karungcabang::dashboard');
    }
}