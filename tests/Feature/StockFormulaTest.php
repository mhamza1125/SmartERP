<?php

namespace Tests\Feature;

use App\Repositories\PurchaseItemRepository;
use App\Repositories\StockItemRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * F-02: a receipt line with several partial returns must be counted once.
 * Receive 100, return 10, return 5 -> 85 everywhere stock is computed.
 */
class StockFormulaTest extends TestCase
{
    use DatabaseTransactions;

    private function repo(): StockItemRepository
    {
        return app(StockItemRepository::class);
    }

    /** Purchase + receipt of 100 (all approved) + two returns (10, 5) of the same receipt line. */
    private function receiveAndReturnTwice(int $materialId, int $productTypeId = 0, int $stageId = 0, ?int $orderId = null): int
    {
        $u = uniqid();
        $vendorId = (int) DB::table('vendors')->value('vendor_id');
        $purchaseId = DB::table('purchases')->insertGetId([
            'purchase_no' => 'ZZP-'.$u, 'purchase_type' => $materialId ? 'material' : 'product', 'order_id' => $orderId,
            'vendor_id' => $vendorId, 'purchase_date' => '2026-01-01', 'require_date' => '2026-01-01', 'created_by' => 1,
        ]);
        $itemId = DB::table('purchase_items')->insertGetId([
            'purchase_id' => $purchaseId, 'product_type_id' => $productTypeId, 'product_stage_id' => $stageId,
            'material_id' => $materialId, 'quantity' => 100, 'price' => 2, 'total' => 200, 'created_by' => 1,
        ]);
        $receiveId = DB::table('receives')->insertGetId([
            'receive_no' => 'ZZR-'.$u, 'purchase_id' => $purchaseId, 'receive_date' => '2026-01-02', 'created_by' => 1,
        ]);
        $lineId = DB::table('receive_materials')->insertGetId([
            'receive_id' => $receiveId, 'purchase_item_id' => $itemId, 'quantity' => 100, 'pending_qty' => 0,
            'approved_qty' => 100, 'rejected_qty' => 0, 'inspection_date' => '2026-01-02', 'created_by' => 1,
        ]);
        foreach ([10, 5] as $i => $qty) {
            $returnId = DB::table('returns')->insertGetId([
                'receive_id' => $receiveId, 'return_no' => "ZZRT-{$u}-{$i}", 'return_date' => '2026-01-03', 'created_by' => 1,
            ]);
            DB::table('return_materials')->insert([
                'return_id' => $returnId, 'receive_material_id' => $lineId, 'quantity' => $qty, 'created_by' => 1,
            ]);
        }

        return $purchaseId;
    }

    private function materialRow($rows, int $materialId): ?object
    {
        return collect($rows)->firstWhere('material_id', $materialId);
    }

    private function materialBalance(?object $row): float
    {
        return $row ? (float) $row->total_received - (float) $row->total_returned + (float) $row->stockIn - (float) $row->stockOut : 0.0;
    }

    public function test_material_stock_counts_a_receipt_with_two_returns_once(): void
    {
        $materialId = (int) DB::table('materials')->where('material_type_id', '!=', 101)->value('material_id');
        $before = $this->repo()->materialStock($materialId);
        $freeBefore = $this->materialBalance($this->materialRow($this->repo()->freeStock(), $materialId));

        $purchaseId = $this->receiveAndReturnTwice($materialId);

        $this->assertEqualsWithDelta($before + 85, $this->repo()->materialStock($materialId), 0.0001, 'material page / availability');
        $this->assertEqualsWithDelta($before + 85, $this->materialBalance($this->materialRow($this->repo()->stock(), $materialId)), 0.0001, '/stock/material');
        $this->assertEqualsWithDelta($freeBefore + 85, $this->materialBalance($this->materialRow($this->repo()->freeStock(), $materialId)), 0.0001, 'free stock');

        $line = app(PurchaseItemRepository::class)->receive($purchaseId)->first();
        $this->assertEqualsWithDelta(100, (float) $line->received, 0.0001, 'purchase receive form: received');
        $this->assertEqualsWithDelta(15, (float) $line->returned, 0.0001, 'purchase receive form: returned');
    }

    public function test_material_stock_for_an_order_counts_a_receipt_with_two_returns_once(): void
    {
        $materialId = (int) DB::table('materials')->where('material_type_id', 96)->value('material_id');
        if (! $materialId) {
            $this->markTestSkipped('needs a material of type 96');
        }
        $orderId = (int) DB::table('orders')->value('order_id');
        $row = fn () => $this->materialRow($this->repo()->stockVehicle($orderId), $materialId);
        $before = $row() ? (float) $row()->total_received - (float) $row()->total_returned : 0.0;

        $this->receiveAndReturnTwice($materialId, 0, 0, $orderId);

        $this->assertEqualsWithDelta($before + 85, (float) $row()->total_received - (float) $row()->total_returned, 0.0001);
    }

    public function test_product_stock_counts_a_receipt_with_two_returns_once(): void
    {
        $line = DB::table('stock_items')->where('material_id', 0)->whereNull('component_product_type_id')->where('ptc_virtual', 0)->where('stage_id', '>', 0)->first();
        $pt = (int) $line->product_type_id;
        $stage = (int) $line->stage_id;
        $at = fn ($rows) => collect($rows)->first(fn ($r) => (int) $r->product_type_id === $pt && (int) $r->stage_id === $stage);
        $balance = fn ($row) => $row ? (float) $row->stockIn - (float) $row->stockOut : 0.0;

        $before = $balance($at($this->repo()->pStock()));
        $purchaseId = $this->receiveAndReturnTwice(0, $pt, $stage);

        $this->assertEqualsWithDelta($before + 85, $balance($at($this->repo()->pStock())), 0.0001, '/stock');
        $this->assertEqualsWithDelta($before + 85, $balance($at($this->repo()->pStockGet($pt))), 0.0001, 'pStockGet');
        $this->assertEqualsWithDelta($before + 85, $balance($at($this->repo()->pStockByProductType($pt))), 0.0001, 'pStockByProductType');
        $this->assertEqualsWithDelta($before + 85, $balance($at($this->repo()->pStockForProductTypes([$pt])->get($pt))), 0.0001, 'pStockForProductTypes');

        $received = app(PurchaseItemRepository::class)->receiveProducts($purchaseId)->first();
        $this->assertEqualsWithDelta(100, (float) $received->received, 0.0001);
        $this->assertEqualsWithDelta(15, (float) $received->returned, 0.0001);
    }

    public function test_no_component_is_stored_as_null_never_zero(): void
    {
        $this->actingAs(\App\Models\User::where("role_id", \App\Models\User::ROLE_ADMIN)->firstOrFail());
        $base = ['stock_id' => 1, 'product_type_id' => (int) DB::table('product_types')->value('product_type_id'), 'material_id' => 0, 'quantity' => 1, 'stage_id' => 0, 'work_logs' => 0, 'work_wages' => 0, 'created_by' => 1];

        foreach ([0, '0', '', null] as $none) {
            $line = \App\Models\StockItem::create($base + ['component_product_type_id' => $none]);
            $this->assertNull(DB::table('stock_items')->where('stock_item_id', $line->stock_item_id)->value('component_product_type_id'), var_export($none, true).' must be stored as NULL');
            $line->update(['component_product_type_id' => 0]);
            $this->assertNull(DB::table('stock_items')->where('stock_item_id', $line->stock_item_id)->value('component_product_type_id'), 'update with 0 must store NULL');
        }

        $line = \App\Models\StockItem::create($base + ['component_product_type_id' => 407]);
        $this->assertSame(407, (int) DB::table('stock_items')->where('stock_item_id', $line->stock_item_id)->value('component_product_type_id'));
    }

    public function test_order_status_counts_a_receipt_with_two_returns_once(): void
    {
        $item = DB::table('order_items')->first();
        $pt = (int) $item->product_type_id;
        $stage = (int) $item->product_stage_id;
        $at = fn () => collect($this->repo()->orderStatus($item->order_id))->first(fn ($r) => (int) $r->product_type_id === $pt && (int) $r->stage_id === $stage);
        $before = $at() ? (float) $at()->stockIn - (float) $at()->stockOut : 0.0;

        $this->receiveAndReturnTwice(0, $pt, $stage);

        $this->assertEqualsWithDelta($before + 85, (float) $at()->stockIn - (float) $at()->stockOut, 0.0001);
    }
}
