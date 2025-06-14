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
        // Modifikasi tabel transaksi pembelian
        Schema::table('karung_purchase_transactions', function (Blueprint $table) {
            // Ubah kolom 'status' untuk menambahkan 'Deleted'
            $table->enum('status', ['Completed', 'Cancelled', 'Deleted'])->default('Completed')->change();
        });

        // Modifikasi tabel transaksi penjualan
        Schema::table('karung_sales_transactions', function (Blueprint $table) {
            // Ubah kolom 'status' untuk menambahkan 'Deleted'
            $table->enum('status', ['Completed', 'Cancelled', 'Deleted'])->default('Completed')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Logika untuk mengembalikan jika migrasi di-rollback
        Schema::table('karung_purchase_transactions', function (Blueprint $table) {
            // Kembalikan kolom 'status' ke kondisi semula
            $table->enum('status', ['Completed', 'Cancelled'])->default('Completed')->change();
        });
        
        Schema::table('karung_sales_transactions', function (Blueprint $table) {
            // Kembalikan kolom 'status' ke kondisi semula
            $table->enum('status', ['Completed', 'Cancelled'])->default('Completed')->change();
        });
    }
};
