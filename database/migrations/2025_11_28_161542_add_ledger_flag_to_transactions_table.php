<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds ledger_flag column to distinguish between:
     * - 1 (default): Transactions that affect cash immediately (payments, receipts)
     * - 0: Transactions that are just accounting entries (general vouchers)
     *
     * General vouchers (ledger_flag=0) will NOT appear in Cash/Bank ledgers
     * but will appear in payee ledgers (Vendor/Contractor/Employee/Customer)
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->tinyInteger('ledger_flag')->default(1)->after('fees_expenses')->comment('1=affects cash ledger, 0=accounting entry only (general voucher)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('ledger_flag');
        });
    }
};
