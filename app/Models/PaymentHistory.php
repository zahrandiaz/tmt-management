<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PaymentHistory extends Model
{
    use HasFactory;

    protected $table = 'karung_payment_histories';

    protected $fillable = [
        'transaction_id',
        'transaction_type',
        'payment_date',
        'amount',
        'payment_method',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the parent transaction model (sales or purchase).
     */
    public function transaction(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'transaction_type', 'transaction_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}