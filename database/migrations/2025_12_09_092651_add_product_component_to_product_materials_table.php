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
        Schema::table('product_materials', function (Blueprint $table) {
            // Add component_type column: 'material' (default) or 'product'
            $table->enum('component_type', ['material', 'product'])->default('material')->after('material_id');
            // Add component_product_type_id for when the component is a product
            $table->bigInteger('component_product_type_id')->nullable()->after('component_type');
            // Make material_id nullable since product components won't have a material_id
            $table->bigInteger('material_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_materials', function (Blueprint $table) {
            $table->dropColumn('component_type');
            $table->dropColumn('component_product_type_id');
            $table->bigInteger('material_id')->nullable(false)->change();
        });
    }
};
