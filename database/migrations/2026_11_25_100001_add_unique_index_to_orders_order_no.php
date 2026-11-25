<?php

use App\Support\UniqueIndexGuard;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * F-16 / D7: UNIQUE(orders.order_no). Order numbers (typed by users or SLE-O generated).
 * Checks for duplicates first and stops with a message listing them; never changes rows (D8).
 * SQL: ALTER TABLE `orders` ADD UNIQUE `orders_order_no_unique` (`order_no`)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (UniqueIndexGuard::hasUniqueIndex('orders', 'order_no')) {
            return; // already unique (e.g. an older index under another name)
        }
        UniqueIndexGuard::assertNoDuplicates('orders', 'order_no');

        Schema::table('orders', function (Blueprint $table) {
            $table->unique('order_no', 'orders_order_no_unique');
        });
    }

    public function down(): void
    {
        if (Schema::hasIndex('orders', 'orders_order_no_unique')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropUnique('orders_order_no_unique');
            });
        }
    }
};
