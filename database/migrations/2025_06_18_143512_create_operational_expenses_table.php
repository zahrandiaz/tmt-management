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
        // [MODIFIKASI] Kita ganti nama tabel menjadi lebih konsisten
        Schema::create('karung_operational_expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_unit_id')->index(); // Untuk nanti jika ada multi-toko
            $table->date('date'); // Tanggal pengeluaran
            $table->string('description'); // Deskripsi/Nama pengeluaran
            $table->decimal('amount', 15, 2); // Jumlah pengeluaran
            
            // Untuk V1, kita buat kategori sebagai string. Nanti bisa dinormalisasi ke tabel sendiri.
            $table->string('category'); 
            
            $table->text('notes')->nullable(); // Catatan tambahan
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Siapa yang mencatat
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karung_operational_expenses');
    }
};