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
        Schema::table('karung_sales_transactions', function (Blueprint $table) {
            $table->string('verification_code', 10)->unique()->nullable()->after('uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('karung_sales_transactions', function (Blueprint $table) {
            $table->dropColumn('verification_code');
        });
    }
};