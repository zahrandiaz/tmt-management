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
        Schema::table('karung_sales_transaction_details', function (Blueprint $table) {
            // Menambahkan kolom baru untuk menyimpan harga modal (HPP) saat transaksi terjadi
            // Diletakkan setelah kolom sub_total, dengan tipe data dan default yang sesuai
            $table->decimal('purchase_price_at_sale', 15, 2)->unsigned()->default(0)->after('sub_total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('karung_sales_transaction_details', function (Blueprint $table) {
            // Perintah untuk menghapus kolom jika migrasi di-rollback
            $table->dropColumn('purchase_price_at_sale');
        });
    }
};