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
            $table->bigIncrements('id');
            $table->unsignedBigInteger('purchase_transaction_id')->index('kptd_purchase_transaction_id_idx');
            $table->unsignedBigInteger('product_id')->index('kptd_product_id_idx');
            $table->integer('quantity');
            $table->decimal('purchase_price_at_transaction', 15);
            $table->decimal('sub_total', 15);
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
