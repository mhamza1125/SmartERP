<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds PTC (Process Travel Card) columns to stocks and stock_items tables
     */
    public function up(): void
    {
        // Add PTC columns to stocks table
        Schema::table('stocks', function (Blueprint $table) {
            $table->unsignedBigInteger('ptc_id')->nullable()->after('issue_id')
                ->comment('Links to parent PTC master record');
            $table->unsignedBigInteger('current_stage_id')->nullable()->after('ptc_id')
                ->comment('Current processing stage for PTC');
            $table->unsignedBigInteger('next_stage_id')->nullable()->after('current_stage_id')
                ->comment('Next stage in PTC sequence');
            $table->tinyInteger('is_ptc_master')->default(0)->after('next_stage_id')
                ->comment('1 if this is the PTC master record');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropColumn(['ptc_id', 'current_stage_id', 'next_stage_id', 'is_ptc_master']);
        });
    }
};

