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
        Schema::create('karung_stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('karung_products')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Tipe penyesuaian, contoh: 'Stok Opname', 'Barang Rusak', 'Koreksi Data', dll.
            $table->string('type'); 
            
            // Jumlah penyesuaian, bisa positif (jika menambah) atau negatif (jika mengurangi).
            $table->integer('quantity'); 
            
            // Mencatat jumlah stok sebelum dan sesudah untuk riwayat yang jelas.
            $table->integer('stock_before');
            $table->integer('stock_after');

            // Alasan penyesuaian yang diinput oleh pengguna.
            $table->text('reason')->nullable(); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karung_stock_adjustments');
    }
};