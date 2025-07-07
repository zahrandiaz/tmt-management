<?php

namespace App\Services;

use App\Models\Product;
use App\Modules\Karung\Models\OperationalExpense;
use App\Modules\Karung\Models\PurchaseReturn;
use App\Modules\Karung\Models\SalesReturn;
use App\Modules\Karung\Models\SalesReturnDetail;
use App\Modules\Karung\Models\SalesTransaction;
use App\Modules\Karung\Models\SalesTransactionDetail;
use Illuminate\Support\Facades\DB;

class ProfitLossReportService
{
    /**
     * Menghasilkan data untuk laporan Laba Rugi.
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function generate(string $startDate, string $endDate): array
    {
        // --- PENDAPATAN ---
        $totalRevenue = $this->calculateTotalRevenue($startDate, $endDate);
        $totalSalesReturns = $this->calculateTotalSalesReturns($startDate, $endDate);
        $netRevenue = $totalRevenue - $totalSalesReturns;

        // --- BIAYA BARANG TERJUAL (COGS) ---
        $totalCostOfGoodsSold = $this->calculateTotalCOGS($startDate, $endDate);
        $costOfReturnedGoods = $this->calculateCostOfReturnedGoods($startDate, $endDate);
        $totalPurchaseReturns = $this->calculateTotalPurchaseReturns($startDate, $endDate);
        $netCostOfGoodsSold = $totalCostOfGoodsSold - $costOfReturnedGoods - $totalPurchaseReturns;

        // --- LABA KOTOR ---
        $grossProfit = $netRevenue - $netCostOfGoodsSold;

        // --- BIAYA OPERASIONAL & LABA BERSIH ---
        $totalExpenses = $this->calculateTotalExpenses($startDate, $endDate);
        $netProfit = $grossProfit - $totalExpenses;

        // --- Data Tambahan untuk Detail ---
        $salesDetails = SalesTransactionDetail::whereHas('transaction', fn ($q) =>
            $q->where('status', 'Completed')->whereBetween('transaction_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
        )->get();

        $profitByCategory = $salesDetails->filter(fn($d) => $d->product && $d->product->category)
            ->groupBy('product.category.name')
            ->map(function ($details, $categoryName) {
                $rev = $details->sum('sub_total');
                $cost = $details->sum(fn($d) => $d->quantity * $d->purchase_price_at_sale);
                return ['category_name' => $categoryName, 'total_profit' => $rev - $cost];
            })->sortByDesc('total_profit');

        return [
            'totalRevenue' => $totalRevenue,
            'totalSalesReturns' => $totalSalesReturns,
            'netRevenue' => $netRevenue,
            'totalCostOfGoodsSold' => $totalCostOfGoodsSold,
            'costOfReturnedGoods' => $costOfReturnedGoods,
            'totalPurchaseReturns' => $totalPurchaseReturns,
            'netCostOfGoodsSold' => $netCostOfGoodsSold,
            'grossProfit' => $grossProfit,
            'totalExpenses' => $totalExpenses,
            'netProfit' => $netProfit,
            'salesDetails' => $salesDetails,
            'profitByCategory' => $profitByCategory,
        ];
    }

    private function calculateTotalRevenue(string $startDate, string $endDate): float
    {
        return SalesTransaction::where('status', 'Completed')
            ->whereBetween('transaction_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->sum('total_amount');
    }

    private function calculateTotalSalesReturns(string $startDate, string $endDate): float
    {
        return SalesReturn::whereBetween('return_date', [$startDate, $endDate])
            ->sum('total_amount');
    }

    private function calculateTotalCOGS(string $startDate, string $endDate): float
    {
        return SalesTransactionDetail::whereHas('transaction', function($q) use ($startDate, $endDate) {
            $q->where('status', 'Completed')
              ->whereBetween('transaction_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        })->sum(DB::raw('quantity * purchase_price_at_sale'));
    }

    private function calculateCostOfReturnedGoods(string $startDate, string $endDate): float
    {
        return SalesReturnDetail::query()
            ->join('karung_sales_returns', 'karung_sales_return_details.sales_return_id', '=', 'karung_sales_returns.id')
            ->join('karung_sales_transaction_details', function ($join) {
                $join->on('karung_sales_returns.sales_transaction_id', '=', 'karung_sales_transaction_details.sales_transaction_id')
                     ->on('karung_sales_return_details.product_id', '=', 'karung_sales_transaction_details.product_id');
            })
            ->whereBetween('karung_sales_returns.return_date', [$startDate, $endDate])
            ->selectRaw('SUM(karung_sales_return_details.quantity * karung_sales_transaction_details.purchase_price_at_sale) as total')
            ->value('total') ?? 0;
    }

    private function calculateTotalPurchaseReturns(string $startDate, string $endDate): float
    {
        return PurchaseReturn::whereBetween('return_date', [$startDate, $endDate])
            ->sum('total_amount');
    }

    private function calculateTotalExpenses(string $startDate, string $endDate): float
    {
        return OperationalExpense::whereBetween('date', [$startDate, $endDate])->sum('amount');
    }
}