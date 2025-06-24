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
            $table->bigIncrements('id');
            $table->char('uuid', 36)->nullable()->unique();
            $table->string('verification_code', 10)->nullable()->unique();
            $table->unsignedBigInteger('business_unit_id')->index();
            $table->string('invoice_number', 255)->unique()->comment('Nomor invoice, akan digenerate otomatis');
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->timestamp('transaction_date')->useCurrentOnUpdate()->useCurrent()->index()->comment('Tanggal & waktu transaksi');
            $table->decimal('total_amount', 15)->default(0);
            $table->enum('status', ['Completed', 'Cancelled', 'Deleted'])->default('Completed')->index();
            $table->string('payment_status', 255)->default('Lunas');
            $table->decimal('amount_paid', 15)->nullable();
            $table->string('payment_method', 255)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('user_id')->index();
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
