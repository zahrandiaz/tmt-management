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
        Schema::create('karung_purchase_return_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('purchase_return_id')->index('karung_purchase_return_details_purchase_return_id_foreign');
            $table->unsignedBigInteger('product_id')->index('karung_purchase_return_details_product_id_foreign');
            $table->integer('quantity');
            $table->decimal('price', 15)->comment('Harga produk saat diretur');
            $table->decimal('subtotal', 15);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karung_purchase_return_details');
    }
};
