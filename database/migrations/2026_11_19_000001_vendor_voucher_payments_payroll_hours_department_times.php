<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * - transactions.voucher_id: the journal voucher a vendor / contractor payment settles
     *   (Pay Vendor > Payment For > a journal voucher). The JV itself moves no money; the payment does.
     * - payroll_items.late_hours: late time in hours (2 decimals), replacing whole late minutes on the
     *   payroll sheet. Existing rows get late_minutes / 60; late_minutes is kept as it was saved.
     * - payroll_items.paid_leaves: leave days that are paid (not deducted from salary).
     * - heads.work_start_time / work_end_time: working hours of a department head (head type 3),
     *   used for late time (start) and overtime (end) of that department's employees.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('voucher_id')->nullable()->after('expense_head_id')->index();
        });

        Schema::table('payroll_items', function (Blueprint $table) {
            $table->decimal('late_hours', 8, 2)->default(0)->after('late_minutes');
            $table->decimal('paid_leaves', 5, 2)->default(0)->after('actual_working_days');
        });
        // Same late time, expressed in hours (the late deduction already saved is not touched)
        DB::table('payroll_items')->where('late_minutes', '>', 0)->update(['late_hours' => DB::raw('ROUND(late_minutes / 60, 2)')]);

        Schema::table('heads', function (Blueprint $table) {
            $table->time('work_start_time')->nullable()->after('head_status');
            $table->time('work_end_time')->nullable()->after('work_start_time');
        });
    }

    public function down(): void
    {
        Schema::table('heads', function (Blueprint $table) {
            $table->dropColumn(['work_start_time', 'work_end_time']);
        });
        Schema::table('payroll_items', function (Blueprint $table) {
            $table->dropColumn(['late_hours', 'paid_leaves']);
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['voucher_id']);
            $table->dropColumn('voucher_id');
        });
    }
};
