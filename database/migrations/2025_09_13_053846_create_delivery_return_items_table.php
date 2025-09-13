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
        Schema::create('delivery_return_items', function (Blueprint $table) {
            $table->id('delivery_return_item_id');
            $table->unsignedBigInteger('delivery_return_id');
            $table->unsignedBigInteger('stock_item_id');
            $table->decimal('quantity', 10, 2);
            $table->string('reason')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('delivery_return_id')->references('delivery_return_id')->on('delivery_returns')->onDelete('cascade');
            $table->foreign('stock_item_id')->references('stock_item_id')->on('stock_items')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_return_items');
    }
};
