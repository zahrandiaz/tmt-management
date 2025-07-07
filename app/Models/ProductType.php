<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ProductType extends Model
{
    use HasFactory;
    // [BARU] Tambahkan trait ini
    use LogsActivity;

    protected $table = 'karung_product_types';

    protected $fillable = [
        'business_unit_id',
        'name',
    ];

    // Relasi ke Produk (jika diperlukan nanti)
    // public function products()
    // {
    //     return $this->hasMany(Product::class, 'product_type_id');
    // }
    // [BARU] Tambahkan method ini untuk kustomisasi log produk
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'purchase_price', 'selling_price', 'stock', 'is_active']) // Kolom penting untuk dilacak
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Produk ini telah di-{$eventName}");
    }
}