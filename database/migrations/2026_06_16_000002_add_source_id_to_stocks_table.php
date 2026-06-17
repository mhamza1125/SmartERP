<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            // Stores the originating record ID (e.g., delivery_return_id when table_name='delivery_returns')
            $table->unsignedBigInteger('source_id')->nullable()->after('order_id');
        });
    }

    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropColumn('source_id');
        });
    }
};
