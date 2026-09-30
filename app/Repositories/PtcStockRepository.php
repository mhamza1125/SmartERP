<?php

namespace App\Repositories;

use App\Exceptions\PtcStockException;
use App\Models\Stock;
use App\Models\StockItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * PTC virtual stock and issuance/receiving balances.
 *
 * Products received back against a PTC issuance stay inside that PTC
 * (stock_items.ptc_virtual = 1) and never enter general stock on their own.
 * From there a quantity is either:
 *   - transferred to another stage: a PTC issuance line with ptc_virtual = 1, or
 *   - released to general stock: a stock_type = 1 record with
 *     stock_status = Stock::STATUS_PTC_RELEASED whose items are ordinary
 *     (ptc_virtual = 0) stock-in lines, counted once by the general stock queries.
 *
 * available(product, stage) = received - transferred - released
 *
 * Materials and components returned on a receiving go back to general stock
 * (ptc_virtual = 0), as before; whatever is not returned is consumed.
 */
class PtcStockRepository implements GlobalInterface
{
    private const EPSILON = 0.0001;

    protected $stockRepository;

    protected $stockItemRepository;

    protected $productCostRepository;

    protected $headRepository;

    public function __construct(
        StockRepository $stockRepository,
        StockItemRepository $stockItemRepository,
        ProductCostRepository $productCostRepository,
        HeadRepository $headRepository
    ) {
        $this->stockRepository = $stockRepository;
        $this->stockItemRepository = $stockItemRepository;
        $this->productCostRepository = $productCostRepository;
        $this->headRepository = $headRepository;
    }

    public function all()
    {
        return Stock::where('is_ptc_master', 1)->get();
    }

    public function get($id)
    {
        return $this->virtualStock($id);
    }

    public function store(array $data)
    {
        return $this->release($data['ptc_id'], $data['lines'], $data['stock_date'], $data['description'] ?? null);
    }

    public function update($id, array $data) {}

    public function delete($id) {}

    // ==================================================
    // ================ Balances ========================
    // ==================================================

    /**
     * Issuance records of a PTC (the master is the initial issuance), oldest first.
     * Keyed by stock_id; each row carries its display sequence (I001, I002, ...).
     */
    public function issuances($ptcId): Collection
    {
        $issuances = Stock::where(function ($query) use ($ptcId) {
            $query->where(function ($q) use ($ptcId) {
                $q->where('stock_id', $ptcId)->where('is_ptc_master', 1);
            })->orWhere(function ($q) use ($ptcId) {
                $q->where('ptc_id', $ptcId)->where('stock_type', 2);
            });
        })
            ->orderBy('stock_id')
            ->get(['stock_id', 'stock_no', 'is_ptc_master', 'issue_for', 'current_stage_id', 'stock_date']);

        return $issuances->values()->each(function ($issuance, $index) {
            $issuance->seq_no = str_pad($index + 1, 3, '0', STR_PAD_LEFT);
        })->keyBy('stock_id');
    }

    /**
     * Issued vs received quantities for every issuance of a PTC.
     *
     * Product lines (finished/semi-finished goods) must be received back in full
     * (any stage, including Rejected) - the difference is "outstanding".
     * Materials and components are inputs: what is not returned is consumed.
     */
    public function issuanceBalances($ptcId): Collection
    {
        $issuances = $this->issuances($ptcId);
        if ($issuances->isEmpty()) {
            return collect();
        }
        $issuanceIds = $issuances->keys()->all();

        $issued = DB::table('stock_items')
            ->whereIn('stock_id', $issuanceIds)
            ->select(
                'stock_id as issuance_id',
                'product_type_id',
                'material_id',
                DB::raw('COALESCE(component_product_type_id, 0) as component_id'),
                DB::raw('SUM(quantity) as qty')
            )
            ->groupBy('stock_id', 'product_type_id', 'material_id', DB::raw('COALESCE(component_product_type_id, 0)'))
            ->get();

        $received = DB::table('stock_items')
            ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->whereIn('stocks.issue_id', $issuanceIds)
            ->where('stocks.ptc_id', $ptcId)
            ->where('stocks.stock_type', 1)
            ->where('stocks.stock_status', '!=', Stock::STATUS_PTC_RELEASED)
            ->select(
                'stocks.issue_id as issuance_id',
                'stock_items.product_type_id',
                'stock_items.material_id',
                DB::raw('COALESCE(stock_items.component_product_type_id, 0) as component_id'),
                DB::raw('SUM(stock_items.quantity) as qty')
            )
            ->groupBy('stocks.issue_id', 'stock_items.product_type_id', 'stock_items.material_id', DB::raw('COALESCE(stock_items.component_product_type_id, 0)'))
            ->get();

        $balances = [];
        foreach ($issuances as $id => $issuance) {
            $balances[$id] = [
                'issuance_id' => $id,
                'seq_no' => $issuance->seq_no,
                'issue_for' => $issuance->issue_for,
                'products' => [],
                'materials' => [],
                'components' => [],
            ];
        }

        foreach (['issued' => $issued, 'received' => $received] as $column => $rows) {
            foreach ($rows as $row) {
                if ($row->component_id > 0) {
                    $group = 'components';
                    $key = $row->component_id;
                } elseif ($row->material_id > 0) {
                    $group = 'materials';
                    $key = $row->material_id;
                } else {
                    $group = 'products';
                    $key = $row->product_type_id;
                }
                $line = $balances[$row->issuance_id][$group][$key] ?? ['issued' => 0, 'received' => 0];
                $line[$column] += (float) $row->qty;
                $balances[$row->issuance_id][$group][$key] = $line;
            }
        }

        foreach ($balances as &$balance) {
            $outstanding = 0;
            $produced = 0;
            foreach ($balance['products'] as &$line) {
                $line['outstanding'] = max(0, $this->round($line['issued'] - $line['received']));
                $outstanding += $line['outstanding'];
                $produced += $line['received'];
            }
            unset($line);
            foreach (['materials', 'components'] as $group) {
                foreach ($balance[$group] as &$line) {
                    $line['returnable'] = max(0, $this->round($line['issued'] - $line['received']));
                }
                unset($line);
            }

            $hasProducts = collect($balance['products'])->contains(fn ($line) => $line['issued'] > 0);
            $receivedAny = collect(['products', 'materials', 'components'])
                ->contains(fn ($group) => collect($balance[$group])->contains(fn ($line) => $line['received'] > 0));

            $balance['has_products'] = $hasProducts;
            $balance['outstanding'] = $this->round($outstanding);
            $balance['produced'] = $this->round($produced);
            $balance['received_any'] = $receivedAny;
            $balance['status'] = ! $receivedAny ? 'pending' : ($outstanding > 0 ? 'partial' : 'received');
            // Product lines are closed once fully received; material-only issuances
            // (e.g. cloth to be cut) can keep producing output, and unused
            // materials/components can still be returned.
            $balance['can_receive'] = $outstanding > 0
                || ! $hasProducts
                || collect($balance['materials'])->contains(fn ($line) => $line['returnable'] > 0)
                || collect($balance['components'])->contains(fn ($line) => $line['returnable'] > 0);
        }
        unset($balance);

        return collect($balances);
    }

    /**
     * PTC virtual stock per product and stage.
     */
    public function virtualStock($ptcId): Collection
    {
        $released = Stock::STATUS_PTC_RELEASED;

        $rows = DB::table('stock_items')
            ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->leftJoin('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
            ->leftJoin('products', 'products.product_id', '=', 'product_types.product_id')
            ->leftJoin('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
            ->leftJoin('heads as sthead', 'sthead.head_id', '=', 'stock_items.stage_id')
            ->where('stocks.ptc_id', $ptcId)
            ->where('stock_items.material_id', 0)
            ->whereRaw('COALESCE(stock_items.component_product_type_id, 0) = 0')
            ->where(function ($query) use ($released) {
                $query->where('stock_items.ptc_virtual', 1)
                    ->orWhere('stocks.stock_status', $released);
            })
            ->select(
                'stock_items.product_type_id',
                'stock_items.stage_id',
                'products.article_no',
                'products.name as product_name',
                'shead.name as size_name',
                'sthead.name as stage_name',
                DB::raw('SUM(CASE WHEN stocks.stock_type = 1 AND stock_items.ptc_virtual = 1 THEN stock_items.quantity ELSE 0 END) as received'),
                DB::raw('SUM(CASE WHEN stocks.stock_type = 2 AND stock_items.ptc_virtual = 1 THEN stock_items.quantity ELSE 0 END) as transferred'),
                DB::raw("SUM(CASE WHEN stocks.stock_status = {$released} AND stock_items.ptc_virtual = 0 THEN stock_items.quantity ELSE 0 END) as released")
            )
            ->groupBy('stock_items.product_type_id', 'stock_items.stage_id', 'products.article_no', 'products.name', 'shead.name', 'sthead.name')
            ->orderBy('stock_items.product_type_id')
            ->orderBy('stock_items.stage_id')
            ->get();

        return $rows->map(function ($row) {
            $row->received = $this->round($row->received);
            $row->transferred = $this->round($row->transferred);
            $row->released = $this->round($row->released);
            $row->available = $this->round($row->received - $row->transferred - $row->released);

            return $row;
        });
    }

    /**
     * Available PTC stock keyed by "{product_type_id}_{stage_id}".
     */
    public function virtualAvailableMap($ptcId): array
    {
        return $this->virtualStock($ptcId)
            ->mapWithKeys(fn ($row) => [$row->product_type_id.'_'.$row->stage_id => $row->available])
            ->all();
    }

    /**
     * Materials and components issued into the PTC vs returned, with the consumed difference.
     */
    public function consumption($ptcId): array
    {
        $issuanceIds = $this->issuances($ptcId)->keys()->all();
        if (empty($issuanceIds)) {
            return ['materials' => collect(), 'components' => collect()];
        }

        $materials = DB::table('stock_items')
            ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->join('materials', 'materials.material_id', '=', 'stock_items.material_id')
            ->leftJoin('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
            ->where('stock_items.material_id', '>', 0)
            ->where(function ($query) use ($issuanceIds) {
                $query->whereIn('stocks.stock_id', $issuanceIds)
                    ->orWhere(function ($q) use ($issuanceIds) {
                        $q->whereIn('stocks.issue_id', $issuanceIds)
                            ->where('stocks.stock_type', 1)
                            ->where('stocks.stock_status', '!=', Stock::STATUS_PTC_RELEASED);
                    });
            })
            ->select(
                'materials.material_id',
                'materials.name',
                'uhead.name as unit',
                DB::raw('SUM(CASE WHEN stocks.stock_type = 2 OR stocks.is_ptc_master = 1 THEN stock_items.quantity ELSE 0 END) as issued'),
                DB::raw('SUM(CASE WHEN stocks.stock_type = 1 THEN stock_items.quantity ELSE 0 END) as returned')
            )
            ->groupBy('materials.material_id', 'materials.name', 'uhead.name')
            ->get();

        $components = DB::table('stock_items')
            ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->join('product_types as cpt', 'cpt.product_type_id', '=', 'stock_items.component_product_type_id')
            ->join('products as cp', 'cp.product_id', '=', 'cpt.product_id')
            ->leftJoin('heads as csize', 'csize.head_id', '=', 'cpt.size_id')
            ->where('stock_items.component_product_type_id', '>', 0)
            ->where(function ($query) use ($issuanceIds) {
                $query->whereIn('stocks.stock_id', $issuanceIds)
                    ->orWhere(function ($q) use ($issuanceIds) {
                        $q->whereIn('stocks.issue_id', $issuanceIds)
                            ->where('stocks.stock_type', 1)
                            ->where('stocks.stock_status', '!=', Stock::STATUS_PTC_RELEASED);
                    });
            })
            ->select(
                'stock_items.component_product_type_id',
                'cp.article_no',
                'cp.name',
                'csize.name as size_name',
                DB::raw('SUM(CASE WHEN stocks.stock_type = 2 OR stocks.is_ptc_master = 1 THEN stock_items.quantity ELSE 0 END) as issued'),
                DB::raw('SUM(CASE WHEN stocks.stock_type = 1 THEN stock_items.quantity ELSE 0 END) as returned')
            )
            ->groupBy('stock_items.component_product_type_id', 'cp.article_no', 'cp.name', 'csize.name')
            ->get();

        $withConsumed = function ($row) {
            $row->issued = $this->round($row->issued);
            $row->returned = $this->round($row->returned);
            $row->consumed = $this->round($row->issued - $row->returned);

            return $row;
        };

        return [
            'materials' => $materials->map($withConsumed),
            'components' => $components->map($withConsumed),
        ];
    }

    /**
     * The product's stages in order (heads with head_id, name).
     */
    public function productStages($ptcId): Collection
    {
        $stageIds = DB::table('product_types')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->where('product_types.product_type_id', $this->ptcProductTypeId($ptcId))
            ->value('products.stage_ids');

        return $stageIds ? $this->headRepository->getByIds(explode('|', $stageIds))->values() : collect();
    }

    /**
     * Last stage the PTC is planned to reach. PTCs created before end_stage_id
     * was stored run to the product's last stage.
     */
    public function endStageId(Stock $ptc, Collection $stages)
    {
        return $ptc->end_stage_id ?: optional($stages->last())->head_id;
    }

    /**
     * Actual production status of each product stage, derived from the
     * issuances made for that stage and what was received back against them.
     * Closing a PTC does not change any of this.
     *
     * Status: outside (before the PTC's start stage or after its end stage),
     * not_started, in_progress (issued, nothing received), partial (some product
     * still outstanding), completed (everything issued for the stage received back).
     */
    public function stageSummary(Stock $ptc, Collection $stages, ?Collection $balances = null): array
    {
        $balances = $balances ?? $this->issuanceBalances($ptc->stock_id);
        $stages = $stages->values();

        $startIndex = $stages->search(fn ($stage) => $stage->head_id == $ptc->issue_for);
        $startIndex = $startIndex === false ? 0 : $startIndex;
        $endStageId = $this->endStageId($ptc, $stages);
        $endIndex = $stages->search(fn ($stage) => $stage->head_id == $endStageId);
        $endIndex = $endIndex === false ? $stages->count() - 1 : $endIndex;
        $isClosed = $ptc->stock_status != Stock::STATUS_PTC_IN_PROGRESS;

        $summary = [];
        $endedAt = null;
        foreach ($stages as $index => $stage) {
            $stageBalances = $balances->filter(fn ($balance) => $balance['issue_for'] == $stage->head_id);
            $issued = $stageBalances->sum(fn ($balance) => collect($balance['products'])->sum('issued'));
            $received = $stageBalances->sum('produced');
            $outstanding = $stageBalances->sum('outstanding');
            $receivedAny = $stageBalances->contains(fn ($balance) => $balance['received_any']);

            if ($stageBalances->isEmpty()) {
                $status = ($index < $startIndex || $index > $endIndex) ? 'outside' : 'not_started';
            } elseif (! $receivedAny) {
                $status = 'in_progress';
            } elseif ($outstanding > 0) {
                $status = 'partial';
            } else {
                $status = 'completed';
            }

            if ($received > 0) {
                $endedAt = $stage;
            }

            $summary[] = (object) [
                'head_id' => $stage->head_id,
                'name' => $stage->name,
                'status' => $status,
                'issuance_count' => $stageBalances->count(),
                'issued' => $this->round($issued),
                'received' => $this->round($received),
                'outstanding' => $this->round($outstanding),
                'is_current' => ! $isClosed && $ptc->current_stage_id == $stage->head_id,
                'is_end' => $stage->head_id == $endStageId,
            ];
        }

        $endStage = collect($summary)->firstWhere('is_end', true);

        return [
            'stages' => $summary,
            'ended_at' => $endedAt,
            'end_stage' => $endStage,
            // The PTC is complete once everything issued for its end stage is received back
            'can_complete' => $endStage && $endStage->status === 'completed',
        ];
    }

    /**
     * Product quantity still outstanding on the issuances made for a stage.
     */
    public function outstandingForStage($ptcId, $stageId): float
    {
        return $this->round($this->issuanceBalances($ptcId)
            ->filter(fn ($balance) => $balance['issue_for'] == $stageId)
            ->sum('outstanding'));
    }

    /**
     * Reasons a PTC cannot be closed yet: product still with workers, or PTC stock
     * that has not been transferred or released.
     */
    public function closeBlockers($ptcId): array
    {
        return [
            'outstanding' => $this->issuanceBalances($ptcId)->filter(fn ($balance) => $balance['outstanding'] > 0)->values(),
            'virtual' => $this->virtualStock($ptcId)->filter(fn ($row) => $row->available > self::EPSILON)->values(),
        ];
    }

    // ==================================================
    // ================ Movements =======================
    // ==================================================

    /**
     * Record a PTC issuance. Product lines with source 'ptc' are transferred out of
     * this PTC's virtual stock; every other line is issued from general stock.
     *
     * @param  array  $lines  [product_type_id, material_id, component_id, stage_id, quantity, source]
     */
    public function issue($ptcId, array $header, array $lines)
    {
        return DB::transaction(function () use ($ptcId, $header, $lines) {
            $ptc = $this->lockOpenPtc($ptcId);
            $ptcProductTypeId = $this->ptcProductTypeId($ptcId);
            $lines = $this->normalizeLines($lines, $ptcProductTypeId, $ptc->current_stage_id);

            if (empty($lines)) {
                throw new PtcStockException('No items to issue.');
            }

            $this->assertVirtualAvailable($ptcId, collect($lines)->where('ptc_virtual', 1));

            $issueId = $this->stockRepository->storePtcIssuance(array_merge($header, [
                'ptc_id' => $ptcId,
                'order_id' => $ptc->order_id,
                'stock_type' => 2,
                'stock_status' => Stock::STATUS_PTC_IN_PROGRESS,
                'current_stage_id' => $ptc->current_stage_id,
                'issue_for' => $header['issue_for'] ?? $ptc->current_stage_id,
            ]));

            foreach ($lines as $line) {
                $this->stockItemRepository->store([
                    'stock_id' => $issueId,
                    'product_type_id' => $line['product_type_id'],
                    'material_id' => $line['material_id'],
                    'component_product_type_id' => $line['component_id'],
                    'quantity' => $line['quantity'],
                    'stage_id' => $line['stage_id'],
                    'ptc_virtual' => $line['ptc_virtual'],
                    'work_logs' => '0',
                    'work_wages' => '0',
                ]);
            }

            return $issueId;
        });
    }

    /**
     * Update an issuance that has not been received yet. Lines transferred from
     * PTC stock are re-checked against what is available.
     *
     * @param  array  $quantities  stock_item_id => new quantity
     */
    public function updateIssuance($ptcId, $issuanceId, array $header, array $quantities)
    {
        DB::transaction(function () use ($ptcId, $issuanceId, $header, $quantities) {
            $this->lockOpenPtc($ptcId);

            $issuance = Stock::where('stock_id', $issuanceId)->where('ptc_id', $ptcId)->where('stock_type', 2)->first();
            if (! $issuance) {
                throw new PtcStockException('Issuance record not found for this PTC.');
            }
            if (Stock::where('issue_id', $issuanceId)->where('stock_type', 1)->exists()) {
                throw new PtcStockException('Cannot edit issuance after receiving has occurred.');
            }

            $items = StockItem::where('stock_id', $issuanceId)->get()->keyBy('stock_item_id');
            $available = $this->virtualAvailableMap($ptcId);
            $requested = [];
            foreach ($quantities as $stockItemId => $quantity) {
                $item = $items->get($stockItemId);
                if (! $item || $quantity <= 0 || ! $item->ptc_virtual) {
                    continue;
                }
                $key = $item->product_type_id.'_'.$item->stage_id;
                // The current quantity of this line is already counted as transferred out.
                $available[$key] = ($available[$key] ?? 0) + $item->quantity;
                $requested[$key] = ($requested[$key] ?? 0) + $quantity;
            }
            foreach ($requested as $key => $quantity) {
                if ($quantity > $available[$key] + self::EPSILON) {
                    throw new PtcStockException("Only {$this->round($available[$key])} available in this PTC's stock for that stage.");
                }
            }

            $issuance->update($header);
            foreach ($quantities as $stockItemId => $quantity) {
                if ($quantity > 0 && $items->has($stockItemId)) {
                    $items->get($stockItemId)->update(['quantity' => $quantity]);
                }
            }
        });
    }

    /**
     * Record a receiving against one issuance of a PTC.
     *
     * Finished/semi-finished product received is capped at what is outstanding on the
     * issuance (when the issuance included that product) and enters PTC virtual stock,
     * not general stock. Components returned are capped at what was issued; materials
     * are not capped (cut quantities vary). Both go back to general stock.
     *
     * @param  array  $lines  [product_type_id, material_id, component_id, stage_id, quantity, work_logs]
     */
    public function receive($ptcId, $issuanceId, array $header, array $lines)
    {
        return DB::transaction(function () use ($ptcId, $issuanceId, $header, $lines) {
            $ptc = $this->lockOpenPtc($ptcId);

            $balance = $this->issuanceBalances($ptcId)->get($issuanceId);
            if (! $balance) {
                throw new PtcStockException('Issuance record not found for this PTC.');
            }

            $ptcProductTypeId = $this->ptcProductTypeId($ptcId);
            $receiveStageId = $balance['issue_for'] ?: $ptc->current_stage_id;
            $lines = $this->normalizeLines($lines, $ptcProductTypeId, $receiveStageId);
            if (empty($lines)) {
                throw new PtcStockException('No items to receive.');
            }

            $this->assertReceivable($balance, $lines);

            // Components go back to the stage they were issued at, so general
            // component stock nets out on the same row.
            $componentStages = StockItem::where('stock_id', $issuanceId)
                ->where('component_product_type_id', '>', 0)
                ->pluck('stage_id', 'component_product_type_id');

            $receiveId = $this->stockRepository->storePtcReceiving(array_merge($header, [
                'issue_id' => $issuanceId,
                'ptc_id' => $ptcId,
                'order_id' => $ptc->order_id,
                'stock_type' => 1,
                'stock_status' => Stock::STATUS_COMPLETELY_RECEIVED,
                'current_stage_id' => $receiveStageId,
            ]));

            foreach ($lines as $line) {
                $isProduct = $line['material_id'] == 0 && ! $line['component_id'];
                $workLog = $line['work_logs'] ?: '0';
                $wages = '0';
                if ($workLog !== '0') {
                    $wages = $this->productCostRepository->wages($line['product_type_id'], $workLog, $header['employee_id'], $header['table_name']) ?: '0';
                }

                $this->stockItemRepository->store([
                    'stock_id' => $receiveId,
                    'product_type_id' => $line['product_type_id'],
                    'material_id' => $line['material_id'],
                    'component_product_type_id' => $line['component_id'],
                    'quantity' => $line['quantity'],
                    'stage_id' => $line['component_id'] ? ($componentStages[$line['component_id']] ?? $line['stage_id']) : $line['stage_id'],
                    'ptc_virtual' => $isProduct ? 1 : 0,
                    'work_logs' => $workLog,
                    'work_wages' => $wages,
                ]);
            }

            return $receiveId;
        });
    }

    /**
     * Release PTC virtual stock to general stock. Creates one stock-in record whose
     * items are counted by the general stock queries exactly once. Allowed on a
     * closed PTC as well, so remaining stock is never stranded.
     *
     * @param  array  $lines  [product_type_id, stage_id, quantity]
     */
    public function release($ptcId, array $lines, $stockDate, $description = null)
    {
        return DB::transaction(function () use ($ptcId, $lines, $stockDate, $description) {
            $ptc = Stock::where('stock_id', $ptcId)->where('is_ptc_master', 1)->lockForUpdate()->first();
            if (! $ptc) {
                throw new PtcStockException('PTC not found.');
            }

            $lines = collect($lines)
                ->filter(fn ($line) => (float) ($line['quantity'] ?? 0) > 0)
                ->map(fn ($line) => [
                    'product_type_id' => (int) $line['product_type_id'],
                    'stage_id' => (int) $line['stage_id'],
                    'quantity' => (float) $line['quantity'],
                ])
                ->values();
            if ($lines->isEmpty()) {
                throw new PtcStockException('Enter a quantity to release.');
            }

            $this->assertVirtualAvailable($ptcId, $lines);

            $releaseId = $this->stockRepository->store([
                'stock_no' => $this->releaseRefNo($ptc),
                'ptc_id' => $ptcId,
                'order_id' => $ptc->order_id,
                'table_name' => Stock::TABLE_PTC_RELEASE,
                'employee_id' => 0,
                'stock_type' => 1,
                'stock_date' => $stockDate,
                'stock_status' => Stock::STATUS_PTC_RELEASED,
                'current_stage_id' => $ptc->current_stage_id,
                'description' => $description,
            ]);

            foreach ($lines as $line) {
                $this->stockItemRepository->store([
                    'stock_id' => $releaseId,
                    'product_type_id' => $line['product_type_id'],
                    'material_id' => 0,
                    'component_product_type_id' => null,
                    'quantity' => $line['quantity'],
                    'stage_id' => $line['stage_id'],
                    'ptc_virtual' => 0,
                    'work_logs' => '0',
                    'work_wages' => '0',
                ]);
            }

            return $releaseId;
        });
    }

    /**
     * Finish a PTC. The outcome follows the facts: Completed when its end stage has
     * been fully received, otherwise Closed early (production stopped before the end
     * stage). Only the PTC status changes - stages keep their actual status and no
     * stock movement is created. Blocked while product is still with workers or PTC
     * stock has not been transferred/released.
     *
     * @return int the resulting status (STATUS_PTC_COMPLETED or STATUS_PTC_CLOSED)
     */
    public function finish($ptcId): int
    {
        return DB::transaction(function () use ($ptcId) {
            $ptc = $this->lockOpenPtc($ptcId);

            $blockers = $this->closeBlockers($ptcId);
            if ($blockers['outstanding']->isNotEmpty()) {
                $refs = $blockers['outstanding']->map(fn ($b) => 'I'.$b['seq_no'].' ('.$b['outstanding'].')')->implode(', ');
                throw new PtcStockException("Cannot finish - product is still outstanding on issuance {$refs}. Receive it back first (use the Rejected stage for damaged or lost pieces).");
            }
            if ($blockers['virtual']->isNotEmpty()) {
                $rows = $blockers['virtual']->map(fn ($row) => $row->available.' at '.($row->stage_name ?? 'N/A'))->implode(', ');
                throw new PtcStockException("Cannot finish - PTC stock remains ({$rows}). Transfer it to a stage or release it to general stock first.");
            }

            $summary = $this->stageSummary($ptc, $this->productStages($ptcId));
            $status = $summary['can_complete'] ? Stock::STATUS_PTC_COMPLETED : Stock::STATUS_PTC_CLOSED;

            $ptc->update(['stock_status' => $status, 'next_stage_id' => null]);

            return $status;
        });
    }

    // ==================================================
    // ================ Helpers =========================
    // ==================================================

    /**
     * Lock the PTC master row for the rest of the transaction so concurrent
     * issue/receive/release requests are validated one after another.
     */
    private function lockOpenPtc($ptcId): Stock
    {
        $ptc = Stock::where('stock_id', $ptcId)->where('is_ptc_master', 1)->lockForUpdate()->first();
        if (! $ptc) {
            throw new PtcStockException('PTC not found.');
        }
        if ($ptc->stock_status != Stock::STATUS_PTC_IN_PROGRESS) {
            throw new PtcStockException('This PTC is already completed/closed.');
        }

        return $ptc;
    }

    private function ptcProductTypeId($ptcId)
    {
        return DB::table('stock_items')
            ->where('stock_id', $ptcId)
            ->where('product_type_id', '>', 0)
            ->orderBy('stock_item_id')
            ->value('product_type_id');
    }

    /**
     * Drop empty lines and fill defaults the forms leave blank: the PTC's product type,
     * the given stage, and NULL (not 0) for "no component" - general stock queries
     * rely on component_product_type_id IS NULL for product lines.
     */
    private function normalizeLines(array $lines, $ptcProductTypeId, $defaultStageId): array
    {
        $normalized = [];
        foreach ($lines as $line) {
            $quantity = (float) ($line['quantity'] ?? 0);
            if ($quantity <= 0) {
                continue;
            }
            $materialId = (int) ($line['material_id'] ?? 0);
            $componentId = (int) ($line['component_id'] ?? 0) ?: null;
            $productTypeId = (int) ($line['product_type_id'] ?? 0) ?: $ptcProductTypeId;
            $stageId = (int) ($line['stage_id'] ?? 0) ?: $defaultStageId;
            $isProduct = $materialId === 0 && ! $componentId;

            $normalized[] = [
                'product_type_id' => $productTypeId,
                'material_id' => $materialId,
                'component_id' => $componentId,
                'stage_id' => $stageId,
                'quantity' => $quantity,
                'work_logs' => (string) ($line['work_logs'] ?? '0'),
                'ptc_virtual' => ($isProduct && ($line['source'] ?? 'general') === 'ptc') ? 1 : 0,
            ];
        }

        return $normalized;
    }

    private function assertVirtualAvailable($ptcId, Collection $lines): void
    {
        if ($lines->isEmpty()) {
            return;
        }

        $available = $this->virtualAvailableMap($ptcId);
        $requested = $lines->groupBy(fn ($line) => $line['product_type_id'].'_'.$line['stage_id'])
            ->map(fn ($group) => $group->sum('quantity'));

        foreach ($requested as $key => $quantity) {
            $have = $available[$key] ?? 0;
            if ($quantity > $have + self::EPSILON) {
                $stageName = DB::table('heads')->where('head_id', explode('_', $key)[1])->value('name') ?? 'N/A';
                throw new PtcStockException("Only {$this->round($have)} available in this PTC's stock at {$stageName}; {$this->round($quantity)} requested.");
            }
        }
    }

    private function assertReceivable(array $balance, array $lines): void
    {
        $products = [];
        $components = [];
        foreach ($lines as $line) {
            if ($line['component_id']) {
                $components[$line['component_id']] = ($components[$line['component_id']] ?? 0) + $line['quantity'];
            } elseif ($line['material_id'] > 0) {
                if (! isset($balance['materials'][$line['material_id']])) {
                    throw new PtcStockException('A material that was not issued on this issuance cannot be received against it.');
                }
            } else {
                $products[$line['product_type_id']] = ($products[$line['product_type_id']] ?? 0) + $line['quantity'];
            }
        }

        foreach ($products as $productTypeId => $quantity) {
            $issued = $balance['products'][$productTypeId]['issued'] ?? 0;
            if ($issued > 0) {
                $outstanding = $balance['products'][$productTypeId]['outstanding'];
                if ($quantity > $outstanding + self::EPSILON) {
                    throw new PtcStockException("Cannot receive {$this->round($quantity)} pieces - only {$outstanding} outstanding on issuance I{$balance['seq_no']}.");
                }
            } elseif ($balance['has_products']) {
                throw new PtcStockException('A product that was not issued on this issuance cannot be received against it.');
            }
            // Material-only issuance: the product is new output, not a return.
        }

        foreach ($components as $componentId => $quantity) {
            $returnable = $balance['components'][$componentId]['returnable'] ?? 0;
            if ($quantity > $returnable + self::EPSILON) {
                throw new PtcStockException("Cannot return {$this->round($quantity)} of a component - only {$returnable} left on issuance I{$balance['seq_no']}.");
            }
        }
    }

    /**
     * Release number {ptcNo}-REL{n}; stock_no is unique table-wide.
     */
    private function releaseRefNo(Stock $ptc): string
    {
        $seq = Stock::where('ptc_id', $ptc->stock_id)->where('stock_status', Stock::STATUS_PTC_RELEASED)->count() + 1;
        do {
            $releaseNo = $ptc->stock_no.'-REL'.$seq++;
        } while (Stock::where('stock_no', $releaseNo)->exists());

        return $releaseNo;
    }

    private function round($value): float
    {
        return round((float) $value, 4);
    }
}
