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
        Schema::table('packing_lists', function (Blueprint $table) {
            $table->integer('pallet_qty')->nullable()->comment('Number of pallets');
            $table->decimal('pallet_weight', 8, 2)->nullable()->comment('Weight per pallet');
            $table->string('pallet_dimension')->nullable()->comment('Pallet dimensions like 100x120 cm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packing_lists', function (Blueprint $table) {
            $table->dropColumn(['pallet_qty', 'pallet_weight', 'pallet_dimension']);
        });
    }
};
