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
        Schema::table('karung_sales_returns', function (Blueprint $table) {
            // Menambahkan kolom business_unit_id setelah kolom 'id'
            // Dibuat nullable untuk sementara agar tidak error pada data lama.
            $table->unsignedBigInteger('business_unit_id')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('karung_sales_returns', function (Blueprint $table) {
            // Logika untuk menghapus kolom jika migrasi di-rollback
            $table->dropColumn('business_unit_id');
        });
    }
};