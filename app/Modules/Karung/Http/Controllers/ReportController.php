<?php

namespace App\Modules\Karung\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Karung\Models\PurchaseTransaction;
use App\Modules\Karung\Models\SalesTransaction;
use App\Modules\Karung\Models\Product;
use App\Modules\Karung\Models\SalesTransactionDetail;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Menampilkan laporan penjualan.
     */
    public function sales(Request $request)
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : Carbon::now()->startOfMonth();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : Carbon::now()->endOfMonth();

        // Ambil data transaksi penjualan berdasarkan rentang tanggal
        $sales = SalesTransaction::with(['customer', 'user'])
                                ->where('status', 'Completed') // <-- PERBAIKAN: Hanya ambil transaksi yang selesai
                                ->whereBetween('transaction_date', [$startDate, $endDate])
                                ->latest('transaction_date')
                                ->get();

        // Hitung ringkasan
        $totalRevenue = $sales->sum('total_amount');
        $totalTransactions = $sales->count();

        // Kirim semua data yang diperlukan ke view
        return view('karung::reports.sales_report', [
            'sales' => $sales,
            'totalRevenue' => $totalRevenue,
            'totalTransactions' => $totalTransactions,
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
        ]);
    }

    /**
     * Menampilkan laporan pembelian.
     */
    public function purchases(Request $request)
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : Carbon::now()->startOfMonth();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : Carbon::now()->endOfMonth();

        // Ambil data transaksi pembelian berdasarkan rentang tanggal
        $purchases = PurchaseTransaction::with(['supplier', 'user'])
                                        ->where('status', 'Completed') // <-- PERBAIKAN: Hanya ambil transaksi yang selesai
                                        ->whereBetween('transaction_date', [$startDate, $endDate])
                                        ->latest('transaction_date')
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

    /**
     * Menampilkan laporan stok.
     */
    public function stockReport(Request $request)
    {
        // Method ini tidak perlu diubah karena tidak bergantung pada status transaksi
        $query = Product::with(['category', 'type']);
        $products = $query->orderBy('name', 'asc')->paginate(20);
        return view('karung::reports.stock_report', ['products' => $products]);
    }

    /**
     * Menampilkan laporan laba rugi sederhana.
     */
    public function profitAndLoss(Request $request)
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : Carbon::now()->startOfMonth();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : Carbon::now()->endOfMonth();

        // Ambil SEMUA DETAIL PENJUALAN dalam rentang tanggal tertentu.
        $salesDetails = SalesTransactionDetail::with('transaction.customer', 'product')
            ->whereHas('transaction', function ($query) use ($startDate, $endDate) {
                $query->where('status', 'Completed') // <-- PERBAIKAN: Hanya ambil dari transaksi yang selesai
                      ->whereBetween('transaction_date', [$startDate, $endDate]);
            })
            ->get();

        $totalRevenue = 0;
        $totalCost = 0;

        foreach ($salesDetails as $detail) {
            $totalRevenue += $detail->sub_total;
            $purchasePrice = $detail->product?->purchase_price ?? 0;
            $totalCost += $detail->quantity * $purchasePrice;
        }

        $grossProfit = $totalRevenue - $totalCost;

        // Hitung Total Pembelian Baru yang statusnya 'Completed' pada periode yang sama
        $totalPurchases = PurchaseTransaction::where('status', 'Completed') // <-- PERBAIKAN: Hanya hitung pembelian yang selesai
                                             ->whereBetween('transaction_date', [$startDate, $endDate])
                                             ->sum('total_amount');

        $netProfit = $grossProfit - $totalPurchases;

        return view('karung::reports.profit_loss_report', [
            'salesDetails'      => $salesDetails,
            'totalRevenue'      => $totalRevenue,
            'totalCost'         => $totalCost,
            'grossProfit'       => $grossProfit,
            'totalPurchases'    => $totalPurchases,
            'netProfit'         => $netProfit,
            'startDate'         => $startDate->toDateString(),
            'endDate'           => $endDate->toDateString(),
        ]);
    }
}
