<?php

namespace App\Modules\Karung\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesReturn extends Model
{
    use HasFactory;

    protected $table = 'karung_sales_returns';

    protected $fillable = [
        'return_code',
        'sales_transaction_id',
        'customer_id',
        'user_id',
        'return_date',
        'total_amount',
        'reason',
    ];

    protected $casts = ['return_date' => 'date'];

    public function originalTransaction() {
        return $this->belongsTo(SalesTransaction::class, 'sales_transaction_id');
    }

    public function customer() {
        return $this->belongsTo(Customer::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function details() {
        return $this->hasMany(SalesReturnDetail::class, 'sales_return_id');
    }
}