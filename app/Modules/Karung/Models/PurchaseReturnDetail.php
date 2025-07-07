<?php

namespace App\Modules\Karung\Models;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturnDetail extends Model
{
    use HasFactory;
    
    protected $table = 'karung_purchase_return_details';

    public $timestamps = false;

    protected $fillable = [
        'purchase_return_id',
        'product_id',
        'quantity',
        'price',
        'subtotal',
    ];

    public function purchaseReturn() {
        return $this->belongsTo(PurchaseReturn::class, 'purchase_return_id');
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }
}