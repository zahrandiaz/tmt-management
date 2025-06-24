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
        Schema::table('karung_purchase_transactions', function (Blueprint $table) {
            $table->foreign(['supplier_id'])->references(['id'])->on('karung_suppliers')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('karung_purchase_transactions', function (Blueprint $table) {
            $table->dropForeign('karung_purchase_transactions_supplier_id_foreign');
            $table->dropForeign('karung_purchase_transactions_user_id_foreign');
        });
    }
};
