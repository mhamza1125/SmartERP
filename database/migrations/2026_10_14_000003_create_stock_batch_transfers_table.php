<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A quantity moved between batches without moving stock (splitting part of a batch off
        // to reserve it for another order or to free it). Not a stock movement: product/stage
        // totals are unchanged, only the owning batch changes.
        Schema::create('stock_batch_transfers', function (Blueprint $table) {
            $table->id('transfer_id');
            $table->unsignedBigInteger('from_batch_id');
            $table->unsignedBigInteger('to_batch_id');
            $table->unsignedBigInteger('stage_id');
            $table->double('quantity')->unsigned();
            $table->date('transfer_date');
            $table->longText('description')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->index('from_batch_id');
            $table->index('to_batch_id');
            $table->foreign('from_batch_id')->references('batch_id')->on('stock_batches');
            $table->foreign('to_batch_id')->references('batch_id')->on('stock_batches');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_batch_transfers');
    }
};
