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
            $table->bigIncrements('id');
            $table->unsignedBigInteger('business_unit_id')->index();
            $table->string('sku', 255)->unique()->comment('Stock Keeping Unit, unik per business_unit_id, akan digenerate otomatis');
            $table->string('name', 255);
            $table->unsignedBigInteger('product_category_id')->nullable()->index('karung_products_product_category_id_foreign');
            $table->unsignedBigInteger('product_type_id')->nullable()->index('karung_products_product_type_id_foreign');
            $table->text('description')->nullable();
            $table->decimal('purchase_price', 15)->default(0);
            $table->decimal('selling_price', 15)->default(0);
            $table->integer('stock')->default(0)->comment('Untuk V1, angka ini bersifat referensi/manual');
            $table->integer('min_stock_level')->default(0)->comment('Untuk notifikasi stok kritis');
            $table->unsignedBigInteger('default_supplier_id')->nullable()->index('karung_products_default_supplier_id_foreign');
            $table->string('image_path', 255)->nullable();
            $table->boolean('is_active')->default(true)->comment('Status Aktif/Tidak Aktif produk');
            $table->timestamps();
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
