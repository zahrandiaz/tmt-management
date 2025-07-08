<?php

namespace App\Modules\KarungCabang\Http\Controllers;

use App\Http\Controllers\ModuleBaseController;
use App\Modules\KarungCabang\Http\Requests\StoreSalesTransactionRequest;
use App\Modules\KarungCabang\Http\Requests\UpdateSalesTransactionRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Modules\KarungCabang\Models\SalesTransaction;
use App\Models\Setting;
use App\Services\TransactionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SalesTransactionController extends ModuleBaseController
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', SalesTransaction::class);
        $status = $request->query('status', 'Completed');
        
        $query = SalesTransaction::with(['customer', 'details.product'])
            ->where('status', $status)
            ->where('business_unit_id', 2); // Filter untuk Karung Cabang
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('invoice_number', 'like', '%' . $searchTerm . '%')
                  ->orWhereHas('customer', function ($customerQuery) use ($searchTerm) {
                      $customerQuery->where('name', 'like', '%' . $searchTerm . '%');
                  });
            });
        }
        $sales = $query->latest()->paginate(15);
        return view('karungcabang::sales.index', compact('sales', 'status'));
    }

    public function create()
    {
        $this->authorize('create', SalesTransaction::class);
        $customers = Customer::orderBy('name', 'asc')->get();
        $products = Product::where('business_unit_id', 2) // Filter untuk Karung Cabang
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->orderBy('name', 'asc')->get();
        return view('karungcabang::sales.create', compact('customers', 'products'));
    }

    public function store(StoreSalesTransactionRequest $request)
    {
        $this->authorize('create', SalesTransaction::class);
        try {
            $this->transactionService->createSale($request, 2); // ID untuk Karung Cabang
            return redirect()->route('karungcabang.sales.index')->with('success', 'Transaksi penjualan baru berhasil disimpan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function show(SalesTransaction $sale)
    {
        $this->authorize('view', $sale);
        $sale->load(['customer', 'user', 'details.product', 'returns']);
        return view('karungcabang::sales.show', compact('sale'));
    }

    public function edit(SalesTransaction $sale)
    {
        $this->authorize('update', $sale);
        $sale->load(['details.product', 'operationalExpenses']);
        $customers = Customer::orderBy('name', 'asc')->get();
        $activeProducts = Product::where('business_unit_id', 2) // Filter untuk Karung Cabang
                    ->where('is_active', true)
                    ->where('stock', '>', 0)
                    ->orderBy('name', 'asc')->get();
        $existingProducts = $sale->details->pluck('product');
        $products = $activeProducts->merge($existingProducts)->unique('id');
        return view('karungcabang::sales.edit', compact('sale', 'customers', 'products'));
    }

    public function update(UpdateSalesTransactionRequest $request, SalesTransaction $sale)
    {
        $this->authorize('update', $sale);
        try {
            $this->transactionService->updateSale($request, $sale);
            return redirect()->route('karungcabang.sales.index')->with('success', 'Transaksi penjualan berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function restore(SalesTransaction $sale)
    {
        $this->authorize('restore', $sale);
        try {
            $this->transactionService->restoreSale($sale);
            return redirect()->route('karungcabang.sales.index', ['status' => 'Deleted'])
                             ->with('success', "Transaksi #{$sale->invoice_number} berhasil dipulihkan.");
        } catch (\Exception $e) {
            return redirect()->route('karungcabang.sales.index', ['status' => 'Deleted'])
                             ->with('error', 'Terjadi kesalahan saat memulihkan transaksi: ' . $e->getMessage());
        }
    }

    public function cancel(SalesTransaction $sale)
    {
        $this->authorize('cancel', $sale);
        try {
            $this->transactionService->cancelSale($sale);
            return redirect()->route('karungcabang.sales.index')->with('success', "Transaksi penjualan dengan invoice #{$sale->invoice_number} berhasil dibatalkan.");
        } catch (\Exception $e) {
            return redirect()->route('karungcabang.sales.index')->with('error', 'Terjadi kesalahan saat membatalkan transaksi: ' . $e->getMessage());
        }
    }

    public function destroy(SalesTransaction $sale)
    {
        $this->authorize('delete', $sale);
        try {
            $this->transactionService->destroySale($sale);
            return redirect()->route('karungcabang.sales.index')->with('success', "Transaksi #{$sale->invoice_number} berhasil dihapus.");
        } catch (\Exception $e) {
            return redirect()->route('karungcabang.sales.index')->with('error', 'Terjadi kesalahan saat menghapus transaksi: ' . $e->getMessage());
        }
    }

    public function printThermal(SalesTransaction $sale)
    {
        $this->authorize('view', $sale);
        $sale->load(['customer', 'user', 'details.product']);
        $settings = Setting::where('business_unit_id', $sale->business_unit_id)
                           ->pluck('setting_value', 'setting_key');
        $qrCode = null;
        if ($sale->uuid) {
            $url = route('receipt.verify', $sale->uuid);
            $qrCode = base64_encode(QrCode::format('svg')->size(80)->generate($url));
        }
        return view('karungcabang::sales.receipts.thermal_receipt', compact('sale', 'settings', 'qrCode'));
    }

    public function downloadPdf(SalesTransaction $sale)
    {
        $this->authorize('view', $sale);
        $sale->load(['customer', 'user', 'details.product']);
        $settings = Setting::where('business_unit_id', $sale->business_unit_id)
                           ->pluck('setting_value', 'setting_key');
        $qrCode = null;
        if ($sale->uuid) {
            $url = route('receipt.verify', $sale->uuid);
            $qrCode = base64_encode(QrCode::format('svg')->size(80)->generate($url));
        }
        $pdf = Pdf::loadView('karungcabang::sales.receipts.pdf_receipt', compact('sale', 'settings', 'qrCode'));
        $safeInvoiceNumber = str_replace('/', '-', $sale->invoice_number);
        $fileName = 'struk-' . strtolower($safeInvoiceNumber) . '.pdf';
        return $pdf->stream($fileName);
    }
}