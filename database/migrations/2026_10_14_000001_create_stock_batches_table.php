<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A batch is one lot of a single product type with a known origin. Every product
        // stock line carries its batch; order_id reserves the batch for that order
        // (NULL = free stock usable by any order).
        Schema::create('stock_batches', function (Blueprint $table) {
            $table->id('batch_id');
            $table->string('batch_no')->unique();
            $table->unsignedBigInteger('product_type_id');
            // ptc | purchase | issuance | opening | split | legacy
            $table->string('source_type', 20);
            // ptc: master stock_id, purchase: purchase_item_id, issuance: issuance stock_id,
            // opening: opening stock_id, split: parent batch_id, legacy: NULL
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('parent_batch_id')->nullable();
            $table->longText('description')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->unique(['source_type', 'source_id', 'product_type_id']);
            $table->index(['product_type_id', 'order_id']);
            $table->foreign('product_type_id')->references('product_type_id')->on('product_types');
            $table->foreign('order_id')->references('order_id')->on('orders');
            $table->foreign('parent_batch_id')->references('batch_id')->on('stock_batches');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_batches');
    }
};
