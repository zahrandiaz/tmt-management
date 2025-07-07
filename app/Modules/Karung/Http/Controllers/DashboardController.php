<?php

namespace App\Modules\Karung\Http\Controllers;

use App\Http\Controllers\ModuleBaseController;
use App\Models\Product;
use App\Modules\Karung\Models\SalesTransaction;
use App\Modules\Karung\Models\SalesTransactionDetail; // Ditambahkan
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity; // Ditambahkan

class DashboardController extends ModuleBaseController
{
    public function index()
    {
        $user = auth()->user();

        // --- Logika untuk Notifikasi Stok Kritis (Kode Asli Anda, sudah benar) ---
        $criticalStockProducts = Product::where('min_stock_level', '>', 0)
                                        ->whereRaw('stock <= min_stock_level')
                                        ->orderBy('name', 'asc')
                                        ->get();

        // --- Logika untuk Grafik Penjualan (Kode Asli Anda, sedikit disederhanakan) ---
        $salesData = SalesTransaction::select(
            DB::raw('DATE(transaction_date) as date'),
            DB::raw('SUM(total_amount) as total')
        )
        ->where('status', 'Completed')
        ->whereBetween('transaction_date', [Carbon::now()->subDays(6), Carbon::now()])
        ->groupBy('date')
        ->orderBy('date', 'asc')
        ->get()
        ->keyBy('date');

        $dates = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $dates->put($date, $salesData->get($date)->total ?? 0);
        }
        $salesChartLabels = $dates->keys()->map(fn($date) => Carbon::parse($date)->format('d M'));
        $salesChartData = $dates->values();


        // --- [BARU] Logika untuk Fitur v1.11 ---
        $kpiCards = [];
        $bestsellingProducts = [];
        $latestActivities = [];

        if ($user->can('karung.view_reports')) {
            // Opsi 2: Data untuk Kartu KPI
            $kpiCards = [
                'todays_revenue' => SalesTransaction::where('status', 'Completed')->whereDate('transaction_date', today())->sum('total_amount'),
                'todays_transactions' => SalesTransaction::where('status', 'Completed')->whereDate('transaction_date', today())->count(),
                // [PERBAIKAN] Menggunakan relasi 'transaction'
                'todays_products_sold' => SalesTransactionDetail::whereHas('transaction', function ($query) {
                    $query->where('status', 'Completed')->whereDate('transaction_date', today());
                })->sum('quantity'),
            ];

            // Opsi 1: Data untuk Produk Terlaris (30 hari terakhir)
            $bestsellingProducts = SalesTransactionDetail::select('product_id', DB::raw('SUM(quantity) as total_sold'))
                ->with('product:id,name')
                // [PERBAIKAN] Menggunakan relasi 'transaction'
                ->whereHas('transaction', function ($query) {
                    $query->where('status', 'Completed')->where('transaction_date', '>=', Carbon::now()->subDays(30));
                })
                ->groupBy('product_id')
                ->orderByDesc('total_sold')
                ->limit(5)
                ->get();
            
            // Opsi 3: Data untuk Umpan Aktivitas Terbaru
            $latestActivities = Activity::with('causer:id,name')
                ->whereIn('subject_type', [
                    'App\Modules\Karung\Models\SalesTransaction',
                    'App\Modules\Karung\Models\PurchaseTransaction',
                    'App\Models\Product',
                ])
                ->latest()
                ->limit(5)
                ->get();
        }


        // --- Kirim semua data ke view ---
        return view('karung::dashboard_modul', compact(
            'criticalStockProducts',
            'salesChartLabels',
            'salesChartData',
            'kpiCards',
            'bestsellingProducts',
            'latestActivities'
        ));
    }
}