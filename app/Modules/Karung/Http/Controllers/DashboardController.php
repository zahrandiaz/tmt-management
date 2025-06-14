<?php

namespace App\Modules\Karung\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Karung\Models\Product;
// [BARU] Import model SalesTransaction dan Carbon
use App\Modules\Karung\Models\SalesTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // --- Logika untuk Notifikasi Stok Kritis (Sudah Ada) ---
        $criticalStockProducts = Product::where('min_stock_level', '>', 0)
                                        ->whereRaw('stock <= min_stock_level')
                                        ->orderBy('name', 'asc')
                                        ->get();

        // --- [BARU] Logika untuk Grafik Penjualan 7 Hari Terakhir ---
        $salesData = SalesTransaction::where('status', 'Completed')
            ->where('transaction_date', '>=', now()->subDays(6)->startOfDay()) // Ambil data 7 hari (6 hari lalu + hari ini)
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get([
                DB::raw('DATE(transaction_date) as date'),
                DB::raw('SUM(total_amount) as total')
            ])
            ->keyBy('date'); // Buat key berdasarkan tanggal agar mudah dicari

        // Siapkan array untuk 7 hari terakhir, default total 0
        $dates = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $dates->put($date, 0);
        }

        // Isi data penjualan ke dalam array tanggal
        foreach ($salesData as $date => $data) {
            $dates[$date] = $data->total;
        }

        // Siapkan data untuk dikirim ke chart
        $salesChartLabels = $dates->keys()->map(function($date) {
            return Carbon::parse($date)->format('d M'); // Format tanggal menjadi "14 Jun"
        });
        $salesChartData = $dates->values();


        // --- Kirim semua data ke view ---
        return view('karung::dashboard_modul', compact(
            'criticalStockProducts',
            'salesChartLabels',
            'salesChartData'
        ));
    }
}