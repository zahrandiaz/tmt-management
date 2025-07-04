<?php

namespace App\Services;

use App\Modules\Karung\Models\Product;
use App\Models\Setting;
use App\Modules\Karung\Models\PurchaseTransaction;
use App\Modules\Karung\Models\SalesTransaction;
use App\Modules\Karung\Models\SalesReturn;
use App\Modules\Karung\Models\PurchaseReturn;

class StockManagementService
{
    protected $isStockManagementActive;

    public function __construct()
    {
        $this->isStockManagementActive = Setting::where('business_unit_id', 1)
            ->where('setting_key', 'automatic_stock_management')
            ->value('setting_value') == 'true';
    }

    public function isStockManagementActive(): bool
    {
        return $this->isStockManagementActive;
    }

    public function handlePurchaseCreation(array $details): void
    {
        if (!$this->isStockManagementActive()) return;
        foreach ($details as $detail) {
            $product = Product::find($detail['product_id']);
            if ($product) $product->increment('stock', $detail['quantity']);
        }
    }

    public function handlePurchaseCancellation(PurchaseTransaction $purchase): void
    {
        if (!$this->isStockManagementActive()) return;
        foreach ($purchase->details as $detail) {
            $product = Product::find($detail->product_id);
            if ($product) $product->decrement('stock', $detail->quantity);
        }
    }

    public function handlePurchaseUpdate(PurchaseTransaction $purchase, array $newDetails): void
    {
        if (!$this->isStockManagementActive()) return;
        $this->handlePurchaseCancellation($purchase);
        $this->handlePurchaseCreation($newDetails);
    }
    
    public function handleSaleCreation(array $details): void
    {
        if (!$this->isStockManagementActive()) return;
        foreach ($details as $detail) {
            $product = Product::find($detail['product_id']);
            if ($product) $product->decrement('stock', $detail['quantity']);
        }
    }

    public function handleSaleCancellation(SalesTransaction $sale): void
    {
        if (!$this->isStockManagementActive()) return;
        foreach ($sale->details as $detail) {
            $product = Product::find($detail->product_id);
            if ($product) $product->increment('stock', $detail->quantity);
        }
    }

    public function handleSaleUpdate(SalesTransaction $sale, array $newDetails): void
    {
        if (!$this->isStockManagementActive()) return;
        $this->handleSaleCancellation($sale);
        $this->handleSaleCreation($newDetails);
    }

    /**
     * [BARU v1.27] Menangani stok untuk retur penjualan.
     * Stok produk akan ditambah (dikembalikan ke inventaris).
     */
    public function handleSaleReturn(SalesReturn $salesReturn): void
    {
        if (!$this->isStockManagementActive()) {
            return;
        }

        foreach ($salesReturn->details as $detail) {
            $product = Product::find($detail->product_id);
            if ($product) {
                $product->increment('stock', $detail->quantity);
            }
        }
    }

    /**
     * [BARU v1.27] Menangani stok untuk retur pembelian.
     * Stok produk akan dikurangi dari inventaris.
     */
    public function handlePurchaseReturn(PurchaseReturn $purchaseReturn): void
    {
        if (!$this->isStockManagementActive()) {
            return;
        }

        foreach ($purchaseReturn->details as $detail) {
            $product = Product::find($detail->product_id);
            if ($product) {
                $product->decrement('stock', $detail->quantity);
            }
        }
    }
}