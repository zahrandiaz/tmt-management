<?php

namespace App\Modules\Karung\Models; // PASTIKAN NAMESPACE INI BENAR

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'karung_products';

    protected $fillable = [
        'business_unit_id',
        'sku',
        'name',
        'product_category_id',
        'product_type_id',
        'description',
        'purchase_price',
        'selling_price',
        'stock',
        'min_stock_level',
        'default_supplier_id',
        'image_path',
        'is_active',
    ];

    // Definisikan relasi ke Kategori Produk
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    // Definisikan relasi ke Jenis Produk
    public function type()
    {
        return $this->belongsTo(ProductType::class, 'product_type_id');
    }

    // Definisikan relasi ke Supplier (untuk supplier langganan)
    public function defaultSupplier()
    {
        return $this->belongsTo(Supplier::class, 'default_supplier_id');
    }

    // Nanti bisa ada relasi lain seperti ke detail penjualan atau pembelian
}