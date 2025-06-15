<?php

namespace App\Modules\Karung\Services;

use App\Modules\Karung\Models\Product;
use App\Modules\Karung\Models\Setting;
use App\Modules\Karung\Models\PurchaseTransaction;
use App\Modules\Karung\Models\SalesTransaction;

class StockManagementService
{
    protected $isStockManagementActive;

    /**
     * Saat service diinisialisasi, langsung cek status manajemen stok dari database.
     */
    public function __construct()
    {
        // TODO: Nanti kita akan buat business_unit_id menjadi dinamis.
        $this->isStockManagementActive = Setting::where('business_unit_id', 1)
                                                ->where('setting_key', 'automatic_stock_management')
                                                ->value('setting_value') == 'true';
    }

    /**
     * Memeriksa apakah manajemen stok otomatis aktif.
     */
    public function isStockManagementActive(): bool
    {
        return $this->isStockManagementActive;
    }

    /**
     * Menangani stok untuk pembuatan transaksi pembelian baru.
     * Stok produk akan ditambah.
     */
    public function handlePurchaseCreation(array $details): void
    {
        if (!$this->isStockManagementActive()) {
            return;
        }

        foreach ($details as $detail) {
            $product = Product::find($detail['product_id']);
            if ($product) {
                $product->increment('stock', $detail['quantity']);
            }
        }
    }

    /**
     * Menangani stok untuk pembatalan atau penghapusan transaksi pembelian.
     * Stok produk akan dikurangi.
     */
    public function handlePurchaseCancellation(PurchaseTransaction $purchase): void
    {
        if (!$this->isStockManagementActive()) {
            return;
        }

        foreach ($purchase->details as $detail) {
            $product = Product::find($detail->product_id);
            if ($product) {
                $product->decrement('stock', $detail->quantity);
            }
        }
    }

    /**
     * Menangani stok untuk pembaruan transaksi pembelian.
     */
    public function handlePurchaseUpdate(PurchaseTransaction $purchase, array $newDetails): void
    {
        if (!$this->isStockManagementActive()) {
            return;
        }
        
        // 1. Kembalikan stok lama
        $this->handlePurchaseCancellation($purchase);

        // 2. Tambahkan stok baru
        $this->handlePurchaseCreation($newDetails);
    }
    
    /**
     * Menangani stok untuk pembuatan transaksi penjualan baru.
     * Stok produk akan dikurangi.
     */
    public function handleSaleCreation(array $details): void
    {
        if (!$this->isStockManagementActive()) {
            return;
        }

        foreach ($details as $detail) {
            $product = Product::find($detail['product_id']);
            if ($product) {
                $product->decrement('stock', $detail['quantity']);
            }
        }
    }

    /**
     * Menangani stok untuk pembatalan atau penghapusan transaksi penjualan.
     * Stok produk akan ditambah (dikembalikan).
     */
    public function handleSaleCancellation(SalesTransaction $sale): void
    {
        if (!$this->isStockManagementActive()) {
            return;
        }

        foreach ($sale->details as $detail) {
            $product = Product::find($detail->product_id);
            if ($product) {
                $product->increment('stock', $detail->quantity);
            }
        }
    }

    /**
     * Menangani stok untuk pembaruan transaksi penjualan.
     */
    public function handleSaleUpdate(SalesTransaction $sale, array $newDetails): void
    {
        if (!$this->isStockManagementActive()) {
            return;
        }
        
        // 1. Kembalikan stok lama
        $this->handleSaleCancellation($sale);

        // 2. Kurangi stok baru
        $this->handleSaleCreation($newDetails);
    }
}