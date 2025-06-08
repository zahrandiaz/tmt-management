<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('karung_product_categories', function (Blueprint $table) {
            $table->id(); // Kolom 'id' (Primary Key, Auto Increment, BIGINT UNSIGNED)

            // Kolom untuk 'Instansi Bisnis' / Multi-Tenancy
            // Asumsi kita akan punya tabel 'business_units' di TMT Core nanti,
            // atau ini bisa berupa ID generik jika belum ada tabelnya.
            // Untuk sekarang, kita buat sebagai unsignedBigInteger dan bisa diindeks.
            // Jika belum ada tabel business_units, kita bisa hapus foreign key constraint dulu.
            // $table->foreignId('business_unit_id')->constrained('tmt_business_units')->onDelete('cascade');
            $table->unsignedBigInteger('business_unit_id')->index(); // WAJIB ADA untuk pemisahan data antar toko

            $table->string('name'); // Nama Kategori Produk
            $table->timestamps(); // Kolom 'created_at' dan 'updated_at'

            // Menambahkan constraint unique untuk kombinasi business_unit_id dan name
            // agar nama kategori unik per instansi bisnis/toko.
            $table->unique(['business_unit_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karung_product_categories');
    }
};
