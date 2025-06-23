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
        Schema::create('karung_sales_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_code')->unique();
            $table->foreignId('sales_transaction_id')->constrained('karung_sales_transactions');
            $table->foreignId('customer_id')->constrained('karung_customers');
            $table->foreignId('user_id')->comment('User yang mencatat retur')->constrained('users');
            $table->date('return_date');
            $table->decimal('total_amount', 15, 2)->comment('Total nilai barang yang diretur');
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karung_sales_returns');
    }
};
