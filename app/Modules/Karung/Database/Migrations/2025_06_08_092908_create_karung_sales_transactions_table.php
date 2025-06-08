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
        Schema::create('karung_sales_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_unit_id')->index();

            $table->string('invoice_number')->unique()->comment('Nomor invoice, akan digenerate otomatis');

            // Jual ke pelanggan siapa? Boleh null jika penjualan tanpa data pelanggan.
            $table->foreignId('customer_id')->nullable()->constrained('karung_customers')->onDelete('set null');

            $table->timestamp('transaction_date')->comment('Tanggal & waktu transaksi');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();

            // Siapa yang mencatat transaksi ini
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karung_sales_transactions');
    }
};