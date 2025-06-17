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

    // ... method store() tidak berubah ...
    public function store(StoreSalesTransactionRequest $request, StockManagementService $stockService)
    {
        $this->authorize('create', SalesTransaction::class);

        // [BARU] Validasi backend untuk stok sebelum menyimpan
        foreach ($request->details as $detail) {
            $product = Product::find($detail['product_id']);
            if ($product->stock < $detail['quantity']) {
                return redirect()->back()
                                 ->with('error', "Stok untuk produk '{$product->name}' tidak mencukupi (tersisa {$product->stock}).")
                                 ->withInput();
            }
        }
        
        $validatedData = $request->validated();
        try {
            DB::beginTransaction();
            $customerId = $validatedData['customer_id'];
            if (is_null($customerId)) { $defaultCustomer = Customer::where('name', 'Pelanggan Umum')->first(); $customerId = $defaultCustomer?->id; }
            $saleData = [ 'business_unit_id' => 1, 'customer_id' => $customerId, 'transaction_date' => $validatedData['transaction_date'], 'notes' => $validatedData['notes'], 'user_id' => auth()->id(), 'invoice_number' => 'INV/'.date('Ymd').'/'.strtoupper(Str::random(6)), ];
            $sale = SalesTransaction::create($saleData);
            $totalAmount = 0;
            foreach ($validatedData['details'] as $detail) {
                $subTotal = $detail['quantity'] * $detail['selling_price_at_transaction'];
                $sale->details()->create([ 'product_id' => $detail['product_id'], 'quantity' => $detail['quantity'], 'selling_price_at_transaction' => $detail['selling_price_at_transaction'], 'sub_total' => $subTotal, ]);
                $totalAmount += $subTotal;
            }
            $stockService->handleSaleCreation($validatedData['details']);
            $sale->total_amount = $totalAmount;
            $sale->save();
            DB::commit();
            activity()->log("Membuat transaksi penjualan baru dengan invoice #{$sale->invoice_number}");
            return redirect()->route('karung.sales.index')->with('success', 'Transaksi penjualan baru berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan transaksi penjualan: ' . $e->getMessage())->withInput();
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

    // ... sisa method (update, cancel, destroy) tidak ada perubahan ...
    public function update(UpdateSalesTransactionRequest $request, SalesTransaction $sale, StockManagementService $stockService)
    {
        $this->authorize('update', $sale);

        // [BARU] Validasi backend untuk stok sebelum menyimpan
        foreach ($request->details as $detail) {
            $product = Product::find($detail['product_id']);
            $originalDetail = $sale->details->firstWhere('product_id', $product->id);
            $originalQuantity = $originalDetail ? $originalDetail->quantity : 0;
            // Stok yang tersedia adalah stok saat ini + stok yang akan dikembalikan dari transaksi edit
            $availableStock = $product->stock + $originalQuantity; 

            if ($availableStock < $detail['quantity']) {
                 return redirect()->back()
                                 ->with('error', "Stok untuk produk '{$product->name}' tidak mencukupi (tersedia {$availableStock}).")
                                 ->withInput();
            }
        }

        $validatedData = $request->validated();
        try {
            DB::beginTransaction();
            $stockService->handleSaleUpdate($sale, $validatedData['details']);
            $sale->update([ 'transaction_date' => $validatedData['transaction_date'], 'customer_id' => $validatedData['customer_id'], 'notes' => $validatedData['notes'], 'user_id' => auth()->id(), ]);
            $sale->details()->delete();
            $newTotalAmount = 0;
            foreach ($validatedData['details'] as $newDetail) {
                $subTotal = $newDetail['quantity'] * $newDetail['selling_price_at_transaction'];
                $sale->details()->create([ 'product_id' => $newDetail['product_id'], 'quantity' => $newDetail['quantity'], 'selling_price_at_transaction' => $newDetail['selling_price_at_transaction'], 'sub_total' => $subTotal, ]);
                $newTotalAmount += $subTotal;
            }
            $sale->total_amount = $newTotalAmount;
            $sale->save();
            activity()->log("Memperbarui transaksi penjualan dengan invoice #{$sale->invoice_number}");
            DB::commit();
            return redirect()->route('karung.sales.index')->with('success', 'Transaksi penjualan berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui transaksi: ' . $e->getMessage())->withInput();
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
}