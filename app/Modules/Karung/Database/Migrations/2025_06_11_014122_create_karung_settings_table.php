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
        Schema::create('karung_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_unit_id')->index(); // Pengaturan per Instansi Bisnis
            $table->string('setting_key'); // Contoh: 'stok_otomatis_aktif'
            $table->string('setting_value'); // Contoh: 'true' atau 'false'
            $table->timestamps();

            // Pastikan setiap kunci pengaturan unik untuk setiap instansi bisnis
            $table->unique(['business_unit_id', 'setting_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karung_settings');
    }
};