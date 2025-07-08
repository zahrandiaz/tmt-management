<?php

namespace App\Services;

use App\Modules\Karung\Models\PurchaseReturn;
use App\Modules\Karung\Models\PurchaseTransaction;
use App\Modules\Karung\Models\SalesReturn;
use App\Modules\Karung\Models\SalesTransaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ReturnService
{
    protected $stockManagementService;

    public function __construct(StockManagementService $stockManagementService)
    {
        $this->stockManagementService = $stockManagementService;
    }

    /**
     * Membuat data retur penjualan dan memproses logika terkait.
     * Menggunakan Model sebagai type-hint agar fleksibel.
     */
    public function createSalesReturn(array $validatedData, Model $salesTransaction): Model
    {
        return DB::transaction(function () use ($validatedData, $salesTransaction) {
            // Logika untuk memilih model SalesReturn yang benar (Pusat atau Cabang)
            $salesReturnModel = ($salesTransaction->business_unit_id == 1)
                ? SalesReturn::class
                : \App\Modules\KarungCabang\Models\SalesReturn::class;

            $totalReturnAmount = 0;

            $salesReturn = $salesReturnModel::create([
                'business_unit_id' => $salesTransaction->business_unit_id,
                'return_code' => 'RTS-' . Carbon::now()->format('YmdHis'),
                'sales_transaction_id' => $salesTransaction->id,
                'customer_id' => $salesTransaction->customer_id,
                'user_id' => auth()->id(),
                'return_date' => $validatedData['return_date'],
                'reason' => $validatedData['reason'],
                'total_amount' => 0, // Placeholder
            ]);

            foreach ($validatedData['items'] as $item) {
                $originalDetail = $salesTransaction->details()->find($item['sales_transaction_detail_id']);
                if (!$originalDetail || $item['return_quantity'] > $originalDetail->quantity) {
                    throw new \Exception("Jumlah retur untuk produk {$originalDetail->product->name} melebihi jumlah pembelian.");
                }

                $subtotal = $originalDetail->selling_price_at_transaction * $item['return_quantity'];
                $totalReturnAmount += $subtotal;

                $salesReturn->details()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['return_quantity'],
                    'price' => $originalDetail->selling_price_at_transaction,
                    'subtotal' => $subtotal,
                ]);
            }

            $salesReturn->total_amount = $totalReturnAmount;
            $salesReturn->save();

            $this->stockManagementService->handleSaleReturn($salesReturn);

            $salesTransaction->total_amount -= $totalReturnAmount;
            if ($salesTransaction->amount_paid >= $salesTransaction->total_amount) {
                $salesTransaction->payment_status = 'Lunas';
            }
            $salesTransaction->save();

            return $salesReturn;
        });
    }

    /**
     * Membuat data retur pembelian dan memproses logika terkait.
     * Menggunakan Model sebagai type-hint agar fleksibel.
     */
    public function createPurchaseReturn(array $validatedData, Model $purchaseTransaction): Model
    {
        return DB::transaction(function () use ($validatedData, $purchaseTransaction) {
            // Logika untuk memilih model PurchaseReturn yang benar (Pusat atau Cabang)
            $purchaseReturnModel = ($purchaseTransaction->business_unit_id == 1)
                ? PurchaseReturn::class
                : \App\Modules\KarungCabang\Models\PurchaseReturn::class;

            $totalReturnAmount = 0;

            $purchaseReturn = $purchaseReturnModel::create([
                'return_code' => 'RTP-' . Carbon::now()->format('YmdHis'),
                'purchase_transaction_id' => $purchaseTransaction->id,
                'supplier_id' => $purchaseTransaction->supplier_id,
                'user_id' => auth()->id(),
                'return_date' => $validatedData['return_date'],
                'reason' => $validatedData['reason'],
                'total_amount' => 0, // Placeholder
            ]);

            foreach ($validatedData['items'] as $item) {
                $originalDetail = $purchaseTransaction->details()->find($item['purchase_transaction_detail_id']);

                if (!$originalDetail || $item['return_quantity'] > $originalDetail->quantity) {
                    throw new \Exception("Jumlah retur melebihi jumlah pembelian.");
                }

                $subtotal = $originalDetail->purchase_price_at_transaction * $item['return_quantity'];
                $totalReturnAmount += $subtotal;

                $purchaseReturn->details()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['return_quantity'],
                    'price' => $originalDetail->purchase_price_at_transaction,
                    'subtotal' => $subtotal,
                ]);
            }

            $purchaseReturn->total_amount = $totalReturnAmount;
            $purchaseReturn->save();

            $this->stockManagementService->handlePurchaseReturn($purchaseReturn);

            $purchaseTransaction->total_amount -= $totalReturnAmount;
            if ($purchaseTransaction->amount_paid >= $purchaseTransaction->total_amount) {
                $purchaseTransaction->payment_status = 'Lunas';
            }
            $purchaseTransaction->save();

            return $purchaseReturn;
        });
    }
}