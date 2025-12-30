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
        Schema::table('packing_cartons', function (Blueprint $table) {
            $table->string('box_dimension')->nullable()->comment('Box dimensions like 65x42x22 cm');
            $table->decimal('box_weight', 8, 2)->nullable()->comment('Weight per individual carton');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packing_cartons', function (Blueprint $table) {
            $table->dropColumn(['box_dimension', 'box_weight']);
        });
    }
};
