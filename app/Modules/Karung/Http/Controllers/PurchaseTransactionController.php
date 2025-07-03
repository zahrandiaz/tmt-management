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
use Intervention\Image\Laravel\Facades\Image;

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

    public function store(StorePurchaseTransactionRequest $request, StockManagementService $stockService)
    {
        $this->authorize('create', PurchaseTransaction::class);
        $validatedData = $request->validated();
        
        try {
            DB::beginTransaction();
            $supplierId = $validatedData['supplier_id'];
            if (is_null($supplierId)) {
                $defaultSupplier = Supplier::where('name', 'Pembelian Umum')->first();
                $supplierId = $defaultSupplier?->id;
            }

            $totalAmount = 0;
            foreach ($validatedData['details'] as $detail) {
                $totalAmount += $detail['quantity'] * $detail['purchase_price_at_transaction'];
            }

            $amountPaid = $request->input('amount_paid', 0);
            if ($validatedData['payment_status'] === 'Lunas') {
                $amountPaid = $totalAmount;
            }
            
            $purchase = new PurchaseTransaction();
            $purchase->business_unit_id = 1;
            $purchase->purchase_code = 'PB/'.date('Ymd').'/'.strtoupper(Str::random(6));
            $purchase->supplier_id = $supplierId;
            $purchase->transaction_date = $validatedData['transaction_date'];
            $purchase->purchase_reference_no = $validatedData['purchase_reference_no'];
            $purchase->notes = $validatedData['notes'];
            $purchase->user_id = auth()->id();
            $purchase->total_amount = $totalAmount;
            $purchase->payment_method = $validatedData['payment_method'];
            $purchase->payment_status = $validatedData['payment_status'];
            $purchase->amount_paid = $amountPaid;

            if ($request->hasFile('attachment_path')) {
                $image = $request->file('attachment_path');
                $imageName = 'nota-'.time().'.webp';
                $img = Image::read($image->getRealPath());
                $img->resize(1200, 1200, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })->toWebp(70);
                $storagePath = storage_path('app/public/purchase_attachments/' . $imageName);
                $img->save($storagePath);
                $purchase->attachment_path = 'purchase_attachments/' . $imageName;
            }
            $purchase->save();
            
            // =====================================================================
            // [PERBAIKAN BUG v1.30] BLOK KODE YANG HILANG DITAMBAHKAN DI SINI
            // =====================================================================
            foreach ($validatedData['details'] as $detail) {
                $subTotal = $detail['quantity'] * $detail['purchase_price_at_transaction'];
                $purchase->details()->create([
                    'product_id'                    => $detail['product_id'],
                    'quantity'                      => $detail['quantity'],
                    'purchase_price_at_transaction' => $detail['purchase_price_at_transaction'],
                    'sub_total'                     => $subTotal,
                ]);
            }
            // =====================================================================

            if ($request->filled('related_expense_amount') && $request->filled('related_expense_description')) {
                $purchase->operationalExpenses()->create([
                    'business_unit_id' => $purchase->business_unit_id,
                    'date' => $purchase->transaction_date,
                    'description' => $request->input('related_expense_description'),
                    'amount' => $request->input('related_expense_amount'),
                    'category' => 'Biaya Transaksi Pembelian',
                    'user_id' => auth()->id(),
                ]);
            }

            $stockService->handlePurchaseCreation($validatedData['details']);
            DB::commit();
            activity()->log("Membuat transaksi pembelian baru dengan kode #{$purchase->purchase_code}");
            return redirect()->route('karung.purchases.index')->with('success', 'Transaksi pembelian baru berhasil disimpan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function show(PurchaseTransaction $purchase)
    {
        $this->authorize('view', $purchase);
        // [MODIFIKASI v1.32.0] Eager load relasi 'returns' untuk ditampilkan di view
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

    public function update(UpdatePurchaseTransactionRequest $request, PurchaseTransaction $purchase, StockManagementService $stockService)
    {
        $this->authorize('update', $purchase);
        $validatedData = $request->validated();
        
        try {
            DB::beginTransaction();
            $stockService->handlePurchaseUpdate($purchase, $validatedData['details']);
            
            $newTotalAmount = 0;
            foreach ($validatedData['details'] as $newDetail) {
                $newTotalAmount += $newDetail['quantity'] * $newDetail['purchase_price_at_transaction'];
            }

            $amountPaid = $request->input('amount_paid', 0); // Ambil dari request
            if ($validatedData['payment_status'] === 'Lunas') {
                $amountPaid = $newTotalAmount;
            }

            // [PERBAIKAN] Update properti secara manual sebelum save
            $purchase->transaction_date = $validatedData['transaction_date'];
            $purchase->supplier_id = $validatedData['supplier_id'];
            $purchase->purchase_reference_no = $validatedData['purchase_reference_no'];
            $purchase->notes = $validatedData['notes'];
            $purchase->user_id = auth()->id();
            $purchase->total_amount = $newTotalAmount;
            $purchase->payment_method = $validatedData['payment_method'];
            $purchase->payment_status = $validatedData['payment_status'];
            $purchase->amount_paid = $amountPaid;
            
            // [MODIFIKASI] Handle upload lampiran dengan kompresi
            if ($request->hasFile('attachment_path')) {
                if ($purchase->attachment_path) { Storage::disk('public')->delete($purchase->attachment_path); }
                
                $image = $request->file('attachment_path');
                $imageName = 'nota-'.time().'.webp';

                $img = Image::read($image->getRealPath());
                $img->resize(1200, 1200, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })->toWebp(70);
                
                $storagePath = storage_path('app/public/purchase_attachments/' . $imageName);
                $img->save($storagePath);

                $purchase->attachment_path = 'purchase_attachments/' . $imageName;
            }
            $purchase->save();
            
            $purchase->details()->delete();
            foreach ($validatedData['details'] as $newDetail) {
                $subTotal = $newDetail['quantity'] * $newDetail['purchase_price_at_transaction'];
                $purchase->details()->create([
                    'product_id' => $newDetail['product_id'], 'quantity' => $newDetail['quantity'],
                    'purchase_price_at_transaction' => $newDetail['purchase_price_at_transaction'], 'sub_total' => $subTotal,
                ]);
            }
            
            activity()->log("Memperbarui transaksi pembelian dengan kode #{$purchase->purchase_code}");
            DB::commit();
            return redirect()->route('karung.purchases.index')->with('success', 'Transaksi pembelian berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    // [BARU] Method untuk restore
    public function restore(PurchaseTransaction $purchase, StockManagementService $stockService)
    {
        $this->authorize('restore', $purchase);
        
        try {
            DB::beginTransaction();
            
            // Saat restore, stok dikembalikan (ditambah) seolah-olah terjadi pembelian baru
            $stockService->handlePurchaseCreation($purchase->details->toArray());
            
            $purchase->status = 'Completed';
            $purchase->save();
            activity()->log("Memulihkan transaksi pembelian dengan kode #{$purchase->purchase_code}");
            DB::commit();
            
            return redirect()->route('karung.purchases.index', ['status' => 'Deleted'])
                             ->with('success', "Transaksi #{$purchase->purchase_code} berhasil dipulihkan.");
                             
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('karung.purchases.index', ['status' => 'Deleted'])
                             ->with('error', 'Terjadi kesalahan saat memulihkan transaksi: ' . $e->getMessage());
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

    public function updatePayment(Request $request, PurchaseTransaction $purchase)
    {
        $this->authorize('managePayment', $purchase);

        $validated = $request->validate([
            'new_payment_amount' => ['required', 'numeric', 'min:0'],
        ]);

        $newPayment = $validated['new_payment_amount'];
        $currentPaid = $purchase->amount_paid;
        $totalAmount = $purchase->total_amount;

        $totalPaid = $currentPaid + $newPayment;

        if ($totalPaid > $totalAmount) {
            return redirect()->back()->with('error', 'Jumlah pembayaran melebihi sisa tagihan!');
        }

        $purchase->amount_paid = $totalPaid;

        if ($totalPaid >= $totalAmount) {
            $purchase->payment_status = 'Lunas';
        }

        $purchase->save();
        
        activity()->log("Memperbarui pembayaran untuk kode pembelian #{$purchase->purchase_code}");

        return redirect()->back()->with('success', 'Pembayaran berhasil diperbarui!');
    }
}