<?php

namespace App\Modules\Karung\Models;

use App\Models\User;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturn extends Model
{
    use HasFactory;

    protected $table = 'karung_purchase_returns';

    protected $fillable = [
        'return_code',
        'purchase_transaction_id',
        'supplier_id',
        'user_id',
        'return_date',
        'total_amount',
        'reason',
    ];

    protected $casts = ['return_date' => 'date'];

    public function originalTransaction() {
        return $this->belongsTo(PurchaseTransaction::class, 'purchase_transaction_id');
    }

    public function supplier() {
        return $this->belongsTo(Supplier::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function details() {
        return $this->hasMany(PurchaseReturnDetail::class, 'purchase_return_id');
    }
}