<?php

namespace App\Modules\Karung\Services;

use App\Services\StockManagementService;
use App\Modules\Karung\Models\PurchaseReturn;
use App\Modules\Karung\Models\PurchaseTransaction;
use App\Modules\Karung\Models\SalesReturn;
use App\Modules\Karung\Models\SalesTransaction;
use Carbon\Carbon;
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
     *
     * @param array $validatedData
     * @param SalesTransaction $salesTransaction
     * @return SalesReturn
     * @throws \Exception
     */
    public function createSalesReturn(array $validatedData, SalesTransaction $salesTransaction): SalesReturn
    {
        return DB::transaction(function () use ($validatedData, $salesTransaction) {
            $totalReturnAmount = 0;

            $salesReturn = SalesReturn::create([
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
            
            // Panggil service lain untuk handle stok
            $this->stockManagementService->handleSaleReturn($salesReturn);

            // Logika penyesuaian finansial
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
     *
     * @param array $validatedData
     * @param PurchaseTransaction $purchaseTransaction
     * @return PurchaseReturn
     * @throws \Exception
     */
    public function createPurchaseReturn(array $validatedData, PurchaseTransaction $purchaseTransaction): PurchaseReturn
    {
        return DB::transaction(function () use ($validatedData, $purchaseTransaction) {
            $totalReturnAmount = 0;

            $purchaseReturn = PurchaseReturn::create([
                'return_code' => 'RTP-' . Carbon::now()->format('YmdHis'),
                'purchase_transaction_id' => $purchaseTransaction->id,
                'supplier_id' => $purchaseTransaction->supplier_id,
                'user_id' => auth()->id(),
                'return_date' => $validatedData['return_date'],
                'reason' => $validatedData['reason'],
                'total_amount' => 0, // Placeholder
            ]);

            foreach ($validatedData['items'] as $item) {
                $originalDetail = \App\Modules\Karung\Models\PurchaseTransactionDetail::find($item['purchase_transaction_detail_id']);

                if (!$originalDetail || $originalDetail->purchase_transaction_id !== $purchaseTransaction->id) {
                    throw new \Exception("Terjadi inkonsistensi data item retur.");
                }
                if ($item['return_quantity'] > $originalDetail->quantity) {
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
            
            // Panggil service lain untuk handle stok
            $this->stockManagementService->handlePurchaseReturn($purchaseReturn);
            
            // Logika penyesuaian finansial
            $purchaseTransaction->total_amount -= $totalReturnAmount;
            if ($purchaseTransaction->amount_paid >= $purchaseTransaction->total_amount) {
                $purchaseTransaction->payment_status = 'Lunas';
            }
            $purchaseTransaction->save();
            
            return $purchaseReturn;
        });
    }
}