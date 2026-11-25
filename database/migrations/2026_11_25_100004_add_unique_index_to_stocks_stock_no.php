<?php

use App\Support\UniqueIndexGuard;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * F-16 / D7: UNIQUE(stocks.stock_no). Stock document numbers (decision 2026-10-14: unique table-wide). Runs LAST: today it stops on the 10 legacy duplicates, and see docs/AUDIT_QUESTIONS.md Q20 before correcting them.
 * Checks for duplicates first and stops with a message listing them; never changes rows (D8).
 * SQL: ALTER TABLE `stocks` ADD UNIQUE `stocks_stock_no_unique` (`stock_no`)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (UniqueIndexGuard::hasUniqueIndex('stocks', 'stock_no')) {
            return; // already unique (e.g. an older index under another name)
        }
        UniqueIndexGuard::assertNoDuplicates('stocks', 'stock_no');

        Schema::table('stocks', function (Blueprint $table) {
            $table->unique('stock_no', 'stocks_stock_no_unique');
        });
    }

    public function down(): void
    {
        if (Schema::hasIndex('stocks', 'stocks_stock_no_unique')) {
            Schema::table('stocks', function (Blueprint $table) {
                $table->dropUnique('stocks_stock_no_unique');
            });
        }
    }
};
