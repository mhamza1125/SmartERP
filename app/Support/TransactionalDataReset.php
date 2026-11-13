<?php

namespace App\Support;

use App\Models\Image;
use App\Repositories\ImageRepository;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * TEMPORARY one-time utility (Settings > Reset Test Data): removes test / transactional data and
 * keeps master data. Remove this class, DataResetController, DataResetRequest, the dataReset view,
 * its two routes, the sidebar link and SettingsPolicy::resetData once the cleanup has been done.
 *
 * Steps run in foreign-key order inside one DB transaction (all tables are InnoDB). Before the
 * commit, verify() checks that every master table still has exactly the rows it had before and
 * that no transactional rows remain; any mismatch throws and rolls the whole reset back.
 * Repeatable: a second run finds nothing to delete.
 */
class TransactionalDataReset
{
    /** System stock headers kept: 0 = material-processing placeholder, 1 = opening-stock header. */
    public const SYSTEM_STOCK_IDS = [0, 1];

    /** images.table_name values that belong to transactional records (master images are kept). */
    public const TRANSACTIONAL_IMAGE_TABLES = [
        'stocks', 'transactions', 'boxes', 'assets', 'orders', 'deliveries', 'purchases', 'receives', 'returns',
    ];

    /** Master / configuration tables that must keep every row (checked before the commit). */
    public const PRESERVED = [
        'customers' => 'Customers',
        'vendors' => 'Vendors & Contractors',
        'employees' => 'Employees',
        'salaries' => 'Employee salary rates (history)',
        'products' => 'Products',
        'product_types' => 'Product sizes / variants',
        'product_materials' => 'Product bill of materials',
        'product_costs' => 'Product cost settings',
        'materials' => 'Materials',
        'machines' => 'Machines',
        'heads' => 'Heads',
        'head_types' => 'Head types',
        'categories' => 'Categories',
        'company' => 'Company details',
        'banks' => 'Bank accounts',
        'users' => 'Users',
        'roles' => 'Roles',
        'permissions' => 'Permissions',
        'permission_role' => 'Role-permission assignments',
        'work_times' => 'Work timings',
        'work_holidays' => 'Work holidays',
        'activity_log' => 'Activity / audit log',
    ];

    /**
     * Ordered deletion plan: [table, label, group, query]. Children before parents.
     *
     * @return array<int, array{table: string, label: string, group: string, query: callable(): Builder}>
     */
    public function steps(): array
    {
        $all = fn (string $table) => fn () => DB::table($table);

        return [
            ['table' => 'stock_batch_transfers', 'label' => 'Batch transfers / splits', 'group' => 'Inventory', 'query' => $all('stock_batch_transfers')],
            ['table' => 'stock_item_batches', 'label' => 'Batch allocations of stock lines', 'group' => 'Inventory', 'query' => $all('stock_item_batches')],
            ['table' => 'return_materials', 'label' => 'Purchase return lines', 'group' => 'Purchasing', 'query' => $all('return_materials')],
            ['table' => 'returns', 'label' => 'Purchase returns', 'group' => 'Purchasing', 'query' => $all('returns')],
            ['table' => 'receive_materials', 'label' => 'Purchase receiving lines', 'group' => 'Purchasing', 'query' => $all('receive_materials')],
            ['table' => 'receives', 'label' => 'Purchase receivings', 'group' => 'Purchasing', 'query' => $all('receives')],
            ['table' => 'delivery_return_items', 'label' => 'Delivery return lines', 'group' => 'Orders & Deliveries', 'query' => $all('delivery_return_items')],
            ['table' => 'delivery_returns', 'label' => 'Delivery returns', 'group' => 'Orders & Deliveries', 'query' => $all('delivery_returns')],
            ['table' => 'packing_carton_items', 'label' => 'Packing carton items', 'group' => 'Orders & Deliveries', 'query' => $all('packing_carton_items')],
            ['table' => 'packing_cartons', 'label' => 'Packing cartons', 'group' => 'Orders & Deliveries', 'query' => $all('packing_cartons')],
            ['table' => 'packing_lists', 'label' => 'Packing lists', 'group' => 'Orders & Deliveries', 'query' => $all('packing_lists')],
            ['table' => 'delivery_boxes', 'label' => 'Delivery boxes', 'group' => 'Orders & Deliveries', 'query' => $all('delivery_boxes')],
            ['table' => 'deliveries', 'label' => 'Deliveries', 'group' => 'Orders & Deliveries', 'query' => $all('deliveries')],
            ['table' => 'mprocess', 'label' => 'Material processing records', 'group' => 'PTC & Processing', 'query' => $all('mprocess')],
            ['table' => 'igroup_items', 'label' => 'Issuance group lines', 'group' => 'PTC & Processing', 'query' => $all('igroup_items')],
            ['table' => 'igroups', 'label' => 'Issuance groups', 'group' => 'PTC & Processing', 'query' => $all('igroups')],
            ['table' => 'stock_items', 'label' => 'Stock lines (opening stock, issues, receipts, PTC, deliveries, wages)', 'group' => 'Inventory', 'query' => $all('stock_items')],
            [
                'table' => 'stocks', 'label' => 'Stock documents (issuances, receipts, PTCs, releases, machine material) - system rows 0 and 1 kept',
                'group' => 'Inventory', 'query' => fn () => DB::table('stocks')->whereNotIn('stock_id', self::SYSTEM_STOCK_IDS),
            ],
            ['table' => 'stock_batches', 'label' => 'Stock batches (opening, purchase, PTC, splits)', 'group' => 'Inventory', 'query' => $all('stock_batches')],
            ['table' => 'purchase_items', 'label' => 'Purchase order lines', 'group' => 'Purchasing', 'query' => $all('purchase_items')],
            ['table' => 'purchases', 'label' => 'Purchase orders', 'group' => 'Purchasing', 'query' => $all('purchases')],
            ['table' => 'order_items', 'label' => 'Order lines', 'group' => 'Orders & Deliveries', 'query' => $all('order_items')],
            ['table' => 'orders', 'label' => 'Orders', 'group' => 'Orders & Deliveries', 'query' => $all('orders')],
            ['table' => 'payroll_items', 'label' => 'Payroll lines', 'group' => 'Payroll', 'query' => $all('payroll_items')],
            ['table' => 'payrolls', 'label' => 'Monthly payrolls', 'group' => 'Payroll', 'query' => $all('payrolls')],
            [
                'table' => 'images', 'label' => 'Attachments of transactional records (and their files)', 'group' => 'Finance',
                'query' => fn () => DB::table('images')->whereIn('table_name', self::TRANSACTIONAL_IMAGE_TABLES),
            ],
            [
                'table' => 'transactions', 'label' => 'Financial transactions (payments, receipts, expenses, JVs, wages, salaries, loans, transfers, adjustments)',
                'group' => 'Finance', 'query' => fn () => DB::table('transactions')->where('transaction_type', '!=', 'openingBalance'),
            ],
            ['table' => 'assets', 'label' => 'Asset transactions', 'group' => 'Finance', 'query' => $all('assets')],
        ];
    }

    /** Opening-balance entries that still carry an amount (reset to zero, rows kept). */
    private function nonZeroOpeningBalances(): Builder
    {
        return DB::table('transactions')->where('transaction_type', 'openingBalance')
            ->where(fn ($q) => $q->where('debit', '!=', 0)->orWhere('credit', '!=', 0)
                ->orWhere('cc_amount', '!=', 0)->orWhere('fb_charges', '!=', 0)->orWhere('db_charges', '!=', 0));
    }

    /**
     * Dry run: what the reset would remove, without changing anything.
     */
    public function preview(): array
    {
        $steps = array_map(fn ($s) => [
            'table' => $s['table'], 'label' => $s['label'], 'group' => $s['group'], 'count' => ($s['query'])()->count(),
        ], $this->steps());

        $opening = DB::table('transactions')->where('transaction_type', 'openingBalance');

        return [
            'steps' => $steps,
            'total' => array_sum(array_column($steps, 'count')),
            'opening_balances' => [
                'rows' => (clone $opening)->count(),
                'non_zero' => $this->nonZeroOpeningBalances()->count(),
                'by_party' => (clone $opening)->selectRaw('transaction_to, COUNT(*) AS n')->groupBy('transaction_to')->pluck('n', 'transaction_to')->all(),
            ],
            'preserved' => $this->preservedCounts(),
            'opening_stock_lines' => DB::table('stock_items')->where('stock_id', 1)->count(),
        ];
    }

    public function preservedCounts(): array
    {
        $counts = [];
        foreach (self::PRESERVED as $table => $label) {
            $counts[$table] = ['label' => $label, 'count' => DB::table($table)->count()];
        }
        $counts['images_master'] = [
            'label' => 'Images of master records (products, materials, people...)',
            'count' => DB::table('images')->whereNotIn('table_name', self::TRANSACTIONAL_IMAGE_TABLES)->count(),
        ];
        $counts['stocks_system'] = [
            'label' => 'System stock headers (rows 0 and 1, kept empty)',
            'count' => DB::table('stocks')->whereIn('stock_id', self::SYSTEM_STOCK_IDS)->count(),
        ];
        $counts['opening_balance_rows'] = [
            'label' => 'Opening-balance entries of parties (rows kept)',
            'count' => DB::table('transactions')->where('transaction_type', 'openingBalance')->count(),
        ];

        return $counts;
    }

    /**
     * Performs the reset. Returns the per-table counts; throws (nothing changed) on any failure.
     */
    public function run(bool $resetOpeningBalances, int $userId): array
    {
        $started = microtime(true);
        $before = $this->preservedCounts();
        $files = [];

        $result = DB::transaction(function () use ($resetOpeningBalances, $before, &$files) {
            // Attachment files are removed only after the commit, so a rollback leaves them in place
            $images = app(ImageRepository::class);
            foreach (Image::whereIn('table_name', self::TRANSACTIONAL_IMAGE_TABLES)->get() as $image) {
                $folder = $images->folderFor($image);
                if ($folder && $image->image !== '' && basename($image->image) === $image->image) {
                    $files[] = public_path('resources/'.$folder.'/'.$image->image);
                }
            }

            // Child batches reference their parent (RESTRICT): detach before deleting
            DB::table('stock_batches')->whereNotNull('parent_batch_id')->update(['parent_batch_id' => null]);

            $deleted = [];
            foreach ($this->steps() as $step) {
                $deleted[$step['table']] = ($step['query'])()->delete();
            }

            $zeroed = 0;
            if ($resetOpeningBalances) {
                foreach (['debit', 'credit', 'cc_amount', 'fb_charges', 'db_charges'] as $column) {
                    $zeroed += DB::table('transactions')->where('transaction_type', 'openingBalance')
                        ->whereNotNull($column)->where($column, '!=', 0)->update([$column => 0]);
                }
            }

            $this->verify($before, $resetOpeningBalances);

            return ['deleted' => $deleted, 'opening_balance_amounts_zeroed' => $zeroed];
        });

        $removedFiles = 0;
        foreach (array_unique($files) as $path) {
            if (is_file($path) && @unlink($path)) {
                $removedFiles++;
            }
        }

        $result['files_removed'] = $removedFiles;
        $result['total_deleted'] = array_sum($result['deleted']);
        $result['seconds'] = round(microtime(true) - $started, 2);

        // Counts only: no record contents are logged
        Log::info('Transactional data reset completed', ['user_id' => $userId] + $result);
        activity('data-reset')->causedBy($userId)->withProperties($result)
            ->log('Test / transactional data reset ('.$result['total_deleted'].' records deleted)');

        return $result;
    }

    /**
     * Post-cleanup checks run inside the transaction: master data untouched, transactional data gone.
     */
    private function verify(array $before, bool $resetOpeningBalances): void
    {
        $problems = [];

        foreach ($this->preservedCounts() as $table => $row) {
            if ($row['count'] !== $before[$table]['count']) {
                $problems[] = "{$row['label']}: {$before[$table]['count']} before, {$row['count']} after";
            }
        }

        foreach ($this->steps() as $step) {
            if (($left = ($step['query'])()->count()) > 0) {
                $problems[] = "{$step['table']}: {$left} rows left";
            }
        }

        if ($resetOpeningBalances && $this->nonZeroOpeningBalances()->count() > 0) {
            $problems[] = 'opening balances still carry amounts';
        }

        if ($problems) {
            throw new RuntimeException('Verification failed, reset rolled back: '.implode('; ', $problems));
        }
    }
}
