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
        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('gross_amount', 15, 2)->nullable()->after('credit')->comment('Total amount customer paid');
            $table->decimal('fees_expenses', 15, 2)->nullable()->after('gross_amount')->comment('Bank fees, processing charges, etc.');
            $table->decimal('net_amount', 15, 2)->nullable()->after('fees_expenses')->comment('Gross amount minus fees/expenses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['gross_amount', 'fees_expenses', 'net_amount']);
        });
    }
};
