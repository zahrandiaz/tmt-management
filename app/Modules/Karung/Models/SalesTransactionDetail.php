<?php

namespace App\Modules\Karung\Models;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesTransactionDetail extends Model
{
    use HasFactory;

    /**
     * Menonaktifkan fitur timestamps otomatis dari Eloquent untuk model ini.
     */
    public $timestamps = false;

    protected $table = 'karung_sales_transaction_details';

    protected $fillable = [
        'sales_transaction_id',
        'product_id',
        'quantity',
        'selling_price_at_transaction',
        'sub_total',
        'purchase_price_at_sale',
    ];

    /**
     * Relasi ke transaksi penjualan induk.
     */
    public function transaction()
    {
        return $this->belongsTo(SalesTransaction::class, 'sales_transaction_id');
    }

    /**
     * Relasi ke produk.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}