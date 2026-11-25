<?php

use App\Support\UniqueIndexGuard;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * F-16 / D7: UNIQUE(orders.job_no). Job numbers {customer_no}/J{YY}-{NNN}.
 * Checks for duplicates first and stops with a message listing them; never changes rows (D8).
 * SQL: ALTER TABLE `orders` ADD UNIQUE `orders_job_no_unique` (`job_no`)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (UniqueIndexGuard::hasUniqueIndex('orders', 'job_no')) {
            return; // already unique (e.g. an older index under another name)
        }
        UniqueIndexGuard::assertNoDuplicates('orders', 'job_no');

        Schema::table('orders', function (Blueprint $table) {
            $table->unique('job_no', 'orders_job_no_unique');
        });
    }

    public function down(): void
    {
        if (Schema::hasIndex('orders', 'orders_job_no_unique')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropUnique('orders_job_no_unique');
            });
        }
    }
};
