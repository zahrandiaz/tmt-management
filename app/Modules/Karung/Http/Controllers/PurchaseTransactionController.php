<?php

namespace App\Modules\Karung\Http\Controllers;

use App\Http\Controllers\ModuleBaseController;
use App\Modules\Karung\Http\Requests\StorePurchaseTransactionRequest;
use App\Modules\Karung\Http\Requests\UpdatePurchaseTransactionRequest;
use App\Models\Product;
use App\Modules\Karung\Models\PurchaseTransaction;
use App\Models\Supplier;
use App\Modules\Karung\Services\TransactionService;
use Illuminate\Http\Request;

class PurchaseTransactionController extends ModuleBaseController
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', PurchaseTransaction::class);
        $status = $request->query('status', 'Completed');
        $query = PurchaseTransaction::with(['supplier', 'details.product'])->where('status', $status);
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('purchase_reference_no', 'like', '%' . $searchTerm . '%')
                  ->orWhere('purchase_code', 'like', '%' . $searchTerm . '%')
                  ->orWhereHas('supplier', function ($supplierQuery) use ($searchTerm) {
                      $supplierQuery->where('name', 'like', '%' . $searchTerm . '%');
                  });
            });
        }
        $purchases = $query->orderBy('transaction_date', 'desc')->paginate(15);
        return view('karung::purchases.index', compact('purchases', 'status'));
    }

    public function create()
    {
        $this->authorize('create', PurchaseTransaction::class);
        $suppliers = Supplier::orderBy('name', 'asc')->get();
        $products = Product::where('is_active', true)->orderBy('name', 'asc')->get();
        return view('karung::purchases.create', compact('suppliers', 'products'));
    }

    public function store(StorePurchaseTransactionRequest $request)
    {
        $this->authorize('create', PurchaseTransaction::class);
        try {
            $this->transactionService->createPurchase($request);
            return redirect()->route('karung.purchases.index')->with('success', 'Transaksi pembelian baru berhasil disimpan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function show(PurchaseTransaction $purchase)
    {
        $this->authorize('view', $purchase);
        $purchase->load(['supplier', 'user', 'details.product', 'returns']);
        return view('karung::purchases.show', compact('purchase'));
    }

    public function edit(PurchaseTransaction $purchase)
    {
        $this->authorize('update', $purchase);
        $purchase->load('details.product');
        $suppliers = Supplier::orderBy('name', 'asc')->get();
        $products = Product::where('is_active', true)->orderBy('name', 'asc')->get();
        return view('karung::purchases.edit', compact('purchase', 'suppliers', 'products'));
    }

    public function update(UpdatePurchaseTransactionRequest $request, PurchaseTransaction $purchase)
    {
        $this->authorize('update', $purchase);
        try {
            $this->transactionService->updatePurchase($request, $purchase);
            return redirect()->route('karung.purchases.index')->with('success', 'Transaksi pembelian berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function restore(PurchaseTransaction $purchase)
    {
        $this->authorize('restore', $purchase);
        try {
            $this->transactionService->restorePurchase($purchase);
            return redirect()->route('karung.purchases.index', ['status' => 'Deleted'])
                             ->with('success', "Transaksi #{$purchase->purchase_code} berhasil dipulihkan.");
        } catch (\Exception $e) {
            return redirect()->route('karung.purchases.index', ['status' => 'Deleted'])
                             ->with('error', 'Terjadi kesalahan saat memulihkan transaksi: ' . $e->getMessage());
        }
    }

    public function cancel(PurchaseTransaction $purchase)
    {
        $this->authorize('cancel', $purchase);
        try {
            $this->transactionService->cancelPurchase($purchase);
            return redirect()->route('karung.purchases.index')->with('success', "Transaksi pembelian dengan kode '{$purchase->purchase_code}' berhasil dibatalkan.");
        } catch (\Exception $e) {
            return redirect()->route('karung.purchases.index')->with('error', 'Terjadi kesalahan saat membatalkan transaksi: ' . $e->getMessage());
        }
    }


    public function destroy(PurchaseTransaction $purchase)
    {
        $this->authorize('delete', $purchase);
        try {
            $this->transactionService->destroyPurchase($purchase);
            return redirect()->route('karung.purchases.index')->with('success', "Transaksi #{$purchase->purchase_code} berhasil dihapus.");
        } catch (\Exception $e) {
            return redirect()->route('karung.purchases.index')->with('error', 'Terjadi kesalahan saat menghapus transaksi: ' . $e->getMessage());
        }
    }
}