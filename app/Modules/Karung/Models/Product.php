<?php

namespace App\Modules\Karung\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// [BARU] Tambahkan use statement ini
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Product extends Model
{
    use HasFactory;

    // [BARU] Tambahkan trait ini
    use LogsActivity;

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

    // ... (Relasi yang sudah ada biarkan saja) ...
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function type()
    {
        return $this->belongsTo(ProductType::class, 'product_type_id');
    }

    public function defaultSupplier()
    {
        return $this->belongsTo(Supplier::class, 'default_supplier_id');
    }

    // [BARU] Tambahkan method ini untuk kustomisasi log produk
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'purchase_price', 'selling_price', 'stock', 'is_active']) // Kolom penting untuk dilacak
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Produk ini telah di-{$eventName}");
    }
}