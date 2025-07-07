<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
// [BARU] Tambahkan use statement untuk model SalesTransaction
use App\Modules\Karung\Models\SalesTransaction;

class Customer extends Model
{
    use HasFactory;
    // [BARU] Tambahkan trait ini
    use LogsActivity;

    protected $table = 'karung_customers';

    protected $fillable = [
        'business_unit_id',
        'customer_code',
        'name',
        'phone_number',
        'email',
        'address',
    ];

    public function salesTransactions()
    {
        return $this->hasMany(SalesTransaction::class, 'customer_id');
    }

    // Relasi ke Penjualan (jika diperlukan nanti)
    // public function salesTransactions()
    // {
    //     return $this->hasMany(SalesTransaction::class, 'customer_id');
    // }
    // [BARU] Tambahkan method ini untuk kustomisasi log produk
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'purchase_price', 'selling_price', 'stock', 'is_active']) // Kolom penting untuk dilacak
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Produk ini telah di-{$eventName}");
    }
}