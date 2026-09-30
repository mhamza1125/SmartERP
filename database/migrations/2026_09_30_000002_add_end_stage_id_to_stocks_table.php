<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            // Last stage a PTC master is planned to reach; the PTC is Completed
            // (not just Closed) once this stage is fully received.
            $table->unsignedBigInteger('end_stage_id')->nullable()->after('next_stage_id');
        });
    }

    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropColumn('end_stage_id');
        });
    }
};
