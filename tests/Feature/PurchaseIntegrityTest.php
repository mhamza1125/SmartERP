<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * F-05 / F-18, D4: a received purchase line cannot change price, item, or quantity below what
 * was received, and cannot be removed; header fields stay editable; the save is atomic.
 */
class PurchaseIntegrityTest extends TestCase
{
    use DatabaseTransactions;

    private int $purchaseId;

    private array $materials;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::where('role_id', User::ROLE_ADMIN)->orderBy('id')->firstOrFail());
        $this->materials = DB::table('materials')->where('material_type_id', '!=', 101)->orderBy('material_id')->limit(3)->pluck('material_id')->map(fn ($v) => (int) $v)->all();
        $this->purchaseId = DB::table('purchases')->insertGetId([
            'purchase_no' => 'ZZP-'.uniqid(), 'purchase_type' => 'material', 'order_id' => 0,
            'vendor_id' => (int) DB::table('vendors')->value('vendor_id'), 'purchase_date' => '2026-01-01', 'require_date' => '2026-01-01', 'created_by' => 1,
        ]);
        foreach ([[$this->materials[0], 100, 2], [$this->materials[1], 50, 3]] as [$material, $qty, $price]) {
            DB::table('purchase_items')->insert(['purchase_id' => $this->purchaseId, 'product_type_id' => 0, 'product_stage_id' => 0,
                'material_id' => $material, 'quantity' => $qty, 'price' => $price, 'total' => $qty * $price, 'created_by' => 1]);
        }
        // 40 of the first line received
        $itemId = DB::table('purchase_items')->where('purchase_id', $this->purchaseId)->where('material_id', $this->materials[0])->value('purchase_item_id');
        $receiveId = DB::table('receives')->insertGetId(['receive_no' => 'ZZR-'.uniqid(), 'purchase_id' => $this->purchaseId, 'receive_date' => '2026-01-02', 'created_by' => 1]);
        DB::table('receive_materials')->insert(['receive_id' => $receiveId, 'purchase_item_id' => $itemId, 'quantity' => 40, 'approved_qty' => 40,
            'pending_qty' => 0, 'rejected_qty' => 0, 'inspection_date' => '2026-01-02', 'created_by' => 1]);
    }

    /** @param array<int, array{0:int,1:float,2:float}> $lines [material, quantity, price] */
    private function edit(array $lines, array $extra = [])
    {
        $purchase = DB::table('purchases')->where('purchase_id', $this->purchaseId)->first();

        return $this->post(route('purchase.update', $this->purchaseId), array_merge([
            'purchase_no' => $purchase->purchase_no, 'purchase_type' => 'material', 'vendor_id' => $purchase->vendor_id, 'order_id' => 0,
            'purchase_date' => '2026-01-01', 'require_date' => '2026-01-05', 'description' => 'edited',
            'material_id' => array_column($lines, 0), 'product_type_id' => array_fill(0, count($lines), 0), 'product_stage_id' => array_fill(0, count($lines), 0),
            'quantity' => array_column($lines, 1), 'price' => array_column($lines, 2), 'total' => array_map(fn ($l) => $l[1] * $l[2], $lines),
        ], $extra));
    }

    private function line(int $material): ?object
    {
        return DB::table('purchase_items')->where('purchase_id', $this->purchaseId)->where('material_id', $material)->first();
    }

    public function test_received_line_price_cannot_change(): void
    {
        $this->edit([[$this->materials[0], 100, 2.5], [$this->materials[1], 50, 3]])->assertSessionHas('fails', fn ($m) => str_contains($m, 'ask an admin'));
        $this->assertEqualsWithDelta(2, (float) $this->line($this->materials[0])->price, 0.0001);
        $this->assertSame('', (string) DB::table('purchases')->where('purchase_id', $this->purchaseId)->value('description'), 'nothing saved');
    }

    public function test_received_line_cannot_be_removed_or_replaced_by_another_item(): void
    {
        $this->edit([[$this->materials[1], 50, 3]])->assertSessionHas('fails');
        $this->edit([[$this->materials[2], 100, 2], [$this->materials[1], 50, 3]])->assertSessionHas('fails');
        $this->assertNotNull($this->line($this->materials[0]));
    }

    public function test_received_line_quantity_cannot_go_below_received(): void
    {
        $this->edit([[$this->materials[0], 39, 2], [$this->materials[1], 50, 3]])->assertSessionHas('fails', fn ($m) => str_contains($m, 'received'));
        $this->edit([[$this->materials[0], 40, 2], [$this->materials[1], 50, 3]])->assertSessionMissing('fails');
        $this->assertEqualsWithDelta(40, (float) $this->line($this->materials[0])->quantity, 0.0001);
    }

    public function test_unreceived_lines_and_header_stay_editable(): void
    {
        $this->edit([[$this->materials[0], 100, 2], [$this->materials[1], 60, 4], [$this->materials[2], 5, 1]])->assertSessionMissing('fails');
        $this->assertEqualsWithDelta(4, (float) $this->line($this->materials[1])->price, 0.0001);
        $this->assertEqualsWithDelta(240, (float) $this->line($this->materials[1])->total, 0.0001, 'total computed by the server');
        $this->assertNotNull($this->line($this->materials[2]));
        $this->assertSame('edited', DB::table('purchases')->where('purchase_id', $this->purchaseId)->value('description'));

        // and an unreceived line can be removed
        $this->edit([[$this->materials[0], 100, 2], [$this->materials[1], 60, 4]])->assertSessionMissing('fails');
        $this->assertNull($this->line($this->materials[2]));
    }

    public function test_purchase_number_type_and_creator_are_not_editable(): void
    {
        $before = DB::table('purchases')->where('purchase_id', $this->purchaseId)->first();
        $this->edit([[$this->materials[0], 100, 2], [$this->materials[1], 50, 3]], ['purchase_no' => 'HACKED', 'purchase_type' => 'product', 'created_by' => 999])
            ->assertSessionMissing('fails');
        $after = DB::table('purchases')->where('purchase_id', $this->purchaseId)->first();
        $this->assertSame($before->purchase_no, $after->purchase_no);
        $this->assertSame('material', $after->purchase_type);
        $this->assertSame((int) $before->created_by, (int) $after->created_by);
    }

    public function test_received_material_processing_lines_cannot_be_rewritten(): void
    {
        $material = $this->materials[0];
        // seed stock so the processing issue itself is allowed
        DB::table('stock_items')->insert(['stock_id' => 1, 'product_type_id' => 0, 'material_id' => $material, 'quantity' => 1000, 'stage_id' => 0, 'work_logs' => 0, 'work_wages' => 0, 'created_by' => 1]);
        $payload = fn ($bquantity, $extra = []) => array_merge([
            'purchase_no' => 'ZZMP-'.uniqid(), 'purchase_type' => 'mprocess', 'vendor_id' => (int) DB::table('vendors')->value('vendor_id'),
            'order_id' => 0, 'purchase_date' => '2026-01-01', 'require_date' => '2026-01-01', 'description' => '',
            'amaterial_id' => [$material], 'aquantity' => [10], 'bmaterial_id' => [$this->materials[1]], 'bquantity' => [$bquantity], 'price' => [1], 'total' => [$bquantity],
        ], $extra);
        $this->post(route('mprocess.store'), $payload(10))->assertSessionMissing('fails');
        $mprocessId = (int) DB::table('purchases')->where('purchase_type', 'mprocess')->max('purchase_id');
        $itemId = DB::table('purchase_items')->where('purchase_id', $mprocessId)->value('purchase_item_id');
        $receiveId = DB::table('receives')->insertGetId(['receive_no' => 'ZZR-'.uniqid(), 'purchase_id' => $mprocessId, 'receive_date' => '2026-01-02', 'created_by' => 1]);
        DB::table('receive_materials')->insert(['receive_id' => $receiveId, 'purchase_item_id' => $itemId, 'quantity' => 5, 'approved_qty' => 5,
            'pending_qty' => 0, 'rejected_qty' => 0, 'inspection_date' => '2026-01-02', 'created_by' => 1]);

        $this->post(route('mprocess.update', $mprocessId), $payload(12))->assertSessionHas('fails', fn ($m) => str_contains($m, 'ask an admin'));
        $this->assertSame((int) $itemId, (int) DB::table('purchase_items')->where('purchase_id', $mprocessId)->value('purchase_item_id'), 'received line kept');

        // header-only edit is still possible
        $this->post(route('mprocess.update', $mprocessId), $payload(10, ['description' => 'note']))->assertSessionMissing('fails');
        $this->assertSame('note', DB::table('purchases')->where('purchase_id', $mprocessId)->value('description'));
        $this->assertSame((int) $itemId, (int) DB::table('purchase_items')->where('purchase_id', $mprocessId)->value('purchase_item_id'));
    }

    public function test_rewriting_unreceived_processing_lines_leaves_no_empty_stock_documents(): void
    {
        $material = $this->materials[0];
        DB::table('stock_items')->insert(['stock_id' => 1, 'product_type_id' => 0, 'material_id' => $material, 'quantity' => 1000, 'stage_id' => 0, 'work_logs' => 0, 'work_wages' => 0, 'created_by' => 1]);
        $payload = fn ($aquantity) => [
            'purchase_no' => 'ZZMP-'.uniqid(), 'purchase_type' => 'mprocess', 'vendor_id' => (int) DB::table('vendors')->value('vendor_id'),
            'order_id' => 0, 'purchase_date' => '2026-01-01', 'require_date' => '2026-01-01', 'description' => '',
            'amaterial_id' => [$material], 'aquantity' => [$aquantity], 'bmaterial_id' => [$this->materials[1]], 'bquantity' => [10], 'price' => [1], 'total' => [10],
        ];
        $this->post(route('mprocess.store'), $payload(10))->assertSessionMissing('fails');
        $mprocessId = (int) DB::table('purchases')->where('purchase_type', 'mprocess')->max('purchase_id');
        $headers = fn () => DB::table('stocks')->where('table_name', 'mprocess')->where('description', 'Material sent for processing (Purchase #'.$mprocessId.')')->count();
        $this->assertSame(1, $headers());

        $this->post(route('mprocess.update', $mprocessId), $payload(12))->assertSessionMissing('fails');
        $this->assertSame(1, $headers(), 'the old stock document is removed with its line');
    }
}
