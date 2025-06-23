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
        Schema::create('karung_sales_return_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_return_id')->constrained('karung_sales_returns')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('karung_products');
            $table->integer('quantity');
            $table->decimal('price', 15, 2)->comment('Harga produk saat diretur');
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karung_sales_return_details');
    }
};
