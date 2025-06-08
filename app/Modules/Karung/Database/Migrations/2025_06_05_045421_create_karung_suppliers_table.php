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
        Schema::create('karung_suppliers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_unit_id')->index();
            $table->string('supplier_code')->nullable()->comment('Kode supplier, bisa otomatis atau manual');
            $table->string('name'); // Nama Supplier
            $table->string('contact_person')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();

            // Kode supplier unik per instansi bisnis jika diisi
            $table->unique(['business_unit_id', 'supplier_code'])->whereNotNull('supplier_code');
            // Nama supplier juga sebaiknya unik per instansi bisnis
            $table->unique(['business_unit_id', 'name']);
            // Email supplier juga sebaiknya unik per instansi bisnis jika diisi
            $table->unique(['business_unit_id', 'email'])->whereNotNull('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karung_suppliers');
    }
};