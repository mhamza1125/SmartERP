<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Removes the UNIQUE constraint on stock_no column to allow multiple deliveries
     * to reference the same order without causing integrity constraint violations.
     * 
     * This is safe because:
     * - stock_no is never used as a lookup/filter key in any query
     * - All code uses stock_id (PRIMARY KEY) for data retrieval
     * - Delivery workflow requires non-unique stock_no values for multi-order deliveries
     */
    public function up(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            // Drop the UNIQUE constraint named 'issue_no'
            $table->dropUnique('issue_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            // Restore the UNIQUE constraint
            $table->unique('stock_no', 'issue_no');
        });
    }
};

