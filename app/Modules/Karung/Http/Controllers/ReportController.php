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

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $selectedCustomerId = $request->input('customer_id');
        $selectedUserId = $request->input('user_id');

        $query = SalesTransaction::with(['customer', 'user', 'details.product'])
                                 ->where('status', 'Completed');

        if ($startDate) {
            $query->whereDate('transaction_date', '>=', Carbon::parse($startDate));
        }
        if ($endDate) {
            $query->whereDate('transaction_date', '<=', Carbon::parse($endDate));
        }
        if ($selectedCustomerId) {
            $query->where('customer_id', $selectedCustomerId);
        }
        if ($selectedUserId) {
            $query->where('user_id', $selectedUserId);
        }

        $queryForTotals = clone $query;

        $totalTransactions = $queryForTotals->count();
        $totalRevenue = $queryForTotals->sum('total_amount');

        $allSalesDetails = $queryForTotals->with('details.product')->get()->pluck('details')->flatten();
        $totalCost = $allSalesDetails->reduce(function ($carry, $detail) {
            return $carry + (($detail->product->purchase_price ?? 0) * $detail->quantity);
        }, 0);
        $grossProfit = $totalRevenue - $totalCost;

        $sales = $query->latest('transaction_date')->paginate(10);
        
        $customers = Customer::orderBy('name')->get();
        $users = User::role(['Super Admin TMT', 'Admin Modul Karung', 'Staff Modul Karung'])->orderBy('name')->get();
        
        return view('karung::reports.sales_report', compact(
            'sales', 'totalRevenue', 'totalTransactions', 'startDate', 'endDate', 
            'customers', 'users', 'selectedCustomerId', 'selectedUserId',
            'totalCost', 'grossProfit'
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
        // Logika tidak berubah, sudah mengambil semua data yang dibutuhkan
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $selectedSupplierId = $request->input('supplier_id');

        $query = PurchaseTransaction::with(['supplier', 'user', 'details.product'])
                                       ->where('status', 'Completed');

        if ($startDate) {
            $query->whereDate('transaction_date', '>=', Carbon::parse($startDate));
        }
        if ($endDate) {
            $query->whereDate('transaction_date', '<=', Carbon::parse($endDate));
        }
        if ($selectedSupplierId) {
            $query->where('supplier_id', $selectedSupplierId);
        }

        $totalTransactions = $query->clone()->count();
        $totalSpending = $query->clone()->sum('total_amount');

        $purchases = $query->latest('transaction_date')->paginate(10); // Dibuat 10 agar konsisten

        $suppliers = Supplier::orderBy('name')->get();

        return view('karung::reports.purchases_report', compact(
            'purchases', 'totalSpending', 'totalTransactions', 'startDate', 
            'endDate', 'suppliers', 'selectedSupplierId'
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
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Query untuk total penjualan pada periode
        $salesQuery = SalesTransaction::where('status', 'Completed');
        if ($startDate) { $salesQuery->whereDate('transaction_date', '>=', Carbon::parse($startDate)); }
        if ($endDate) { $salesQuery->whereDate('transaction_date', '<=', Carbon::parse($endDate)); }
        $totalSales = $salesQuery->sum('total_amount');

        // Query untuk total pembelian baru pada periode
        $purchaseQuery = PurchaseTransaction::where('status', 'Completed');
        if ($startDate) { $purchaseQuery->whereDate('transaction_date', '>=', Carbon::parse($startDate)); }
        if ($endDate) { $purchaseQuery->whereDate('transaction_date', '<=', Carbon::parse($endDate)); }
        $totalPurchases = $purchaseQuery->sum('total_amount');

        // [PERBAIKAN] Kalkulasi untuk Total Modal Terjual (HPP/COGS)
        $salesDetailsQuery = \App\Modules\Karung\Models\SalesTransactionDetail::whereHas('transaction', function ($query) use ($startDate, $endDate) {
            $query->where('status', 'Completed');
            if ($startDate) { $query->whereDate('transaction_date', '>=', Carbon::parse($startDate)); }
            if ($endDate) { $query->whereDate('transaction_date', '<=', Carbon::parse($endDate)); }
        })->with('product');

        $salesDetails = $salesDetailsQuery->get();

        $totalCost = $salesDetails->reduce(function ($carry, $detail) {
            $cost = ($detail->product->purchase_price ?? 0) * $detail->quantity;
            return $carry + $cost;
        }, 0);

        // Kalkulasi Laba
        $grossProfit = $totalSales - $totalCost;
        $netProfit = $grossProfit - $totalPurchases; // Ini adalah kalkulasi sederhana, bisa disesuaikan

        // Kirim semua variabel ke view
        return view('karung::reports.profit_loss_report', compact(
            'totalSales', 
            'totalPurchases', 
            'totalCost', 
            'grossProfit', 
            'netProfit', 
            'salesDetails',
            'startDate', 
            'endDate'
        ));
    }

    public function exportProfitLoss(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Lakukan kalkulasi yang sama persis seperti di method show
        $salesQuery = SalesTransaction::where('status', 'Completed');
        $purchaseQuery = PurchaseTransaction::where('status', 'Completed');
        if ($startDate) {
            $salesQuery->whereDate('transaction_date', '>=', Carbon::parse($startDate));
            $purchaseQuery->whereDate('transaction_date', '>=', Carbon::parse($startDate));
        }
        if ($endDate) {
            $salesQuery->whereDate('transaction_date', '<=', Carbon::parse($endDate));
            $purchaseQuery->whereDate('transaction_date', '<=', Carbon::parse($endDate));
        }
        $totalSales = $salesQuery->sum('total_amount');
        $totalPurchases = $purchaseQuery->sum('total_amount');
        $profitLoss = $totalSales - $totalPurchases;

        $fileName = 'Laporan_Laba_Rugi_' . Carbon::now()->format('Y-m-d_H-i-s') . '.xlsx';
        return Excel::download(new ProfitLossReportExport($totalSales, $totalPurchases, $profitLoss), $fileName);
    }

    public function exportProfitLossPdf(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Lakukan kalkulasi yang sama persis seperti di method show
        $salesQuery = SalesTransaction::where('status', 'Completed');
        $purchaseQuery = PurchaseTransaction::where('status', 'Completed');
        if ($startDate) {
            $salesQuery->whereDate('transaction_date', '>=', Carbon::parse($startDate));
            $purchaseQuery->whereDate('transaction_date', '>=', Carbon::parse($startDate));
        }
        if ($endDate) {
            $salesQuery->whereDate('transaction_date', '<=', Carbon::parse($endDate));
            $purchaseQuery->whereDate('transaction_date', '<=', Carbon::parse($endDate));
        }
        $totalSales = $salesQuery->sum('total_amount');
        $totalPurchases = $purchaseQuery->sum('total_amount');
        $profitLoss = $totalSales - $totalPurchases;

        $pdf = PDF::loadView('karung::reports.pdf.profit_loss_report_pdf', compact('totalSales', 'totalPurchases', 'profitLoss', 'startDate', 'endDate'));
        $fileName = 'Laporan_Laba_Rugi_' . Carbon::now()->format('Y-m-d') . '.pdf';
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
 }