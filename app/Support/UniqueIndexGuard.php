<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Used by the UNIQUE-index migrations (2026_11_25_1000xx): never rename or change rows to make an
 * index fit (D8). If duplicates exist the migration stops and lists them; the owner corrects them
 * (docs/DATA_FIXES.md) and runs `php artisan migrate` again.
 */
class UniqueIndexGuard
{
    /** @throws \RuntimeException listing the duplicate values */
    public static function assertNoDuplicates(string $table, string $column, int $show = 25): void
    {
        $duplicates = DB::table($table)
            ->whereNotNull($column)
            ->groupBy($column)
            ->havingRaw('COUNT(*) > 1')
            ->orderBy($column)
            ->selectRaw("{$column} as value, COUNT(*) as n")
            ->get();
        if ($duplicates->isEmpty()) {
            return;
        }
        $list = $duplicates->take($show)->map(fn ($d) => "'{$d->value}' x{$d->n}")->implode(', ');
        $more = $duplicates->count() > $show ? ' (+'.($duplicates->count() - $show).' more)' : '';

        throw new \RuntimeException(
            "Cannot add UNIQUE({$table}.{$column}): {$duplicates->count()} value(s) are used more than once: {$list}{$more}. "
            .'No data was changed. Correct them (see docs/DATA_FIXES.md), then run php artisan migrate again.'
        );
    }

    /** True when the column already has a single-column UNIQUE index (under any name). */
    public static function hasUniqueIndex(string $table, string $column): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('non_unique', 0)
            ->where('column_name', $column)
            ->whereNotExists(fn ($q) => $q->from('information_schema.statistics as s2')
                ->whereColumn('s2.table_schema', 'information_schema.statistics.table_schema')
                ->whereColumn('s2.table_name', 'information_schema.statistics.table_name')
                ->whereColumn('s2.index_name', 'information_schema.statistics.index_name')
                ->where('s2.seq_in_index', '>', 1))
            ->exists();
    }
}
