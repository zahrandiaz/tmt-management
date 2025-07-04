<?php

namespace App\Modules\Karung\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Karung\Http\Requests\StorePurchaseReturnRequest;
use App\Modules\Karung\Http\Requests\StoreSalesReturnRequest;
use App\Modules\Karung\Models\PurchaseReturn;
use App\Modules\Karung\Models\PurchaseTransaction;
use App\Modules\Karung\Models\SalesReturn;
use App\Modules\Karung\Models\SalesTransaction;
use App\Modules\Karung\Models\Setting;
use App\Modules\Karung\Services\ReturnService; // [BARU v1.32.1]
use Barryvdh\DomPDF\Facade\Pdf;

class ReturnController extends Controller
{
    protected $returnService; // [BARU v1.32.1]

    public function __construct(ReturnService $returnService) // [MODIFIKASI v1.32.1]
    {
        $this->returnService = $returnService; // [BARU v1.32.1]
    }

    public function salesReturnIndex()
    {
        $this->authorize('viewAny', SalesReturn::class);
        $returns = SalesReturn::with('customer', 'originalTransaction')->latest()->paginate(15);
        return view('karung::returns.sales.index', compact('returns'));
    }

    public function createSalesReturn(SalesTransaction $salesTransaction)
    {
        $this->authorize('create', SalesReturn::class);
        $salesTransaction->load('details.product');
        return view('karung::returns.sales.create', compact('salesTransaction'));
    }

    public function storeSalesReturn(StoreSalesReturnRequest $request, SalesTransaction $salesTransaction)
    {
        try {
            // [REFACTOR v1.32.1] Panggil service untuk menangani logika
            $return = $this->returnService->createSalesReturn($request->validated(), $salesTransaction);

            return redirect()->route('karung.returns.sales.show', $return->id)
                ->with('success', 'Retur penjualan berhasil dibuat dan piutang telah disesuaikan. Kode: ' . $return->return_code);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membuat retur: ' . $e->getMessage())->withInput();
        }
    }

    public function showSalesReturn(SalesReturn $salesReturn)
    {
        $this->authorize('view', $salesReturn);
        $salesReturn->load('details.product', 'customer', 'user', 'originalTransaction');
        return view('karung::returns.sales.show', compact('salesReturn'));
    }

    public function purchaseReturnIndex()
    {
        $this->authorize('viewAny', PurchaseReturn::class);
        $returns = PurchaseReturn::with('supplier', 'originalTransaction')->latest()->paginate(15);
        return view('karung::returns.purchases.index', compact('returns'));
    }

    public function createPurchaseReturn(PurchaseTransaction $purchaseTransaction)
    {
        $this->authorize('create', PurchaseReturn::class);
        $loadedTransaction = PurchaseTransaction::with('details.product')
            ->findOrFail($purchaseTransaction->id);
        return view('karung::returns.purchases.create', ['purchaseTransaction' => $loadedTransaction]);
    }

    public function storePurchaseReturn(StorePurchaseReturnRequest $request, PurchaseTransaction $purchaseTransaction)
    {
        try {
            // [REFACTOR v1.32.1] Panggil service untuk menangani logika
            $return = $this->returnService->createPurchaseReturn($request->validated(), $purchaseTransaction);

            return redirect()->route('karung.returns.purchases.show', $return->id)
                ->with('success', 'Retur pembelian berhasil dibuat dan utang telah disesuaikan. Kode: ' . $return->return_code);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membuat retur: ' . $e->getMessage())->withInput();
        }
    }

    public function showPurchaseReturn(PurchaseReturn $purchaseReturn)
    {
        $this->authorize('view', $purchaseReturn);
        $purchaseReturn->load('details.product', 'supplier', 'user', 'originalTransaction');
        return view('karung::returns.purchases.show', compact('purchaseReturn'));
    }

    public function downloadCreditNotePdf(SalesReturn $salesReturn)
    {
        $this->authorize('view', $salesReturn);
        $salesReturn->load('details.product', 'customer', 'originalTransaction');
        $settings = Setting::pluck('setting_value', 'setting_key');
        $pdf = Pdf::loadView('karung::returns.sales.credit_note_pdf', compact('salesReturn', 'settings'));
        $fileName = 'nota-kredit-' . strtolower($salesReturn->return_code) . '.pdf';
        return $pdf->stream($fileName);
    }

    public function downloadDebitNotePdf(PurchaseReturn $purchaseReturn)
    {
        $this->authorize('view', $purchaseReturn);
        $purchaseReturn->load('details.product', 'supplier', 'originalTransaction');
        $settings = Setting::pluck('setting_value', 'setting_key');
        $pdf = Pdf::loadView('karung::returns.purchases.debit_note_pdf', compact('purchaseReturn', 'settings'));
        $fileName = 'nota-debit-' . strtolower($purchaseReturn->return_code) . '.pdf';
        return $pdf->stream($fileName);
    }
}