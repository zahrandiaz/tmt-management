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
        // Menambahkan kolom ke tabel penjualan
        Schema::table('karung_sales_transactions', function (Blueprint $table) {
            $table->string('payment_status')->default('Lunas')->after('status');
            $table->decimal('amount_paid', 15, 2)->nullable()->after('payment_status');
            $table->string('payment_method')->nullable()->after('amount_paid');
        });

        // Menambahkan kolom ke tabel pembelian
        Schema::table('karung_purchase_transactions', function (Blueprint $table) {
            $table->string('payment_status')->default('Lunas')->after('status');
            $table->decimal('amount_paid', 15, 2)->nullable()->after('payment_status');
            $table->string('payment_method')->nullable()->after('amount_paid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Menghapus kolom dari tabel penjualan
        Schema::table('karung_sales_transactions', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'amount_paid', 'payment_method']);
        });

        // Menghapus kolom dari tabel pembelian
        Schema::table('karung_purchase_transactions', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'amount_paid', 'payment_method']);
        });
    }
};