<?php

namespace App\Modules\Karung\Services;

use App\Services\StockManagementService;
use App\Models\Customer;
use App\Models\Product;
use App\Modules\Karung\Models\PurchaseTransaction;
use App\Modules\Karung\Models\SalesTransaction;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class TransactionService
{
    protected $stockService;

    public function __construct(StockManagementService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Membuat transaksi penjualan baru.
     *
     * @param Request $request
     * @return SalesTransaction
     * @throws \Exception
     */
    public function createSale(Request $request): SalesTransaction
    {
        $validatedData = $request->validated();
        $productIds = collect($validatedData['details'])->pluck('product_id');
        $products = Product::find($productIds)->keyBy('id');

        // Validasi stok
        foreach ($validatedData['details'] as $detail) {
            $product = $products->get($detail['product_id']);
            if (!$product || $product->stock < $detail['quantity']) {
                throw new \Exception("Stok untuk produk '{$product->name}' tidak mencukupi (tersisa {$product->stock}).");
            }
        }

        return DB::transaction(function () use ($request, $validatedData, $products) {
            $customerId = $validatedData['customer_id'] ?? Customer::where('name', 'Pelanggan Umum')->first()->id;

            $totalAmount = collect($validatedData['details'])->sum(fn($d) => $d['quantity'] * $d['selling_price_at_transaction']);
            $amountPaid = $validatedData['payment_status'] === 'Lunas' ? $totalAmount : ($request->input('amount_paid', 0));

            $sale = SalesTransaction::create([
                'business_unit_id' => 1,
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
                $sale->details()->create([
                    'product_id' => $detail['product_id'],
                    'quantity' => $detail['quantity'],
                    'selling_price_at_transaction' => $detail['selling_price_at_transaction'],
                    'purchase_price_at_sale' => $product->purchase_price,
                    'sub_total' => $detail['quantity'] * $detail['selling_price_at_transaction'],
                ]);
            }

            if ($request->filled('related_expense_amount')) {
                $sale->operationalExpenses()->create([
                    'business_unit_id' => $sale->business_unit_id,
                    'date' => $sale->transaction_date,
                    'description' => $request->input('related_expense_description'),
                    'amount' => $request->input('related_expense_amount'),
                    'category' => 'Biaya Transaksi Penjualan',
                    'user_id' => auth()->id(),
                ]);
            }

            $this->stockService->handleSaleCreation($validatedData['details']);
            activity()->log("Membuat transaksi penjualan baru dengan invoice #{$sale->invoice_number}");

            return $sale;
        });
    }

    /**
     * Memperbarui transaksi penjualan yang ada.
     *
     * @param Request $request
     * @param SalesTransaction $sale
     * @return SalesTransaction
     */
    public function updateSale(Request $request, SalesTransaction $sale): SalesTransaction
    {
        $validatedData = $request->validated();

        return DB::transaction(function () use ($request, $validatedData, $sale) {
            $this->stockService->handleSaleUpdate($sale, $validatedData['details']);

            $newTotalAmount = collect($validatedData['details'])->sum(fn($d) => $d['quantity'] * $d['selling_price_at_transaction']);
            $amountPaid = $validatedData['payment_status'] === 'Lunas' ? $newTotalAmount : ($request->input('amount_paid', 0));

            $sale->update([
                'transaction_date' => $validatedData['transaction_date'],
                'customer_id' => $validatedData['customer_id'],
                'notes' => $validatedData['notes'],
                'total_amount' => $newTotalAmount,
                'payment_method' => $validatedData['payment_method'],
                'payment_status' => $validatedData['payment_status'],
                'amount_paid' => $amountPaid,
            ]);

            $sale->details()->delete();
            foreach ($validatedData['details'] as $newDetail) {
                $sale->details()->create([
                    'product_id' => $newDetail['product_id'],
                    'quantity' => $newDetail['quantity'],
                    'selling_price_at_transaction' => $newDetail['selling_price_at_transaction'],
                    'purchase_price_at_sale' => $newDetail['purchase_price_at_sale'],
                    'sub_total' => $newDetail['quantity'] * $newDetail['selling_price_at_transaction'],
                ]);
            }

            $relatedExpense = $sale->operationalExpenses()->first();
            $hasNewExpenseData = $request->filled('related_expense_amount') && $request->filled('related_expense_description');

            if ($hasNewExpenseData) {
                $sale->operationalExpenses()->updateOrCreate(
                    ['sales_transaction_id' => $sale->id],
                    [
                        'business_unit_id' => $sale->business_unit_id,
                        'date' => $sale->transaction_date,
                        'description' => $request->input('related_expense_description'),
                        'amount' => $request->input('related_expense_amount'),
                        'category' => 'Biaya Transaksi Penjualan',
                        'user_id' => auth()->id(),
                    ]
                );
            } elseif ($relatedExpense) {
                $relatedExpense->delete();
            }

            activity()->log("Memperbarui transaksi penjualan dengan invoice #{$sale->invoice_number}");
            return $sale;
        });
    }

    /**
     * Membuat transaksi pembelian baru.
     *
     * @param Request $request
     * @return PurchaseTransaction
     */
    public function createPurchase(Request $request): PurchaseTransaction
    {
        $validatedData = $request->validated();

        return DB::transaction(function () use ($request, $validatedData) {
            $supplierId = $validatedData['supplier_id'] ?? Supplier::where('name', 'Pembelian Umum')->first()->id;

            $totalAmount = collect($validatedData['details'])->sum(fn($d) => $d['quantity'] * $d['purchase_price_at_transaction']);
            $amountPaid = $validatedData['payment_status'] === 'Lunas' ? $totalAmount : ($request->input('amount_paid', 0));

            $purchase = PurchaseTransaction::create([
                'business_unit_id' => 1,
                'purchase_code' => 'PB/'.date('Ymd').'/'.strtoupper(Str::random(6)),
                'supplier_id' => $supplierId,
                'transaction_date' => $validatedData['transaction_date'],
                'purchase_reference_no' => $validatedData['purchase_reference_no'],
                'notes' => $validatedData['notes'],
                'user_id' => auth()->id(),
                'total_amount' => $totalAmount,
                'payment_method' => $validatedData['payment_method'],
                'payment_status' => $validatedData['payment_status'],
                'amount_paid' => $amountPaid,
                'attachment_path' => $this->handleAttachmentUpload($request),
            ]);

            foreach ($validatedData['details'] as $detail) {
                $purchase->details()->create([
                    'product_id' => $detail['product_id'],
                    'quantity' => $detail['quantity'],
                    'purchase_price_at_transaction' => $detail['purchase_price_at_transaction'],
                    'sub_total' => $detail['quantity'] * $detail['purchase_price_at_transaction'],
                ]);
            }

            if ($request->filled('related_expense_amount')) {
                $purchase->operationalExpenses()->create([
                    'business_unit_id' => $purchase->business_unit_id,
                    'date' => $purchase->transaction_date,
                    'description' => $request->input('related_expense_description'),
                    'amount' => $request->input('related_expense_amount'),
                    'category' => 'Biaya Transaksi Pembelian',
                    'user_id' => auth()->id(),
                ]);
            }

            $this->stockService->handlePurchaseCreation($validatedData['details']);
            activity()->log("Membuat transaksi pembelian baru dengan kode #{$purchase->purchase_code}");

            return $purchase;
        });
    }

    /**
     * Memperbarui transaksi pembelian yang ada.
     *
     * @param Request $request
     * @param PurchaseTransaction $purchase
     * @return PurchaseTransaction
     */
    public function updatePurchase(Request $request, PurchaseTransaction $purchase): PurchaseTransaction
    {
        $validatedData = $request->validated();

        return DB::transaction(function () use ($request, $validatedData, $purchase) {
            $this->stockService->handlePurchaseUpdate($purchase, $validatedData['details']);

            $newTotalAmount = collect($validatedData['details'])->sum(fn($d) => $d['quantity'] * $d['purchase_price_at_transaction']);
            $amountPaid = $validatedData['payment_status'] === 'Lunas' ? $newTotalAmount : ($request->input('amount_paid', 0));

            $purchase->transaction_date = $validatedData['transaction_date'];
            $purchase->supplier_id = $validatedData['supplier_id'];
            $purchase->purchase_reference_no = $validatedData['purchase_reference_no'];
            $purchase->notes = $validatedData['notes'];
            $purchase->total_amount = $newTotalAmount;
            $purchase->payment_method = $validatedData['payment_method'];
            $purchase->payment_status = $validatedData['payment_status'];
            $purchase->amount_paid = $amountPaid;

            if ($request->hasFile('attachment_path')) {
                if ($purchase->attachment_path) {
                    Storage::disk('public')->delete($purchase->attachment_path);
                }
                $purchase->attachment_path = $this->handleAttachmentUpload($request);
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
            return $purchase;
        });
    }

    /**
     * Membatalkan transaksi penjualan.
     */
    public function cancelSale(SalesTransaction $sale): void
    {
        DB::transaction(function () use ($sale) {
            $this->stockService->handleSaleCancellation($sale);
            $sale->status = 'Cancelled';
            $sale->save();
            activity()->log("Membatalkan transaksi penjualan dengan invoice #{$sale->invoice_number}");
        });
    }

    /**
     * Memulihkan transaksi penjualan yang dihapus/dibatalkan.
     */
    public function restoreSale(SalesTransaction $sale): void
    {
        DB::transaction(function () use ($sale) {
            $this->stockService->handleSaleCreation($sale->details->toArray());
            $sale->status = 'Completed';
            $sale->save();
            activity()->log("Memulihkan transaksi penjualan dengan invoice #{$sale->invoice_number}");
        });
    }

    /**
     * Menghapus (soft delete) transaksi penjualan.
     */
    public function destroySale(SalesTransaction $sale): void
    {
        DB::transaction(function () use ($sale) {
            $this->stockService->handleSaleCancellation($sale);
            $sale->status = 'Deleted';
            $sale->save();
            activity()->log("Menghapus transaksi penjualan dengan invoice #{$sale->invoice_number}");
        });
    }

    /**
     * Membatalkan transaksi pembelian.
     */
    public function cancelPurchase(PurchaseTransaction $purchase): void
    {
        DB::transaction(function () use ($purchase) {
            $this->stockService->handlePurchaseCancellation($purchase);
            $purchase->status = 'Cancelled';
            $purchase->save();
            activity()->log("Membatalkan transaksi pembelian dengan kode #{$purchase->purchase_code}");
        });
    }

    /**
     * Memulihkan transaksi pembelian yang dihapus/dibatalkan.
     */
    public function restorePurchase(PurchaseTransaction $purchase): void
    {
        DB::transaction(function () use ($purchase) {
            $this->stockService->handlePurchaseCreation($purchase->details->toArray());
            $purchase->status = 'Completed';
            $purchase->save();
            activity()->log("Memulihkan transaksi pembelian dengan kode #{$purchase->purchase_code}");
        });
    }

    /**
     * Menghapus (soft delete) transaksi pembelian.
     */
    public function destroyPurchase(PurchaseTransaction $purchase): void
    {
        DB::transaction(function () use ($purchase) {
            $this->stockService->handlePurchaseCancellation($purchase);
            $purchase->status = 'Deleted';
            $purchase->save();
            activity()->log("Menghapus transaksi pembelian dengan kode #{$purchase->purchase_code}");
        });
    }

    /**
     * Menangani upload dan kompresi lampiran.
     *
     * @param Request $request
     * @return string|null
     */
    private function handleAttachmentUpload(Request $request): ?string
    {
        if (!$request->hasFile('attachment_path')) {
            return null;
        }

        $image = $request->file('attachment_path');
        $imageName = 'nota-'.time().'.webp';

        $img = Image::read($image->getRealPath());
        $img->resize(1200, 1200, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        })->toWebp(70);
        
        $storagePath = storage_path('app/public/purchase_attachments/' . $imageName);
        $img->save($storagePath);

        return 'purchase_attachments/' . $imageName;
    }
}