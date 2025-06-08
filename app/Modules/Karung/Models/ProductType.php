<?php

namespace App\Modules\Karung\Models; // PASTIKAN NAMESPACE INI BENAR

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductType extends Model
{
    use HasFactory;

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
}