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
            $table->id();
            $table->unsignedBigInteger('business_unit_id')->index();

            // Beli ke supplier apa? Boleh null jika pembelian tanpa supplier tercatat.
            $table->foreignId('supplier_id')->nullable()->constrained('karung_suppliers')->onDelete('set null');

            $table->date('transaction_date');
            $table->string('purchase_reference_no')->nullable()->comment('No. Faktur/Invoice dari supplier');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable()->comment('Path ke file upload struk/nota pembelian');

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
        Schema::dropIfExists('karung_purchase_transactions');
    }
};