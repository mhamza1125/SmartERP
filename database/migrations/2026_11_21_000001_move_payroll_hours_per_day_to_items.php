<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Hours per day now come from each employee's department working times (Work In / Work Out on
 * Settings > Heads > Departments), so they move from the payroll header to each employee line.
 * Saved lines keep the header's value they were calculated with.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('payroll_items', 'hours_per_day')) {
            Schema::table('payroll_items', function (Blueprint $table) {
                $table->decimal('hours_per_day', 5, 2)->nullable()->after('paid_leaves');
            });
        }

        if (Schema::hasColumn('payrolls', 'hours_per_day')) {
            DB::table('payroll_items')
                ->join('payrolls', 'payrolls.payroll_id', '=', 'payroll_items.payroll_id')
                ->whereNull('payroll_items.hours_per_day')
                ->update(['payroll_items.hours_per_day' => DB::raw('payrolls.hours_per_day')]);

            Schema::table('payrolls', function (Blueprint $table) {
                $table->dropColumn('hours_per_day');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('payrolls', 'hours_per_day')) {
            Schema::table('payrolls', function (Blueprint $table) {
                $table->decimal('hours_per_day', 5, 2)->default(8)->after('total_working_days');
            });
        }

        if (Schema::hasColumn('payroll_items', 'hours_per_day')) {
            Schema::table('payroll_items', function (Blueprint $table) {
                $table->dropColumn('hours_per_day');
            });
        }
    }
};
