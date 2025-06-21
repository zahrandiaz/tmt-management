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
        // [PERBAIKAN] Menggunakan nama tabel yang benar
        Schema::table('karung_operational_expenses', function (Blueprint $table) {
            $table->foreignId('sales_transaction_id')
                  ->nullable()
                  ->after('amount')
                  ->constrained('karung_sales_transactions')
                  ->onDelete('set null');

            $table->foreignId('purchase_transaction_id')
                  ->nullable()
                  ->after('sales_transaction_id')
                  ->constrained('karung_purchase_transactions')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // [PERBAIKAN] Menggunakan nama tabel yang benar
        Schema::table('karung_operational_expenses', function (Blueprint $table) {
            $table->dropForeign(['sales_transaction_id']);
            $table->dropForeign(['purchase_transaction_id']);
            
            $table->dropColumn(['sales_transaction_id', 'purchase_transaction_id']);
        });
    }
};