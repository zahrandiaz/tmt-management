<?php

namespace App\Modules\KarungCabang\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class OperationalExpense extends Model
{
    use HasFactory;

    protected $table = 'karung_operational_expenses';

    protected $fillable = [
        'business_unit_id',
        'date',
        'description',
        'amount',
        'category',
        'notes',
        'user_id',
        // [BARU] Tambahkan foreign key ke daftar fillable
        'sales_transaction_id',
        'purchase_transaction_id',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * [BARU] Relasi ke transaksi penjualan (opsional).
     */
    public function salesTransaction()
    {
        return $this->belongsTo(SalesTransaction::class, 'sales_transaction_id');
    }

    /**
     * [BARU] Relasi ke transaksi pembelian (opsional).
     */
    public function purchaseTransaction()
    {
        return $this->belongsTo(PurchaseTransaction::class, 'purchase_transaction_id');
    }
}