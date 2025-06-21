<?php

namespace App\Modules\Karung\Models;

use App\Models\User; // Menggunakan model User global
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseTransaction extends Model
{
    use HasFactory;

    protected $table = 'karung_purchase_transactions';

    protected $fillable = [
        'business_unit_id',
        'purchase_code',
        'supplier_id',
        'transaction_date',
        'purchase_reference_no',
        'total_amount',
        'notes',
        'attachment_path',
        'user_id',
        'status',
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
     * Relasi ke detail pembelian (satu transaksi memiliki banyak detail).
     */
    public function details()
    {
        return $this->hasMany(PurchaseTransactionDetail::class, 'purchase_transaction_id');
    }

    /**
     * Relasi ke supplier (satu transaksi milik satu supplier).
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * Relasi ke user (satu transaksi dicatat oleh satu user).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function operationalExpenses()
    {
        return $this->hasMany(OperationalExpense::class, 'purchase_transaction_id');
    }
}