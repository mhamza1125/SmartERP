<?php

namespace Tests\Feature;

use App\Exceptions\StockBatchException;
use App\Models\Stock;
use App\Models\StockBatch;
use App\Models\StockItem;
use App\Models\User;
use App\Repositories\PurchaseRepository;
use App\Repositories\ReceiveMaterialRepository;
use App\Repositories\StockBatchRepository;
use App\Repositories\StockItemRepository;
use App\Repositories\StockRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Batch-tracked product stock: every product stock-in gets the batch of its source and
 * the order that source belongs to. Runs inside a transaction that is rolled back.
 */
class StockBatchTest extends TestCase
{
    use DatabaseTransactions;

    private StockBatchRepository $batches;

    private int $pt;

    private array $stages;

    private int $employeeId;

    private int $orderX;

    private int $orderY;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = User::where('role_id', User::ROLE_ADMIN)->first();
        $product = DB::table('product_types')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->whereRaw("(LENGTH(products.stage_ids) - LENGTH(REPLACE(products.stage_ids, '|', ''))) >= 2")
            ->select('product_types.product_type_id', 'products.stage_ids')
            ->first();
        $employee = DB::table('employees')->value('employee_id');
        $orders = DB::table('orders')->orderBy('order_id')->limit(2)->pluck('order_id');

        if (! $admin || ! $product || ! $employee || $orders->count() < 2) {
            $this->markTestSkipped('Needs an admin user, a product with 3+ stages, an employee and two orders.');
        }

        $this->actingAs($admin);
        $this->batches = app(StockBatchRepository::class);
        $this->pt = $product->product_type_id;
        $this->stages = array_map('intval', array_slice(explode('|', $product->stage_ids), 0, 3));
        $this->employeeId = $employee;
        [$this->orderX, $this->orderY] = $orders->all();
    }

    public function test_ptc_receipt_and_release_stay_in_the_ptc_batch_reserved_for_its_order(): void
    {
        [$s1, $s2, $s3] = $this->stages;
        $this->seedOpening($this->pt, $s1, 1000);
        $ptcId = $this->createPtc($s1, $this->orderX);

        $this->receive($ptcId, $ptcId, 10, $s2)->assertSessionHas('success');
        $batch = StockBatch::where('source_type', StockBatch::SOURCE_PTC)->where('source_id', $ptcId)->firstOrFail();
        $this->assertSame($this->orderX, (int) $batch->order_id);
        $this->assertSame('PTC-'.Stock::find($ptcId)->stock_no, $batch->batch_no);

        // Receipt is PTC stock only: allocated to the batch, not general stock
        $this->assertSame(0.0, $this->generalBalance($batch->batch_id, $s2));

        // Stage transfer inside the PTC keeps the batch
        $this->issue($ptcId, $s3, 4, $s2)->assertSessionHas('success');
        $transfer = StockItem::where('stock_id', $this->latestIssuance($ptcId))->where('ptc_virtual', 1)->firstOrFail();
        $this->assertAllocated($transfer->stock_item_id, [$batch->batch_id => 4]);

        // Release enters general stock, still the PTC's batch, still reserved for order X
        $this->release($ptcId, $s2, 6)->assertSessionHas('success');
        $this->assertSame(6.0, $this->generalBalance($batch->batch_id, $s2));
        $this->assertContains($batch->batch_id, $this->batches->available($this->pt, $s2, $this->orderX)->pluck('batch_id')->all());
        $this->assertNotContains($batch->batch_id, $this->batches->available($this->pt, $s2, $this->orderY)->pluck('batch_id')->all());
        $this->assertNotContains($batch->batch_id, $this->batches->available($this->pt, $s2)->pluck('batch_id')->all());

        $this->assertSame([], $this->unallocatedIn($ptcId));
    }

    public function test_purchase_receipt_is_reserved_for_the_purchase_order_and_follows_it(): void
    {
        $stage = $this->stages[2];
        [$purchaseId, $purchaseItemId, $receiveId] = $this->purchase($this->orderX, $stage);

        app(ReceiveMaterialRepository::class)->store($this->receiptLine($receiveId, $purchaseItemId, 30));
        $batchId = (int) DB::table('receive_materials')->where('receive_id', $receiveId)->value('batch_id');
        $this->assertSame($this->orderX, (int) StockBatch::find($batchId)->order_id);
        $this->assertSame(30.0, $this->generalBalance($batchId, $stage));

        // Purchase returns reduce the batch through the receipt line
        DB::table('return_materials')->insert([
            'return_id' => 0, 'receive_material_id' => DB::table('receive_materials')->where('receive_id', $receiveId)->value('receive_material_id'),
            'quantity' => 5, 'created_by' => auth()->id(),
        ]);
        $this->assertSame(25.0, $this->generalBalance($batchId, $stage));

        app(PurchaseRepository::class)->update($purchaseId, ['order_id' => $this->orderY]);
        $this->assertSame($this->orderY, (int) StockBatch::find($batchId)->order_id);
    }

    public function test_issuance_receipt_gets_the_issuance_batch_and_follows_its_order(): void
    {
        $stage = $this->stages[2];
        $issuance = $this->stock(['stock_type' => 2, 'order_id' => $this->orderX, 'stock_status' => 0]);
        $receipt = $this->stock(['stock_type' => 1, 'order_id' => $this->orderX, 'issue_id' => $issuance->stock_id]);
        $line = $this->line($receipt->stock_id, 12, $stage);

        $batch = StockBatch::where('source_type', StockBatch::SOURCE_ISSUANCE)->where('source_id', $issuance->stock_id)->firstOrFail();
        $this->assertSame($issuance->stock_no, $batch->batch_no);
        $this->assertSame($this->orderX, (int) $batch->order_id);
        $this->assertAllocated($line->stock_item_id, [$batch->batch_id => 12]);

        // Editing the receipt quantity re-syncs the allocation
        $line->update(['quantity' => 9]);
        $this->assertAllocated($line->stock_item_id, [$batch->batch_id => 9]);

        app(StockRepository::class)->update($issuance->stock_id, ['order_id' => $this->orderY]);
        $this->assertSame($this->orderY, (int) $batch->fresh()->order_id);
    }

    public function test_opening_stock_is_free(): void
    {
        $productId = DB::table('product_types')->where('product_type_id', $this->pt)->value('product_id');
        $firstType = DB::table('product_types')->where('product_id', $productId)->where('product_type_status', 1)->orderBy('product_type_id')->value('product_type_id');

        app(StockItemRepository::class)->updateProductStock($productId, 77);

        $line = StockItem::where('stock_id', 1)->where('product_type_id', $firstType)->where('material_id', 0)->firstOrFail();
        $batch = StockBatch::where('source_type', StockBatch::SOURCE_OPENING)->where('product_type_id', $firstType)->firstOrFail();
        $this->assertNull($batch->order_id);
        $this->assertAllocated($line->stock_item_id, [$batch->batch_id => 77]);
    }

    public function test_delivery_return_goes_back_to_the_batches_it_was_delivered_from(): void
    {
        $stage = $this->stages[2];
        $issuance = $this->stock(['stock_type' => 2, 'order_id' => $this->orderX]);
        $batchA = $this->batches->forIssuance($issuance, $this->pt);
        $batchB = $this->batches->forOpening($this->pt);

        $delivery = $this->stock(['stock_type' => 2, 'stock_status' => Stock::STATUS_DELIVERY, 'table_name' => 'delivery', 'order_id' => $this->orderX]);
        $delivered = $this->line($delivery->stock_id, 10, $stage);
        $this->batches->assign($delivered->stock_item_id, [$batchA->batch_id => 6, $batchB->batch_id => 4]);

        $returnId = DB::table('delivery_returns')->insertGetId([
            'return_no' => 'T-RET-'.uniqid(), 'delivery_id' => DB::table('deliveries')->value('delivery_id'),
            'return_date' => now()->toDateString(), 'return_reason' => 'test', 'created_by' => auth()->id(),
        ]);
        DB::table('delivery_return_items')->insert([
            'delivery_return_id' => $returnId, 'stock_item_id' => $delivered->stock_item_id, 'quantity' => 8, 'created_by' => auth()->id(),
        ]);

        $returnStock = $this->stock(['stock_type' => 1, 'table_name' => 'delivery_returns', 'source_id' => $returnId, 'order_id' => $this->orderX]);
        $returned = $this->line($returnStock->stock_id, 8, $stage);

        $this->assertAllocated($returned->stock_item_id, [$batchA->batch_id => 6, $batchB->batch_id => 2]);
    }

    public function test_allocation_prefers_own_order_then_free_and_never_another_order(): void
    {
        $stage = $this->stages[2];
        $mine = $this->receiptIntoNewBatch($this->orderX, 5, $stage);
        $this->receiptIntoNewBatch(null, 8, $stage);
        $theirs = $this->receiptIntoNewBatch($this->orderY, 50, $stage);

        // Existing data may hold more batches: all of order X's stock is taken before any free stock
        $available = $this->batches->available($this->pt, $stage, $this->orderX);
        $own = $available->where('order_id', $this->orderX);
        $this->assertContains($mine, $own->pluck('batch_id')->all());

        $allocation = $this->batches->allocate($this->pt, $stage, $own->sum('balance') + 1, $this->orderX);
        foreach ($own as $batch) {
            $this->assertEqualsWithDelta($batch->balance, $allocation[$batch->batch_id], 0.0001);
        }
        $freeTaken = collect($allocation)->except($own->pluck('batch_id')->all());
        $this->assertEqualsWithDelta(1, $freeTaken->sum(), 0.0001);
        $this->assertSame($available->whereNull('order_id')->first()->batch_id, $freeTaken->keys()->first());
        $this->assertArrayNotHasKey($theirs, $allocation);

        $this->expectException(StockBatchException::class);
        $this->batches->allocate($this->pt, $stage, 1, $this->orderX, $theirs);
    }

    public function test_stock_out_never_takes_another_orders_batch(): void
    {
        $stage = $this->stages[2];
        $theirs = $this->receiptIntoNewBatch($this->orderY, 50, $stage);
        $ownAndFree = $this->batches->available($this->pt, $stage, $this->orderX)->sum('balance');

        $delivery = $this->stock(['stock_type' => 2, 'stock_status' => Stock::STATUS_DELIVERY, 'table_name' => 'delivery', 'order_id' => $this->orderX]);
        $this->line($delivery->stock_id, $ownAndFree + 1, $stage);

        try {
            $this->batches->allocateStockOut($delivery->stock_id, $this->orderX);
            $this->fail('Order Y stock must not be delivered to order X.');
        } catch (StockBatchException $e) {
            $this->assertStringContainsString('available for this order', $e->getMessage());
        }

        // Pinning another order's batch is refused too
        $this->expectException(StockBatchException::class);
        $this->batches->allocateStockOut($delivery->stock_id, $this->orderX, ["{$this->pt}_{$stage}" => $theirs]);
    }

    public function test_reallocating_an_edited_stock_out_does_not_count_it_twice(): void
    {
        $stage = $this->stages[2];
        $batchId = $this->receiptIntoNewBatch($this->orderX, 10, $stage);
        $issuance = $this->stock(['stock_type' => 2, 'order_id' => $this->orderX, 'stock_status' => 0]);
        $line = $this->line($issuance->stock_id, 10, $stage);

        $this->batches->allocateStockOut($issuance->stock_id, $this->orderX, ["{$this->pt}_{$stage}" => $batchId]);
        $this->assertSame(0.0, $this->generalBalance($batchId, $stage));

        // Same quantity again (an edit that changed nothing) still fits: its own lines are left out
        $this->batches->allocateStockOut($issuance->stock_id, $this->orderX, ["{$this->pt}_{$stage}" => $batchId]);
        $line->update(['quantity' => 7]);
        $this->batches->allocateStockOut($issuance->stock_id, $this->orderX, ["{$this->pt}_{$stage}" => $batchId]);

        $this->assertAllocated($line->stock_item_id, [$batchId => 7]);
        $this->assertSame(3.0, $this->generalBalance($batchId, $stage));
    }

    public function test_ptc_cannot_issue_more_than_its_orders_and_free_stock(): void
    {
        $s1 = $this->stages[0];
        $before = Stock::where('is_ptc_master', 1)->max('stock_id');
        $ownAndFree = $this->batches->available($this->pt, $s1, $this->orderX)->sum('balance');

        $this->post(route('ptc.store'), [
            'stock_no' => app(StockRepository::class)->ptcRefNo().'T',
            'stock_date' => now()->toDateString(),
            'ptc_product_type_id' => $this->pt,
            'start_stage_id' => $s1,
            'end_stage_id' => $this->stages[2],
            'order_id' => $this->orderX,
            'table_name' => 'employee',
            'employee_id' => $this->employeeId,
            'product_quantity' => 10,
            'quantity' => [$ownAndFree + 1], 'product_type_id' => [$this->pt], 'material_id' => [0], 'stage_id' => [$s1], 'component_id' => [''],
        ])->assertSessionHas('fails');

        $this->assertSame($before, Stock::where('is_ptc_master', 1)->max('stock_id'), 'nothing is saved');
    }

    public function test_multi_order_delivery_line_is_marked_with_the_order_whose_stock_it_took(): void
    {
        $stage = $this->stages[2];
        $theirs = $this->receiptIntoNewBatch($this->orderY, 5, $stage);
        $delivery = $this->stock(['stock_type' => 2, 'stock_status' => Stock::STATUS_DELIVERY, 'table_name' => 'delivery', 'order_id' => $this->orderX]);
        $line = $this->line($delivery->stock_id, 5, $stage);

        $this->batches->allocateStockOut($delivery->stock_id, [$this->orderX, $this->orderY], ["{$this->pt}_{$stage}" => $theirs]);
        $this->batches->markLineOrders($delivery->stock_id, [$this->orderX, $this->orderY]);

        $this->assertSame($this->orderY, (int) $line->fresh()->order_id);
    }

    public function test_stock_and_delivery_pages_show_batches(): void
    {
        $stage = $this->stages[2];
        $batchId = $this->receiptIntoNewBatch($this->orderX, 25, $stage);
        $batchNo = StockBatch::find($batchId)->batch_no;

        $this->get(route('stock'))->assertOk()->assertSee('Reserved')->assertSee($batchNo);

        $deliveryId = DB::table('deliveries')->value('delivery_id');
        $this->get(route('delivery.add', $this->orderX))->assertOk();
        if ($deliveryId) {
            $this->get(route('delivery.edit', $deliveryId))->assertOk();
        }
    }

    public function test_reserving_and_freeing_a_whole_batch(): void
    {
        $stage = $this->stages[2];
        $batchId = $this->receiptIntoNewBatch(null, 10, $stage);

        $this->post(route('batch.reserve', $batchId), ['order_id' => $this->orderY, 'note' => 'for Y'])->assertSessionHas('success');
        $this->assertSame($this->orderY, (int) StockBatch::find($batchId)->order_id);
        $this->assertNotContains($batchId, $this->batches->available($this->pt, $stage, $this->orderX)->pluck('batch_id')->all());

        $this->post(route('batch.reserve', $batchId), ['order_id' => ''])->assertSessionHas('success');
        $this->assertNull(StockBatch::find($batchId)->order_id);
        $this->assertStringContainsString('for Y', StockBatch::find($batchId)->description);
    }

    public function test_split_moves_ownership_without_changing_stock_totals(): void
    {
        $stage = $this->stages[2];
        $batchId = $this->receiptIntoNewBatch($this->orderX, 20, $stage);
        $totalsBefore = $this->productStageTotal($stage);

        $this->post(route('batch.split', $batchId), [
            'stage_id' => $stage, 'quantity' => 8, 'order_id' => '', 'transfer_date' => now()->toDateString(), 'note' => 'freed',
        ])->assertSessionHas('success');

        $child = StockBatch::where('parent_batch_id', $batchId)->firstOrFail();
        $this->assertSame(StockBatch::find($batchId)->batch_no.'-S1', $child->batch_no);
        $this->assertNull($child->order_id);
        $this->assertSame(12.0, $this->generalBalance($batchId, $stage));
        $this->assertSame(8.0, $this->generalBalance($child->batch_id, $stage));
        $this->assertSame($totalsBefore, $this->productStageTotal($stage), '/stock totals are unchanged');

        // The freed part can now go to another order; the rest stays with order X
        $this->assertContains($child->batch_id, $this->batches->available($this->pt, $stage, $this->orderY)->pluck('batch_id')->all());

        // Splitting more than the batch holds is refused
        $this->post(route('batch.split', $batchId), [
            'stage_id' => $stage, 'quantity' => 13, 'transfer_date' => now()->toDateString(),
        ])->assertSessionHas('fails');

        $movements = $this->batches->movements($batchId)->pluck('movement')->all();
        $this->assertContains('Split off', $movements);
    }

    public function test_batch_order_status_and_report_pages_render(): void
    {
        $stage = $this->stages[2];
        $batchId = $this->receiptIntoNewBatch($this->orderX, 5, $stage);

        $this->get(route('batch.index'))->assertOk()->assertSee(StockBatch::find($batchId)->batch_no);
        $this->get(route('batch.index', ['all' => 1]))->assertOk();
        $this->get(route('batch.show', $batchId))->assertOk()->assertSee('Movement History')->assertSee('Issuance receipt');
        $this->get(route('order.status', $this->orderX))->assertOk()->assertSee('To Make');
        $this->get(route('reports.product.stock.requirements'))->assertOk();
        $this->get(route('reports.product.stock.ledger', ['product_type_id' => $this->pt]))->assertOk();

        // PTC forms offer the batches the PTC's order may take from
        [$s1, , $s3] = $this->stages;
        $this->seedOpening($this->pt, $s1, 50);
        $this->get(route('ptc.create', ['order_id' => $this->orderX, 'product_type_id' => $this->pt, 'start_stage_id' => $s1, 'end_stage_id' => $s3]))
            ->assertOk()->assertSee('Take Product From Batch');
        $ptcId = $this->createPtc($s1, $this->orderX);
        $this->get(route('ptc.issue.form', $ptcId))->assertOk()->assertSee('Take Product From Batch');
        $this->get(route('ptc.edit', $ptcId))->assertOk();
    }

    public function test_order_progress_counts_reserved_stock_and_what_is_left_to_make(): void
    {
        $orderItem = DB::table('order_items')->where('order_id', $this->orderX)->first();
        if (! $orderItem) {
            $this->markTestSkipped('Order X has no lines.');
        }

        $before = $this->orderLine($orderItem);
        $issuance = $this->stock(['stock_type' => 2, 'order_id' => $this->orderX]);
        $receipt = $this->stock(['stock_type' => 1, 'order_id' => $this->orderX, 'issue_id' => $issuance->stock_id]);
        StockItem::create([
            'stock_id' => $receipt->stock_id, 'product_type_id' => $orderItem->product_type_id, 'material_id' => 0, 'quantity' => 7,
            'stage_id' => $orderItem->product_stage_id, 'work_logs' => '0', 'work_wages' => '0', 'created_by' => auth()->id(),
        ]);
        $after = $this->orderLine($orderItem);

        $this->assertEqualsWithDelta($before->reserved_ready + 7, $after->reserved_ready, 0.0001);
        $this->assertEqualsWithDelta(max(0, $before->to_make - 7), $after->to_make, 0.0001);
    }

    // ----------------------------------------------------------------------

    private function orderLine(object $orderItem): object
    {
        return $this->batches->orderProgress(
            $this->orderX,
            app(StockItemRepository::class)->orderRemainingItems($this->orderX),
            app(\App\Repositories\PtcStockRepository::class)->orderWorkInProgress($this->orderX)
        )->firstWhere('order_item_id', $orderItem->order_item_id);
    }

    /** Pooled /stock figure for the test product at a stage. */
    private function productStageTotal(int $stage): float
    {
        $row = app(StockItemRepository::class)->pStock()->where('product_type_id', $this->pt)->where('stage_id', $stage)->first();

        return $row ? round($row->stockIn - $row->stockOut, 4) : 0.0;
    }

    private function seedOpening(int $productTypeId, int $stage, float $quantity): void
    {
        StockItem::create([
            'stock_id' => 1, 'product_type_id' => $productTypeId, 'material_id' => 0, 'quantity' => $quantity,
            'stage_id' => $stage, 'work_logs' => '0', 'work_wages' => '0', 'created_by' => auth()->id(),
        ]);
    }

    /** A stock-in into a fresh batch reserved for $orderId; returns the batch id. */
    private function receiptIntoNewBatch(?int $orderId, float $quantity, int $stage): int
    {
        $issuance = $this->stock(['stock_type' => 2, 'order_id' => $orderId ?? 0]);
        $receipt = $this->stock(['stock_type' => 1, 'order_id' => $orderId ?? 0, 'issue_id' => $issuance->stock_id]);
        $this->line($receipt->stock_id, $quantity, $stage);

        return (int) StockBatch::where('source_type', StockBatch::SOURCE_ISSUANCE)->where('source_id', $issuance->stock_id)->value('batch_id');
    }

    private function stock(array $data): Stock
    {
        return Stock::create(array_merge([
            'stock_no' => 'T'.uniqid(),
            'order_id' => 0,
            'table_name' => 'employee',
            'employee_id' => $this->employeeId,
            'stock_type' => 1,
            'stock_date' => now()->toDateString(),
            'stock_status' => 1,
            'created_by' => auth()->id(),
        ], $data));
    }

    private function line(int $stockId, float $quantity, int $stage): StockItem
    {
        return StockItem::create([
            'stock_id' => $stockId, 'product_type_id' => $this->pt, 'material_id' => 0, 'quantity' => $quantity,
            'stage_id' => $stage, 'work_logs' => '0', 'work_wages' => '0', 'created_by' => auth()->id(),
        ]);
    }

    /** @return array{0: int, 1: int, 2: int} purchase_id, purchase_item_id, receive_id */
    private function purchase(int $orderId, int $stage): array
    {
        $purchaseId = DB::table('purchases')->insertGetId([
            'purchase_no' => 'TP'.uniqid(), 'purchase_type' => 'product', 'order_id' => $orderId,
            'vendor_id' => DB::table('vendors')->value('vendor_id') ?? 0,
            'purchase_date' => now()->toDateString(), 'require_date' => now()->toDateString(), 'created_by' => auth()->id(),
        ]);
        $purchaseItemId = DB::table('purchase_items')->insertGetId([
            'purchase_id' => $purchaseId, 'product_type_id' => $this->pt, 'product_stage_id' => $stage, 'material_id' => 0,
            'quantity' => 30, 'price' => 1, 'total' => 30, 'created_by' => auth()->id(),
        ]);
        $receiveId = DB::table('receives')->insertGetId([
            'receive_no' => 'TR'.uniqid(), 'purchase_id' => $purchaseId, 'receive_date' => now()->toDateString(), 'created_by' => auth()->id(),
        ]);

        return [$purchaseId, $purchaseItemId, $receiveId];
    }

    private function receiptLine(int $receiveId, int $purchaseItemId, float $approved): array
    {
        return [
            'receive_id' => $receiveId, 'purchase_item_id' => $purchaseItemId, 'quantity' => $approved, 'pending_qty' => 0,
            'approved_qty' => $approved, 'rejected_qty' => 0, 'inspection_date' => now()->toDateString(),
        ];
    }

    private function generalBalance(int $batchId, int $stage): float
    {
        return round((float) $this->batches->balances(['batch_id' => $batchId, 'stage_id' => $stage])->sum('balance'), 4);
    }

    private function assertAllocated(int $stockItemId, array $expected): void
    {
        $actual = DB::table('stock_item_batches')->where('stock_item_id', $stockItemId)
            ->pluck('quantity', 'batch_id')->map(fn ($qty) => (float) $qty)->all();
        ksort($actual);
        ksort($expected);
        $this->assertEquals(array_map('floatval', $expected), $actual);
    }

    /** Stock-in / PTC transfer lines of a PTC without a batch allocation. */
    private function unallocatedIn(int $ptcId): array
    {
        $stockIds = Stock::where('ptc_id', $ptcId)->pluck('stock_id');

        return $this->batches->unallocatedLines()
            ->whereIn('stock_id', $stockIds->all())
            ->pluck('stock_item_id')
            ->all();
    }

    private function createPtc(int $startStage, int $orderId): int
    {
        $this->post(route('ptc.store'), [
            'stock_no' => app(StockRepository::class)->ptcRefNo().'T',
            'stock_date' => now()->toDateString(),
            'ptc_product_type_id' => $this->pt,
            'start_stage_id' => $startStage,
            'end_stage_id' => $this->stages[2],
            'order_id' => $orderId,
            'table_name' => 'employee',
            'employee_id' => $this->employeeId,
            'product_quantity' => 10,
            'quantity' => [10], 'product_type_id' => [$this->pt], 'material_id' => [0], 'stage_id' => [$startStage], 'component_id' => [''],
        ]);

        return (int) Stock::where('is_ptc_master', 1)->max('stock_id');
    }

    private function issue(int $ptcId, int $issueFor, float $quantity, int $fromStage)
    {
        return $this->post(route('ptc.issue.store', $ptcId), [
            'stock_date' => now()->toDateString(),
            'issue_for' => $issueFor,
            'table_name' => 'employee',
            'employee_id' => $this->employeeId,
            'quantity' => [$quantity], 'product_type_id' => [$this->pt], 'material_id' => [0],
            'stage_id' => [$fromStage], 'component_id' => [''], 'source' => ['ptc'],
        ]);
    }

    private function receive(int $ptcId, int $issuanceId, float $quantity, int $stage)
    {
        return $this->post(route('ptc.receive.store', $ptcId), [
            'issue_id' => $issuanceId,
            'stock_date' => now()->toDateString(),
            'table_name' => 'employee',
            'employee_id' => $this->employeeId,
            'r_quantity' => [$quantity], 'r_product_type_id' => [$this->pt], 'r_material_id' => [0],
            'r_stage_id' => [$stage], 'r_work_logs' => ['0'], 'r_component_product_type_id' => [''],
        ]);
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

    private function latestIssuance(int $ptcId): int
    {
        return (int) Stock::where('ptc_id', $ptcId)->where('stock_type', 2)->max('stock_id');
    }
}
