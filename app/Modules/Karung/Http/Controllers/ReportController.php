<?php

namespace App\Modules\Karung\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Karung\Models\PurchaseTransaction;
use App\Modules\Karung\Models\SalesTransaction; // <-- PASTIKAN BARIS INI ADA
use App\Modules\Karung\Models\Product;
use App\Modules\Karung\Models\SalesTransactionDetail;
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

    public function purchases(Request $request)
    {
        // Tentukan tanggal awal dan akhir dari input request.
        // Jika tidak ada input, gunakan default awal dan akhir bulan ini.
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : Carbon::now()->startOfMonth();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : Carbon::now()->endOfMonth();

        // TODO: Nantinya, filter transaksi ini berdasarkan business_unit_id yang aktif.
        // $currentBusinessUnitId = 1;

        // Ambil data transaksi pembelian berdasarkan rentang tanggal
        $purchases = PurchaseTransaction::with(['supplier', 'user'])
                                        // ->where('business_unit_id', $currentBusinessUnitId) // Ini untuk nanti
                                        ->whereBetween('transaction_date', [$startDate, $endDate])
                                        ->latest('transaction_date') // Urutkan dari tanggal terbaru dalam rentang
                                        ->get();

        // Hitung ringkasan
        $totalSpending = $purchases->sum('total_amount');
        $totalTransactions = $purchases->count();

        // Kirim semua data yang diperlukan ke view
        return view('karung::reports.purchases_report', [
            'purchases' => $purchases,
            'totalSpending' => $totalSpending,
            'totalTransactions' => $totalTransactions,
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
        ]);
    }

    public function stockReport(Request $request)
    {
        // Mulai query dengan eager loading untuk relasi
        $query = Product::with(['category', 'type']);

        // Lanjutkan query dengan urutan dan paginasi
        $products = $query->orderBy('name', 'asc')->paginate(20);

        // Kirim data yang diperlukan ke view
        return view('karung::reports.stock_report', [
            'products' => $products,
        ]);
    }

    public function profitAndLoss(Request $request)
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : Carbon::now()->startOfMonth();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : Carbon::now()->endOfMonth();

        // Ambil SEMUA DETAIL PENJUALAN dalam rentang tanggal
        $salesDetails = SalesTransactionDetail::with('transaction', 'product')
            ->whereHas('transaction', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('transaction_date', [$startDate, $endDate]);
            })
            ->get();

        // Inisialisasi variabel untuk kalkulasi
        $totalRevenue = 0; // Total pendapatan (omzet)
        $totalCost = 0;    // Total modal barang terjual (HPP)

        foreach ($salesDetails as $detail) {
            $totalRevenue += $detail->sub_total;
            $purchasePrice = $detail->product?->purchase_price ?? 0;
            $totalCost += $detail->quantity * $purchasePrice;
        }

        // Hitung Laba Kotor
        $grossProfit = $totalRevenue - $totalCost;

        // --- BAGIAN BARU DIMULAI DI SINI ---
        // Hitung Total Pembelian Baru pada periode yang sama
        $totalPurchases = PurchaseTransaction::whereBetween('transaction_date', [$startDate, $endDate])->sum('total_amount');

        // Hitung Laba Bersih (versi Anda)
        $netProfit = $grossProfit - $totalPurchases;
        // --- AKHIR BAGIAN BARU ---


        return view('karung::reports.profit_loss_report', [
            'salesDetails'      => $salesDetails,
            'totalRevenue'      => $totalRevenue,
            'totalCost'         => $totalCost,
            'grossProfit'       => $grossProfit, // Ganti nama variabel agar lebih jelas
            'totalPurchases'    => $totalPurchases, // Kirim data total pembelian
            'netProfit'         => $netProfit,      // Kirim data laba bersih
            'startDate'         => $startDate->toDateString(),
            'endDate'           => $endDate->toDateString(),
        ]);
    }
    // Nanti kita tambahkan method untuk laporan lain di sini
}
