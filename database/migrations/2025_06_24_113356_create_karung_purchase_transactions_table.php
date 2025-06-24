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
        Schema::create('karung_purchase_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('business_unit_id')->index();
            $table->string('purchase_code', 255)->nullable()->unique();
            $table->unsignedBigInteger('supplier_id')->nullable()->index();
            $table->timestamp('transaction_date')->useCurrentOnUpdate()->useCurrent()->index();
            $table->string('purchase_reference_no', 255)->nullable()->comment('No. Faktur/Invoice dari supplier');
            $table->decimal('total_amount', 15)->default(0);
            $table->enum('status', ['Completed', 'Cancelled', 'Deleted'])->default('Completed')->index();
            $table->string('payment_status', 255)->default('Lunas');
            $table->decimal('amount_paid', 15)->nullable();
            $table->string('payment_method', 255)->nullable();
            $table->text('notes')->nullable();
            $table->string('attachment_path', 255)->nullable()->comment('Path ke file upload struk/nota pembelian');
            $table->unsignedBigInteger('user_id')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karung_purchase_transactions');
    }
};
