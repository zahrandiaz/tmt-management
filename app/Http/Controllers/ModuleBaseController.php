<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * Ini adalah controller induk untuk semua modul.
 * Logika atau properti yang bersifat umum untuk semua controller modul
 * dapat ditempatkan di sini di masa depan.
 */
class ModuleBaseController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}