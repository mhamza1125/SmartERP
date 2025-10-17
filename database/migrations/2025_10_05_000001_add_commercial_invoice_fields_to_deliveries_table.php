<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->string('fi_no')->nullable()->after('delivery_status')->comment('FI Number for commercial invoice');
            $table->string('rex_no')->nullable()->after('fi_no')->comment('REX Number for commercial invoice');
            $table->string('ntn')->nullable()->after('rex_no')->comment('NTN for commercial invoice');
            $table->text('so_origin')->nullable()->after('ntn')->comment('Statement of Origin for commercial invoice');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropColumn(['fi_no', 'rex_no', 'ntn', 'so_origin']);
        });
    }
};
