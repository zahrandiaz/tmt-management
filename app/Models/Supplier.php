<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
// [BARU] Tambahkan use statement untuk model PurchaseTransaction
use App\Modules\Karung\Models\PurchaseTransaction;

class Supplier extends Model
{
    use HasFactory;
    // [BARU] Tambahkan trait ini
    use LogsActivity;

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

    public function purchaseTransactions()
    {
        return $this->hasMany(PurchaseTransaction::class, 'supplier_id');
    }

    // Relasi ke Pembelian (jika diperlukan nanti)
    // public function purchases()
    // {
    //     return $this->hasMany(PurchaseTransaction::class, 'supplier_id');
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