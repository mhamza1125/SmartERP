<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Payroll hours per day are now worked out per department: Work In to Work Out minus the
 * department's break (client request 2026-11-25).
 * - heads.break_minutes: break of a department head (head type 3), in minutes (0 = no break)
 * - company.work_hours_per_day: the manual "hours per day" setting is removed (no fallback any more)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('heads', 'break_minutes')) {
            Schema::table('heads', function (Blueprint $table) {
                $table->unsignedSmallInteger('break_minutes')->default(0)->after('work_end_time');
            });
        }

        if (Schema::hasColumn('company', 'work_hours_per_day')) {
            Schema::table('company', function (Blueprint $table) {
                $table->dropColumn('work_hours_per_day');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('company', 'work_hours_per_day')) {
            Schema::table('company', function (Blueprint $table) {
                $table->decimal('work_hours_per_day', 5, 2)->default(8)->after('late_multiplier');
            });
        }

        if (Schema::hasColumn('heads', 'break_minutes')) {
            Schema::table('heads', function (Blueprint $table) {
                $table->dropColumn('break_minutes');
            });
        }
    }
};
