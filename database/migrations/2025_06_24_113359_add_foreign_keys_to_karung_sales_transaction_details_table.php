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
            $table->foreign(['product_id'])->references(['id'])->on('karung_products')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['sales_transaction_id'], 'sales_details_transaction_fk')->references(['id'])->on('karung_sales_transactions')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('karung_sales_transaction_details', function (Blueprint $table) {
            $table->dropForeign('karung_sales_transaction_details_product_id_foreign');
            $table->dropForeign('sales_details_transaction_fk');
        });
    }
};
