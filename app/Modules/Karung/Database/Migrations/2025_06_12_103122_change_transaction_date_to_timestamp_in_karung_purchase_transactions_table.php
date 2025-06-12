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
            // Mengubah tipe kolom dari DATE menjadi TIMESTAMP
            $table->timestamp('transaction_date')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('karung_purchase_transactions', function (Blueprint $table) {
            // Mengembalikan ke tipe DATE jika migrasi dibatalkan
            $table->date('transaction_date')->change();
        });
    }
};
