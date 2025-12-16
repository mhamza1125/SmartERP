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
        Schema::create('packing_carton_items', function (Blueprint $table) {
            $table->id('packing_carton_item_id');
            $table->unsignedBigInteger('packing_carton_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('pcs_each_carton');
            $table->integer('total_pcs');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('packing_carton_id')->references('packing_carton_id')->on('packing_cartons')->onDelete('cascade');
            $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packing_carton_items');
    }
};

