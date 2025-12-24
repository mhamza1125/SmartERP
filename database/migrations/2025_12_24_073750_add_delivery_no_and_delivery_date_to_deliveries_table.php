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
            $table->string('delivery_no')->nullable()->after('delivery_status')->comment('Auto-generated delivery number (SLE-001-26 format)');
            $table->date('delivery_date')->nullable()->after('delivery_no')->comment('Actual delivery date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropColumn(['delivery_no', 'delivery_date']);
        });
    }
};
