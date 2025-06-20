<?php

namespace App\Modules\Karung\Models;

use App\Models\User; // Menggunakan model User global
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesTransaction extends Model
{
    use HasFactory;

    protected $table = 'karung_sales_transactions';

    protected $fillable = [
        'business_unit_id',
        'invoice_number',
        'customer_id',
        'transaction_date',
        'total_amount',
        'notes',
        'user_id',
        'payment_method',
        'payment_status',
        'amount_paid',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'transaction_date' => 'datetime',
    ];

    /**
     * Relasi ke detail penjualan (satu transaksi memiliki banyak detail).
     */
    public function details()
    {
        return $this->hasMany(SalesTransactionDetail::class, 'sales_transaction_id');
    }

    /**
     * Relasi ke pelanggan (satu transaksi milik satu pelanggan).
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Relasi ke user (satu transaksi dicatat oleh satu user).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}