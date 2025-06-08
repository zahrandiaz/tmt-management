<?php

namespace App\Modules\Karung\Http\Controllers; // Pastikan namespace ini benar

use App\Http\Controllers\Controller; // Controller dasar Laravel
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Menggunakan namespace 'karung::' yang kita definisikan di KarungServiceProvider
        return view('karung::dashboard_modul'); // Kita akan buat view ini sebentar lagi
    }
}