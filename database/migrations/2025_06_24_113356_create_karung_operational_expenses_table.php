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
        Schema::create('karung_operational_expenses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('business_unit_id')->index();
            $table->date('date');
            $table->string('description', 255);
            $table->decimal('amount', 15);
            $table->unsignedBigInteger('sales_transaction_id')->nullable()->index('karung_operational_expenses_sales_transaction_id_foreign');
            $table->unsignedBigInteger('purchase_transaction_id')->nullable()->index('karung_operational_expenses_purchase_transaction_id_foreign');
            $table->string('category', 255);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index('karung_operational_expenses_user_id_foreign');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karung_operational_expenses');
    }
};
