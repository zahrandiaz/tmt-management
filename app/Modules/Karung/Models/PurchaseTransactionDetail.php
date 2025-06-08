<?php

namespace App\Modules\Karung\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseTransactionDetail extends Model
{
    use HasFactory;

    /**
     * Karena tabel detail ini tidak memiliki kolom created_at dan updated_at,
     * kita perlu menonaktifkan fitur timestamps otomatis dari Eloquent untuk model ini.
     */
    public $timestamps = false;

    protected $table = 'karung_purchase_transaction_details';

    protected $fillable = [
        'purchase_transaction_id',
        'product_id',
        'quantity',
        'purchase_price_at_transaction',
        'sub_total',
    ];

    /**
     * Relasi ke transaksi pembelian induk (satu detail milik satu transaksi).
     */
    public function transaction()
    {
        return $this->belongsTo(PurchaseTransaction::class, 'purchase_transaction_id');
    }

    /**
     * Relasi ke produk (satu detail untuk satu produk).
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}