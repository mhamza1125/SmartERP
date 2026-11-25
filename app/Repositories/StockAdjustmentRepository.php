<?php

namespace App\Repositories;

use App\Exceptions\DeletionBlockedException;
use App\Models\Stock;
use App\Models\StockAdjustment;
use App\Support\DeletionImpact;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Stock adjustments: a general increase or decrease of material / product stock (count corrections,
 * damage, found stock...). One document = one direction: stocks row table_name Stock::TABLE_ADJUSTMENT,
 * stock_type 1 (increase) or 2 (decrease), stock_status Stock::STATUS_ADJUSTMENT; its stock_items are
 * the adjusted lines, so every stock page, ledger and batch figure counts them like any other movement.
 *
 * Products: any of the product's stages or Rejection (with the stage it was rejected at). An increase
 * gets its own batch (free stock); a decrease takes the chosen order's reserved stock first, then free
 * stock (batches), and from Rejection only what was rejected at the chosen stage.
 */
class StockAdjustmentRepository implements GlobalInterface
{
    public const INCREASE = 1;

    public const DECREASE = 2;

    public function all()
    {
        return StockAdjustment::query()
            ->leftJoin('orders', 'orders.order_id', '=', 'stocks.order_id')
            ->leftJoin('users', 'users.id', '=', 'stocks.created_by')
            ->select('stocks.stock_id', 'stocks.stock_no', 'stocks.stock_type', 'stocks.stock_date', 'stocks.description',
                'stocks.order_id', 'orders.job_no', 'users.name as user_name', 'stocks.created_at')
            ->selectSub(DB::table('stock_items')->whereColumn('stock_items.stock_id', 'stocks.stock_id')->selectRaw('COUNT(*)'), 'lines')
            ->selectSub(DB::table('stock_items')->whereColumn('stock_items.stock_id', 'stocks.stock_id')->selectRaw('SUM(quantity)'), 'quantity')
            ->orderBy('stocks.stock_date', 'desc')
            ->orderBy('stocks.stock_id', 'desc')
            ->get();
    }

    public function get($id)
    {
        return StockAdjustment::query()
            ->where('stocks.stock_id', $id)
            ->leftJoin('orders', 'orders.order_id', '=', 'stocks.order_id')
            ->leftJoin('users', 'users.id', '=', 'stocks.created_by')
            ->select('stocks.*', 'orders.job_no', 'users.name as user_name')
            ->firstOrFail();
    }

    /** Lines of an adjustment with item, stage and rejected-at names. */
    public function lines($id)
    {
        return DB::table('stock_items')
            ->where('stock_items.stock_id', $id)
            ->leftJoin('materials', 'materials.material_id', '=', 'stock_items.material_id')
            ->leftJoin('heads as munit', 'munit.head_id', '=', 'materials.unit_id')
            ->leftJoin('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
            ->leftJoin('products', 'products.product_id', '=', 'product_types.product_id')
            ->leftJoin('heads as size', 'size.head_id', '=', 'product_types.size_id')
            ->leftJoin('heads as punit', 'punit.head_id', '=', 'products.unit_id')
            ->leftJoin('heads as stage', 'stage.head_id', '=', 'stock_items.stage_id')
            ->leftJoin('heads as rejected', 'rejected.head_id', '=', 'stock_items.rejected_stage_id')
            ->select('stock_items.stock_item_id', 'stock_items.material_id', 'stock_items.product_type_id', 'stock_items.stage_id',
                'stock_items.rejected_stage_id', 'stock_items.quantity',
                'materials.material_no', 'materials.name as material_name', 'munit.name as material_unit',
                'products.product_id', 'products.article_no', 'products.name as product_name', 'size.name as size_name', 'punit.name as product_unit',
                'stage.name as stage_name', 'rejected.name as rejected_stage_name')
            ->orderBy('stock_items.stock_item_id')
            ->get();
    }

    public function refNo(): string
    {
        // ADJ-{YYMM}{NNN}: highest + 1 (Asia/Karachi month), locked inside the save, a preview otherwise
        return (new \App\Support\DocumentNumbers())->nextAdjustmentNo(DB::transactionLevel() === 0);
    }

    /**
     * $data: adjustment_type (1 increase | 2 decrease), stock_date, order_id (decrease: whose reserved
     * stock may be used, 0 = free stock only), description (reason), lines: [type material|product,
     * material_id, product_type_id, stage_id, rejected_stage_id, quantity].
     *
     * @throws ValidationException|\App\Exceptions\StockBatchException when stock is short (nothing is saved)
     */
    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $type = (int) $data['adjustment_type'];
            $orderId = $type === self::DECREASE ? (int) ($data['order_id'] ?? 0) : 0;
            $lines = $this->normalise($data['lines']);

            if ($type === self::DECREASE) {
                // Same rule as every material stock-out: row lock on the materials before the first read
                app(StockItemRepository::class)->lockMaterials(array_column($lines, 'material_id'));
                $this->assertRejectionAvailable($lines);
            }

            $stockId = app(StockRepository::class)->store([
                'stock_no' => $this->refNo(),
                'order_id' => $orderId,
                'table_name' => Stock::TABLE_ADJUSTMENT,
                'employee_id' => 0,
                'stock_type' => $type,
                'stock_date' => $data['stock_date'],
                'stock_status' => Stock::STATUS_ADJUSTMENT,
                'description' => $data['description'],
            ]);

            foreach ($lines as $line) {
                app(StockItemRepository::class)->store([
                    'stock_id' => $stockId,
                    'product_type_id' => $line['product_type_id'],
                    'material_id' => $line['material_id'],
                    'component_product_type_id' => null,
                    'quantity' => $line['quantity'],
                    'stage_id' => $line['stage_id'],
                    'rejected_stage_id' => $line['rejected_stage_id'],
                    'ptc_virtual' => 0,
                    'work_logs' => '0',
                    'work_wages' => '0',
                ]);
            }

            if ($type === self::DECREASE) {
                $this->assertMaterialsAvailable($lines);
                // Product lines: batches of the chosen order, then free stock; a shortage throws
                app(StockBatchRepository::class)->allocateStockOut($stockId, $orderId ?: null);
            }

            return $stockId;
        });
    }

    public function update($id, array $data)
    {
        throw new \LogicException('Stock adjustments are not edited: delete the adjustment and enter it again.');
    }

    /**
     * An increase cannot be deleted once its stock was used (stock would go below zero);
     * a decrease can always be deleted (its stock comes back).
     */
    public function deletionImpact($id): DeletionImpact
    {
        $adjustment = $this->get($id);
        $impact = DeletionImpact::for('Stock adjustment '.$adjustment->stock_no);
        if ((int) $adjustment->stock_type === self::INCREASE) {
            foreach (app(StockItemRepository::class)->stockInRemovalShortfalls([(int) $id]) as $shortfall) {
                $impact->block('added stock already used: '.$shortfall);
            }
        }
        $impact->cascadeIf(DB::table('stock_items')->where('stock_id', $id)->count(), 'adjusted line');
        $impact->note((int) $adjustment->stock_type === self::INCREASE
            ? 'The added quantity leaves stock again.'
            : 'The removed quantity comes back into stock.');

        return $impact;
    }

    public function delete($id)
    {
        $adjustment = $this->get($id);
        DB::table('stocks')->where('stock_id', $id)->lockForUpdate()->first();
        if ((int) $adjustment->stock_type === self::INCREASE) {
            $shortfalls = app(StockItemRepository::class)->stockInRemovalShortfalls([(int) $id]);
            if ($shortfalls) {
                throw new DeletionBlockedException('Stock adjustment '.$adjustment->stock_no.' cannot be deleted: '.implode('; ', $shortfalls));
            }
        }

        return app(StockRepository::class)->purge([(int) $id]);
    }

    /**
     * Form lines -> stock line values, checked: a material has no stage; a product line is at one of the
     * product's stages or Rejection (rejected_stage_id = one of its stages, NULL = not recorded).
     */
    private function normalise(array $lines): array
    {
        $rejection = app(RejectionRepository::class);
        $out = [];
        foreach ($lines as $i => $line) {
            $quantity = round((float) ($line['quantity'] ?? 0), 4);
            if ($quantity <= 0) {
                continue;
            }
            if (($line['type'] ?? '') === 'material') {
                $out[] = ['material_id' => (int) $line['material_id'], 'product_type_id' => 0, 'stage_id' => 0, 'rejected_stage_id' => null, 'quantity' => $quantity];

                continue;
            }

            $productTypeId = (int) ($line['product_type_id'] ?? 0);
            $stageId = (int) ($line['stage_id'] ?? 0);
            $stages = $rejection->rejectableStages($productTypeId)->pluck('head_id')->map(fn ($id) => (int) $id);
            if ($stageId !== Stock::STAGE_REJECTED && ! $stages->contains($stageId)) {
                throw ValidationException::withMessages(["lines.$i.stage_id" => 'Line '.($i + 1).': choose one of the product\'s stages or Rejection.']);
            }
            $rejectedAt = null;
            if ($stageId === Stock::STAGE_REJECTED) {
                $rejectedAt = (int) ($line['rejected_stage_id'] ?? 0);
                if ($rejectedAt !== RejectionRepository::NOT_RECORDED && ! $stages->contains($rejectedAt)) {
                    throw ValidationException::withMessages(["lines.$i.rejected_stage_id" => 'Line '.($i + 1).': Rejected At must be one of the product\'s stages.']);
                }
            }
            $out[] = ['material_id' => 0, 'product_type_id' => $productTypeId, 'stage_id' => $stageId, 'rejected_stage_id' => $rejectedAt ?: null, 'quantity' => $quantity];
        }
        if (! $out) {
            throw ValidationException::withMessages(['lines' => 'Add at least one line with a quantity.']);
        }

        return $out;
    }

    /** Decrease from Rejection: the rejected-at stage must hold the quantity. */
    private function assertRejectionAvailable(array $lines): void
    {
        $rejection = app(RejectionRepository::class);
        collect($lines)->where('stage_id', Stock::STAGE_REJECTED)
            ->groupBy(fn ($l) => $l['product_type_id'].'_'.(int) $l['rejected_stage_id'])
            ->each(function ($group) use ($rejection) {
                $first = $group->first();
                if ($error = $rejection->shortfall($first['product_type_id'], (int) $first['rejected_stage_id'], (float) $group->sum('quantity'))) {
                    throw ValidationException::withMessages(['lines' => $error]);
                }
            });
    }

    /** Decrease of materials: stock may not go below zero (checked after the lines are written). */
    private function assertMaterialsAvailable(array $lines): void
    {
        $stock = app(StockItemRepository::class);
        foreach (collect($lines)->where('material_id', '>', 0)->pluck('material_id')->unique() as $materialId) {
            $left = $stock->materialStock((int) $materialId);
            if ($left < -0.0001) {
                $name = DB::table('materials')->where('material_id', $materialId)->value(DB::raw("CONCAT(material_no, ' - ', name)"));
                throw ValidationException::withMessages(['lines' => 'Not enough stock of '.$name.': '.rtrim(rtrim(number_format(-$left, 2), '0'), '.').' more than is in stock.']);
            }
        }
    }
}
