<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ProductCategory extends Model
{
    use HasFactory;
    // [BARU] Tambahkan trait ini
    use LogsActivity;

    /**
     * Nama tabel yang terhubung dengan model ini.
     *
     * @var string
     */
    protected $table = 'karung_product_categories';

    /**
     * Atribut yang bisa diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'business_unit_id', // Penting untuk multi-instansi
        'name',
    ];

    // Nanti di sini kita bisa definisikan relasi, misalnya:
    // public function products()
    // {
    //     return $this->hasMany(Product::class, 'product_category_id');
    // }

    // public function businessUnit()
    // {
    //    // Jika ada model BusinessUnit di TMT Core
    //    // return $this->belongsTo(App\Models\BusinessUnit::class, 'business_unit_id');
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