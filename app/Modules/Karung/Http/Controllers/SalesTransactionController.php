<?php
// File: app/Modules/Karung/Http/Controllers/SalesTransactionController.php

namespace App\Modules\Karung\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Karung\Models\SalesTransaction;
use App\Modules\Karung\Models\Customer;
use App\Modules\Karung\Models\Product;
use App\Modules\Karung\Services\StockManagementService;
use App\Modules\Karung\Http\Requests\StoreSalesTransactionRequest;
use App\Modules\Karung\Http\Requests\UpdateSalesTransactionRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class SalesTransactionController extends Controller
{
    // ... method index() tidak berubah ...
    public function index(Request $request)
    {
        $this->authorize('viewAny', SalesTransaction::class);
        $status = $request->query('status', 'Completed');
        $query = SalesTransaction::with(['customer', 'details.product'])->where('status', $status);
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
        return view('karung::sales.index', compact('sales', 'status'));
    }


    public function create()
    {
        $this->authorize('create', SalesTransaction::class);
        $customers = Customer::orderBy('name', 'asc')->get();
        // [MODIFIKASI] Tambahkan ->where('stock', '>', 0)
        $products = Product::where('is_active', true)
                           ->where('stock', '>', 0)
                           ->orderBy('name', 'asc')->get();
        return view('karung::sales.create', compact('customers', 'products'));
    }

    public function store(StoreSalesTransactionRequest $request, StockManagementService $stockService)
    {
        $this->authorize('create', SalesTransaction::class);
        $validatedData = $request->validated();
        
        foreach ($validatedData['details'] as $detail) {
            $product = Product::find($detail['product_id']);
            if ($product->stock < $detail['quantity']) {
                return redirect()->back()
                                 ->with('error', "Stok untuk produk '{$product->name}' tidak mencukupi (tersisa {$product->stock}).")
                                 ->withInput();
            }
        }
        
        try {
            DB::beginTransaction();
            $customerId = $validatedData['customer_id'];
            if (is_null($customerId)) { 
                $defaultCustomer = Customer::where('name', 'Pelanggan Umum')->first();
                $customerId = $defaultCustomer?->id;
            }

            $totalAmount = 0;
            foreach ($validatedData['details'] as $detail) {
                $totalAmount += $detail['quantity'] * $detail['selling_price_at_transaction'];
            }

            $amountPaid = $request->input('amount_paid', 0); // Ambil dari request, bukan validatedData
            if ($validatedData['payment_status'] === 'Lunas') {
                $amountPaid = $totalAmount;
            }

            // [PERBAIKAN] Buat objek dan set properti secara manual
            $sale = new SalesTransaction();
            $sale->business_unit_id = 1;
            $sale->customer_id = $customerId;
            $sale->transaction_date = $validatedData['transaction_date'];
            $sale->notes = $validatedData['notes'];
            $sale->user_id = auth()->id();
            $sale->invoice_number = 'INV/'.date('Ymd').'/'.strtoupper(Str::random(6));
            $sale->total_amount = $totalAmount;
            $sale->payment_method = $validatedData['payment_method'];
            $sale->payment_status = $validatedData['payment_status'];
            $sale->amount_paid = $amountPaid;
            $sale->save(); // Simpan
            
            // Simpan detail
            foreach ($validatedData['details'] as $detail) {
                $subTotal = $detail['quantity'] * $detail['selling_price_at_transaction'];
                $sale->details()->create([
                    'product_id' => $detail['product_id'], 'quantity' => $detail['quantity'],
                    'selling_price_at_transaction' => $detail['selling_price_at_transaction'], 'sub_total' => $subTotal,
                ]);
            }

            $stockService->handleSaleCreation($validatedData['details']);
            DB::commit();
            activity()->log("Membuat transaksi penjualan baru dengan invoice #{$sale->invoice_number}");
            return redirect()->route('karung.sales.index')->with('success', 'Transaksi penjualan baru berhasil disimpan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }


    // ... method show() tidak berubah ...
    public function show(SalesTransaction $sale)
    {
        $this->authorize('view', $sale);
        $sale->load(['customer', 'user', 'details.product']);
        return view('karung::sales.show', compact('sale'));
    }


    public function edit(SalesTransaction $sale)
    {
        $this->authorize('update', $sale);
        $sale->load('details.product');
        $customers = Customer::orderBy('name', 'asc')->get();
        
        // [MODIFIKASI] Kita ambil semua produk aktif, lalu di view kita akan gabungkan
        // dengan produk yang sudah ada di transaksi (meskipun stoknya 0)
        $activeProducts = Product::where('is_active', true)
                                 ->where('stock', '>', 0)
                                 ->orderBy('name', 'asc')->get();
        
        // Gabungkan produk aktif dengan produk yang sudah ada di transaksi ini
        $existingProducts = $sale->details->pluck('product');
        $products = $activeProducts->merge($existingProducts)->unique('id');

        return view('karung::sales.edit', compact('sale', 'customers', 'products'));
    }

    public function update(UpdateSalesTransactionRequest $request, SalesTransaction $sale, StockManagementService $stockService)
    {
        $this->authorize('update', $sale);
        $validatedData = $request->validated();
        
        // ... (validasi stok tidak berubah) ...

        try {
            DB::beginTransaction();
            $stockService->handleSaleUpdate($sale, $validatedData['details']);
            
            $newTotalAmount = 0;
            foreach ($validatedData['details'] as $newDetail) {
                $newTotalAmount += $newDetail['quantity'] * $newDetail['selling_price_at_transaction'];
            }

            $amountPaid = $request->input('amount_paid', 0); // Ambil dari request
            if ($validatedData['payment_status'] === 'Lunas') {
                $amountPaid = $newTotalAmount;
            }

            // [PERBAIKAN] Update properti secara manual sebelum save
            $sale->transaction_date = $validatedData['transaction_date'];
            $sale->customer_id = $validatedData['customer_id'];
            $sale->notes = $validatedData['notes'];
            $sale->user_id = auth()->id();
            $sale->total_amount = $newTotalAmount;
            $sale->payment_method = $validatedData['payment_method'];
            $sale->payment_status = $validatedData['payment_status'];
            $sale->amount_paid = $amountPaid;
            $sale->save(); // Simpan perubahan
            
            $sale->details()->delete();
            foreach ($validatedData['details'] as $newDetail) {
                $subTotal = $newDetail['quantity'] * $newDetail['selling_price_at_transaction'];
                $sale->details()->create([
                    'product_id' => $newDetail['product_id'], 'quantity' => $newDetail['quantity'],
                    'selling_price_at_transaction' => $newDetail['selling_price_at_transaction'], 'sub_total' => $subTotal,
                ]);
            }

            activity()->log("Memperbarui transaksi penjualan dengan invoice #{$sale->invoice_number}");
            DB::commit();
            return redirect()->route('karung.sales.index')->with('success', 'Transaksi penjualan berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }
    
    // [BARU] Method untuk restore
    public function restore(SalesTransaction $sale, StockManagementService $stockService)
    {
        $this->authorize('restore', $sale);
        
        try {
            DB::beginTransaction();

            // Saat restore, stok dikurangi lagi seolah-olah terjadi penjualan baru
            $stockService->handleSaleCreation($sale->details->toArray());

            $sale->status = 'Completed';
            $sale->save();
            activity()->log("Memulihkan transaksi penjualan dengan invoice #{$sale->invoice_number}");
            DB::commit();

            return redirect()->route('karung.sales.index', ['status' => 'Deleted'])
                             ->with('success', "Transaksi #{$sale->invoice_number} berhasil dipulihkan.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('karung.sales.index', ['status' => 'Deleted'])
                             ->with('error', 'Terjadi kesalahan saat memulihkan transaksi: ' . $e->getMessage());
        }
    }

    public function cancel(SalesTransaction $sale, StockManagementService $stockService)
    {
        $this->authorize('cancel', $sale);
        try {
            DB::beginTransaction();
            $stockService->handleSaleCancellation($sale);
            $sale->status = 'Cancelled';
            $sale->save();
            DB::commit();
            activity()->log("Membatalkan transaksi penjualan dengan invoice #{$sale->invoice_number}");
            return redirect()->route('karung.sales.index')->with('success', "Transaksi penjualan dengan invoice #{$sale->invoice_number} berhasil dibatalkan.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('karung.sales.index')->with('error', 'Terjadi kesalahan saat membatalkan transaksi: ' . $e->getMessage());
        }
    }

    public function destroy(SalesTransaction $sale, StockManagementService $stockService)
    {
        $this->authorize('delete', $sale);
        try {
            DB::beginTransaction();
            $stockService->handleSaleCancellation($sale);
            $sale->status = 'Deleted';
            $sale->save();
            activity()->log("Menghapus transaksi penjualan dengan invoice #{$sale->invoice_number}");
            DB::commit();
            return redirect()->route('karung.sales.index')->with('success', "Transaksi #{$sale->invoice_number} berhasil dihapus.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('karung.sales.index')->with('error', 'Terjadi kesalahan saat menghapus transaksi: ' . $e->getMessage());
        }
    }

    public function updatePayment(Request $request, SalesTransaction $sale)
    {
        $this->authorize('managePayment', $sale);

        $validated = $request->validate([
            'new_payment_amount' => ['required', 'numeric', 'min:0'],
        ]);

        $newPayment = $validated['new_payment_amount'];
        $currentPaid = $sale->amount_paid;
        $totalAmount = $sale->total_amount;

        $totalPaid = $currentPaid + $newPayment;

        if ($totalPaid > $totalAmount) {
            return redirect()->back()->with('error', 'Jumlah pembayaran melebihi sisa tagihan!');
        }

        $sale->amount_paid = $totalPaid;

        if ($totalPaid >= $totalAmount) {
            $sale->payment_status = 'Lunas';
        }

        $sale->save();
        
        activity()->log("Memperbarui pembayaran untuk invoice #{$sale->invoice_number}");

        return redirect()->back()->with('success', 'Pembayaran berhasil diperbarui!');
    }
}