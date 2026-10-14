<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            // Stage of the stock a PTC master starts from (not a work stage); its first
            // issuance is for the stage after it. NULL on PTCs created before this column.
            $table->unsignedBigInteger('start_stage_id')->nullable()->after('current_stage_id');
        });
    }

    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropColumn('start_stage_id');
        });
    }
};
