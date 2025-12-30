<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration simplifies currency handling by:
     * 1. Removing price2, head_id and exchange columns from order_items table
     *    (price2 was customer currency, now using price for customer currency)
     *    (head_id and exchange were used for complex currency exchange calculations)
     * 2. Adding cc_amount column to transactions table
     *    (Stores customer currency amounts for payments)
     */
    public function up(): void
    {
        // Remove complex currency exchange columns from order_items
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'price2')) {
                $table->dropColumn('price2');
            }
            if (Schema::hasColumn('order_items', 'head_id')) {
                $table->dropColumn('head_id');
            }
            if (Schema::hasColumn('order_items', 'exchange')) {
                $table->dropColumn('exchange');
            }
        });

        // Add customer currency amount column to transactions
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'cc_amount')) {
                $table->decimal('cc_amount', 15, 2)->nullable()->after('credit')
                    ->comment('Customer currency amount (for dual-currency payments)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore price2, head_id and exchange columns to order_items
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'price2')) {
                $table->double('price2')->unsigned()->nullable()->after('price');
            }
            if (!Schema::hasColumn('order_items', 'head_id')) {
                $table->unsignedBigInteger('head_id')->nullable()->after('price2');
            }
            if (!Schema::hasColumn('order_items', 'exchange')) {
                $table->double('exchange')->nullable()->after('head_id');
            }
        });

        // Remove cc_amount column from transactions
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'cc_amount')) {
                $table->dropColumn('cc_amount');
            }
        });
    }
};

