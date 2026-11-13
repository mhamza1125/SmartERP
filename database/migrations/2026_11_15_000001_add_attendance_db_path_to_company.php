<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Settings > Company: location of the attendance machine database (ATT2000.mdb).
     * NULL = config('attendance.default_path') (public/resources/att2000.mdb).
     */
    public function up(): void
    {
        Schema::table('company', function (Blueprint $table) {
            $table->string('attendance_db_path', 500)->nullable()->after('work_hours_per_day');
        });
    }

    public function down(): void
    {
        Schema::table('company', function (Blueprint $table) {
            $table->dropColumn('attendance_db_path');
        });
    }
};
