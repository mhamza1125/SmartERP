<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * F-19: secondary indexes for the stock and ledger lookups (additive; rows are not touched).
 * An index is skipped when one with the same leading columns already exists
 * (stock_items.order_id already has its foreign-key index, so it is not listed).
 * Runs before the UNIQUE-number migrations so it applies even while those stop on duplicates.
 *
 * SQL (each only when missing):
 *   ALTER TABLE stock_items  ADD INDEX stock_items_stock_id_index (stock_id);
 *   ALTER TABLE stock_items  ADD INDEX stock_items_product_type_id_stage_id_index (product_type_id, stage_id);
 *   ALTER TABLE stock_items  ADD INDEX stock_items_material_id_index (material_id);
 *   ALTER TABLE stocks       ADD INDEX stocks_ptc_id_index (ptc_id);
 *   ALTER TABLE stocks       ADD INDEX stocks_issue_id_index (issue_id);
 *   ALTER TABLE stocks       ADD INDEX stocks_order_id_index (order_id);
 *   ALTER TABLE stocks       ADD INDEX stocks_table_name_stock_type_index (table_name, stock_type);
 *   ALTER TABLE transactions ADD INDEX transactions_transaction_to_payee_id_index (transaction_to, payee_id);
 *   ALTER TABLE transactions ADD INDEX transactions_bank_id_index (bank_id);
 *   ALTER TABLE transactions ADD INDEX transactions_transaction_date_index (transaction_date);
 */
return new class extends Migration
{
    private const INDEXES = [
        'stock_items' => [
            'stock_items_stock_id_index' => ['stock_id'],
            'stock_items_product_type_id_stage_id_index' => ['product_type_id', 'stage_id'],
            'stock_items_material_id_index' => ['material_id'],
        ],
        'stocks' => [
            'stocks_ptc_id_index' => ['ptc_id'],
            'stocks_issue_id_index' => ['issue_id'],
            'stocks_order_id_index' => ['order_id'],
            'stocks_table_name_stock_type_index' => ['table_name', 'stock_type'],
        ],
        'transactions' => [
            'transactions_transaction_to_payee_id_index' => ['transaction_to', 'payee_id'],
            'transactions_bank_id_index' => ['bank_id'],
            'transactions_transaction_date_index' => ['transaction_date'],
        ],
    ];

    public function up(): void
    {
        foreach (self::INDEXES as $table => $indexes) {
            foreach ($indexes as $name => $columns) {
                if (Schema::hasIndex($table, $name) || $this->coveredBy($table, $columns)) {
                    continue;
                }
                Schema::table($table, fn ($blueprint) => $blueprint->index($columns, $name));
            }
        }
    }

    public function down(): void
    {
        foreach (self::INDEXES as $table => $indexes) {
            foreach (array_keys($indexes) as $name) {
                if (Schema::hasIndex($table, $name)) {
                    Schema::table($table, fn ($blueprint) => $blueprint->dropIndex($name));
                }
            }
        }
    }

    /** An existing index whose leading columns are exactly these columns, in this order. */
    private function coveredBy(string $table, array $columns): bool
    {
        $rows = DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())->where('table_name', $table)
            ->orderBy('index_name')->orderBy('seq_in_index')
            ->get(['index_name', 'seq_in_index', 'column_name'])
            ->groupBy('index_name');
        foreach ($rows as $index) {
            if ($index->pluck('column_name')->take(count($columns))->all() === $columns) {
                return true;
            }
        }

        return false;
    }
};
