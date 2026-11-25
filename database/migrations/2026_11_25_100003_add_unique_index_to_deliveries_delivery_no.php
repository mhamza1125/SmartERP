<?php

use App\Support\UniqueIndexGuard;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * F-16 / D7: UNIQUE(deliveries.delivery_no). Delivery numbers SLE-D{NNN}-{YY} (NULL allowed, several NULLs are fine).
 * Checks for duplicates first and stops with a message listing them; never changes rows (D8).
 * SQL: ALTER TABLE `deliveries` ADD UNIQUE `deliveries_delivery_no_unique` (`delivery_no`)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (UniqueIndexGuard::hasUniqueIndex('deliveries', 'delivery_no')) {
            return; // already unique (e.g. an older index under another name)
        }
        UniqueIndexGuard::assertNoDuplicates('deliveries', 'delivery_no');

        Schema::table('deliveries', function (Blueprint $table) {
            $table->unique('delivery_no', 'deliveries_delivery_no_unique');
        });
    }

    public function down(): void
    {
        if (Schema::hasIndex('deliveries', 'deliveries_delivery_no_unique')) {
            Schema::table('deliveries', function (Blueprint $table) {
                $table->dropUnique('deliveries_delivery_no_unique');
            });
        }
    }
};
