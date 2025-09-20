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
        Schema::table('orders', function (Blueprint $table) {
            $table->date('due_date')->nullable()->after('order_date')->comment('Due date for order completion');
            $table->text('payment_terms')->nullable()->after('due_date')->comment('Payment terms and conditions');
            $table->text('so_origin')->nullable()->after('payment_terms')->comment('Statement of origin');
            $table->string('fi_no')->nullable()->after('so_origin')->comment('FI number');
            $table->string('rex_no')->nullable()->after('fi_no')->comment('REX number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['due_date', 'payment_terms', 'so_origin', 'fi_no', 'rex_no']);
        });
    }
};
