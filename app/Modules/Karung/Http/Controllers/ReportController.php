<?php

namespace App\Modules\Karung\Http\Controllers;

use App\Http\Controllers\ModuleBaseController;
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
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\User;
use App\Modules\Karung\Models\Customer;
use App\Modules\Karung\Models\ProductCategory;
use App\Modules\Karung\Models\Supplier;
use App\Modules\Karung\Models\SalesTransactionDetail;
use App\Modules\Karung\Models\PurchaseTransactionDetail;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Modules\Karung\Models\OperationalExpense;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ExportSalesReportJob;
use App\Models\ExportedReport;
use App\Models\Setting;
use App\Modules\Karung\Services\ProfitLossReportService; // <-- [BARU v1.32.1]

class ReportController extends ModuleBaseController
{
    protected $profitLossReportService; // <-- [BARU v1.32.1]

    public function __construct(ProfitLossReportService $profitLossReportService) // <-- [MODIFIKASI v1.32.1]
    {
        $this->profitLossReportService = $profitLossReportService; // <-- [BARU v1.32.1]
    }

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
            $startDate = now()->format('Y-m-d');
            $endDate = now()->format('Y-m-d');
            $preset = 'today';
        } else {
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

        $query = SalesTransaction::with(['customer', 'user', 'details'])
            ->withSum('details as total_cost', DB::raw('quantity * purchase_price_at_sale'))
            ->where('status', 'Completed');

        if ($startDate) { $query->whereDate('transaction_date', '>=', Carbon::parse($startDate)); }
        if ($endDate) { $query->whereDate('transaction_date', '<=', Carbon::parse($endDate)); }
        if ($selectedCustomerId) { $query->where('customer_id', $selectedCustomerId); }
        if ($selectedUserId) { $query->where('user_id', $selectedUserId); }

        $queryForTotals = clone $query;
        $summary = $queryForTotals->select(
                DB::raw('COUNT(*) as total_transactions'),
                DB::raw('SUM(total_amount) as total_revenue'),
                DB::raw('SUM((SELECT SUM(quantity * purchase_price_at_sale) FROM karung_sales_transaction_details WHERE sales_transaction_id = karung_sales_transactions.id)) as total_cost')
            )->first();
            
        $totalTransactions = $summary->total_transactions ?? 0;
        $totalRevenue = $summary->total_revenue ?? 0;
        $totalCost = $summary->total_cost ?? 0;
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
        
        ExportSalesReportJob::dispatch(auth()->user(), $startDate, $endDate, $customerId, $userId);

        return redirect()->back()->with('success', 'Laporan Anda sedang diproses. Anda akan diberi notifikasi jika sudah selesai.');
    }

    public function exportSalesPdf(Request $request)
    {
        $settings = Setting::pluck('setting_value', 'setting_key');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $customerId = $request->input('customer_id');
        $userId = $request->input('user_id');

        $query = SalesTransaction::with(['customer', 'user', 'details'])
            ->withSum('details as total_cost', DB::raw('quantity * purchase_price_at_sale'))
            ->where('status', 'Completed');

        if ($startDate) { $query->whereDate('transaction_date', '>=', Carbon::parse($startDate)); }
        if ($endDate) { $query->whereDate('transaction_date', '<=', Carbon::parse($endDate)); }
        if ($customerId) { $query->where('customer_id', $customerId); }
        if ($userId) { $query->where('user_id', $userId); }

        $sales = $query->latest('transaction_date')->get();
        
        $totalRevenue = $sales->sum('total_amount');
        $totalCost = $sales->sum('total_cost');
        $grossProfit = $totalRevenue - $totalCost;
        
        $pdf = PDF::loadView('karung::reports.pdf.sales_report_pdf', compact('sales', 'totalRevenue', 'totalCost', 'grossProfit', 'startDate', 'endDate', 'settings'));
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
        $supplierId = $request->input('supplier_id');
        
        $fileName = 'Laporan_Pembelian_Detail_' . Carbon::now()->format('Y-m-d_H-i-s') . '.xlsx';
        return Excel::download(new PurchasesReportExport($startDate, $endDate, $supplierId), $fileName);
    }

    public function exportPurchasesPdf(Request $request)
    {
        $settings = Setting::pluck('setting_value', 'setting_key');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $supplierId = $request->input('supplier_id');

        $query = PurchaseTransaction::with(['supplier', 'user', 'details.product'])->where('status', 'Completed');

        if ($startDate) { $query->whereDate('transaction_date', '>=', Carbon::parse($startDate)); }
        if ($endDate) { $query->whereDate('transaction_date', '<=', Carbon::parse($endDate)); }
        if ($supplierId) { $query->where('supplier_id', $supplierId); }

        $purchases = $query->latest('transaction_date')->get();
        $totalPembelian = $query->sum('total_amount');

        $pdf = PDF::loadView('karung::reports.pdf.purchases_report_pdf', compact('purchases', 'totalPembelian', 'startDate', 'endDate', 'settings'));
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

    public function exportStock(Request $request)
    {
        $categoryId = $request->input('category_id');
        $fileName = 'Laporan_Stok_Produk_' . Carbon::now()->format('Y-m-d_H-i-s') . '.xlsx';
        return Excel::download(new StockReportExport($categoryId), $fileName);
    }

    public function exportStockPdf(Request $request)
    {
        $settings = Setting::pluck('setting_value', 'setting_key');
        $categoryId = $request->input('category_id');
        
        $query = Product::with(['category', 'type']);
        if ($categoryId) {
            $query->where('product_category_id', $categoryId);
        }
        $products = $query->orderBy('name')->get();
        
        $pdf = PDF::loadView('karung::reports.pdf.stock_report_pdf', compact('products', 'settings'));
        $fileName = 'Laporan_Stok_' . Carbon::now()->format('Y-m-d') . '.pdf';
        return $pdf->download($fileName);
    }

    public function profitAndLoss(Request $request)
    {
        // --- PERSIAPAN ---
        $dateRange = $this->getDateRange($request);
        $startDate = $dateRange['startDate'];
        $endDate = $dateRange['endDate'];
        $activePreset = $dateRange['activePreset'];
        
        // [REFACTOR v1.32.1] Panggil service untuk mendapatkan semua data laporan
        $reportData = $this->profitLossReportService->generate($startDate, $endDate);
        
        // Kirim semua data ke view
        return view('karung::reports.profit_loss_report', array_merge(
            $reportData,
            [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'activePreset' => $activePreset
            ]
        ));
    }

    public function exportProfitLoss(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // [REFACTOR v1.32.1] Gunakan service untuk mendapatkan data
        $exportData = $this->profitLossReportService->generate($startDate, $endDate);

        $fileName = 'Laporan_Laba_Rugi_' . Carbon::now()->format('Y-m-d_H-i-s') . '.xlsx';
        return Excel::download(new ProfitLossReportExport($exportData), $fileName);
    }

    public function exportProfitLossPdf(Request $request)
    {
        $settings = Setting::pluck('setting_value', 'setting_key');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // [REFACTOR v1.32.1] Gunakan service untuk mendapatkan data
        $reportData = $this->profitLossReportService->generate($startDate, $endDate);

        $pdf = PDF::loadView('karung::reports.pdf.profit_loss_report_pdf', array_merge(
            $reportData,
            [
                'settings' => $settings,
                'startDate' => $startDate,
                'endDate' => $endDate
            ]
        ));
        $fileName = 'Laporan_Laba_Rugi_Detail_' . Carbon::now()->format('Y-m-d') . '.pdf';
        return $pdf->download($fileName);
    }

    public function stockHistory(Request $request, Product $product)
    {
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

        $stockHistory = (new Collection($salesDetails->concat($purchaseDetails)))->sortBy('date');

        return view('karung::reports.stock_history', compact('product', 'stockHistory'));
    }

    public function customerPerformance(Request $request)
    {
        $sortBy = $request->input('sort_by', 'total_spent');
        $sortOrder = $request->input('sort_order', 'desc');

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

        $receivablesQuery = SalesTransaction::where('status', 'Completed')
            ->where('payment_status', 'Belum Lunas');
        if ($startDate) { $receivablesQuery->whereDate('transaction_date', '>=', Carbon::parse($startDate)); }
        if ($endDate) { $receivablesQuery->whereDate('transaction_date', '<=', Carbon::parse($endDate)); }
        $pendingReceivables = $receivablesQuery->with('customer')->get();

        $payablesQuery = PurchaseTransaction::where('status', 'Completed')
            ->where('payment_status', 'Belum Lunas');
        if ($startDate) { $payablesQuery->whereDate('transaction_date', '>=', Carbon::parse($startDate)); }
        if ($endDate) { $payablesQuery->whereDate('transaction_date', '<=', Carbon::parse($endDate)); }
        $pendingPayables = $payablesQuery->with('supplier')->get();


        return view('karung::reports.cash_flow_report', compact(
            'totalIncome', 'purchaseExpense', 'operationalExpense', 
            'netCashFlow', 'startDate', 'endDate', 'activePreset',
            'pendingReceivables', 'pendingPayables'
        ));
    }

    public function downloadCenter()
    {
        $this->authorize('viewAny', SalesTransaction::class);

        $exportedReports = ExportedReport::where('user_id', auth()->id())
                                              ->latest()
                                              ->paginate(15);

        return view('karung::reports.download_center', compact('exportedReports'));
    }

    public function downloadExportedReport($filename)
    {
        $path = 'public/report_exports/' . $filename;

        if (!Storage::exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::download($path);
    }

    public function destroyExportedReport(ExportedReport $report)
    {
        try {
            $filePath = 'report_exports/' . $report->filename;

            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            $report->delete();

            return redirect()->route('karung.reports.download_center')->with('success', 'Riwayat dan file laporan berhasil dihapus.');

        } catch (\Exception $e) {
            report($e);
            return redirect()->route('karung.reports.download_center')->with('error', 'Terjadi kesalahan saat mencoba menghapus laporan.');
        }
    }
}