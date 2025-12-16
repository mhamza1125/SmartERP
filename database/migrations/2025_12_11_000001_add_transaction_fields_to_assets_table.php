<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration transforms the assets table from a simple record-keeping system
     * to a complete debit/credit ledger system similar to transactions table.
     * 
     * Changes:
     * 1. Adds transaction_type to distinguish between different asset transactions
     * 2. Adds description for transaction notes/details
     * 3. Adds transaction_date for when the transaction occurred
     * 4. Adds debit/credit columns following double-entry bookkeeping
     * 5. Removes quantity field (quantity is implicit in amount)
     * 6. Renames amount to debit (asset acquisitions increase asset value)
     * 
     * Transaction Types:
     * - purchase: Asset acquisition (debit - increases asset value)
     * - sale: Asset disposal/sale (credit - decreases asset value)
     * - depreciation: Asset depreciation (credit - decreases asset value)
     * - writeoff: Asset write-off (credit - decreases asset value)
     * - adjustment: Other adjustments (debit or credit)
     */
    public function up(): void
    {
        // Check if columns already exist and add only if they don't
        if (!Schema::hasColumn('assets', 'transaction_type')) {
            Schema::table('assets', function (Blueprint $table) {
                $table->string('transaction_type')->default('purchase')->after('asset_name')
                    ->comment('purchase, sale, depreciation, writeoff, adjustment');
            });
        }

        if (!Schema::hasColumn('assets', 'description')) {
            Schema::table('assets', function (Blueprint $table) {
                $table->text('description')->nullable()->after('transaction_type')
                    ->comment('Transaction description/notes');
            });
        }

        if (!Schema::hasColumn('assets', 'transaction_date')) {
            Schema::table('assets', function (Blueprint $table) {
                $table->date('transaction_date')->nullable()->after('description')
                    ->comment('Date of the transaction');
            });
        }

        if (!Schema::hasColumn('assets', 'debit')) {
            Schema::table('assets', function (Blueprint $table) {
                $table->decimal('debit', 15, 2)->nullable()->after('transaction_date')
                    ->comment('Asset acquisition/increase amount');
            });
        }

        if (!Schema::hasColumn('assets', 'credit')) {
            Schema::table('assets', function (Blueprint $table) {
                $table->decimal('credit', 15, 2)->nullable()->after('debit')
                    ->comment('Asset disposal/decrease amount');
            });
        }

        // Migrate existing data if amount column still exists
        if (Schema::hasColumn('assets', 'amount')) {
            DB::statement('UPDATE assets SET debit = amount, transaction_date = DATE(created_at) WHERE amount IS NOT NULL AND debit IS NULL');

            // Drop obsolete columns
            Schema::table('assets', function (Blueprint $table) {
                $table->dropColumn(['amount', 'quantity']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            // Restore amount column
            $table->decimal('amount', 10, 2)->after('asset_name');
            
            // Restore quantity column
            $table->integer('quantity')->default(1)->after('asset_name');
        });

        // Migrate data back: move debit to amount
        DB::statement('UPDATE assets SET amount = COALESCE(debit, credit, 0) WHERE amount IS NULL');
        
        // Drop new columns
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn([
                'transaction_type',
                'description',
                'transaction_date',
                'debit',
                'credit'
            ]);
        });
    }
};

