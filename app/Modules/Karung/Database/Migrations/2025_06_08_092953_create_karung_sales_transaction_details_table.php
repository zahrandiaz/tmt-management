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
        Schema::create('karung_sales_transaction_details', function (Blueprint $table) {
            $table->id();

            // Terhubung ke transaksi penjualan mana?
            // Kita akan beri nama kustom pada constraint agar tidak terlalu panjang
            $table->foreignId('sales_transaction_id')->constrained('karung_sales_transactions', 'id', 'sales_details_transaction_fk')->onDelete('cascade');

            // Produk apa yang dijual?
            $table->foreignId('product_id')->constrained('karung_products')->onDelete('restrict');

            $table->integer('quantity');
            $table->decimal('selling_price_at_transaction', 15, 2); // Harga jual per item saat transaksi
            $table->decimal('sub_total', 15, 2); // Hasil dari quantity * selling_price

            // Tidak perlu timestamps di tabel detail ini
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karung_sales_transaction_details');
    }
};