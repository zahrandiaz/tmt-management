<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // <-- Tambahkan ini

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Menambahkan indeks pada tabel transaksi pembelian
        Schema::table('karung_purchase_transactions', function (Blueprint $table) {
            $table->index('supplier_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('transaction_date');
        });

        // Menambahkan indeks pada tabel detail transaksi pembelian
        Schema::table('karung_purchase_transaction_details', function (Blueprint $table) {
            $table->index('purchase_transaction_id', 'kptd_purchase_transaction_id_idx'); 
            $table->index('product_id', 'kptd_product_id_idx');
        });

        // Menambahkan indeks pada tabel transaksi penjualan
        Schema::table('karung_sales_transactions', function (Blueprint $table) {
            $table->index('customer_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('transaction_date');
        });

        // Menambahkan indeks pada tabel detail transaksi penjualan
        Schema::table('karung_sales_transaction_details', function (Blueprint $table) {
            $table->index('sales_transaction_id', 'kstd_sales_transaction_id_idx');
            $table->index('product_id', 'kstd_product_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // [PERBAIKAN] "Bersihkan" data 'Deleted' sebelum mengubah struktur kolom
        DB::table('karung_purchase_transactions')->where('status', 'Deleted')->update(['status' => 'Cancelled']);
        DB::table('karung_sales_transactions')->where('status', 'Deleted')->update(['status' => 'Cancelled']);

        // Logika untuk menghapus indeks jika migrasi di-rollback
        Schema::table('karung_purchase_transactions', function (Blueprint $table) {
            $table->dropIndex(['supplier_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['transaction_date']);
            // Kembalikan kolom 'status' ke kondisi semula
            $table->enum('status', ['Completed', 'Cancelled'])->default('Completed')->change();
        });

        Schema::table('karung_purchase_transaction_details', function (Blueprint $table) {
            $table->dropIndex('kptd_purchase_transaction_id_idx');
            $table->dropIndex('kptd_product_id_idx');
        });

        Schema::table('karung_sales_transactions', function (Blueprint $table) {
            $table->dropIndex(['customer_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['transaction_date']);
             // Kembalikan kolom 'status' ke kondisi semula
            $table->enum('status', ['Completed', 'Cancelled'])->default('Completed')->change();
        });

        Schema::table('karung_sales_transaction_details', function (Blueprint $table) {
            $table->dropIndex('kstd_sales_transaction_id_idx');
            $table->dropIndex('kstd_product_id_idx');
        });
    }
};