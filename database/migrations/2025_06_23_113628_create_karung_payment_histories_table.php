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
        Schema::create('karung_payment_histories', function (Blueprint $table) {
            $table->id();

            // Kolom ini akan menampung ID dari transaksi induk (bisa penjualan atau pembelian)
            $table->unsignedBigInteger('transaction_id');
            $table->string('transaction_type'); // 'sales' atau 'purchase'

            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->string('payment_method')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('user_id')->comment('User yang mencatat pembayaran')->constrained('users');
            
            $table->timestamps();

            // Index untuk mempercepat query
            $table->index(['transaction_id', 'transaction_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karung_payment_histories');
    }
};
