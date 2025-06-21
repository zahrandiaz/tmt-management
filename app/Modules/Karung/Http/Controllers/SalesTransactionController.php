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
use App\Modules\Karung\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;

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
        
        $productIds = collect($validatedData['details'])->pluck('product_id');
        $products = Product::find($productIds)->keyBy('id');

        // Validasi stok
        foreach ($validatedData['details'] as $detail) {
            $product = $products->get($detail['product_id']);
            if (!$product || $product->stock < $detail['quantity']) {
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

            $totalAmount = collect($validatedData['details'])->sum(function ($detail) {
                return $detail['quantity'] * $detail['selling_price_at_transaction'];
            });

            $amountPaid = $request->input('amount_paid', 0);
            if ($validatedData['payment_status'] === 'Lunas') {
                $amountPaid = $totalAmount;
            }

            $sale = SalesTransaction::create([
                'business_unit_id' => 1, //TODO: Dibuat dinamis nanti
                'customer_id' => $customerId,
                'transaction_date' => $validatedData['transaction_date'],
                'notes' => $validatedData['notes'],
                'user_id' => auth()->id(),
                'invoice_number' => 'INV/'.date('Ymd').'/'.strtoupper(Str::random(6)),
                'total_amount' => $totalAmount,
                'payment_method' => $validatedData['payment_method'],
                'payment_status' => $validatedData['payment_status'],
                'amount_paid' => $amountPaid,
            ]);
            
            foreach ($validatedData['details'] as $detail) {
                $product = $products->get($detail['product_id']);
                $purchasePrice = $product ? $product->purchase_price : 0;
                $subTotal = $detail['quantity'] * $detail['selling_price_at_transaction'];

                $sale->details()->create([
                    'product_id' => $detail['product_id'],
                    'quantity' => $detail['quantity'],
                    'selling_price_at_transaction' => $detail['selling_price_at_transaction'],
                    'purchase_price_at_sale' => $purchasePrice,
                    'sub_total' => $subTotal,
                ]);
            }

            // [BARU] Logika untuk menyimpan biaya operasional terkait
            if ($request->filled('related_expense_amount') && $request->filled('related_expense_description')) {
                // Gunakan relasi yang sudah kita buat di Model
                $sale->operationalExpenses()->create([
                    'business_unit_id' => $sale->business_unit_id,
                    'date' => $sale->transaction_date, // Gunakan tanggal transaksi sebagai tanggal biaya
                    'description' => $request->input('related_expense_description'),
                    'amount' => $request->input('related_expense_amount'),
                    'category' => 'Biaya Transaksi Penjualan', // Kategori default
                    'user_id' => auth()->id(),
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
        // [MODIFIKASI] Eager load relasi operationalExpenses
        $sale->load(['details.product', 'operationalExpenses']);
        $customers = Customer::orderBy('name', 'asc')->get();
        
        $activeProducts = Product::where('is_active', true)
                                      ->where('stock', '>', 0)
                                      ->orderBy('name', 'asc')->get();
        
        $existingProducts = $sale->details->pluck('product');
        $products = $activeProducts->merge($existingProducts)->unique('id');

        return view('karung::sales.edit', compact('sale', 'customers', 'products'));
    }

    public function update(UpdateSalesTransactionRequest $request, SalesTransaction $sale, StockManagementService $stockService)
    {
        $this->authorize('update', $sale);
        $validatedData = $request->validated();
        
        try {
            DB::beginTransaction();
            
            // ... Logika penyesuaian stok dan kalkulasi ulang total tidak berubah ...
            $stockService->handleSaleUpdate($sale, $validatedData['details']);
            
            $newTotalAmount = collect($validatedData['details'])->sum(function ($detail) {
                return $detail['quantity'] * $detail['selling_price_at_transaction'];
            });

            $amountPaid = $request->input('amount_paid', 0);
            if ($validatedData['payment_status'] === 'Lunas') {
                $amountPaid = $newTotalAmount;
            }

            $sale->update([
                'transaction_date' => $validatedData['transaction_date'],
                'customer_id'      => $validatedData['customer_id'],
                'notes'            => $validatedData['notes'],
                'total_amount'     => $newTotalAmount,
                'payment_method'   => $validatedData['payment_method'],
                'payment_status'   => $validatedData['payment_status'],
                'amount_paid'      => $amountPaid,
            ]);
            
            // ... Logika hapus dan buat ulang detail tidak berubah ...
            $sale->details()->delete();
            foreach ($validatedData['details'] as $newDetail) {
                 $sale->details()->create([
                    'product_id'                     => $newDetail['product_id'],
                    'quantity'                       => $newDetail['quantity'],
                    'selling_price_at_transaction'   => $newDetail['selling_price_at_transaction'],
                    'purchase_price_at_sale'         => $newDetail['purchase_price_at_sale'], 
                    'sub_total'                      => $newDetail['quantity'] * $newDetail['selling_price_at_transaction'],
                ]);
            }

            // [MODIFIKASI] Logika untuk menangani biaya operasional terkait
            $relatedExpense = $sale->operationalExpenses()->first();
            $hasNewExpenseData = $request->filled('related_expense_amount') && $request->filled('related_expense_description');

            if ($hasNewExpenseData) {
                // Jika ada data baru, update atau buat baru
                $sale->operationalExpenses()->updateOrCreate(
                    ['sales_transaction_id' => $sale->id], // Kunci untuk mencari
                    [ // Data untuk diupdate atau dibuat
                        'business_unit_id' => $sale->business_unit_id,
                        'date' => $sale->transaction_date,
                        'description' => $request->input('related_expense_description'),
                        'amount' => $request->input('related_expense_amount'),
                        'category' => 'Biaya Transaksi Penjualan',
                        'user_id' => auth()->id(),
                    ]
                );
            } elseif ($relatedExpense) {
                // Jika tidak ada data baru DAN ada biaya lama, hapus
                $relatedExpense->delete();
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

    public function printThermal(SalesTransaction $sale)
    {
        $this->authorize('view', $sale);
        $sale->load(['customer', 'user', 'details.product']);

        // Ambil pengaturan toko
        $settings = Setting::where('business_unit_id', $sale->business_unit_id)
                           ->pluck('setting_value', 'setting_key');

        // Kita akan membuat view ini di langkah berikutnya
        return view('karung::sales.receipts.thermal_receipt', compact('sale', 'settings'));
    }

    public function downloadPdf(SalesTransaction $sale)
    {
        $this->authorize('view', $sale);
        $sale->load(['customer', 'user', 'details.product']);
        
        $settings = Setting::where('business_unit_id', $sale->business_unit_id)
                           ->pluck('setting_value', 'setting_key');
        
        $pdf = Pdf::loadView('karung::sales.receipts.pdf_receipt', compact('sale', 'settings'));
        
        // [MODIFIKASI] Ganti karakter '/' dengan '-' pada nama file agar valid
        $safeInvoiceNumber = str_replace('/', '-', $sale->invoice_number);
        $fileName = 'struk-' . strtolower($safeInvoiceNumber) . '.pdf';
        
        return $pdf->stream($fileName); 
    }

}