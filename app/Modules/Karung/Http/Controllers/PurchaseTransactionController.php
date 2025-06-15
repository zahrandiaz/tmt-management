<?php
// File: app/Modules/Karung/Http/Controllers/PurchaseTransactionController.php

namespace App\Modules\Karung\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Karung\Models\PurchaseTransaction;
use App\Modules\Karung\Models\Supplier;
use App\Modules\Karung\Models\Product;
use App\Modules\Karung\Models\Setting;
use App\Modules\Karung\Services\StockManagementService; // <-- Pastikan ini di-import
use App\Modules\Karung\Http\Requests\StorePurchaseTransactionRequest;
use App\Modules\Karung\Http\Requests\UpdatePurchaseTransactionRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;


class PurchaseTransactionController extends Controller
{
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

    // [MODIFIKASI] Gunakan StorePurchaseTransactionRequest
    public function store(StorePurchaseTransactionRequest $request, StockManagementService $stockService)
    {
        $this->authorize('create', PurchaseTransaction::class);

        // Data yang masuk di sini sudah dijamin valid oleh StorePurchaseTransactionRequest
        $validatedData = $request->validated();

        try {
            DB::beginTransaction();
            $supplierId = $validatedData['supplier_id'];
            if (is_null($supplierId)) { $defaultSupplier = Supplier::where('name', 'Pembelian Umum')->first(); $supplierId = $defaultSupplier?->id; }
            $purchaseCode = 'PB/'.date('Ymd').'/'.strtoupper(Str::random(6));
            $purchaseData = ['business_unit_id' => 1, 'purchase_code' => $purchaseCode, 'supplier_id' => $supplierId, 'transaction_date' => $validatedData['transaction_date'], 'purchase_reference_no' => $validatedData['purchase_reference_no'], 'notes' => $validatedData['notes'], 'user_id' => auth()->id(),];
            
            if ($request->hasFile('attachment_path')) {
                // Gunakan $validatedData karena sudah aman
                $purchaseData['attachment_path'] = $validatedData['attachment_path']->store('purchase_attachments', 'public');
            }
            
            $purchase = PurchaseTransaction::create($purchaseData);
            $totalAmount = 0;
            foreach ($validatedData['details'] as $detail) {
                $subTotal = $detail['quantity'] * $detail['purchase_price_at_transaction'];
                $purchase->details()->create(['product_id' => $detail['product_id'], 'quantity' => $detail['quantity'], 'purchase_price_at_transaction' => $detail['purchase_price_at_transaction'], 'sub_total' => $subTotal,]);
                $totalAmount += $subTotal;
            }
            
            $stockService->handlePurchaseCreation($validatedData['details']);
            
            $purchase->total_amount = $totalAmount;
            $purchase->save();
            DB::commit();
            activity()->log("Membuat transaksi pembelian baru dengan kode #{$purchase->purchase_code}");
            return redirect()->route('karung.purchases.index')->with('success', 'Transaksi pembelian baru berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan transaksi pembelian: ' . $e->getMessage())->withInput();
        }
    }

    public function show(PurchaseTransaction $purchase)
    {
        $this->authorize('view', $purchase);
        $purchase->load(['supplier', 'user', 'details.product']);
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

    // [MODIFIKASI] Gunakan UpdatePurchaseTransactionRequest
    public function update(UpdatePurchaseTransactionRequest $request, PurchaseTransaction $purchase, StockManagementService $stockService)
    {
        $this->authorize('update', $purchase);

        // Data yang masuk di sini sudah dijamin valid oleh UpdatePurchaseTransactionRequest
        $validatedData = $request->validated();
        
        try {
            DB::beginTransaction();
            $stockService->handlePurchaseUpdate($purchase, $validatedData['details']);
            $purchaseData = ['transaction_date' => $validatedData['transaction_date'], 'supplier_id' => $validatedData['supplier_id'], 'purchase_reference_no' => $validatedData['purchase_reference_no'], 'notes' => $validatedData['notes'], 'user_id' => auth()->id(),];
            if ($request->hasFile('attachment_path')) { if ($purchase->attachment_path) { Storage::disk('public')->delete($purchase->attachment_path); } $purchaseData['attachment_path'] = $validatedData['attachment_path']->store('purchase_attachments', 'public'); }
            $purchase->update($purchaseData);
            $purchase->details()->delete();
            $newTotalAmount = 0;
            foreach ($validatedData['details'] as $newDetail) {
                $subTotal = $newDetail['quantity'] * $newDetail['purchase_price_at_transaction'];
                $purchase->details()->create(['product_id' => $newDetail['product_id'], 'quantity' => $newDetail['quantity'], 'purchase_price_at_transaction' => $newDetail['purchase_price_at_transaction'], 'sub_total' => $subTotal,]);
                $newTotalAmount += $subTotal;
            }
            $purchase->total_amount = $newTotalAmount;
            $purchase->save();
            activity()->log("Memperbarui transaksi pembelian dengan kode #{$purchase->purchase_code}");
            DB::commit();
            return redirect()->route('karung.purchases.index')->with('success', 'Transaksi pembelian berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui transaksi: ' . $e->getMessage())->withInput();
        }
    }

    public function cancel(PurchaseTransaction $purchase, StockManagementService $stockService)
    {
        $this->authorize('cancel', $purchase);
        try {
            DB::beginTransaction();

            // [REFACTORING] Panggil service untuk menangani logika stok cancel
            $stockService->handlePurchaseCancellation($purchase);

            $purchase->status = 'Cancelled';
            $purchase->save();
            DB::commit();
            activity()->log("Membatalkan transaksi pembelian dengan kode #{$purchase->purchase_code}");
            return redirect()->route('karung.purchases.index')->with('success', "Transaksi pembelian dengan kode '{$purchase->purchase_code}' berhasil dibatalkan.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('karung.purchases.index')->with('error', 'Terjadi kesalahan saat membatalkan transaksi: ' . $e->getMessage());
        }
    }

    public function destroy(PurchaseTransaction $purchase, StockManagementService $stockService)
    {
        $this->authorize('delete', $purchase);
        try {
            DB::beginTransaction();

            // [REFACTORING] Kita bisa gunakan method yang sama dengan cancel
            $stockService->handlePurchaseCancellation($purchase);

            $purchase->status = 'Deleted';
            $purchase->save();
            activity()->log("Menghapus transaksi pembelian dengan kode #{$purchase->purchase_code}");
            DB::commit();
            return redirect()->route('karung.purchases.index')->with('success', "Transaksi #{$purchase->purchase_code} berhasil dihapus.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('karung.purchases.index')->with('error', 'Terjadi kesalahan saat menghapus transaksi: ' . $e->getMessage());
        }
    }
}