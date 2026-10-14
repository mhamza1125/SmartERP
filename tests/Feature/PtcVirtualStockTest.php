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

    private int $employeeId;  // issue / receive forms require an employee

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
        $employee = DB::table('employees')->value('employee_id');

        if (! $admin || ! $product || ! $material || ! $employee) {
            $this->markTestSkipped('Needs an admin user, a product with 3+ stages, a material and an employee.');
        }

        $this->actingAs($admin);
        $this->pt = $product->product_type_id;
        $this->stages = array_map('intval', array_slice(explode('|', $product->stage_ids), 0, 3));
        $this->materialId = $material;
        $this->employeeId = $employee;
        $this->componentPt = DB::table('product_types')->where('product_type_id', '!=', $this->pt)->value('product_type_id');
    }

    public function test_full_ptc_flow_keeps_stock_accurate(): void
    {
        [$s1, $s2, $s3] = $this->stages;
        $rejected = Stock::STAGE_REJECTED;
        $generalBefore = $this->generalProductStock();
        $materialBefore = $this->generalMaterialStock();
        $componentBefore = $this->generalComponentStock();

        // 1. Stage 1 is the starting stock: the initial issuance takes 100 Stage 1 pieces
        //    from general stock and issues them for Stage 2
        $ptcId = $this->createPtc($s1, [$this->productRow(100, $s1)]);
        $ptc = Stock::find($ptcId);
        $this->assertSame([$s1, $s2, $s2, $s3], [(int) $ptc->start_stage_id, (int) $ptc->issue_for, (int) $ptc->current_stage_id, (int) $ptc->next_stage_id]);
        $this->assertNull(StockItem::where('stock_id', $ptcId)->value('component_product_type_id'), 'no component must be stored as NULL');
        $this->assertGeneralDelta($generalBefore, ["{$this->pt}_{$s1}" => -100]);

        // 2. Receive 100 pieces into Stage 2 -> PTC stock, not general stock
        $this->receive($ptcId, $ptcId, [$this->receiveProductRow(100, $s2)])->assertSessionHas('success');
        $this->assertVirtual($ptcId, [$s2 => 100]);
        $this->assertGeneralDelta($generalBefore, ["{$this->pt}_{$s1}" => -100]);
        $this->assertSame(0.0, $this->balance($ptcId, $ptcId)['outstanding']);

        // Duplicate / over-receipt is rejected and writes nothing
        $receiptsBefore = Stock::where('ptc_id', $ptcId)->where('stock_type', 1)->count();
        $this->receive($ptcId, $ptcId, [$this->receiveProductRow(1, $s2)])->assertSessionHas('fails');
        $this->assertSame($receiptsBefore, Stock::where('ptc_id', $ptcId)->where('stock_type', 1)->count());

        // 3. Issue 60 Stage 2 pieces (this PTC's stock) for Stage 3; the current stage follows
        $this->issue($ptcId, $s3, [$this->productRow(41 + 19, $s2, 'ptc')])->assertSessionHas('success');
        $i2 = $this->latestIssuance($ptcId);
        $this->assertVirtual($ptcId, [$s2 => 40]);
        $this->assertGeneralDelta($generalBefore, ["{$this->pt}_{$s1}" => -100]);
        $this->assertSame($s3, (int) Stock::find($ptcId)->current_stage_id, 'issuing for a later stage advances the current stage');
        // cannot transfer more than the PTC holds
        $this->issue($ptcId, $s3, [$this->productRow(41, $s2, 'ptc')])->assertSessionHas('fails');
        // the starting stock stage is not a work stage
        $this->issue($ptcId, $s1, [$this->materialRow(1)])->assertSessionHas('fails');

        // 4. Receive 40 into Stage 3 -> 20 remain outstanding; close is blocked
        $this->receive($ptcId, $i2, [$this->receiveProductRow(40, $s3)])->assertSessionHas('success');
        $this->assertSame(20.0, $this->balance($ptcId, $i2)['outstanding']);
        $this->assertSame('partial', $this->balance($ptcId, $i2)['status']);
        $this->post(route('ptc.close', $ptcId))->assertSessionHas('fails');
        $this->receive($ptcId, $i2, [$this->receiveProductRow(21, $s3)])->assertSessionHas('fails');

        // 5. Receive the remaining 20
        $this->receive($ptcId, $i2, [$this->receiveProductRow(20, $s3)])->assertSessionHas('success');
        $this->assertSame(0.0, $this->balance($ptcId, $i2)['outstanding']);
        $this->assertVirtual($ptcId, [$s2 => 40, $s3 => 60]);

        // Material-only issuance (+ a component) for Stage 3; output is new product, not capped
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

        // 7. The remaining 200 are re-worked at Stage 3; some come back rejected
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
        $this->assertSame($s3, (int) $ptc->current_stage_id, 'closing keeps the stage production reached');
        $this->assertSame($movementsBeforeClose, Stock::where('ptc_id', $ptcId)->count(), 'closing creates no stock movement');

        // 9. Actual stage status after close: Stage 1 was the starting stock, 2 and 3 were worked
        $summary = $this->stageSummary($ptc);
        $this->assertSame(['start', 'completed', 'completed'], array_column($summary['stages'], 'status'));
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

        // Stage 1 stock issued for Stage 2; part of it still with the worker -> close blocked
        $ptcId = $this->createPtc($s1, [$this->productRow(10, $s1)]);
        $this->receive($ptcId, $ptcId, [$this->receiveProductRow(6, $s2)])->assertSessionHas('success');
        $this->post(route('ptc.close', $ptcId))->assertSessionHas('fails');
        $this->receive($ptcId, $ptcId, [$this->receiveProductRow(4, $s2)])->assertSessionHas('success');
        $this->release($ptcId, $s2, 10)->assertSessionHas('success');

        $this->post(route('ptc.close', $ptcId))->assertSessionHas('success', 'PTC closed early - production stopped before its end stage');
        $this->assertSame(Stock::STATUS_PTC_CLOSED, (int) Stock::find($ptcId)->stock_status, 'end stage not reached -> Closed Early, not Completed');

        $summary = $this->stageSummary(Stock::find($ptcId));
        $this->assertSame(['start', 'completed', 'not_started'], array_column($summary['stages'], 'status'));
        $this->assertSame($s2, (int) $summary['ended_at']->head_id);

        // A closed PTC accepts no further issues or receipts
        $this->issue($ptcId, $s2, [$this->materialRow(1)])->assertSessionHas('fails');
    }

    public function test_ptc_completes_when_its_end_stage_is_received(): void
    {
        [$s1, $s2, $s3] = $this->stages;

        // PTC planned for stages 1..2 only (the product has more stages): Stage 1 stock -> Stage 2
        $ptcId = $this->createPtc($s1, [$this->productRow(10, $s1)], $s2);
        $ptc = Stock::find($ptcId);
        $this->assertSame([$s2, $s2, $s2], [(int) $ptc->end_stage_id, (int) $ptc->issue_for, (int) $ptc->current_stage_id]);
        $this->assertNull($ptc->next_stage_id);
        $this->post(route('ptc.next.stage', $ptcId))->assertSessionHas('fails'); // already at end stage

        // Stage 3 is outside this PTC (after its end stage)
        $this->issue($ptcId, $s3, [$this->materialRow(1)])->assertSessionHas('fails');

        $this->get(route('ptc.show', $ptcId))->assertSee('Close PTC (stop early)');
        $this->receive($ptcId, $ptcId, [$this->receiveProductRow(10, $s2)])->assertSessionHas('success');
        $this->release($ptcId, $s2, 10)->assertSessionHas('success');

        $this->get(route('ptc.show', $ptcId))->assertSee('Complete PTC')->assertDontSee('Close PTC (stop early)');
        $this->post(route('ptc.close', $ptcId))->assertSessionHas('success', 'PTC completed');
        $this->assertSame(Stock::STATUS_PTC_COMPLETED, (int) Stock::find($ptcId)->stock_status);
        $this->get(route('ptc.show', $ptcId))->assertSee('Completed');
        $summary = $this->stageSummary(Stock::find($ptcId));
        $this->assertSame(['start', 'completed', 'outside'], array_column($summary['stages'], 'status'));
    }

    public function test_references_stage_moves_and_current_stage(): void
    {
        [$s1, $s2, $s3] = $this->stages;
        $repository = app(PtcStockRepository::class);

        // Stage 1 stock -> issued for Stage 2 -> received into Stage 2
        $ptcId = $this->createPtc($s1, [$this->productRow(10, $s1)]);
        $stages = $repository->productStages($ptcId);
        $this->receive($ptcId, $ptcId, [$this->receiveProductRow(10, $s2)])->assertSessionHas('success');
        $r1 = (int) Stock::where('issue_id', $ptcId)->max('stock_id');

        // New issuance defaults to the stage after the current one (Stage 2 -> Stage 3)
        $this->assertSame($s3, (int) $repository->nextStageId(Stock::find($ptcId), $stages));
        $this->assertSame([$s2, $s3], $repository->issueStages(Stock::find($ptcId), $stages)->pluck('head_id')->map(fn ($id) => (int) $id)->all());

        // Stage 2 PTC stock -> issued for Stage 3 -> received into Stage 3
        $this->issue($ptcId, $s3, [$this->productRow(10, $s2, 'ptc')])->assertSessionHas('success');
        $i2 = $this->latestIssuance($ptcId);
        $this->receive($ptcId, $i2, [$this->receiveProductRow(4, $s3)])->assertSessionHas('success');
        $r2 = (int) Stock::where('issue_id', $i2)->max('stock_id');

        // Stored numbers skip the master (I002 is stored as -001); display numbers do not
        $this->assertStringEndsWith('-001', Stock::find($i2)->stock_no);
        $refs = $repository->references($ptcId);
        $this->assertSame(['I001', 'R1-I001', 'I002', 'R1-I002'], [$refs[$ptcId], $refs[$r1], $refs[$i2], $refs[$r2]]);
        $this->get(route('ptc.show', $ptcId))->assertOk()->assertSee('/ R1-I002');

        // Every movement reads From -> To
        $moves = $repository->movementStages($ptcId);
        $this->assertSame(['from' => [$s1], 'to' => [$s2]], $this->moveStageIds($moves[$ptcId]));
        $this->assertSame(['from' => [$s1], 'to' => [$s2]], $this->moveStageIds($moves[$r1]));
        $this->assertSame(['from' => [$s2], 'to' => [$s3]], $this->moveStageIds($moves[$i2]));
        $this->assertSame(['from' => [$s2], 'to' => [$s3]], $this->moveStageIds($moves[$r2]));
        $this->assertSame(['Received into PTC stock'], array_column($moves[$r2]['to'], 'note'));

        // Current Stage moved to Stage 3 and never moves back for re-work at an earlier stage
        $this->assertSame($s3, (int) Stock::find($ptcId)->current_stage_id);
        $this->issue($ptcId, $s2, [$this->materialRow(1)])->assertSessionHas('success');
        $this->assertSame($s3, (int) Stock::find($ptcId)->current_stage_id);

        // "Show all": any of the product's stages can be received into for a manual flow,
        // but not a stage that is not one of the product's stages
        $this->receive($ptcId, $i2, [$this->receiveProductRow(1, $s2)])->assertSessionHas('success');
        $otherHead = (int) DB::table('heads')->whereNotIn('head_id', $stages->pluck('head_id')->push(Stock::STAGE_REJECTED))->value('head_id');
        $this->receive($ptcId, $i2, [$this->receiveProductRow(1, $otherHead)])->assertSessionHas('fails');
    }

    /** Stage ids of a movement's from/to endpoints, resolved by name. */
    private function moveStageIds(array $move): array
    {
        $ids = DB::table('heads')->whereIn('head_id', $this->stages)->pluck('head_id', 'name');

        return [
            'from' => array_map(fn ($point) => (int) $ids[$point['label']], $move['from']),
            'to' => array_map(fn ($point) => (int) $ids[$point['label']], $move['to']),
        ];
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
            'employee_id' => $this->employeeId,
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
            'employee_id' => $this->employeeId,
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
            'employee_id' => $this->employeeId,
        ], $this->columns($rows, ['quantity', 'product_type_id', 'material_id', 'stage_id', 'component_id', 'source'])));
    }

    private function receive(int $ptcId, int $issuanceId, array $rows)
    {
        return $this->post(route('ptc.receive.store', $ptcId), array_merge([
            'issue_id' => $issuanceId,
            'stock_date' => now()->toDateString(),
            'table_name' => 'employee',
            'employee_id' => $this->employeeId,
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
