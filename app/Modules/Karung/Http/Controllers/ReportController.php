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

        // Simpan total sebelum paginasi
        $totalTransactions = $query->count();
        $totalRevenue = $query->sum('total_amount');

        $sales = $query->latest('transaction_date')->paginate(20);
        
        // Ambil data untuk dropdown filter
        $customers = Customer::orderBy('name')->get();
        $users = User::role(['Super Admin TMT', 'Admin Modul Karung', 'Staff Modul Karung'])->orderBy('name')->get();
        
        return view('karung::reports.sales_report', compact('sales', 'totalRevenue', 'totalTransactions', 'startDate', 'endDate', 'customers', 'users', 'selectedCustomerId', 'selectedUserId'));
    }

    public function exportSales(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $customerId = $request->input('customer_id');
        $userId = $request->input('user_id');
        
        $fileName = 'Laporan_Penjualan_' . Carbon::now()->format('Y-m-d_H-i-s') . '.xlsx';

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

        $query = SalesTransaction::with(['customer', 'user'])->where('status', 'Completed');

        if ($startDate) { $query->whereDate('transaction_date', '>=', Carbon::parse($startDate)); }
        if ($endDate) { $query->whereDate('transaction_date', '<=', Carbon::parse($endDate)); }
        if ($customerId) { $query->where('customer_id', $customerId); }
        if ($userId) { $query->where('user_id', $userId); }

        $sales = $query->latest('transaction_date')->get(); 
        $totalOmzet = $query->sum('total_amount'); // Nama variabel ini digunakan di PDF view
        
        $pdf = PDF::loadView('karung::reports.pdf.sales_report_pdf', compact('sales', 'totalOmzet', 'startDate', 'endDate'));
        $fileName = 'Laporan_Penjualan_' . Carbon::now()->format('Y-m-d') . '.pdf';
        return $pdf->download($fileName);
    }


    public function purchases(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        // [BARU] Ambil input dari filter supplier
        $selectedSupplierId = $request->input('supplier_id');

        $query = PurchaseTransaction::with(['supplier', 'user', 'details.product'])
                                    ->where('status', 'Completed');

        if ($startDate) {
            $query->whereDate('transaction_date', '>=', Carbon::parse($startDate));
        }
        if ($endDate) {
            $query->whereDate('transaction_date', '<=', Carbon::parse($endDate));
        }
        // [BARU] Terapkan filter jika supplier dipilih
        if ($selectedSupplierId) {
            $query->where('supplier_id', $selectedSupplierId);
        }

        $totalTransactions = $query->clone()->count();
        $totalSpending = $query->clone()->sum('total_amount');

        $purchases = $query->latest('transaction_date')->paginate(20);

        // [BARU] Ambil daftar supplier untuk dropdown
        $suppliers = \App\Modules\Karung\Models\Supplier::orderBy('name')->get();

        return view('karung::reports.purchases_report', compact(
            'purchases', 
            'totalSpending', 
            'totalTransactions', 
            'startDate', 
            'endDate', 
            'suppliers', // <-- Kirim data supplier ke view
            'selectedSupplierId' // <-- Kirim ID supplier yang dipilih
        ));
    }

    public function exportPurchases(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $fileName = 'Laporan_Pembelian_' . Carbon::now()->format('Y-m-d_H-i-s') . '.xlsx';
        return Excel::download(new PurchasesReportExport($startDate, $endDate), $fileName);
    }

    public function exportPurchasesPdf(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $query = PurchaseTransaction::with(['supplier', 'user'])->where('status', 'Completed');
        if ($startDate) { $query->whereDate('transaction_date', '>=', Carbon::parse($startDate)); }
        if ($endDate) { $query->whereDate('transaction_date', '<=', Carbon::parse($endDate)); }
        $purchases = $query->latest('transaction_date')->get();
        $totalPembelian = $query->sum('total_amount');

        $pdf = PDF::loadView('karung::reports.pdf.purchases_report_pdf', compact('purchases', 'totalPembelian', 'startDate', 'endDate'));
        $fileName = 'Laporan_Pembelian_' . Carbon::now()->format('Y-m-d') . '.pdf';
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

 }