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
        Schema::create('karung_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_unit_id')->index();
            $table->string('sku')->unique()->comment('Stock Keeping Unit, unik per business_unit_id, akan digenerate otomatis');
            $table->string('name'); // Nama Produk

            $table->foreignId('product_category_id')->nullable()->constrained('karung_product_categories')->onDelete('set null');
            // Jika kategori dihapus, product_category_id di produk ini jadi NULL
            // Atau bisa juga ->restrictOnDelete() agar tidak bisa dihapus jika masih ada produk terkait

            $table->foreignId('product_type_id')->nullable()->constrained('karung_product_types')->onDelete('set null');
            // Jika jenis dihapus, product_type_id di produk ini jadi NULL

            $table->text('description')->nullable(); // Spesifikasi Lain / Keterangan
            $table->decimal('purchase_price', 15, 2)->default(0); // Harga beli referensi
            $table->decimal('selling_price', 15, 2)->default(0); // Harga jual standar

            $table->integer('stock')->default(0)->comment('Untuk V1, angka ini bersifat referensi/manual');
            $table->integer('min_stock_level')->default(0)->comment('Untuk notifikasi stok kritis');

            $table->foreignId('default_supplier_id')->nullable()->constrained('karung_suppliers')->onDelete('set null');
            // Supplier langganan (opsional)

            $table->string('image_path')->nullable(); // Path ke file foto produk
            $table->boolean('is_active')->default(true)->comment('Status Aktif/Tidak Aktif produk');

            $table->timestamps();

            // Membuat SKU unik untuk setiap business_unit_id
            // $table->unique(['business_unit_id', 'sku']); // SKU sudah unique secara global di atas
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karung_products');
    }
};