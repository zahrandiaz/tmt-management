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
        Schema::table('karung_operational_expenses', function (Blueprint $table) {
            $table->foreign(['purchase_transaction_id'])->references(['id'])->on('karung_purchase_transactions')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['sales_transaction_id'])->references(['id'])->on('karung_sales_transactions')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('restrict')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('karung_operational_expenses', function (Blueprint $table) {
            $table->dropForeign('karung_operational_expenses_purchase_transaction_id_foreign');
            $table->dropForeign('karung_operational_expenses_sales_transaction_id_foreign');
            $table->dropForeign('karung_operational_expenses_user_id_foreign');
        });
    }
};
