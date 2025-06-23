<?php

namespace App\Modules\Karung\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Modules\Karung\Models\SalesTransactionDetail;

class Product extends Model
{
    use HasFactory, LogsActivity;

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

    public function salesDetails()
    {
        return $this->hasMany(SalesTransactionDetail::class, 'product_id');
    }

    public function salesReturnDetails()
    {
        return $this->hasMany(SalesReturnDetail::class, 'product_id');
    }

    public function purchaseReturnDetails()
    {
        return $this->hasMany(PurchaseReturnDetail::class, 'product_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'purchase_price', 'selling_price', 'stock', 'is_active'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Produk ini telah di-{$eventName}");
    }
}