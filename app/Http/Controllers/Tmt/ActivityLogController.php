<?php

namespace App\Http\Controllers\Tmt;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity; // <-- Import model Activity

class ActivityLogController extends Controller
{
    /**
     * Menampilkan daftar log aktivitas.
     */
    public function index()
    {
        // Ambil semua log, urutkan dari yang paling baru, dan gunakan paginasi
        $activities = Activity::with(['causer', 'subject']) // Eager load relasi causer (pelaku) dan subject (objek)
                              ->latest()
                              ->paginate(25); // Tampilkan 25 log per halaman

        return view('tmt.activity_log.index', compact('activities'));
    }
}