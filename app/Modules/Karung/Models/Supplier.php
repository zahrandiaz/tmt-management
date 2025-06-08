<?php

namespace App\Modules\Karung\Models; // PASTIKAN NAMESPACE INI BENAR

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'karung_suppliers';

    protected $fillable = [
        'business_unit_id',
        'supplier_code',
        'name',
        'contact_person',
        'phone_number',
        'email',
        'address',
    ];

    // Relasi ke Pembelian (jika diperlukan nanti)
    // public function purchases()
    // {
    //     return $this->hasMany(PurchaseTransaction::class, 'supplier_id');
    // }
}