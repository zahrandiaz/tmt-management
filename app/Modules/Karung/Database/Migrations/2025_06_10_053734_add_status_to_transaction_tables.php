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
        // Menambahkan kolom 'status' ke tabel transaksi penjualan
        Schema::table('karung_sales_transactions', function (Blueprint $table) {
            $table->string('status')->default('Completed')->after('total_amount')->comment('Contoh: Completed, Cancelled');
        });

        // Menambahkan kolom 'status' ke tabel transaksi pembelian
        Schema::table('karung_purchase_transactions', function (Blueprint $table) {
            $table->string('status')->default('Completed')->after('total_amount')->comment('Contoh: Completed, Cancelled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Menghapus kolom 'status' dari tabel transaksi penjualan
        Schema::table('karung_sales_transactions', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        // Menghapus kolom 'status' dari tabel transaksi pembelian
        Schema::table('karung_purchase_transactions', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
