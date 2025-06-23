<?php

namespace App\Modules\Karung\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesReturnDetail extends Model
{
    use HasFactory;

    protected $table = 'karung_sales_return_details';

    public $timestamps = false; // Detail tidak perlu timestamp terpisah

    protected $fillable = [
        'sales_return_id',
        'product_id',
        'quantity',
        'price',
        'subtotal',
    ];

    public function salesReturn() {
        return $this->belongsTo(SalesReturn::class, 'sales_return_id');
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }
}