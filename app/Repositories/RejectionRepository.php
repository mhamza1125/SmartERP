<?php

namespace App\Repositories;

use App\Models\Stock;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Rejection stock by the stage it was rejected at.
 *
 * Rejected pieces are product stock at the Rejection stage (Stock::STAGE_REJECTED). Each such line
 * carries stock_items.rejected_stage_id = the work stage the pieces were rejected at (Cutting,
 * Printing...); 0 / NULL = not recorded (lines saved before this was tracked, or movements that do
 * not say). A "bucket" is the balance of one product size + rejected-at stage.
 *
 * Movements that take rejection stock out without saying which stage (older lines, other forms)
 * are taken from the recorded stages, oldest stage id first, so the buckets always add up to the
 * Rejection stage total of the stock page.
 */
class RejectionRepository implements GlobalInterface
{
    public const NOT_RECORDED = 0;

    public function all()
    {
        return $this->generalBuckets();
    }

    public function get($id)
    {
        return $this->generalBuckets([(int) $id])[(int) $id] ?? [];
    }

    public function store(array $data)
    {
        throw new \LogicException('Rejection stock is written by the stock documents, not stored directly.');
    }

    public function update($id, array $data)
    {
        throw new \LogicException('Rejection stock is written by the stock documents, not updated directly.');
    }

    public function delete($id)
    {
        throw new \LogicException('Rejection stock is written by the stock documents, not deleted directly.');
    }

    /**
     * General (non-PTC) rejection stock: [product_type_id => [rejected_stage_id (0 = not recorded) => qty]].
     *
     * @param  array<int>|null  $productTypeIds
     * @param  array<int>  $excludeStockIds  stock documents left out (a document being edited)
     */
    public function generalBuckets(?array $productTypeIds = null, array $excludeStockIds = []): array
    {
        $rows = DB::table('stock_items')
            ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->where('stock_items.stage_id', Stock::STAGE_REJECTED)
            ->where('stock_items.ptc_virtual', 0)
            ->where(fn ($q) => StockBatchRepository::whereProductLine($q))
            ->when($excludeStockIds, fn ($q) => $q->whereNotIn('stock_items.stock_id', $excludeStockIds))
            ->when($productTypeIds !== null, fn ($q) => $q->whereIn(DB::raw('COALESCE(stock_items.component_product_type_id, stock_items.product_type_id)'), $productTypeIds ?: [0]))
            ->selectRaw('COALESCE(stock_items.component_product_type_id, stock_items.product_type_id) as product_type_id')
            ->selectRaw('COALESCE(stock_items.rejected_stage_id, 0) as origin')
            ->selectRaw('SUM(CASE WHEN stocks.stock_type = 1 THEN stock_items.quantity ELSE -stock_items.quantity END) as qty')
            ->groupBy(DB::raw('COALESCE(stock_items.component_product_type_id, stock_items.product_type_id)'), DB::raw('COALESCE(stock_items.rejected_stage_id, 0)'))
            ->get();

        return $this->settle($rows);
    }

    /**
     * Rejection stock still inside one PTC (received into Rejection, not yet transferred or released):
     * [product_type_id => [rejected_stage_id => qty]].
     */
    public function ptcBuckets(int $ptcId): array
    {
        $released = Stock::STATUS_PTC_RELEASED;
        $rows = DB::table('stock_items')
            ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->where(fn ($q) => $q->where('stocks.ptc_id', $ptcId)->orWhere('stocks.stock_id', $ptcId))
            ->where('stock_items.stage_id', Stock::STAGE_REJECTED)
            ->where('stock_items.material_id', 0)
            ->whereRaw('COALESCE(stock_items.component_product_type_id, 0) = 0')
            ->where(fn ($q) => $q->where('stock_items.ptc_virtual', 1)->orWhere('stocks.stock_status', $released))
            ->select('stock_items.product_type_id')
            ->selectRaw('COALESCE(stock_items.rejected_stage_id, 0) as origin')
            ->selectRaw("SUM(CASE
                WHEN stock_items.ptc_virtual = 1 AND stocks.stock_type = 1 THEN stock_items.quantity
                WHEN stock_items.ptc_virtual = 1 AND stocks.stock_type = 2 THEN -stock_items.quantity
                WHEN stocks.stock_status = {$released} THEN -stock_items.quantity
                ELSE 0 END) as qty")
            ->groupBy('stock_items.product_type_id', DB::raw('COALESCE(stock_items.rejected_stage_id, 0)'))
            ->get();

        return $this->settle($rows);
    }

    /**
     * Splits a quantity taken out of rejection stock over the buckets: recorded stages first
     * (lowest stage id first), then "not recorded". Anything beyond the buckets goes to not recorded.
     *
     * @param  array<int, float>  $buckets  rejected_stage_id => qty
     * @return array<int, float> rejected_stage_id => qty
     */
    public function split(array $buckets, float $quantity): array
    {
        $order = collect($buckets)->keys()->sortBy(fn ($origin) => $origin === self::NOT_RECORDED ? PHP_INT_MAX : $origin);
        $parts = [];
        foreach ($order as $origin) {
            if ($quantity <= 0) {
                break;
            }
            $take = min($quantity, (float) $buckets[$origin]);
            if ($take > 0) {
                $parts[$origin] = $take;
                $quantity = round($quantity - $take, 4);
            }
        }
        if ($quantity > 0) {
            $parts[self::NOT_RECORDED] = ($parts[self::NOT_RECORDED] ?? 0) + $quantity;
        }

        return $parts;
    }

    /**
     * Refuses taking more rejection stock rejected at $origin than general stock holds.
     *
     * @return string|null error message
     */
    public function shortfall(int $productTypeId, int $origin, float $quantity, array $excludeStockIds = []): ?string
    {
        $available = (float) ($this->generalBuckets([$productTypeId], $excludeStockIds)[$productTypeId][$origin] ?? 0);
        if ($quantity > $available + 0.0001) {
            $label = $origin === self::NOT_RECORDED ? 'rejection (stage not recorded)' : $this->stageName($origin).' rejection';

            return 'Only '.rtrim(rtrim(number_format($available, 2), '0'), '.').' pcs of '.$label.' stock is available.';
        }

        return null;
    }

    /**
     * Rejection stock per product size with stage names, for the stock page:
     * [product_type_id => Collection of (origin, stage_name, quantity)].
     */
    public function byStage(?array $productTypeIds = null): array
    {
        $buckets = $this->generalBuckets($productTypeIds);
        $names = $this->stageNames(collect($buckets)->flatMap(fn ($b) => array_keys($b))->all());

        return collect($buckets)->map(fn ($origins) => collect($origins)
            ->filter(fn ($qty) => abs($qty) > 0.0001)
            ->map(fn ($qty, $origin) => (object) [
                'origin' => (int) $origin,
                'stage_name' => $origin ? ($names[$origin] ?? 'Stage #'.$origin) : 'Not recorded',
                'quantity' => round((float) $qty, 2),
            ])->values())->all();
    }

    /** Stage heads a product can be rejected at (its own stages), in the product's order. */
    public function rejectableStages(int $productTypeId): Collection
    {
        $stageIds = DB::table('product_types')->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->where('product_types.product_type_id', $productTypeId)->value('products.stage_ids');

        return app(HeadRepository::class)->getStage((string) $stageIds)
            ->reject(fn ($stage) => (int) $stage->head_id === Stock::STAGE_REJECTED)->values();
    }

    /**
     * rejectableStages() for many product sizes in one query: [product_type_id => [[id, name], ...]].
     *
     * @param  iterable  $productTypes  rows with product_type_id and stage_ids (products.stage_ids)
     */
    public function stagesForTypes(iterable $productTypes): array
    {
        $productTypes = collect($productTypes);
        $ids = $productTypes->flatMap(fn ($row) => explode('|', (string) $row->stage_ids))->filter()->unique()->all();
        $names = $this->stageNames($ids);

        return $productTypes->mapWithKeys(fn ($row) => [$row->product_type_id => collect(explode('|', (string) $row->stage_ids))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0 && $id !== Stock::STAGE_REJECTED && isset($names[$id]))
            ->unique()
            ->map(fn ($id) => ['id' => $id, 'name' => $names[$id]])
            ->values()->all()])->all();
    }

    public function stageName(int $stageId): string
    {
        return $this->stageNames([$stageId])[$stageId] ?? 'Stage #'.$stageId;
    }

    private function stageNames(array $ids): array
    {
        return DB::table('heads')->whereIn('head_id', array_filter($ids))->pluck('name', 'head_id')->all();
    }

    /**
     * Raw rows (product_type_id, origin, qty) -> buckets where unrecorded movements are settled:
     * a negative bucket takes from the others (a recorded stage from "not recorded" first,
     * "not recorded" from the recorded stages, lowest stage id first).
     */
    private function settle($rows): array
    {
        $buckets = [];
        foreach ($rows as $row) {
            $buckets[(int) $row->product_type_id][(int) $row->origin] = round((float) $row->qty, 4);
        }

        foreach ($buckets as $productTypeId => $origins) {
            ksort($origins);
            foreach (array_keys($origins) as $origin) {
                if ($origins[$origin] >= 0) {
                    continue;
                }
                $need = -$origins[$origin];
                $origins[$origin] = 0;
                $donors = array_keys($origins);
                usort($donors, fn ($a, $b) => $origin !== self::NOT_RECORDED
                    ? (($a === self::NOT_RECORDED ? -1 : $a) <=> ($b === self::NOT_RECORDED ? -1 : $b))
                    : ($a <=> $b));
                foreach ($donors as $donor) {
                    if ($need <= 0 || $donor === $origin || $origins[$donor] <= 0) {
                        continue;
                    }
                    $take = min($need, $origins[$donor]);
                    $origins[$donor] = round($origins[$donor] - $take, 4);
                    $need = round($need - $take, 4);
                }
                if ($need > 0) {
                    $origins[$origin] = -$need; // more taken out than ever came in (shown as negative)
                }
            }
            $buckets[$productTypeId] = $origins;
        }

        return $buckets;
    }
}
