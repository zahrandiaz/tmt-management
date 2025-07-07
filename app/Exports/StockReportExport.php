<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StockReportExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // Ambil semua produk, diurutkan berdasarkan nama
        return Product::with(['category', 'type'])->orderBy('name')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'SKU',
            'Nama Produk',
            'Kategori',
            'Jenis',
            'Stok Saat Ini',
            'Harga Beli',
            'Harga Jual',
            'Nilai Inventaris (Beli)',
            'Potensi Nilai Inventaris (Jual)',
        ];
    }

    /**
     * @var Product $product
     */
    public function map($product): array
    {
        $inventoryValuePurchase = $product->stock * $product->purchase_price;
        $inventoryValueSell = $product->stock * $product->selling_price;

        return [
            $product->sku,
            $product->name,
            $product->category->name ?? 'N/A',
            $product->type->name ?? 'N/A',
            $product->stock,
            $product->purchase_price,
            $product->selling_price,
            $inventoryValuePurchase,
            $inventoryValueSell,
        ];
    }
}