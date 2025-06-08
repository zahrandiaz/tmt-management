<?php

namespace App\Modules\Karung\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Karung\Models\SalesTransaction; // <-- PASTIKAN BARIS INI ADA
use Illuminate\Http\Request;
use Carbon\Carbon; // Kita gunakan Carbon untuk manipulasi tanggal

class ReportController extends Controller
{
    //
    public function sales(Request $request)
    {
        // Tentukan tanggal awal dan akhir dari input request.
        // Jika tidak ada input, gunakan default awal dan akhir bulan ini.
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : Carbon::now()->startOfMonth();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : Carbon::now()->endOfMonth();

        // TODO: Nantinya, filter transaksi ini berdasarkan business_unit_id yang aktif.
        // $currentBusinessUnitId = 1;

        // Ambil data transaksi penjualan berdasarkan rentang tanggal
        $sales = SalesTransaction::with(['customer', 'user'])
                                // ->where('business_unit_id', $currentBusinessUnitId) // Ini untuk nanti
                                ->whereBetween('transaction_date', [$startDate, $endDate])
                                ->latest('transaction_date') // Urutkan dari tanggal terbaru dalam rentang
                                ->get(); // Gunakan get() karena kita akan menghitung total dari koleksi ini

        // Hitung ringkasan
        $totalRevenue = $sales->sum('total_amount');
        $totalTransactions = $sales->count();

        // Kirim semua data yang diperlukan ke view
        return view('karung::reports.sales_report', [
            'sales' => $sales,
            'totalRevenue' => $totalRevenue,
            'totalTransactions' => $totalTransactions,
            'startDate' => $startDate->toDateString(), // Kirim tanggal dalam format Y-m-d untuk diisi di form
            'endDate' => $endDate->toDateString(),
        ]);
    }

    // Nanti kita tambahkan method untuk laporan lain di sini
}
