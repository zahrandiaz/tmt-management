<?php

namespace App\Modules\Karung\Models; // PASTIKAN NAMESPACE INI BENAR

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'karung_customers';

    protected $fillable = [
        'business_unit_id',
        'customer_code',
        'name',
        'phone_number',
        'email',
        'address',
    ];

    // Relasi ke Penjualan (jika diperlukan nanti)
    // public function salesTransactions()
    // {
    //     return $this->hasMany(SalesTransaction::class, 'customer_id');
    // }
}