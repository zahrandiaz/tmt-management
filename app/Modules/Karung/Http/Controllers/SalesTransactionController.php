<?php
// File: app/Modules/Karung/Http/Controllers/SalesTransactionController.php

namespace App\Modules\Karung\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Karung\Models\SalesTransaction;
use App\Modules\Karung\Models\Customer;
use App\Modules\Karung\Models\Product;
use App\Modules\Karung\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class SalesTransactionController extends Controller
{
    // ... (method index() dan create() tidak berubah) ...
    public function index(Request $request)
    {
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
        $customers = Customer::orderBy('name', 'asc')->get();
        $products = Product::where('is_active', true)->orderBy('name', 'asc')->get();
        return view('karung::sales.create', compact('customers', 'products'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'transaction_date'      => ['required', 'date'],
            'customer_id'           => ['nullable', 'integer', 'exists:karung_customers,id'],
            'notes'                 => ['nullable', 'string'],
            'details'               => ['required', 'array', 'min:1'],
            'details.*.product_id'  => ['required', 'integer', 'exists:karung_products,id'],
            'details.*.quantity'    => ['required', 'integer', 'min:1'],
            'details.*.selling_price_at_transaction' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            DB::beginTransaction();

            $currentBusinessUnitId = 1;

            $isStockManagementActive = Setting::where('business_unit_id', $currentBusinessUnitId)
                                            ->where('setting_key', 'automatic_stock_management')
                                            ->first()->setting_value == 'true';

            $customerId = $validatedData['customer_id'];
            if (is_null($customerId)) {
                $defaultCustomer = Customer::where('name', 'Pelanggan Umum')->first();
                $customerId = $defaultCustomer?->id;
            }

            $saleData = [
                'business_unit_id'      => $currentBusinessUnitId,
                'customer_id'           => $customerId,
                'transaction_date'      => $validatedData['transaction_date'],
                'notes'                 => $validatedData['notes'],
                'user_id'               => auth()->id(),
                'invoice_number'        => 'INV/'.date('Ymd').'/'.strtoupper(Str::random(6)),
            ];

            $sale = SalesTransaction::create($saleData);
            $totalAmount = 0;

            foreach ($validatedData['details'] as $detail) {
                $subTotal = $detail['quantity'] * $detail['selling_price_at_transaction'];
                $sale->details()->create([
                    'product_id' => $detail['product_id'],
                    'quantity' => $detail['quantity'],
                    'selling_price_at_transaction' => $detail['selling_price_at_transaction'],
                    'sub_total' => $subTotal,
                ]);
                $totalAmount += $subTotal;
                if ($isStockManagementActive) {
                    $product = Product::find($detail['product_id']);
                    if ($product) {
                        $product->decrement('stock', $detail['quantity']);
                    }
                }
            }

            $sale->total_amount = $totalAmount;
            $sale->save();

            DB::commit();

            // [BARU] Catat aktivitas setelah transaksi berhasil
            activity()->log("Membuat transaksi penjualan baru dengan invoice #{$sale->invoice_number}");

            return redirect()->route('karung.sales.index')
                            ->with('success', 'Transaksi penjualan baru berhasil disimpan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                            ->with('error', 'Terjadi kesalahan saat menyimpan transaksi penjualan: ' . $e->getMessage())
                            ->withInput();
        }
    }

    public function show(SalesTransaction $sale)
    {
        $sale->load(['customer', 'user', 'details.product']);
        return view('karung::sales.show', compact('sale'));
    }

    public function cancel(SalesTransaction $sale)
    {
        if ($sale->status == 'Cancelled') {
            return redirect()->route('karung.sales.index')
                            ->with('error', 'Transaksi ini sudah pernah dibatalkan sebelumnya.');
        }

        try {
            DB::beginTransaction();
            $currentBusinessUnitId = 1;
            $isStockManagementActive = Setting::where('business_unit_id', $currentBusinessUnitId)
                                            ->where('setting_key', 'automatic_stock_management')
                                            ->first()->setting_value == 'true';
            if ($isStockManagementActive) {
                foreach ($sale->details as $detail) {
                    $product = $detail->product;
                    if ($product) {
                        $product->increment('stock', $detail->quantity);
                    }
                }
            }
            $sale->status = 'Cancelled';
            $sale->save();
            DB::commit();

            // [BARU] Catat aktivitas pembatalan setelah berhasil
            activity()->log("Membatalkan transaksi penjualan dengan invoice #{$sale->invoice_number}");

            return redirect()->route('karung.sales.index')
                            ->with('success', "Transaksi penjualan dengan invoice #{$sale->invoice_number} berhasil dibatalkan.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('karung.sales.index')
                            ->with('error', 'Terjadi kesalahan saat membatalkan transaksi: ' . $e->getMessage());
        }
    }
}