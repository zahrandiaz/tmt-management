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
            $table->bigIncrements('id');
            $table->string('return_code')->unique();
            $table->unsignedBigInteger('sales_transaction_id')->index('karung_sales_returns_sales_transaction_id_foreign');
            $table->unsignedBigInteger('customer_id')->index('karung_sales_returns_customer_id_foreign');
            $table->unsignedBigInteger('user_id')->index('karung_sales_returns_user_id_foreign')->comment('User yang mencatat retur');
            $table->date('return_date');
            $table->decimal('total_amount', 15)->comment('Total nilai barang yang diretur');
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
