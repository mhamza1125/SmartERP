<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Optional Expense Head (heads.head_type_id = 7) on a Journal Voucher. A label only:
     * it does not change the voucher's debit / credit or any balance.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('expense_head_id')->nullable()->after('payee_bank_id');
            $table->foreign('expense_head_id')->references('head_id')->on('heads')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['expense_head_id']);
            $table->dropColumn('expense_head_id');
        });
    }
};
