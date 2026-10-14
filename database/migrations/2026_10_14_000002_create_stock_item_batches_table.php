<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Which batches a product stock line (material_id = 0, incl. component lines) moved.
        // A stock-in has one row; a stock-out may draw from several batches. Kept apart from
        // stock_items so every product line stays a single row for the existing screens.
        // SUM(quantity) per stock_item_id = stock_items.quantity.
        Schema::create('stock_item_batches', function (Blueprint $table) {
            $table->id('stock_item_batch_id');
            $table->unsignedBigInteger('stock_item_id');
            $table->unsignedBigInteger('batch_id');
            $table->double('quantity')->unsigned();
            $table->timestamps();

            $table->unique(['stock_item_id', 'batch_id']);
            $table->index('batch_id');
            $table->foreign('stock_item_id')->references('stock_item_id')->on('stock_items')->cascadeOnDelete();
            $table->foreign('batch_id')->references('batch_id')->on('stock_batches');
        });

        Schema::table('stock_items', function (Blueprint $table) {
            // Order a delivery line was delivered for (a multi-order delivery header only keeps the first order)
            $table->unsignedBigInteger('order_id')->nullable()->after('ptc_virtual');

            $table->foreign('order_id')->references('order_id')->on('orders');
        });

        Schema::table('receive_materials', function (Blueprint $table) {
            // Batch of a received product line (purchase item with material_id = 0)
            $table->unsignedBigInteger('batch_id')->nullable()->after('purchase_item_id');

            $table->foreign('batch_id')->references('batch_id')->on('stock_batches');
        });
    }

    public function down(): void
    {
        Schema::table('receive_materials', function (Blueprint $table) {
            $table->dropForeign(['batch_id']);
            $table->dropColumn('batch_id');
        });

        Schema::table('stock_items', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropColumn('order_id');
        });

        Schema::dropIfExists('stock_item_batches');
    }
};
