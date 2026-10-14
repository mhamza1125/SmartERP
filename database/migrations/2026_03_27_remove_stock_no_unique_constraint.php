<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Intentionally does nothing.
     *
     * This migration used to drop the UNIQUE index 'issue_no' on stocks.stock_no. The decision
     * (2026-10-14) is to KEEP stock numbers unique: all code generates table-wide unique numbers
     * (PTC records are prefixed with the PTC number). The migration is kept as a no-op so a plain
     * `php artisan migrate` records it as run without dropping the index.
     */
    public function up(): void
    {
        //
    }

    public function down(): void
    {
        //
    }
};
