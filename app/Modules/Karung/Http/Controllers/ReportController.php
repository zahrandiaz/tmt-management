<?php

namespace App\Modules\Karung\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Karung\Models\SalesTransaction;
use App\Modules\Karung\Models\PurchaseTransaction;
use App\Modules\Karung\Models\Product;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesReportExport;
use App\Exports\PurchasesReportExport;
use App\Exports\StockReportExport;
use App\Exports\ProfitLossReportExport;
use Barryvdh\DomPDF\Facade\Pdf; // <-- [BARU] Import facade PDF
use App\Models\User;
use App\Modules\Karung\Models\Customer;
use App\Modules\Karung\Models\ProductCategory;
use App\Modules\Karung\Models\Supplier;
use App\Modules\Karung\Models\SalesTransactionDetail;
use App\Modules\Karung\Models\PurchaseTransactionDetail;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Modules\Karung\Models\OperationalExpense;

class ReportController extends Controller
{
    private function getDateRange(Request $request): array
    {
        $preset = $request->input('preset');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($preset) {
            switch ($preset) {
                case 'this_week':
                    $startDate = now()->startOfWeek()->format('Y-m-d');
                    $endDate = now()->endOfWeek()->format('Y-m-d');
                    break;
                case 'this_month':
                    $startDate = now()->startOfMonth()->format('Y-m-d');
                    $endDate = now()->endOfMonth()->format('Y-m-d');
                    break;
                case 'this_year':
                    $startDate = now()->startOfYear()->format('Y-m-d');
                    $endDate = now()->endOfYear()->format('Y-m-d');
                    break;
                case 'today':
                default:
                    $startDate = now()->format('Y-m-d');
                    $endDate = now()->format('Y-m-d');
                    break;
            }
        } elseif (!$startDate && !$endDate) {
            // Default ke hari ini jika tidak ada filter manual atau preset
            $startDate = now()->format('Y-m-d');
            $endDate = now()->format('Y-m-d');
            $preset = 'today'; // Set preset aktif
        } else {
            // Jika ada filter manual, preset dianggap custom
            $preset = 'custom';
        }

        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'activePreset' => $preset,
        ];
    }
    
    public function sales(Request $request)
    {
        $dateRange = $this->getDateRange($request);
        $startDate = $dateRange['startDate'];
        $endDate = $dateRange['endDate'];
        $activePreset = $dateRange['activePreset'];

        $selectedCustomerId = $request->input('customer_id');
        $selectedUserId = $request->input('user_id');

        $query = SalesTransaction::with(['customer', 'user', 'details.product'])
                                     ->where('status', 'Completed');

        if ($startDate) { $query->whereDate('transaction_date', '>=', Carbon::parse($startDate)); }
        if ($endDate) { $query->whereDate('transaction_date', '<=', Carbon::parse($endDate)); }
        if ($selectedCustomerId) { $query->where('customer_id', $selectedCustomerId); }
        if ($selectedUserId) { $query->where('user_id', $selectedUserId); }

        $queryForTotals = clone $query;
        $totalTransactions = $queryForTotals->count();
        $totalRevenue = $queryForTotals->sum('total_amount');

        $allSalesDetails = $queryForTotals->with('details.product')->get()->pluck('details')->flatten();
        $totalCost = $allSalesDetails->reduce(fn ($c, $d) => $c + (($d->product->purchase_price ?? 0) * $d->quantity), 0);
        $grossProfit = $totalRevenue - $totalCost;

        $sales = $query->latest('transaction_date')->paginate(10);
        $customers = Customer::orderBy('name')->get();
        $users = User::role(['Super Admin TMT', 'Admin Modul Karung', 'Staff Modul Karung'])->orderBy('name')->get();
        
        return view('karung::reports.sales_report', compact(
            'sales', 'totalRevenue', 'totalTransactions', 'startDate', 'endDate', 
            'customers', 'users', 'selectedCustomerId', 'selectedUserId',
            'totalCost', 'grossProfit', 'activePreset'
        ));
    }

    public function exportSales(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $customerId = $request->input('customer_id');
        $userId = $request->input('user_id');
        
        $fileName = 'Laporan_Penjualan_Detail_' . Carbon::now()->format('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new SalesReportExport($startDate, $endDate, $customerId, $userId), $fileName);
    }

    /**
     * [BARU] Method untuk menangani permintaan export laporan penjualan ke PDF.
     */
    public function exportSalesPdf(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $customerId = $request->input('customer_id');
        $userId = $request->input('user_id');

        $query = SalesTransaction::with(['customer', 'user', 'details.product'])->where('status', 'Completed');

        if ($startDate) { $query->whereDate('transaction_date', '>=', Carbon::parse($startDate)); }
        if ($endDate) { $query->whereDate('transaction_date', '<=', Carbon::parse($endDate)); }
        if ($customerId) { $query->where('customer_id', $customerId); }
        if ($userId) { $query->where('user_id', $userId); }

        $sales = $query->latest('transaction_date')->get();
        
        $totalRevenue = $sales->sum('total_amount');
        $totalCost = $sales->pluck('details')->flatten()->reduce(function ($carry, $detail) {
            return $carry + (($detail->product->purchase_price ?? 0) * $detail->quantity);
        }, 0);
        $grossProfit = $totalRevenue - $totalCost;
        
        $pdf = PDF::loadView('karung::reports.pdf.sales_report_pdf', compact('sales', 'totalRevenue', 'totalCost', 'grossProfit', 'startDate', 'endDate'));
        $fileName = 'Laporan_Penjualan_Detail_' . Carbon::now()->format('Y-m-d') . '.pdf';
        return $pdf->download($fileName);
    }


    public function purchases(Request $request)
    {
        $dateRange = $this->getDateRange($request);
        $startDate = $dateRange['startDate'];
        $endDate = $dateRange['endDate'];
        $activePreset = $dateRange['activePreset'];

        $selectedSupplierId = $request->input('supplier_id');

        $query = PurchaseTransaction::with(['supplier', 'user', 'details.product'])
                                        ->where('status', 'Completed');

        if ($startDate) { $query->whereDate('transaction_date', '>=', Carbon::parse($startDate)); }
        if ($endDate) { $query->whereDate('transaction_date', '<=', Carbon::parse($endDate)); }
        if ($selectedSupplierId) { $query->where('supplier_id', $selectedSupplierId); }

        $totalTransactions = $query->clone()->count();
        $totalSpending = $query->clone()->sum('total_amount');
        $purchases = $query->latest('transaction_date')->paginate(10);
        $suppliers = Supplier::orderBy('name')->get();

        return view('karung::reports.purchases_report', compact(
            'purchases', 'totalSpending', 'totalTransactions', 'startDate', 
            'endDate', 'suppliers', 'selectedSupplierId', 'activePreset'
        ));
    }

    public function exportPurchases(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $supplierId = $request->input('supplier_id'); // [BARU] Ambil filter supplier
        
        $fileName = 'Laporan_Pembelian_Detail_' . Carbon::now()->format('Y-m-d_H-i-s') . '.xlsx';
        return Excel::download(new PurchasesReportExport($startDate, $endDate, $supplierId), $fileName);
    }

    public function exportPurchasesPdf(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $supplierId = $request->input('supplier_id'); // [BARU] Ambil filter supplier

        $query = PurchaseTransaction::with(['supplier', 'user', 'details.product'])->where('status', 'Completed');

        if ($startDate) { $query->whereDate('transaction_date', '>=', Carbon::parse($startDate)); }
        if ($endDate) { $query->whereDate('transaction_date', '<=', Carbon::parse($endDate)); }
        if ($supplierId) { $query->where('supplier_id', $supplierId); }

        $purchases = $query->latest('transaction_date')->get();
        $totalPembelian = $query->sum('total_amount');

        $pdf = PDF::loadView('karung::reports.pdf.purchases_report_pdf', compact('purchases', 'totalPembelian', 'startDate', 'endDate'));
        $fileName = 'Laporan_Pembelian_Detail_' . Carbon::now()->format('Y-m-d') . '.pdf';
        return $pdf->download($fileName);
    }

    public function stockReport(Request $request)
    {
        $selectedCategoryId = $request->input('category_id');

        $query = Product::with(['category', 'type']);

        if ($selectedCategoryId) {
            $query->where('product_category_id', $selectedCategoryId);
        }
        
        $products = $query->orderBy('name')->paginate(20);
        $categories = ProductCategory::orderBy('name')->get();

        return view('karung::reports.stock_report', compact('products', 'categories', 'selectedCategoryId'));
    }

    public function exportStock(Request $request) // <-- Tambahkan Request
    {
        $categoryId = $request->input('category_id');
        $fileName = 'Laporan_Stok_Produk_' . Carbon::now()->format('Y-m-d_H-i-s') . '.xlsx';
        return Excel::download(new StockReportExport($categoryId), $fileName);
    }

    public function exportStockPdf(Request $request) // <-- Tambahkan Request
    {
        $categoryId = $request->input('category_id');
        
        $query = Product::with(['category', 'type']);
        if ($categoryId) {
            $query->where('product_category_id', $categoryId);
        }
        $products = $query->orderBy('name')->get();
        
        $pdf = PDF::loadView('karung::reports.pdf.stock_report_pdf', compact('products'));
        $fileName = 'Laporan_Stok_' . Carbon::now()->format('Y-m-d') . '.pdf';
        return $pdf->download($fileName);
    }

    public function profitAndLoss(Request $request)
    {
        $dateRange = $this->getDateRange($request);
        $startDate = $dateRange['startDate'];
        $endDate = $dateRange['endDate'];
        $activePreset = $dateRange['activePreset'];
        
        $salesQuery = SalesTransaction::where('status', 'Completed');
        if ($startDate) { $salesQuery->whereDate('transaction_date', '>=', Carbon::parse($startDate)); }
        if ($endDate) { $salesQuery->whereDate('transaction_date', '<=', Carbon::parse($endDate)); }

        // [MODIFIKASI] Kita tidak perlu lagi ->with('product.category') di sini karena HPP sudah ada
        $salesDetails = SalesTransactionDetail::whereHas('transaction', fn ($q) => $q->whereIn('id', $salesQuery->pluck('id')))->get();
        
        $totalRevenue = $salesDetails->sum('sub_total');

        // [MODIFIKASI] Kalkulasi Total HPP sekarang menggunakan data historis yang akurat
        $totalCost = $salesDetails->sum(function ($detail) {
            return $detail->quantity * $detail->purchase_price_at_sale;
        });

        $grossProfit = $totalRevenue - $totalCost;

        $expensesQuery = OperationalExpense::query();
        if ($startDate) { $expensesQuery->whereDate('date', '>=', Carbon::parse($startDate)); }
        if ($endDate) { $expensesQuery->whereDate('date', '<=', Carbon::parse($endDate)); }
        $totalExpenses = $expensesQuery->sum('amount');
        
        $netProfit = $grossProfit - $totalExpenses;
        
        // [MODIFIKASI] Kalkulasi laba per kategori juga menggunakan HPP akurat
        $profitByCategory = $salesDetails->filter(fn($d) => $d->product && $d->product->category)
            ->groupBy('product.category.name')
            ->map(function ($details, $categoryName) {
                $rev = $details->sum('sub_total');
                $cost = $details->sum(fn($d) => $d->quantity * $d->purchase_price_at_sale);
                return ['category_name' => $categoryName, 'total_profit' => $rev - $cost];
            })->sortByDesc('total_profit');
            
        return view('karung::reports.profit_loss_report', compact(
            'totalRevenue', 'totalCost', 'grossProfit', 
            'totalExpenses', 'netProfit', 'salesDetails', 
            'profitByCategory', 'startDate', 'endDate', 'activePreset'
        ));
    }

    public function exportProfitLoss(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $salesQuery = SalesTransaction::where('status', 'Completed');
        if ($startDate) { $salesQuery->whereDate('transaction_date', '>=', Carbon::parse($startDate)); }
        if ($endDate) { $salesQuery->whereDate('transaction_date', '<=', Carbon::parse($endDate)); }
        
        $salesDetails = SalesTransactionDetail::whereHas('transaction', fn($q) => $q->whereIn('id', $salesQuery->pluck('id')))
            ->with(['product.category', 'transaction'])->get();
        
        $totalRevenue = $salesDetails->sum('sub_total');

        // [MODIFIKASI] Kalkulasi Total HPP Akurat disamakan dengan method utama
        $totalCost = $salesDetails->sum(fn($detail) => $detail->quantity * $detail->purchase_price_at_sale);
        
        $grossProfit = $totalRevenue - $totalCost;

        $expensesQuery = OperationalExpense::query();
        if ($startDate) { $expensesQuery->whereDate('date', '>=', Carbon::parse($startDate)); }
        if ($endDate) { $expensesQuery->whereDate('date', '<=', Carbon::parse($endDate)); }
        $totalExpenses = $expensesQuery->sum('amount');

        $netProfit = $grossProfit - $totalExpenses;

        // [MODIFIKASI] Kalkulasi Laba per Kategori Akurat disamakan dengan method utama
        $profitByCategory = $salesDetails->filter(fn($d) => $d->product && $d->product->category)
            ->groupBy('product.category.name')
            ->map(function ($details, $categoryName) {
                $rev = $details->sum('sub_total');
                $cost = $details->sum(fn($d) => $d->quantity * $d->purchase_price_at_sale);
                return ['category_name' => $categoryName, 'total_profit' => $rev - $cost];
            })->sortByDesc('total_profit');

        $exportData = [
            'totalRevenue' => $totalRevenue, 'totalCost' => $totalCost,
            'grossProfit' => $grossProfit, 'totalExpenses' => $totalExpenses,
            'netProfit' => $netProfit, 'profitByCategory' => $profitByCategory,
            'salesDetails' => $salesDetails,
        ];

        $fileName = 'Laporan_Laba_Rugi_' . Carbon::now()->format('Y-m-d_H-i-s') . '.xlsx';
        return Excel::download(new ProfitLossReportExport($exportData), $fileName);
    }

    public function exportProfitLossPdf(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $salesQuery = SalesTransaction::where('status', 'Completed');
        if ($startDate) { $salesQuery->whereDate('transaction_date', '>=', Carbon::parse($startDate)); }
        if ($endDate) { $salesQuery->whereDate('transaction_date', '<=', Carbon::parse($endDate)); }

        $salesDetails = SalesTransactionDetail::whereHas('transaction', fn($q) => $q->whereIn('id', $salesQuery->pluck('id')))
            ->with('product.category')->get();
        
        $totalRevenue = $salesDetails->sum('sub_total');
        
        // [MODIFIKASI] Kalkulasi Total HPP Akurat disamakan dengan method utama
        $totalCost = $salesDetails->sum(fn($detail) => $detail->quantity * $detail->purchase_price_at_sale);

        $grossProfit = $totalRevenue - $totalCost;

        $expensesQuery = OperationalExpense::query();
        if ($startDate) { $expensesQuery->whereDate('date', '>=', Carbon::parse($startDate)); }
        if ($endDate) { $expensesQuery->whereDate('date', '<=', Carbon::parse($endDate)); }
        $totalExpenses = $expensesQuery->sum('amount');
        $expensesDetails = $expensesQuery->get();

        $netProfit = $grossProfit - $totalExpenses;

        // [MODIFIKASI] Kalkulasi Laba per Kategori Akurat disamakan dengan method utama
        $profitByCategory = $salesDetails->filter(fn($d) => $d->product && $d->product->category)
            ->groupBy('product.category.name')
            ->map(function ($details, $categoryName) {
                $rev = $details->sum('sub_total');
                $cost = $details->sum(fn($d) => $d->quantity * $d->purchase_price_at_sale);
                return ['category_name' => $categoryName, 'total_profit' => $rev - $cost];
            })->sortByDesc('total_profit');

        $pdf = PDF::loadView('karung::reports.pdf.profit_loss_report_pdf', compact('totalRevenue', 'totalCost', 'grossProfit', 'profitByCategory', 'salesDetails', 'totalExpenses', 'expensesDetails', 'netProfit', 'startDate', 'endDate'));
        $fileName = 'Laporan_Laba_Rugi_Detail_' . Carbon::now()->format('Y-m-d') . '.pdf';
        return $pdf->download($fileName);
    }

    public function stockHistory(Request $request, Product $product)
    {
        // Ambil semua detail penjualan untuk produk ini
        $salesDetails = SalesTransactionDetail::with('transaction')
            ->where('product_id', $product->id)
            ->whereHas('transaction', function ($q) {
                $q->where('status', 'Completed');
            })
            ->get()
            ->map(function ($detail) {
                return (object) [
                    'date' => $detail->transaction->transaction_date,
                    'type' => 'Penjualan',
                    'reference' => $detail->transaction->invoice_number,
                    'url' => route('karung.sales.show', $detail->transaction->id),
                    'quantity_in' => 0,
                    'quantity_out' => $detail->quantity,
                ];
            });

        // Ambil semua detail pembelian untuk produk ini
        $purchaseDetails = PurchaseTransactionDetail::with('transaction')
            ->where('product_id', $product->id)
            ->whereHas('transaction', function ($q) {
                $q->where('status', 'Completed');
            })
            ->get()
            ->map(function ($detail) {
                return (object) [
                    'date' => $detail->transaction->transaction_date,
                    'type' => 'Pembelian',
                    'reference' => $detail->transaction->purchase_code,
                    'url' => route('karung.purchases.show', $detail->transaction->id),
                    'quantity_in' => $detail->quantity,
                    'quantity_out' => 0,
                ];
            });

        // Gabungkan kedua koleksi dan urutkan berdasarkan tanggal
        $stockHistory = (new Collection($salesDetails->concat($purchaseDetails)))->sortBy('date');

        return view('karung::reports.stock_history', compact('product', 'stockHistory'));
    }

    public function customerPerformance(Request $request)
    {
        $sortBy = $request->input('sort_by', 'total_spent');
        $sortOrder = $request->input('sort_order', 'desc');

        // [PERBAIKAN] Mengganti 'sales' menjadi 'salesTransactions'
        $customers = Customer::where('name', '!=', 'Pelanggan Umum')
            ->withCount(['salesTransactions as transaction_count' => function ($query) {
                $query->where('status', 'Completed');
            }])
            ->withSum(['salesTransactions as total_spent' => function ($query) {
                $query->where('status', 'Completed');
            }], 'total_amount')
            ->withMax(['salesTransactions as last_purchase_date' => function ($query) {
                $query->where('status', 'Completed');
            }], 'transaction_date')
            ->orderBy($sortBy, $sortOrder)
            ->paginate(20);
        
        return view('karung::reports.customer_performance_report', compact('customers', 'sortBy', 'sortOrder'));
    }

    public function productPerformance(Request $request)
    {
        $sortBy = $request->input('sort_by', 'total_profit');
        $sortOrder = $request->input('sort_order', 'desc');

        // Kode ini sekarang akan bekerja karena relasi 'salesDetails' sudah kita tambahkan di Model Product
        $products = Product::where('is_active', true)
            ->withSum(['salesDetails as units_sold' => function ($query) {
                $query->whereHas('transaction', fn($q) => $q->where('status', 'Completed'));
            }], 'quantity')
            ->withSum(['salesDetails as total_revenue' => function ($query) {
                $query->whereHas('transaction', fn($q) => $q->where('status', 'Completed'));
            }], 'sub_total')
            ->addSelect([
                'total_profit' => SalesTransactionDetail::query()
                    ->select(DB::raw('SUM(quantity * (selling_price_at_transaction - purchase_price))'))
                    ->join('karung_products as p', 'p.id', '=', 'karung_sales_transaction_details.product_id')
                    ->whereColumn('karung_sales_transaction_details.product_id', 'karung_products.id')
                    ->whereHas('transaction', fn($q) => $q->where('status', 'Completed'))
            ])
            ->orderBy($sortBy, $sortOrder)
            ->paginate(20);

        return view('karung::reports.product_performance_report', compact('products', 'sortBy', 'sortOrder'));
    }

    public function cashFlow(Request $request)
    {
        $dateRange = $this->getDateRange($request);
        $startDate = $dateRange['startDate'];
        $endDate = $dateRange['endDate'];
        $activePreset = $dateRange['activePreset'];

        $salesQuery = SalesTransaction::where('status', 'Completed');
        if ($startDate) { $salesQuery->whereDate('transaction_date', '>=', Carbon::parse($startDate)); }
        if ($endDate) { $salesQuery->whereDate('transaction_date', '<=', Carbon::parse($endDate)); }
        $totalIncome = $salesQuery->sum('amount_paid');

        $purchaseQuery = PurchaseTransaction::where('status', 'Completed');
        if ($startDate) { $purchaseQuery->whereDate('transaction_date', '>=', Carbon::parse($startDate)); }
        if ($endDate) { $purchaseQuery->whereDate('transaction_date', '<=', Carbon::parse($endDate)); }
        $purchaseExpense = $purchaseQuery->sum('amount_paid');
        
        $operationalExpenseQuery = OperationalExpense::query();
        if ($startDate) { $operationalExpenseQuery->whereDate('date', '>=', Carbon::parse($startDate)); }
        if ($endDate) { $operationalExpenseQuery->whereDate('date', '<=', Carbon::parse($endDate)); }
        $operationalExpense = $operationalExpenseQuery->sum('amount');

        $totalExpense = $purchaseExpense + $operationalExpense;
        $netCashFlow = $totalIncome - $totalExpense;

        return view('karung::reports.cash_flow_report', compact(
            'totalIncome', 'purchaseExpense', 'operationalExpense', 
            'netCashFlow', 'startDate', 'endDate', 'activePreset'
        ));
    }
 }