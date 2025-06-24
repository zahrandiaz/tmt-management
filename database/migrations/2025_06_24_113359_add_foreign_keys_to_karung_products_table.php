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
        Schema::table('karung_products', function (Blueprint $table) {
            $table->foreign(['default_supplier_id'])->references(['id'])->on('karung_suppliers')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['product_category_id'])->references(['id'])->on('karung_product_categories')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['product_type_id'])->references(['id'])->on('karung_product_types')->onUpdate('restrict')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('karung_products', function (Blueprint $table) {
            $table->dropForeign('karung_products_default_supplier_id_foreign');
            $table->dropForeign('karung_products_product_category_id_foreign');
            $table->dropForeign('karung_products_product_type_id_foreign');
        });
    }
};
