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
            $table->bigIncrements('id');
            $table->unsignedBigInteger('business_unit_id')->index();
            $table->string('name', 255);
            $table->timestamps();

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
