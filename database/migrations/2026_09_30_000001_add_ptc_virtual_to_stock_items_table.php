<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_items', function (Blueprint $table) {
            // 1 = movement inside a PTC's virtual stock (PTC receipts / stage transfers).
            // These rows are excluded from general stock balances; only an explicit
            // PTC release (a normal ptc_virtual = 0 stock-in) enters general stock.
            $table->unsignedTinyInteger('ptc_virtual')->default(0)->after('component_product_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('stock_items', function (Blueprint $table) {
            $table->dropColumn('ptc_virtual');
        });
    }
};
