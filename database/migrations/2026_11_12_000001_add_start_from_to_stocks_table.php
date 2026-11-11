<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * stocks.start_from (PTC masters only) - what a PTC starts from:
     *   'stock'     stock already at the start stage; the first issuance is for the next stage (existing behaviour)
     *   'materials' raw materials; the first issuance is for the start stage itself
     * NULL (every PTC created before this column, and non-PTC records) behaves as 'stock'.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('stocks', 'start_from')) {
            Schema::table('stocks', function (Blueprint $table) {
                $table->string('start_from', 10)->nullable()->after('start_stage_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('stocks', 'start_from')) {
            Schema::table('stocks', function (Blueprint $table) {
                $table->dropColumn('start_from');
            });
        }
    }
};
