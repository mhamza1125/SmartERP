<?php

namespace Tests\Feature;

use App\Models\Stock;
use App\Models\User;
use App\Repositories\StockItemRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * F-03 / F-06 / F-23 / F-25: control fields (stock direction, status, document type, order
 * status, totals, amounts) are decided by the server, never by hidden form inputs.
 */
class ClientControlledFieldsTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::where('role_id', User::ROLE_ADMIN)->orderBy('id')->firstOrFail();
    }

    /** A material that has at least 1 unit in stock. */
    private function materialInStock(): int
    {
        $repo = app(StockItemRepository::class);
        foreach (DB::table('materials')->pluck('material_id') as $id) {
            if ($repo->materialStock((int) $id) >= 1) {
                return (int) $id;
            }
        }
        $this->markTestSkipped('needs a material with stock');
    }

    private function issuancePayload(array $overrides = []): array
    {
        return array_merge([
            'stock_no' => 'ZZT-'.uniqid(),
            'order_id' => (int) DB::table('orders')->value('order_id'),
            'employee_id' => (int) DB::table('employees')->value('employee_id'),
            'table_name' => 'employee',
            'stock_date' => now()->toDateString(),
            'stock_type' => 2,
            'stock_status' => 0,
            'issue_for' => 0,
            'product_type_id' => [0],
            'material_id' => [$this->materialInStock()],
            'stage_id' => [0],
            'quantity' => [1],
            'work_logs' => [0],
        ], $overrides);
    }

    public function test_issuance_direction_and_status_are_set_by_the_server(): void
    {
        $this->actingAs($this->admin());
        // in-list values that do not fit an issuance: ignored, the server decides
        $payload = $this->issuancePayload(['stock_type' => 1, 'stock_status' => 2]);

        $this->post(route('stock.store'), $payload)->assertSessionHasNoErrors();

        $row = Stock::orderByDesc('stock_id')->firstOrFail();
        $this->assertSame(2, (int) $row->stock_type, 'an issuance is always a stock-out');
        $this->assertSame(Stock::STATUS_NOT_RECEIVED, (int) $row->stock_status);
        $this->assertSame('employee', $row->table_name);
    }

    public function test_unknown_table_name_or_status_is_rejected(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('stock.store'), $this->issuancePayload(['table_name' => 'transactions']))->assertSessionHasErrors('table_name');
        $this->post(route('stock.store'), $this->issuancePayload(['stock_status' => 9]))->assertSessionHasErrors('stock_status');
        $this->post(route('stock.store'), $this->issuancePayload(['stock_type' => 5]))->assertSessionHasErrors('stock_type');
    }

    public function test_receiving_cannot_change_the_status_of_a_document_that_is_not_an_issuance(): void
    {
        $this->actingAs($this->admin());
        $ptc = Stock::where('is_ptc_master', 1)->firstOrFail();
        $status = (int) $ptc->stock_status;

        $this->post(route('stock.store'), $this->issuancePayload([
            'issue_id' => $ptc->stock_id, 'stock_type' => 1, 'stock_status' => 2,
        ]))->assertSessionHasErrors('issue_id');

        $this->assertSame($status, (int) $ptc->fresh()->stock_status);
    }

    public function test_issuance_edit_cannot_rewrite_control_columns(): void
    {
        $this->actingAs($this->admin());
        $payload = $this->issuancePayload();
        $this->post(route('stock.store'), $payload)->assertSessionHasNoErrors();
        $row = Stock::orderByDesc('stock_id')->firstOrFail();
        $ptcId = (int) Stock::where('is_ptc_master', 1)->value('stock_id');

        $this->post(route('stock.update', $row->stock_id), array_merge($payload, [
            'description' => 'edited',
            'stock_type' => 1, 'stock_status' => 1, 'table_name' => 'vendor',
            'ptc_id' => $ptcId, 'is_ptc_master' => 1, 'issue_id' => $ptcId, 'created_by' => 999,
        ]))->assertSessionHasNoErrors();

        $row = $row->fresh();
        $this->assertSame('edited', $row->description, 'harmless field still editable');
        $this->assertSame(2, (int) $row->stock_type);
        $this->assertSame(Stock::STATUS_NOT_RECEIVED, (int) $row->stock_status);
        $this->assertEmpty($row->ptc_id);
        $this->assertSame(0, (int) $row->is_ptc_master);
        $this->assertEmpty($row->issue_id);
        $this->assertNotSame(999, (int) $row->created_by);
        $this->assertSame('vendor', $row->table_name, 'worker type (employee / vendor) stays a user choice');
    }

    public function test_stock_update_refuses_documents_that_are_not_issuances(): void
    {
        $this->actingAs($this->admin());
        $ptc = Stock::where('is_ptc_master', 1)->firstOrFail();
        $before = $ptc->toArray();

        $this->post(route('stock.update', $ptc->stock_id), $this->issuancePayload(['description' => 'hijack']))
            ->assertStatus(404);

        $this->assertSame($before['description'], $ptc->fresh()->description);
    }

    public function test_delivery_edit_cannot_target_another_stock_row_or_flip_direction(): void
    {
        $this->actingAs($this->admin());
        // A delivery whose product lines are all batch-allocated, so re-saving it succeeds
        $delivery = DB::table('deliveries')
            ->whereNotExists(fn ($q) => $q->from('delivery_returns')->whereColumn('delivery_returns.delivery_id', 'deliveries.delivery_id'))
            ->whereExists(fn ($q) => $q->from('stock_items')->whereColumn('stock_items.stock_id', 'deliveries.stock_id')->where('stock_items.material_id', 0))
            ->whereNotExists(fn ($q) => $q->from('stock_items')->whereColumn('stock_items.stock_id', 'deliveries.stock_id')->where('stock_items.material_id', 0)
                ->whereNotExists(fn ($b) => $b->from('stock_item_batches')->whereColumn('stock_item_batches.stock_item_id', 'stock_items.stock_item_id')))
            ->first();
        if (! $delivery) {
            $this->markTestSkipped('needs a fully batch-allocated delivery');
        }
        $stock = Stock::findOrFail($delivery->stock_id);
        $other = Stock::where('is_ptc_master', 1)->firstOrFail();
        $otherBefore = $other->only(['stock_type', 'stock_status', 'description', 'table_name', 'stock_date']);
        $lines = DB::table('stock_items')->where('stock_id', $stock->stock_id)->get();

        $this->post(route('delivery.update', $delivery->delivery_id), [
            'stock_id' => $other->stock_id,
            'order_id' => $stock->order_id,
            'employee_id' => $stock->employee_id,
            'stock_date' => $stock->stock_date,
            'table_name' => 'delivery', 'stock_type' => 2, 'stock_status' => 3,
            'description' => 'tampered',
            'order_status' => DB::table('orders')->where('order_id', $stock->order_id)->value('order_status'),
            'delivery_method' => $delivery->delivery_method, 'delivery_status' => $delivery->delivery_status,
            'product_type_id' => $lines->pluck('product_type_id')->all(),
            'material_id' => $lines->pluck('material_id')->all(),
            'stage_id' => $lines->pluck('stage_id')->all(),
            'quantity' => $lines->pluck('quantity')->all(),
        ]);

        $this->assertSame($otherBefore, $other->fresh()->only(array_keys($otherBefore)), 'another document was not touched');
        $stock = $stock->fresh();
        $this->assertSame('tampered', $stock->description, 'the edit lands on the delivery\'s own stock row');
        $this->assertSame(2, (int) $stock->stock_type);
        $this->assertSame(Stock::STATUS_DELIVERY, (int) $stock->stock_status);
        $this->assertSame('delivery', $stock->table_name);

        // A different direction / type / status is refused outright
        foreach (['stock_type' => 1, 'stock_status' => 0, 'table_name' => 'employee'] as $field => $value) {
            $this->post(route('delivery.update', $delivery->delivery_id), [$field => $value, 'stock_date' => $stock->stock_date, 'employee_id' => $stock->employee_id, 'delivery_method' => $delivery->delivery_method, 'delivery_status' => $delivery->delivery_status, 'quantity' => [1]])
                ->assertSessionHasErrors($field);
        }
    }

    /** A Confirmed order without deliveries (edits are allowed). */
    private function editableOrder(): object
    {
        $order = DB::table('orders')->where('order_status', 2)
            ->whereNotExists(fn ($q) => $q->from('stocks')->whereColumn('stocks.order_id', 'orders.order_id')->where('stocks.stock_status', 3))
            ->whereNotExists(fn ($q) => $q->from('stock_items')->whereColumn('stock_items.order_id', 'orders.order_id'))
            ->first();
        if (! $order) {
            $this->markTestSkipped('needs a confirmed order without deliveries');
        }

        return $order;
    }

    public function test_order_edit_ignores_status_number_creator_and_client_totals(): void
    {
        $this->actingAs($this->admin());
        $order = $this->editableOrder();
        $item = DB::table('order_items')->where('order_id', $order->order_id)->first();

        $this->post(route('order.update', $order->order_id), [
            'customer_id' => $order->customer_id,
            'order_date' => $order->order_date,
            'description' => 'edited order',
            'order_status' => 5, 'order_no' => 'HACKED', 'created_by' => 999, 'job_no' => 'X/J99-999',
            'product_type_id' => [$item->product_type_id],
            'product_stage_id' => [$item->product_stage_id],
            'price' => [10], 'quantity' => [3], 'total' => [1],
        ])->assertSessionHasNoErrors()->assertRedirect(route('order.show', $order->order_id));

        $after = DB::table('orders')->where('order_id', $order->order_id)->first();
        $this->assertSame('edited order', $after->description);
        $this->assertSame((int) $order->order_status, (int) $after->order_status);
        $this->assertSame($order->order_no, $after->order_no);
        $this->assertSame($order->job_no, $after->job_no);
        $this->assertSame((int) $order->created_by, (int) $after->created_by);
        $line = DB::table('order_items')->where('order_id', $order->order_id)->where('product_type_id', $item->product_type_id)->first();
        $this->assertEqualsWithDelta(30.0, (float) $line->total, 0.0001, 'total = price x quantity, computed by the server');
    }

    public function test_order_edit_rejects_invalid_lines(): void
    {
        $this->actingAs($this->admin());
        $order = $this->editableOrder();
        $item = DB::table('order_items')->where('order_id', $order->order_id)->first();
        $base = [
            'customer_id' => $order->customer_id, 'order_date' => $order->order_date,
            'product_type_id' => [$item->product_type_id], 'product_stage_id' => [$item->product_stage_id],
        ];

        $this->post(route('order.update', $order->order_id), $base + ['price' => [-1], 'quantity' => [1], 'total' => [0]])->assertSessionHasErrors('price.0');
        $this->post(route('order.update', $order->order_id), $base + ['price' => [1], 'quantity' => ['abc'], 'total' => [0]])->assertSessionHasErrors('quantity.0');
    }

    private function transactionPayload(array $overrides = []): array
    {
        return array_merge([
            'transaction_to' => 'expense', 'transaction_type' => 'expense', 'expense_type' => 'credit',
            'transaction_date' => now()->toDateString(), 'bank_id' => 0,
            'payee_id' => (int) DB::table('heads')->where('head_type_id', 7)->value('head_id'),
            'amount' => 1, 'description' => 'test',
        ], $overrides);
    }

    public function test_transaction_amount_must_be_positive(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('transaction.store'), $this->transactionPayload(['amount' => 0]))->assertSessionHasErrors('amount');
        $this->post(route('transaction.store'), $this->transactionPayload(['amount' => -50]))->assertSessionHasErrors('amount');
        $this->post(route('transaction.store'), $this->transactionPayload(['amount' => 'ten']))->assertSessionHasErrors('amount');
        $this->post(route('transaction.store'), $this->transactionPayload([
            'transaction_to' => 'vendor', 'transaction_type' => 'payment', 'amount' => null, 'debit' => 0, 'credit' => 0,
            'payee_id' => (int) DB::table('vendors')->value('vendor_id'),
        ]))->assertSessionHasErrors('amount');
    }

    public function test_transaction_party_type_and_bank_come_from_allowed_lists(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('transaction.store'), $this->transactionPayload(['transaction_to' => 'admin']))->assertSessionHasErrors('transaction_to');
        $this->post(route('transaction.store'), $this->transactionPayload(['transaction_type' => 'wages']))->assertSessionHasErrors('transaction_type');
        $this->post(route('transaction.store'), $this->transactionPayload(['transaction_to' => 'customer', 'transaction_type' => 'salary']))->assertSessionHasErrors('transaction_type');
        $this->post(route('transaction.store'), $this->transactionPayload(['bank_id' => 987654]))->assertSessionHasErrors('bank_id');
    }

    public function test_status_links_are_post_only_and_validated(): void
    {
        $this->actingAs($this->admin());
        $delivery = DB::table('deliveries')->first();
        $receive = DB::table('receives')->first();

        $this->get('/deliveryStatus/'.$delivery->delivery_id.'/2')->assertStatus(405);
        $this->get('/receiveStatus/'.$receive->receive_id.'/1')->assertStatus(405);

        $this->post(route('delivery.updateStatus', [$delivery->delivery_id, 9]))->assertNotFound();
        $this->post(route('delivery.updateStatus', [$delivery->delivery_id, 'x']))->assertNotFound();
        $this->post(route('receive.updateStatus', [$receive->receive_id, 7]))->assertNotFound();
        $this->assertSame((int) $delivery->delivery_status, (int) DB::table('deliveries')->where('delivery_id', $delivery->delivery_id)->value('delivery_status'));

        $this->post(route('delivery.updateStatus', [$delivery->delivery_id, 2]))->assertRedirect(route('delivery'));
        $this->assertSame(2, (int) DB::table('deliveries')->where('delivery_id', $delivery->delivery_id)->value('delivery_status'));
        $this->post(route('receive.updateStatus', [$receive->receive_id, 1]))->assertRedirect(route('receive'));
        $this->assertSame(1, (int) DB::table('receives')->where('receive_id', $receive->receive_id)->value('receive_status'));
    }

    public function test_status_change_still_needs_the_edit_permission(): void
    {
        $role = \App\Models\Role::create(['name' => 'Viewer '.uniqid()]);
        $viewer = User::create(['name' => 'V', 'email' => uniqid().'@test.local', 'password' => 'secret123', 'role_id' => $role->id]);
        $delivery = DB::table('deliveries')->first();

        $this->actingAs($viewer)->post(route('delivery.updateStatus', [$delivery->delivery_id, 2]))->assertForbidden();
    }
}
