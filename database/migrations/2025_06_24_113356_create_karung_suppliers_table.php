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
            $table->bigIncrements('id');
            $table->unsignedBigInteger('business_unit_id')->index();
            $table->string('supplier_code', 255)->nullable()->comment('Kode supplier, bisa otomatis atau manual');
            $table->string('name', 255);
            $table->string('contact_person', 255)->nullable();
            $table->string('phone_number', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->text('address')->nullable();
            $table->timestamps();

            $table->unique(['business_unit_id', 'email']);
            $table->unique(['business_unit_id', 'name']);
            $table->unique(['business_unit_id', 'supplier_code']);
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
