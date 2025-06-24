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
        Schema::table('karung_sales_returns', function (Blueprint $table) {
            $table->foreign(['customer_id'])->references(['id'])->on('karung_customers')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['sales_transaction_id'])->references(['id'])->on('karung_sales_transactions')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('karung_sales_returns', function (Blueprint $table) {
            $table->dropForeign('karung_sales_returns_customer_id_foreign');
            $table->dropForeign('karung_sales_returns_sales_transaction_id_foreign');
            $table->dropForeign('karung_sales_returns_user_id_foreign');
        });
    }
};
