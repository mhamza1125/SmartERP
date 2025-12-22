<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            // Drop indexes if they exist
            if (Schema::hasColumn('vendors', 'material_id')) {
                $table->dropIndex(['material_id']);
            }

            if (Schema::hasColumn('vendors', 'product_id')) {
                $table->dropIndex(['product_id']);
            }
        });

        Schema::table('vendors', function (Blueprint $table) {
            // Change column types (data is preserved)
            $table->text('material_id')->change();
            $table->text('product_id')->change();
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->string('material_id', 255)->change();
            $table->string('product_id', 255)->change();

            // Re-add indexes if they were originally indexed
            $table->index('material_id');
            $table->index('product_id');
        });
    }
};
