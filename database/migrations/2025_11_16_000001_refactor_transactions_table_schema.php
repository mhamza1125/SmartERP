<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration:
     * 1. Changes debit and credit columns from bigint(20) UNSIGNED to decimal(15,2)
     *    to support fractional currency amounts and maintain consistency with fees_expenses
     * 2. Drops the redundant gross_amount and net_amount columns
     *    These values can be calculated dynamically:
     *    - gross_amount = COALESCE(debit, credit)
     *    - net_amount = gross_amount - COALESCE(fees_expenses, 0)
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Change debit and credit to decimal for consistency and fractional amount support
            $table->decimal('debit', 15, 2)->nullable()->change();
            $table->decimal('credit', 15, 2)->nullable()->change();
            
            // Drop redundant calculated columns
            $table->dropColumn(['gross_amount', 'net_amount']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Restore original bigint columns
            $table->bigInteger('debit')->unsigned()->nullable()->change();
            $table->bigInteger('credit')->unsigned()->nullable()->change();
            
            // Restore dropped columns
            $table->decimal('gross_amount', 15, 2)->nullable()->after('credit')->comment('Total amount customer paid');
            $table->decimal('net_amount', 15, 2)->nullable()->after('fees_expenses')->comment('Gross amount minus fees/expenses');
        });
    }
};

