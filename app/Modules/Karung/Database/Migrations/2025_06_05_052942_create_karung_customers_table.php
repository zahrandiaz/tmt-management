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
        Schema::create('karung_customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_unit_id')->index();
            $table->string('customer_code')->nullable()->comment('Kode pelanggan, bisa otomatis atau manual');
            $table->string('name'); // Nama Pelanggan
            $table->string('phone_number')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();

            // Kode pelanggan unik per instansi bisnis jika diisi
            $table->unique(['business_unit_id', 'customer_code'])->whereNotNull('customer_code');
            // Email pelanggan juga sebaiknya unik per instansi bisnis jika diisi (pertimbangkan jika pelanggan bisa sama di banyak unit bisnis)
            // Untuk sekarang, kita buat unik per business_unit_id jika diisi
            $table->unique(['business_unit_id', 'email'])->whereNotNull('email');
            // Mungkin nama pelanggan dan nomor telepon bisa jadi kandidat unique constraint juga, tergantung kebutuhan.
            // Untuk awal, name kita biarkan bisa duplikat (mungkin ada pelanggan dengan nama sama).
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karung_customers');
    }
};