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
        Schema::table('karung_purchase_transactions', function (Blueprint $table) {
            // Menambahkan kolom purchase_code setelah business_unit_id
            // Dibuat unik dan nullable untuk sementara agar tidak error pada data lama jika ada.
            // Kita akan isi data lama dan membuatnya not nullable nanti jika perlu.
            $table->string('purchase_code')->unique()->nullable()->after('business_unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('karung_purchase_transactions', function (Blueprint $table) {
            // Hapus unique index terlebih dahulu sebelum menghapus kolom
            // Nama index defaultnya adalah 'nama_tabel_nama_kolom_unique'
            $table->dropUnique('karung_purchase_transactions_purchase_code_unique');
            $table->dropColumn('purchase_code');
        });
    }
};
