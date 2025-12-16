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
        Schema::create('packing_cartons', function (Blueprint $table) {
            $table->id('packing_carton_id');
            $table->unsignedBigInteger('packing_list_id');
            $table->integer('carton_from');
            $table->integer('carton_to');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('packing_list_id')->references('packing_list_id')->on('packing_lists')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packing_cartons');
    }
};

