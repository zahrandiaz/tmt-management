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
        Schema::create('karung_purchase_transaction_details', function (Blueprint $table) {
            $table->id();

            // Terhubung ke transaksi pembelian mana?
            $table->unsignedBigInteger('purchase_transaction_id');
            $table->foreign('purchase_transaction_id', 'purchase_details_transaction_fk')
                ->references('id')
                ->on('karung_purchase_transactions')
                ->onDelete('cascade');
            // onDelete('cascade') artinya jika transaksi induk dihapus, detailnya ikut terhapus.

            // Produk apa yang dibeli?
            $table->foreignId('product_id')->constrained('karung_products')->onDelete('restrict');
            // onDelete('restrict') artinya produk tidak bisa dihapus jika sudah pernah tercatat di transaksi pembelian.

            $table->integer('quantity');
            $table->decimal('purchase_price_at_transaction', 15, 2); // Harga beli per item saat transaksi
            $table->decimal('sub_total', 15, 2); // Hasil dari quantity * purchase_price

            // Tidak perlu timestamps di tabel detail ini untuk menyederhanakan
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karung_purchase_transaction_details');
    }
};