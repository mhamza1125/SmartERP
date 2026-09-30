<?php

namespace Tests\Feature;

use App\Models\Stock;
use App\Models\StockItem;
use App\Models\User;
use App\Repositories\PtcStockRepository;
use App\Repositories\StockItemRepository;
use App\Repositories\StockRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * PTC issuance / receiving / virtual stock / release / close, end to end through
 * the HTTP routes. Runs inside a transaction that is rolled back.
 */
class PtcVirtualStockTest extends TestCase
{
    use DatabaseTransactions;

    private int $pt;          // PTC product type

    private array $stages;    // first three product stages

    private int $materialId;

    private int $componentPt; // another product type used as a component

    protected function setUp(): void
    {
        parent::setUp();

        $admin = User::where('role_id', User::ROLE_ADMIN)->first();
        $product = DB::table('product_types')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->whereRaw("(LENGTH(products.stage_ids) - LENGTH(REPLACE(products.stage_ids, '|', ''))) >= 2")
            ->select('product_types.product_type_id', 'products.stage_ids')
            ->first();
        $material = DB::table('materials')->value('material_id');

        if (! $admin || ! $product || ! $material) {
            $this->markTestSkipped('Needs an admin user, a product with 3+ stages and a material.');
        }

        $this->actingAs($admin);
        $this->pt = $product->product_type_id;
        $this->stages = array_map('intval', array_slice(explode('|', $product->stage_ids), 0, 3));
        $this->materialId = $material;
        $this->componentPt = DB::table('product_types')->where('product_type_id', '!=', $this->pt)->value('product_type_id');
    }

    public function test_full_ptc_flow_keeps_stock_accurate(): void
    {
        [$s1, $s2, $s3] = $this->stages;
        $rejected = Stock::STAGE_REJECTED;
        $generalBefore = $this->generalProductStock();
        $materialBefore = $this->generalMaterialStock();
        $componentBefore = $this->generalComponentStock();

        // 1. Issue 100 pieces from Stage 1 (initial PTC issuance, from general stock)
        $ptcId = $this->createPtc($s1, [$this->productRow(100, $s1)]);
        $this->assertNull(StockItem::where('stock_id', $ptcId)->value('component_product_type_id'), 'no component must be stored as NULL');
        $this->assertGeneralDelta($generalBefore, ["{$this->pt}_{$s1}" => -100]);

        // 2. Receive 100 pieces at Stage 2 -> PTC stock, not general stock
        $this->receive($ptcId, $ptcId, [$this->receiveProductRow(100, $s2)])->assertSessionHas('success');
        $this->assertVirtual($ptcId, [$s2 => 100]);
        $this->assertGeneralDelta($generalBefore, ["{$this->pt}_{$s1}" => -100]);
        $this->assertSame(0.0, $this->balance($ptcId, $ptcId)['outstanding']);

        // Duplicate / over-receipt is rejected and writes nothing
        $receiptsBefore = Stock::where('ptc_id', $ptcId)->where('stock_type', 1)->count();
        $this->receive($ptcId, $ptcId, [$this->receiveProductRow(1, $s2)])->assertSessionHas('fails');
        $this->assertSame($receiptsBefore, Stock::where('ptc_id', $ptcId)->where('stock_type', 1)->count());

        // 3. Issue 60 pieces from Stage 2 (this PTC's stock) to the next stage
        $this->issue($ptcId, $s2, [$this->productRow(41 + 19, $s2, 'ptc')])->assertSessionHas('success');
        $i2 = $this->latestIssuance($ptcId);
        $this->assertVirtual($ptcId, [$s2 => 40]);
        $this->assertGeneralDelta($generalBefore, ["{$this->pt}_{$s1}" => -100]);
        // cannot transfer more than the PTC holds
        $this->issue($ptcId, $s2, [$this->productRow(41, $s2, 'ptc')])->assertSessionHas('fails');

        // 4. Receive 40 -> 20 remain outstanding; close is blocked
        $this->receive($ptcId, $i2, [$this->receiveProductRow(40, $s3)])->assertSessionHas('success');
        $this->assertSame(20.0, $this->balance($ptcId, $i2)['outstanding']);
        $this->assertSame('partial', $this->balance($ptcId, $i2)['status']);
        $this->post(route('ptc.close', $ptcId))->assertSessionHas('fails');
        $this->receive($ptcId, $i2, [$this->receiveProductRow(21, $s3)])->assertSessionHas('fails');

        // 5. Receive the remaining 20
        $this->receive($ptcId, $i2, [$this->receiveProductRow(20, $s3)])->assertSessionHas('success');
        $this->assertSame(0.0, $this->balance($ptcId, $i2)['outstanding']);
        $this->assertVirtual($ptcId, [$s2 => 40, $s3 => 60]);

        // Material-only issuance (+ a component); output is new product, not capped
        $this->issue($ptcId, $s3, [$this->materialRow(5), $this->componentRow(10)])->assertSessionHas('success');
        $i3 = $this->latestIssuance($ptcId);
        $this->receive($ptcId, $i3, [$this->receiveComponentRow(7)])->assertSessionHas('success');
        $this->receive($ptcId, $i3, [$this->receiveComponentRow(4)])->assertSessionHas('fails'); // only 3 left
        $this->receive($ptcId, $i3, [
            $this->receiveProductRow(940, $s3),
            $this->receiveMaterialRow(2),
            $this->receiveComponentRow(3),
        ])->assertSessionHas('success');
        $this->assertVirtual($ptcId, [$s2 => 40, $s3 => 1000]);

        // 6. Release 800 of 1,000 at Stage 3 -> exactly 800 enter general stock
        $this->release($ptcId, $s3, 800)->assertSessionHas('success');
        $this->assertVirtual($ptcId, [$s2 => 40, $s3 => 200]);
        $this->assertGeneralDelta($generalBefore, ["{$this->pt}_{$s1}" => -100, "{$this->pt}_{$s3}" => 800]);
        $this->release($ptcId, $s3, 201)->assertSessionHas('fails');

        // 7. The remaining 200 continue through the PTC
        $this->issue($ptcId, $s3, [$this->productRow(200, $s3, 'ptc')])->assertSessionHas('success');
        $i4 = $this->latestIssuance($ptcId);
        $this->assertVirtual($ptcId, [$s2 => 40, $s3 => 0]);
        $this->receive($ptcId, $i4, [$this->receiveProductRow(150, $s3), $this->receiveProductRow(50, $rejected)])->assertSessionHas('success');
        $this->assertVirtual($ptcId, [$s2 => 40, $s3 => 150, $rejected => 50]);

        // PTC stock is never visible to general stock until released
        $this->assertGeneralDelta($generalBefore, ["{$this->pt}_{$s1}" => -100, "{$this->pt}_{$s3}" => 800]);

        // 8. Close is blocked while PTC stock remains; release it all, then close
        $this->post(route('ptc.close', $ptcId))->assertSessionHas('fails');
        $this->release($ptcId, $s2, 40)->assertSessionHas('success');
        $this->release($ptcId, $s3, 150)->assertSessionHas('success');
        $this->release($ptcId, $rejected, 50)->assertSessionHas('success');

        $movementsBeforeClose = Stock::where('ptc_id', $ptcId)->count();
        $this->post(route('ptc.close', $ptcId))->assertSessionHas('success');
        $ptc = Stock::find($ptcId);
        $this->assertSame(Stock::STATUS_PTC_COMPLETED, (int) $ptc->stock_status);
        $this->assertSame($s1, (int) $ptc->current_stage_id, 'closing keeps the stage production reached');
        $this->assertSame($movementsBeforeClose, Stock::where('ptc_id', $ptcId)->count(), 'closing creates no stock movement');

        // 9. Actual stage status after close
        $summary = $this->stageSummary($ptc);
        $this->assertSame(['completed', 'completed', 'completed'], array_column($summary['stages'], 'status'));
        $this->assertSame($s3, (int) $summary['ended_at']->head_id);

        // 10. Nothing duplicated or lost: 100 in from general + 940 produced = 1,040 released
        $this->assertVirtual($ptcId, [$s2 => 0, $s3 => 0, $rejected => 0]);
        $this->assertGeneralDelta($generalBefore, [
            "{$this->pt}_{$s1}" => -100,
            "{$this->pt}_{$s2}" => 40,
            "{$this->pt}_{$s3}" => 950,
            "{$this->pt}_{$rejected}" => 50,
        ]);
        $materialAfter = $this->generalMaterialStock();
        $this->assertEqualsWithDelta(-3, ($materialAfter[$this->materialId] ?? 0) - ($materialBefore[$this->materialId] ?? 0), 0.0001, '5 issued - 2 returned = 3 consumed');
        $componentAfter = $this->generalComponentStock();
        $this->assertEqualsWithDelta(0, ($componentAfter[$this->componentPt] ?? 0) - ($componentBefore[$this->componentPt] ?? 0), 0.0001, '10 issued - 10 returned');

        $consumption = app(PtcStockRepository::class)->consumption($ptcId);
        $this->assertEquals(3, $consumption['materials']->firstWhere('material_id', $this->materialId)->consumed);
        $this->assertEquals(0, $consumption['components']->first()->consumed);
    }

    public function test_closing_early_keeps_actual_stage_status(): void
    {
        [$s1, $s2] = $this->stages;

        $ptcId = $this->createPtc($s1, [$this->productRow(10, $s1)]);
        $this->receive($ptcId, $ptcId, [$this->receiveProductRow(10, $s2)])->assertSessionHas('success');
        // Next stage has an issuance that is never received -> close blocked
        $this->issue($ptcId, $s2, [$this->productRow(4, $s2, 'ptc')])->assertSessionHas('success');
        $this->post(route('ptc.close', $ptcId))->assertSessionHas('fails');
        $this->receive($ptcId, $this->latestIssuance($ptcId), [$this->receiveProductRow(4, $s2)])->assertSessionHas('success');
        $this->release($ptcId, $s2, 10)->assertSessionHas('success');

        $this->post(route('ptc.close', $ptcId))->assertSessionHas('success', 'PTC closed early - production stopped before its end stage');
        $this->assertSame(Stock::STATUS_PTC_CLOSED, (int) Stock::find($ptcId)->stock_status, 'end stage not reached -> Closed Early, not Completed');

        $summary = $this->stageSummary(Stock::find($ptcId));
        $this->assertSame(['completed', 'completed', 'not_started'], array_column($summary['stages'], 'status'));
        $this->assertSame($s2, (int) $summary['ended_at']->head_id);

        // A closed PTC accepts no further issues or receipts
        $this->issue($ptcId, $s2, [$this->materialRow(1)])->assertSessionHas('fails');
    }

    public function test_ptc_completes_when_its_end_stage_is_received(): void
    {
        [$s1, $s2, $s3] = $this->stages;

        // PTC planned for stages 1..2 only (the product has more stages)
        $ptcId = $this->createPtc($s1, [$this->productRow(10, $s1)], $s2);
        $this->assertSame($s2, (int) Stock::find($ptcId)->end_stage_id);
        $this->receive($ptcId, $ptcId, [$this->receiveProductRow(10, $s2)])->assertSessionHas('success');
        $this->post(route('ptc.next.stage', $ptcId))->assertSessionHas('success');
        $this->post(route('ptc.next.stage', $ptcId))->assertSessionHas('fails'); // already at end stage

        $this->issue($ptcId, $s2, [$this->productRow(10, $s2, 'ptc')])->assertSessionHas('success');
        $this->get(route('ptc.show', $ptcId))->assertSee('Close PTC (stop early)');
        $this->receive($ptcId, $this->latestIssuance($ptcId), [$this->receiveProductRow(10, $s3)])->assertSessionHas('success');
        $this->release($ptcId, $s3, 10)->assertSessionHas('success');

        $this->get(route('ptc.show', $ptcId))->assertSee('Complete PTC')->assertDontSee('Close PTC (stop early)');
        $this->post(route('ptc.close', $ptcId))->assertSessionHas('success', 'PTC completed');
        $this->assertSame(Stock::STATUS_PTC_COMPLETED, (int) Stock::find($ptcId)->stock_status);
        $this->get(route('ptc.show', $ptcId))->assertSee('Completed');
        $this->assertSame('outside', $this->stageSummary(Stock::find($ptcId))['stages'][2]->status);
    }

    public function test_editing_ptc_keeps_item_keys(): void
    {
        [$s1] = $this->stages;
        $ptcId = $this->createPtc($s1, [$this->productRow(10, $s1), $this->materialRow(3)]);
        $before = StockItem::where('stock_id', $ptcId)->orderBy('stock_item_id')->get(['stock_item_id', 'product_type_id', 'stage_id']);

        // editPTC posts material rows with product_type_id = 0 and stage_id = 0
        $this->put(route('ptc.update', $ptcId), [
            'stock_date' => now()->toDateString(),
            'table_name' => 'employee',
            'employee_id' => 0,
            'description' => '[QTY:10]',
            'quantity' => [10, 3],
            'product_type_id' => [$this->pt, 0],
            'material_id' => [0, $this->materialId],
            'stage_id' => [$s1, 0],
            'component_id' => ['', ''],
        ])->assertSessionHas('success');

        $after = StockItem::where('stock_id', $ptcId)->orderBy('stock_item_id')->get(['stock_item_id', 'product_type_id', 'stage_id']);
        $this->assertEquals($before->toArray(), $after->toArray());
    }

    public function test_ptc_pages_render(): void
    {
        [$s1, $s2] = $this->stages;
        $ptcId = $this->createPtc($s1, [$this->productRow(10, $s1)]);
        $this->receive($ptcId, $ptcId, [$this->receiveProductRow(6, $s2)]);
        $this->release($ptcId, $s2, 2);

        foreach (Stock::where('is_ptc_master', 1)->pluck('stock_id') as $id) {
            $this->get(route('ptc.show', $id))->assertOk();
            $this->get(route('ptc.print', $id))->assertOk();
            $this->get(route('ptc.issue.form', ['id' => $id, 'source' => 'ptc', 'stage' => $s2]))->assertStatus(Stock::find($id)->stock_status != Stock::STATUS_PTC_IN_PROGRESS ? 302 : 200);
            foreach (app(PtcStockRepository::class)->issuanceBalances($id) as $issuanceId => $balance) {
                $response = $this->get(route('ptc.receive.issuance', [$id, $issuanceId]));
                $balance['can_receive'] && Stock::find($id)->stock_status == Stock::STATUS_PTC_IN_PROGRESS
                    ? $response->assertOk()
                    : $response->assertRedirect();
            }
        }

        $this->get(route('ptc.show', $ptcId))->assertSee('PTC Stock')->assertSee('Release to Stock')->assertSee('Released to Stock')
            ->assertSee('Close PTC (stop early)')->assertDontSee('Issue for Stage')->assertDontSee('Receive from Stage');
        $this->get(route('ptc'))->assertOk();
    }

    // ------------------------------------------------------------------

    private function createPtc(int $startStage, array $rows, ?int $endStage = null): int
    {
        $this->post(route('ptc.store'), array_merge([
            'stock_no' => app(StockRepository::class)->ptcRefNo().'T',
            'stock_date' => now()->toDateString(),
            'ptc_product_type_id' => $this->pt,
            'start_stage_id' => $startStage,
            'end_stage_id' => $endStage ?? $this->stages[2],
            'order_id' => 0,
            'table_name' => 'employee',
            'employee_id' => 0,
            'product_quantity' => 100,
        ], $this->columns($rows, ['quantity', 'product_type_id', 'material_id', 'stage_id', 'component_id'])));

        return (int) Stock::where('is_ptc_master', 1)->max('stock_id');
    }

    private function issue(int $ptcId, int $issueFor, array $rows)
    {
        return $this->post(route('ptc.issue.store', $ptcId), array_merge([
            'stock_date' => now()->toDateString(),
            'issue_for' => $issueFor,
            'table_name' => 'employee',
            'employee_id' => 0,
        ], $this->columns($rows, ['quantity', 'product_type_id', 'material_id', 'stage_id', 'component_id', 'source'])));
    }

    private function receive(int $ptcId, int $issuanceId, array $rows)
    {
        return $this->post(route('ptc.receive.store', $ptcId), array_merge([
            'issue_id' => $issuanceId,
            'stock_date' => now()->toDateString(),
            'table_name' => 'employee',
            'employee_id' => 0,
        ], $this->columns($rows, ['r_quantity', 'r_product_type_id', 'r_material_id', 'r_stage_id', 'r_work_logs', 'r_component_product_type_id'])));
    }

    private function release(int $ptcId, int $stageId, float $quantity)
    {
        return $this->post(route('ptc.release', $ptcId), [
            'product_type_id' => $this->pt,
            'stage_id' => $stageId,
            'quantity' => $quantity,
            'stock_date' => now()->toDateString(),
        ]);
    }

    private function productRow(float $qty, int $stage, string $source = 'general'): array
    {
        return ['quantity' => $qty, 'product_type_id' => $this->pt, 'material_id' => 0, 'stage_id' => $stage, 'component_id' => '', 'source' => $source];
    }

    private function materialRow(float $qty): array
    {
        return ['quantity' => $qty, 'product_type_id' => 0, 'material_id' => $this->materialId, 'stage_id' => 0, 'component_id' => '', 'source' => 'general'];
    }

    private function componentRow(float $qty): array
    {
        return ['quantity' => $qty, 'product_type_id' => $this->pt, 'material_id' => 0, 'stage_id' => 0, 'component_id' => $this->componentPt, 'source' => 'general'];
    }

    private function receiveProductRow(float $qty, int $stage): array
    {
        return ['r_quantity' => $qty, 'r_product_type_id' => $this->pt, 'r_material_id' => 0, 'r_stage_id' => $stage, 'r_work_logs' => '0', 'r_component_product_type_id' => ''];
    }

    private function receiveMaterialRow(float $qty): array
    {
        return ['r_quantity' => $qty, 'r_product_type_id' => $this->pt, 'r_material_id' => $this->materialId, 'r_stage_id' => 0, 'r_work_logs' => '0', 'r_component_product_type_id' => ''];
    }

    private function receiveComponentRow(float $qty): array
    {
        return ['r_quantity' => $qty, 'r_product_type_id' => 0, 'r_material_id' => 0, 'r_stage_id' => 0, 'r_work_logs' => '0', 'r_component_product_type_id' => $this->componentPt];
    }

    /** Turn row arrays into the parallel "name[]" arrays the forms post. */
    private function columns(array $rows, array $names): array
    {
        $data = [];
        foreach ($names as $name) {
            $data[$name] = array_column($rows, $name);
        }

        return $data;
    }

    private function latestIssuance(int $ptcId): int
    {
        return (int) Stock::where('ptc_id', $ptcId)->where('stock_type', 2)->max('stock_id');
    }

    private function balance(int $ptcId, int $issuanceId): array
    {
        return app(PtcStockRepository::class)->issuanceBalances($ptcId)->get($issuanceId);
    }

    private function stageSummary(Stock $ptc): array
    {
        $stages = collect($this->stages)->map(fn ($id) => (object) ['head_id' => $id, 'name' => (string) $id]);

        return app(PtcStockRepository::class)->stageSummary($ptc, $stages);
    }

    private function assertVirtual(int $ptcId, array $expected): void
    {
        $available = app(PtcStockRepository::class)->virtualAvailableMap($ptcId);
        foreach ($expected as $stage => $qty) {
            $this->assertEqualsWithDelta($qty, $available["{$this->pt}_{$stage}"] ?? 0, 0.0001, "PTC stock at stage {$stage}");
        }
    }

    /** General product stock (the /stock page calculation) keyed "pt_stage" => in - out. */
    private function generalProductStock(): array
    {
        return app(StockItemRepository::class)->pStock()
            ->mapWithKeys(fn ($row) => [$row->product_type_id.'_'.$row->stage_id => $row->stockIn - $row->stockOut])
            ->all();
    }

    private function generalMaterialStock(): array
    {
        return app(StockItemRepository::class)->stock()
            ->mapWithKeys(fn ($row) => [$row->material_id => $row->total_received - $row->total_returned + $row->stockIn - $row->stockOut])
            ->all();
    }

    private function generalComponentStock(): array
    {
        $totals = [];
        foreach (app(StockItemRepository::class)->pStock() as $row) {
            $totals[$row->product_type_id] = ($totals[$row->product_type_id] ?? 0) + $row->stockIn - $row->stockOut;
        }

        return $totals;
    }

    /** Every product/stage balance equals the baseline plus exactly the expected deltas. */
    private function assertGeneralDelta(array $before, array $expected): void
    {
        $after = $this->generalProductStock();
        foreach (array_unique(array_merge(array_keys($before), array_keys($after))) as $key) {
            if (str_starts_with($key, $this->componentPt.'_')) {
                continue; // component balance is asserted separately
            }
            $delta = ($after[$key] ?? 0) - ($before[$key] ?? 0);
            $this->assertEqualsWithDelta($expected[$key] ?? 0, $delta, 0.0001, "general stock change at {$key}");
        }
    }
}
