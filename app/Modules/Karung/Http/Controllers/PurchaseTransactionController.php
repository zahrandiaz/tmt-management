<?php
// File: app/Modules/Karung/Http/Controllers/PurchaseTransactionController.php

namespace App\Modules\Karung\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Karung\Models\PurchaseTransaction;
use App\Modules\Karung\Models\Supplier;
use App\Modules\Karung\Models\Product;
use App\Modules\Karung\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class PurchaseTransactionController extends Controller
{
    // ... (method index() dan create() tidak berubah) ...
    public function index(Request $request)
    {
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
        $suppliers = Supplier::orderBy('name', 'asc')->get();
        $products = Product::where('is_active', true)->orderBy('name', 'asc')->get();
        return view('karung::purchases.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'transaction_date'      => ['required', 'date'],
            'supplier_id'           => ['nullable', 'integer', 'exists:karung_suppliers,id'],
            'purchase_reference_no' => ['nullable', 'string', 'max:255'],
            'notes'                 => ['nullable', 'string'],
            'attachment_path'       => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'details'               => ['required', 'array', 'min:1'],
            'details.*.product_id'  => ['required', 'integer', 'exists:karung_products,id'],
            'details.*.quantity'    => ['required', 'integer', 'min:1'],
            'details.*.purchase_price_at_transaction' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            DB::beginTransaction();
            $currentBusinessUnitId = 1;
            $isStockManagementActive = Setting::where('business_unit_id', $currentBusinessUnitId)
                                            ->where('setting_key', 'automatic_stock_management')
                                            ->first()->setting_value == 'true';
            $supplierId = $validatedData['supplier_id'];
            if (is_null($supplierId)) {
                $defaultSupplier = Supplier::where('name', 'Pembelian Umum')->first();
                $supplierId = $defaultSupplier?->id;
            }
            $purchaseCode = 'PB/'.date('Ymd').'/'.strtoupper(Str::random(6));
            $purchaseData = [
                'business_unit_id'      => $currentBusinessUnitId,
                'purchase_code'         => $purchaseCode,
                'supplier_id'           => $supplierId,
                'transaction_date'      => $validatedData['transaction_date'],
                'purchase_reference_no' => $validatedData['purchase_reference_no'],
                'notes'                 => $validatedData['notes'],
                'user_id'               => auth()->id(),
            ];
            if ($request->hasFile('attachment_path')) {
                $purchaseData['attachment_path'] = $request->file('attachment_path')->store('purchase_attachments', 'public');
            }
            $purchase = PurchaseTransaction::create($purchaseData);
            $totalAmount = 0;
            foreach ($validatedData['details'] as $detail) {
                $subTotal = $detail['quantity'] * $detail['purchase_price_at_transaction'];
                $purchase->details()->create([
                    'product_id' => $detail['product_id'],
                    'quantity' => $detail['quantity'],
                    'purchase_price_at_transaction' => $detail['purchase_price_at_transaction'],
                    'sub_total' => $subTotal,
                ]);
                $totalAmount += $subTotal;
                if ($isStockManagementActive) {
                    $product = Product::find($detail['product_id']);
                    if ($product) {
                        $product->increment('stock', $detail['quantity']);
                    }
                }
            }
            $purchase->total_amount = $totalAmount;
            $purchase->save();
            DB::commit();

            // [BARU] Catat aktivitas setelah transaksi berhasil
            activity()->log("Membuat transaksi pembelian baru dengan kode #{$purchase->purchase_code}");

            return redirect()->route('karung.purchases.index')
                            ->with('success', 'Transaksi pembelian baru berhasil disimpan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                            ->with('error', 'Terjadi kesalahan saat menyimpan transaksi pembelian: ' . $e->getMessage())
                            ->withInput();
        }
    }

    public function show(PurchaseTransaction $purchase)
    {
        $purchase->load(['supplier', 'user', 'details.product']);
        return view('karung::purchases.show', compact('purchase'));
    }

    public function cancel(PurchaseTransaction $purchase)
    {
        if ($purchase->status == 'Cancelled') {
            return redirect()->route('karung.purchases.index')->with('error', 'Transaksi ini sudah pernah dibatalkan sebelumnya.');
        }
        try {
            DB::beginTransaction();
            $currentBusinessUnitId = 1;
            $isStockManagementActive = Setting::where('business_unit_id', $currentBusinessUnitId)
                                            ->where('setting_key', 'automatic_stock_management')
                                            ->first()->setting_value == 'true';
            if ($isStockManagementActive) {
                foreach ($purchase->details as $detail) {
                    $product = $detail->product;
                    if ($product) {
                        $product->decrement('stock', $detail->quantity);
                    }
                }
            }
            $purchase->status = 'Cancelled';
            $purchase->save();
            DB::commit();

            // [BARU] Catat aktivitas pembatalan setelah berhasil
            activity()->log("Membatalkan transaksi pembelian dengan kode #{$purchase->purchase_code}");

            return redirect()->route('karung.purchases.index')->with('success', "Transaksi pembelian dengan kode '{$purchase->purchase_code}' berhasil dibatalkan.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('karung.purchases.index')->with('error', 'Terjadi kesalahan saat membatalkan transaksi: ' . $e->getMessage());
        }
    }
}