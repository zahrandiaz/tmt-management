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
        Schema::create('exported_reports', function (Blueprint $table) {
            $table->id();
            // Relasi ke pengguna yang membuat laporan
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
            // Nama file laporan, misal: Laporan_Penjualan_...
            $table->string('filename'); 
            // Path lengkap ke file di dalam storage
            $table->string('path'); 
            // Disk storage yang digunakan, misal: 'public'
            $table->string('disk');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exported_reports');
    }
};
