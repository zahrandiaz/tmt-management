<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('karung_sales_transactions', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->after('id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('karung_sales_transactions', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
