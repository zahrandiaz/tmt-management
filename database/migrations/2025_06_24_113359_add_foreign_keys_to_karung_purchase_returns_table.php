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
        Schema::table('karung_purchase_returns', function (Blueprint $table) {
            $table->foreign(['purchase_transaction_id'])->references(['id'])->on('karung_purchase_transactions')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['supplier_id'])->references(['id'])->on('karung_suppliers')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('karung_purchase_returns', function (Blueprint $table) {
            $table->dropForeign('karung_purchase_returns_purchase_transaction_id_foreign');
            $table->dropForeign('karung_purchase_returns_supplier_id_foreign');
            $table->dropForeign('karung_purchase_returns_user_id_foreign');
        });
    }
};
