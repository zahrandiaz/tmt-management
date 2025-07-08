<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Model; // <-- TAMBAHKAN INI

class StockManagementService
{
    protected $isStockManagementActive;

    public function __construct()
    {
        // TODO: Nanti, logika ini perlu diperbarui agar bisa menangani pengaturan
        // dari business_unit_id yang berbeda secara dinamis.
        // Untuk sekarang, kita asumsikan pengaturan 'true' berlaku untuk semua.
        $this->isStockManagementActive = true; 
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

    // Ganti type-hint menjadi Model
    public function handlePurchaseCancellation(Model $purchase): void
    {
        if (!$this->isStockManagementActive()) return;
        foreach ($purchase->details as $detail) {
            $product = Product::find($detail->product_id);
            if ($product) $product->decrement('stock', $detail->quantity);
        }
    }

    // Ganti type-hint menjadi Model
    public function handlePurchaseUpdate(Model $purchase, array $newDetails): void
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

    // Ganti type-hint menjadi Model
    public function handleSaleCancellation(Model $sale): void
    {
        if (!$this->isStockManagementActive()) return;
        foreach ($sale->details as $detail) {
            $product = Product::find($detail->product_id);
            if ($product) $product->increment('stock', $detail->quantity);
        }
    }

    // Ganti type-hint menjadi Model
    public function handleSaleUpdate(Model $sale, array $newDetails): void
    {
        if (!$this->isStockManagementActive()) return;
        $this->handleSaleCancellation($sale);
        $this->handleSaleCreation($newDetails);
    }

    // Ganti type-hint menjadi Model
    public function handleSaleReturn(Model $salesReturn): void
    {
        if (!$this->isStockManagementActive()) return;
        foreach ($salesReturn->details as $detail) {
            $product = Product::find($detail->product_id);
            if ($product) {
                $product->increment('stock', $detail->quantity);
            }
        }
    }

    // Ganti type-hint menjadi Model
    public function handlePurchaseReturn(Model $purchaseReturn): void
    {
        if (!$this->isStockManagementActive()) return;
        foreach ($purchaseReturn->details as $detail) {
            $product = Product::find($detail->product_id);
            if ($product) {
                $product->decrement('stock', $detail->quantity);
            }
        }
    }
}