<?php

namespace App\Services;

use App\Modules\Karung\Models\OperationalExpense;
use App\Modules\Karung\Models\PurchaseTransaction;
use App\Modules\Karung\Models\SalesTransaction;
use Carbon\Carbon;

class CashFlowReportService
{
    /**
     * Menghasilkan data untuk laporan Arus Kas.
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function generate(string $startDate, string $endDate): array
    {
        // Menghitung total pemasukan dari transaksi penjualan yang lunas/dibayar
        $salesQuery = SalesTransaction::where('status', 'Completed')
            ->whereBetween('transaction_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        $totalIncome = $salesQuery->sum('amount_paid');

        // Menghitung total pengeluaran dari transaksi pembelian yang lunas/dibayar
        $purchaseQuery = PurchaseTransaction::where('status', 'Completed')
             ->whereBetween('transaction_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        $purchaseExpense = $purchaseQuery->sum('amount_paid');

        // Menghitung total pengeluaran dari biaya operasional
        $operationalExpenseQuery = OperationalExpense::query()
            ->whereBetween('date', [$startDate, $endDate]);
        $operationalExpense = $operationalExpenseQuery->sum('amount');

        // Menghitung total pengeluaran dan arus kas bersih
        $totalExpense = $purchaseExpense + $operationalExpense;
        $netCashFlow = $totalIncome - $totalExpense;

        // Mengambil data piutang yang masih tertunda
        $pendingReceivables = SalesTransaction::with('customer')
            ->where('status', 'Completed')
            ->where('payment_status', 'Belum Lunas')
            ->whereBetween('transaction_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->get();

        // Mengambil data utang yang masih tertunda
        $pendingPayables = PurchaseTransaction::with('supplier')
            ->where('status', 'Completed')
            ->where('payment_status', 'Belum Lunas')
            ->whereBetween('transaction_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->get();

        return [
            'totalIncome' => $totalIncome,
            'purchaseExpense' => $purchaseExpense,
            'operationalExpense' => $operationalExpense,
            'totalExpense' => $totalExpense,
            'netCashFlow' => $netCashFlow,
            'pendingReceivables' => $pendingReceivables,
            'pendingPayables' => $pendingPayables,
        ];
    }
}