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
        Schema::table('karung_stock_adjustments', function (Blueprint $table) {
            $table->foreign(['product_id'])->references(['id'])->on('karung_products')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('restrict')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('karung_stock_adjustments', function (Blueprint $table) {
            $table->dropForeign('karung_stock_adjustments_product_id_foreign');
            $table->dropForeign('karung_stock_adjustments_user_id_foreign');
        });
    }
};
