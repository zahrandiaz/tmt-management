<?php

namespace App\Modules\KarungCabang\Models;

use App\Models\User; // Menggunakan model User global
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; // <-- [TAMBAHKAN]

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
        'uuid',
        'verification_code',
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
     * [BARU v1.27] Boot the model to attach creating event.
     */
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            // Generate UUID if empty
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid()->toString();
            }

            // [BARU v1.28] Generate human-readable verification code if empty
            if (empty($model->verification_code)) {
                do {
                    // Membuat kode acak 8 karakter, contoh: "A5B1C8D2"
                    $code = strtoupper(Str::random(8)); 
                } while (static::where('verification_code', $code)->exists()); // Pastikan kode unik

                $model->verification_code = $code;
            }
        });
    }

    public function details()
    {
        return $this->hasMany(SalesTransactionDetail::class, 'sales_transaction_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke model yang baru kita buat di KarungCabang
    public function operationalExpenses()
    {
        return $this->hasMany(OperationalExpense::class, 'sales_transaction_id');
    }

    public function returns()
    {
        return $this->hasMany(SalesReturn::class, 'sales_transaction_id');
    }
}