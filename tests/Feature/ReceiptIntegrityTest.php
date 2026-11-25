<?php

namespace Tests\Feature;

use App\Models\User;
use App\Repositories\StockItemRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * F-04 / F-17 / F-18, D2, D4: purchase receipts are validated, atomic and locked;
 * receiving above the ordered quantity is refused; used receipts cannot be lowered or dropped.
 */
class ReceiptIntegrityTest extends TestCase
{
    use DatabaseTransactions;

    private int $materialId;

    private int $purchaseId;

    /** @var array<int> purchase_item_id of lines A (ordered 100) and B (ordered 50) */
    private array $items;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::where('role_id', User::ROLE_ADMIN)->orderBy('id')->firstOrFail());
        $this->materialId = (int) DB::table('materials')->where('material_type_id', '!=', 101)->orderBy('material_id')->value('material_id');
        $other = (int) DB::table('materials')->where('material_id', '!=', $this->materialId)->orderBy('material_id')->value('material_id');
        $this->purchaseId = DB::table('purchases')->insertGetId([
            'purchase_no' => 'ZZP-'.uniqid(), 'purchase_type' => 'material', 'order_id' => 0,
            'vendor_id' => (int) DB::table('vendors')->value('vendor_id'), 'purchase_date' => '2026-01-01', 'require_date' => '2026-01-01', 'created_by' => 1,
        ]);
        foreach ([[$this->materialId, 100], [$other, 50]] as [$material, $qty]) {
            $this->items[] = DB::table('purchase_items')->insertGetId([
                'purchase_id' => $this->purchaseId, 'product_type_id' => 0, 'product_stage_id' => 0, 'material_id' => $material,
                'quantity' => $qty, 'price' => 2, 'total' => 2 * $qty, 'created_by' => 1,
            ]);
        }
    }

    /** @param array<int, array{0: float, 1?: float, 2?: float, 3?: float}> $lines [quantity, approved, rejected, pending] per item index */
    private function lines(array $lines): array
    {
        $out = ['purchase_item_id' => [], 'quantity' => [], 'approved_qty' => [], 'rejected_qty' => [], 'pending_qty' => [], 'inspection_date' => []];
        foreach ($lines as $index => $line) {
            $out['purchase_item_id'][] = $this->items[$index];
            $out['quantity'][] = $line[0];
            $out['approved_qty'][] = $line[1] ?? $line[0];
            $out['rejected_qty'][] = $line[2] ?? 0;
            $out['pending_qty'][] = $line[3] ?? 0;
            $out['inspection_date'][] = '2026-01-02';
        }

        return $out;
    }

    private function receive(array $lines, array $extra = [])
    {
        return $this->post(route('receive.store'), array_merge([
            'receive_no' => 'ZZR-'.uniqid(), 'purchase_id' => $this->purchaseId, 'receive_date' => '2026-01-02', 'description' => '',
        ], $this->lines($lines), $extra));
    }

    private function lastReceiveId(): int
    {
        return (int) DB::table('receives')->where('purchase_id', $this->purchaseId)->max('receive_id');
    }

    private function edit(int $receiveId, array $lines, array $extra = [])
    {
        return $this->post(route('receive.update', $receiveId), array_merge(['receive_date' => '2026-01-03', 'description' => 'edited'], $this->lines($lines), $extra));
    }

    public function test_quantities_must_be_numbers_and_split_must_add_up(): void
    {
        $this->receive([[10, 5, 0, 0]])->assertSessionHasErrors('approved_qty.0');
        $this->receive([[-1, -1]])->assertSessionHasErrors('quantity.0');
        $this->receive([['abc', 0]])->assertSessionHasErrors('quantity.0');
        $this->receive([[10, 4, 3, 3]])->assertSessionHasNoErrors()->assertSessionMissing('fails');
    }

    public function test_lines_must_belong_to_the_purchase(): void
    {
        $foreign = (int) DB::table('purchase_items')->where('purchase_id', '!=', $this->purchaseId)->value('purchase_item_id');
        $this->receive([[5]], ['purchase_item_id' => [$foreign]])->assertSessionHasErrors('purchase_item_id.0');
    }

    public function test_receiving_above_the_ordered_quantity_is_refused(): void
    {
        $this->receive([[60]])->assertSessionMissing('fails');
        $before = DB::table('receives')->count();

        $this->receive([[50]])->assertSessionHas('fails', fn ($m) => str_contains($m, 'ordered 100'));
        $this->assertSame($before, DB::table('receives')->count(), 'nothing saved');

        $this->receive([[40]])->assertSessionMissing('fails');
        $this->assertEqualsWithDelta(100, (float) DB::table('receive_materials')->where('purchase_item_id', $this->items[0])->sum('quantity'), 0.0001);
    }

    public function test_an_already_over_received_line_does_not_block_other_saves(): void
    {
        // Legacy row (like P2611002): 125 received against 100 ordered, written before the rule
        $legacy = DB::table('receives')->insertGetId(['receive_no' => 'ZZL-'.uniqid(), 'purchase_id' => $this->purchaseId, 'receive_date' => '2026-01-02', 'created_by' => 1]);
        DB::table('receive_materials')->insert(['receive_id' => $legacy, 'purchase_item_id' => $this->items[0], 'quantity' => 125, 'approved_qty' => 125,
            'pending_qty' => 0, 'rejected_qty' => 0, 'inspection_date' => '2026-01-02', 'created_by' => 1]);

        // receiving the other line only is fine
        $this->receive([[0], [20]])->assertSessionMissing('fails');
        // editing the legacy receipt without touching its quantities is fine
        $this->edit($legacy, [[125]])->assertSessionMissing('fails');
        $this->assertSame('edited', DB::table('receives')->where('receive_id', $legacy)->value('description'));
        // lowering it (still above ordered) is fine; raising it is refused
        $this->edit($legacy, [[120]])->assertSessionMissing('fails');
        $this->edit($legacy, [[121]])->assertSessionHas('fails');
        // receiving more of it is refused
        $this->receive([[1]])->assertSessionHas('fails');
    }

    public function test_receipt_with_a_return_cannot_be_lowered_or_dropped(): void
    {
        $this->receive([[50], [10]])->assertSessionMissing('fails');
        $receiveId = $this->lastReceiveId();
        $line = DB::table('receive_materials')->where('receive_id', $receiveId)->where('purchase_item_id', $this->items[0])->first();
        $returnId = DB::table('returns')->insertGetId(['receive_id' => $receiveId, 'return_no' => 'ZZRT-'.uniqid(), 'return_date' => '2026-01-04', 'created_by' => 1]);
        DB::table('return_materials')->insert(['return_id' => $returnId, 'receive_material_id' => $line->receive_material_id, 'quantity' => 5, 'created_by' => 1]);

        $this->edit($receiveId, [[40], [10]])->assertSessionHas('fails', fn ($m) => str_contains($m, 'ask an admin'));
        $this->edit($receiveId, [1 => [10]])->assertSessionHas('fails', fn ($m) => str_contains($m, 'ask an admin'));
        $this->assertEqualsWithDelta(50, (float) DB::table('receive_materials')->where('receive_material_id', $line->receive_material_id)->value('approved_qty'), 0.0001);

        // harmless fields stay editable; raising stays possible (up to ordered)
        $this->edit($receiveId, [[55], [10]])->assertSessionMissing('fails');
        $this->assertSame('edited', DB::table('receives')->where('receive_id', $receiveId)->value('description'));
    }

    public function test_receipt_whose_stock_was_used_cannot_be_lowered_below_what_was_used(): void
    {
        $repo = app(StockItemRepository::class);
        $this->receive([[50]])->assertSessionMissing('fails');
        $receiveId = $this->lastReceiveId();
        $onHand = $repo->materialStock($this->materialId);
        // use everything now on hand (issued out)
        $stockId = DB::table('stocks')->insertGetId(['stock_no' => 'ZZU-'.uniqid(), 'order_id' => 0, 'table_name' => 'employee', 'employee_id' => 0,
            'stock_type' => 2, 'stock_date' => '2026-01-05', 'stock_status' => 0, 'created_by' => 1]);
        DB::table('stock_items')->insert(['stock_id' => $stockId, 'product_type_id' => 0, 'material_id' => $this->materialId, 'quantity' => $onHand,
            'stage_id' => 0, 'work_logs' => 0, 'work_wages' => 0, 'created_by' => 1]);

        $this->edit($receiveId, [[30]])->assertSessionHas('fails', fn ($m) => str_contains($m, 'ask an admin'));
        $this->assertEqualsWithDelta(0, $repo->materialStock($this->materialId), 0.0001);

        DB::table('stock_items')->where('stock_id', $stockId)->update(['quantity' => $onHand - 20]);
        $this->edit($receiveId, [[30]])->assertSessionMissing('fails');
    }

    public function test_receipt_save_locks_the_purchase_lines(): void
    {
        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = strtolower($query->sql);
        });
        $this->receive([[5]])->assertSessionMissing('fails');

        $this->assertNotEmpty(array_filter($queries, fn ($sql) => str_contains($sql, 'from `purchase_items`') && str_contains($sql, 'for update')));
    }

    public function test_receipt_header_cannot_move_to_another_purchase(): void
    {
        $this->receive([[5]])->assertSessionMissing('fails');
        $receiveId = $this->lastReceiveId();
        $otherPurchase = (int) DB::table('purchases')->where('purchase_id', '!=', $this->purchaseId)->value('purchase_id');

        $this->edit($receiveId, [[5]], ['purchase_id' => $otherPurchase, 'receive_status' => 1, 'created_by' => 999]);

        $row = DB::table('receives')->where('receive_id', $receiveId)->first();
        $this->assertSame($this->purchaseId, (int) $row->purchase_id);
        $this->assertSame(0, (int) $row->receive_status);
        $this->assertNotSame(999, (int) $row->created_by);
    }
}
