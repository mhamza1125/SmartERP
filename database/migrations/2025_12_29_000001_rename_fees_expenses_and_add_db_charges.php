<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration:
     * 1. Renames fees_expenses column to fb_charges (Foreign Bank Charges)
     * 2. Adds new db_charges column (Domestic Bank Charges)
     * 
     * Payment breakdown:
     * - cc_amount: Customer currency amount (e.g., 1000 USD)
     * - fb_charges: Foreign bank charges (e.g., 50 USD)
     * - db_charges: Domestic bank charges (e.g., 500 PKR)
     * - debit: Actual amount received in local currency
     */
    public function up(): void
    {
        // Use raw SQL for MariaDB compatibility
        DB::statement('ALTER TABLE transactions CHANGE COLUMN fees_expenses fb_charges DECIMAL(15,2) NULL COMMENT "Foreign bank charges"');

        // Add db_charges column after fb_charges
        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('db_charges', 15, 2)->nullable()->after('fb_charges')
                ->comment('Domestic bank charges (in local currency)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Use raw SQL for MariaDB compatibility
        DB::statement('ALTER TABLE transactions CHANGE COLUMN fb_charges fees_expenses DECIMAL(15,2) NULL COMMENT "Bank fees, processing charges, etc."');

        // Drop db_charges column
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('db_charges');
        });
    }
};

