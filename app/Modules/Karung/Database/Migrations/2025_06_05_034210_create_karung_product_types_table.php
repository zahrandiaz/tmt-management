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
        Schema::create('karung_product_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_unit_id')->index(); // Untuk multi-instansi
            $table->string('name'); // Nama Jenis Produk (misal: Karung Tenun, Karung Laminasi, Tali)
            $table->timestamps();

            // Nama jenis unik per instansi bisnis
            $table->unique(['business_unit_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karung_product_types');
    }
};