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
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sales_transaction_id')->index('kstd_sales_transaction_id_idx');
            $table->unsignedBigInteger('product_id')->index('kstd_product_id_idx');
            $table->integer('quantity');
            $table->decimal('selling_price_at_transaction', 15);
            $table->decimal('sub_total', 15);
            $table->decimal('purchase_price_at_sale', 15)->unsigned()->default(0);
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
