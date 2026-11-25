<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * F-06 / F-24, D4, D5: delivered order lines keep price, customer and at least the delivered
 * quantity; order status follows the allowed transitions; re-opening is for admins only.
 */
class OrderIntegrityTest extends TestCase
{
    use DatabaseTransactions;

    private object $order;

    private array $items;

    private function admin(): User
    {
        return User::where('role_id', User::ROLE_ADMIN)->orderBy('id')->firstOrFail();
    }

    private function userWith(array $permissions): User
    {
        $role = Role::create(['name' => 'Test role '.uniqid()]);
        foreach (DB::table('permissions')->whereIn('name', $permissions)->pluck('id') as $id) {
            DB::table('permission_role')->insert(['role_id' => $role->id, 'permission_id' => $id]);
        }

        return User::create(['name' => 'Tester', 'email' => uniqid().'@test.local', 'password' => 'secret123', 'role_id' => $role->id]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->admin());
        $types = DB::table('order_items')->select('product_type_id', 'product_stage_id')->distinct()->limit(2)->get();
        $customer = (int) DB::table('customers')->value('customer_id');
        $orderId = DB::table('orders')->insertGetId(['order_no' => 'ZZO-'.uniqid(), 'job_no' => 'ZZ/J26-'.uniqid(), 'customer_id' => $customer,
            'order_status' => Order::STATUS_CONFIRMED, 'order_date' => '2026-01-01', 'created_by' => 1]);
        foreach ($types as $i => $type) {
            DB::table('order_items')->insert(['order_id' => $orderId, 'product_type_id' => $type->product_type_id, 'product_stage_id' => $type->product_stage_id,
                'price' => 10, 'quantity' => 20, 'total' => 200, 'created_by' => 1]);
        }
        $this->order = DB::table('orders')->where('order_id', $orderId)->first();
        $this->items = $types->all();
    }

    /** 5 of the first line delivered. */
    private function deliverFive(): void
    {
        $stockId = DB::table('stocks')->insertGetId(['stock_no' => 'ZZD-'.uniqid(), 'order_id' => $this->order->order_id, 'table_name' => 'delivery',
            'employee_id' => 0, 'stock_type' => 2, 'stock_date' => '2026-01-05', 'stock_status' => 3, 'created_by' => 1]);
        DB::table('stock_items')->insert(['stock_id' => $stockId, 'product_type_id' => $this->items[0]->product_type_id, 'material_id' => 0,
            'stage_id' => $this->items[0]->product_stage_id, 'quantity' => 5, 'work_logs' => 0, 'work_wages' => 0, 'created_by' => 1]);
    }

    /** @param array<int, array{0:float,1:float}> $lines index => [quantity, price] */
    private function edit(array $lines, array $extra = [])
    {
        return $this->post(route('order.update', $this->order->order_id), array_merge([
            'customer_id' => $this->order->customer_id, 'order_date' => '2026-01-01', 'description' => 'edited',
            'product_type_id' => array_map(fn ($i) => $this->items[$i]->product_type_id, array_keys($lines)),
            'product_stage_id' => array_map(fn ($i) => $this->items[$i]->product_stage_id, array_keys($lines)),
            'quantity' => array_column($lines, 0), 'price' => array_column($lines, 1),
        ], $extra));
    }

    private function line(int $index): ?object
    {
        return DB::table('order_items')->where('order_id', $this->order->order_id)->where('product_type_id', $this->items[$index]->product_type_id)
            ->where('product_stage_id', $this->items[$index]->product_stage_id)->first();
    }

    public function test_delivered_line_price_cannot_change_but_harmless_fields_can(): void
    {
        $this->deliverFive();
        $this->edit([[20, 12], [20, 10]])->assertSessionHas('fails', fn ($m) => str_contains($m, 'ask an admin'));
        $this->assertEqualsWithDelta(10, (float) $this->line(0)->price, 0.0001);

        $this->edit([[20, 10], [25, 11]])->assertSessionMissing('fails');
        $this->assertSame('edited', DB::table('orders')->where('order_id', $this->order->order_id)->value('description'));
        $this->assertEqualsWithDelta(11, (float) $this->line(1)->price, 0.0001, 'undelivered line stays editable');
    }

    public function test_delivered_line_cannot_be_removed_or_go_below_delivered(): void
    {
        $this->deliverFive();
        $this->edit([1 => [20, 10]])->assertSessionHas('fails');
        $this->edit([[4, 10], [20, 10]])->assertSessionHas('fails', fn ($m) => str_contains($m, 'delivered'));
        $this->assertNotNull($this->line(0));

        $this->edit([[5, 10], [20, 10]])->assertSessionMissing('fails');
        $this->assertEqualsWithDelta(5, (float) $this->line(0)->quantity, 0.0001);
    }

    public function test_customer_cannot_change_once_delivered(): void
    {
        $other = (int) DB::table('customers')->where('customer_id', '!=', $this->order->customer_id)->value('customer_id');
        $this->deliverFive();
        $this->edit([[20, 10], [20, 10]], ['customer_id' => $other])->assertSessionHas('fails');
        $this->assertSame((int) $this->order->customer_id, (int) DB::table('orders')->where('order_id', $this->order->order_id)->value('customer_id'));
    }

    public function test_undelivered_order_can_change_customer(): void
    {
        $other = (int) DB::table('customers')->where('customer_id', '!=', $this->order->customer_id)->value('customer_id');
        $this->edit([[20, 10], [20, 10]], ['customer_id' => $other])->assertSessionMissing('fails');
        $this->assertSame($other, (int) DB::table('orders')->where('order_id', $this->order->order_id)->value('customer_id'));
    }

    private function setStatus(int $status): void
    {
        DB::table('orders')->where('order_id', $this->order->order_id)->update(['order_status' => $status]);
    }

    private function changeStatus(int $to)
    {
        return $this->post(route('order.updateStatus', $this->order->order_id), ['order_status' => $to]);
    }

    private function orderStatusNow(): int
    {
        return (int) DB::table('orders')->where('order_id', $this->order->order_id)->value('order_status');
    }

    public function test_status_transitions_follow_the_sequence(): void
    {
        $this->actingAs($this->userWith(['orders_access', 'orders_show', 'orders_edit']));

        $this->setStatus(Order::STATUS_DRAFT);
        $this->changeStatus(Order::STATUS_DELIVERED)->assertSessionHas('fails');
        $this->assertSame(Order::STATUS_DRAFT, $this->orderStatusNow());
        $this->changeStatus(Order::STATUS_CONFIRMED)->assertSessionHas('success');
        $this->assertSame(Order::STATUS_CONFIRMED, $this->orderStatusNow());

        // nothing delivered yet: cannot be Dispatched / Delivered
        $this->changeStatus(Order::STATUS_DISPATCHED)->assertSessionHas('fails');
        $this->changeStatus(Order::STATUS_DELIVERED)->assertSessionHas('fails');
        // going back is a re-open: admin only
        $this->changeStatus(Order::STATUS_DRAFT)->assertSessionHas('fails');

        $this->deliverFive();
        $this->changeStatus(Order::STATUS_CANCELLED)->assertSessionHas('fails', fn ($m) => str_contains($m, 'delivered'));
        $this->changeStatus(Order::STATUS_DISPATCHED)->assertSessionHas('success');
        $this->changeStatus(Order::STATUS_DELIVERED)->assertSessionHas('success');
        $this->changeStatus(Order::STATUS_CONFIRMED)->assertSessionHas('fails');
        $this->assertSame(Order::STATUS_DELIVERED, $this->orderStatusNow());
    }

    public function test_cancel_only_without_deliveries_and_reopen_by_admin(): void
    {
        $user = $this->userWith(['orders_access', 'orders_show', 'orders_edit']);
        $this->actingAs($user);
        $this->changeStatus(Order::STATUS_CANCELLED)->assertSessionHas('success');
        $this->changeStatus(Order::STATUS_DRAFT)->assertSessionHas('fails', fn ($m) => str_contains($m, 'admin'));
        $this->assertSame(Order::STATUS_CANCELLED, $this->orderStatusNow());

        $this->actingAs($this->admin());
        $this->changeStatus(Order::STATUS_DRAFT)->assertSessionHas('success');
        $this->assertSame(Order::STATUS_DRAFT, $this->orderStatusNow());
        // the delivery conditions apply to admins too
        $this->changeStatus(Order::STATUS_DELIVERED)->assertSessionHas('fails');
    }

    public function test_delivering_more_than_ordered_is_refused_on_the_server(): void
    {
        $repo = app(\App\Repositories\OrderRepository::class);
        $this->deliverFive();
        $repo->assertNotOverDelivered((int) $this->order->order_id); // 5 of 20: fine

        DB::table('order_items')->where('order_id', $this->order->order_id)->where('product_type_id', $this->items[0]->product_type_id)
            ->where('product_stage_id', $this->items[0]->product_stage_id)->update(['quantity' => 4]);
        $this->expectException(\App\Exceptions\DocumentLockedException::class);
        $this->expectExceptionMessage('ordered 4');
        $repo->assertNotOverDelivered((int) $this->order->order_id);
    }

    public function test_delivery_form_cannot_set_a_disallowed_order_status(): void
    {
        $delivery = DB::table('deliveries')
            ->whereNotExists(fn ($q) => $q->from('delivery_returns')->whereColumn('delivery_returns.delivery_id', 'deliveries.delivery_id'))
            ->whereExists(fn ($q) => $q->from('stock_items')->whereColumn('stock_items.stock_id', 'deliveries.stock_id')->where('stock_items.material_id', 0))
            ->whereNotExists(fn ($q) => $q->from('stock_items')->whereColumn('stock_items.stock_id', 'deliveries.stock_id')->where('stock_items.material_id', 0)
                ->whereNotExists(fn ($b) => $b->from('stock_item_batches')->whereColumn('stock_item_batches.stock_item_id', 'stock_items.stock_item_id')))
            ->first();
        if (! $delivery) {
            $this->markTestSkipped('needs a fully batch-allocated delivery');
        }
        $stock = DB::table('stocks')->where('stock_id', $delivery->stock_id)->first();
        $lines = DB::table('stock_items')->where('stock_id', $stock->stock_id)->get();
        $status = (int) DB::table('orders')->where('order_id', $stock->order_id)->value('order_status');
        $payload = fn ($orderStatus) => [
            'order_id' => $stock->order_id, 'employee_id' => $stock->employee_id, 'stock_date' => $stock->stock_date, 'description' => 'x',
            'delivery_method' => $delivery->delivery_method, 'delivery_status' => $delivery->delivery_status, 'order_status' => $orderStatus,
            'product_type_id' => $lines->pluck('product_type_id')->all(), 'material_id' => $lines->pluck('material_id')->all(),
            'stage_id' => $lines->pluck('stage_id')->all(), 'quantity' => $lines->pluck('quantity')->all(),
        ];

        $this->post(route('delivery.update', $delivery->delivery_id), $payload(Order::STATUS_CANCELLED))->assertSessionHas('fails', fn ($m) => str_contains($m, 'cancelled'));
        $this->assertSame($status, (int) DB::table('orders')->where('order_id', $stock->order_id)->value('order_status'));

        $this->post(route('delivery.update', $delivery->delivery_id), $payload($status))->assertSessionMissing('fails');
    }

    public function test_new_order_starts_as_draft_or_confirmed(): void
    {
        $customer = (int) $this->order->customer_id;
        $base = ['order_no' => 'ZZN-'.uniqid(), 'customer_id' => $customer, 'order_date' => '2026-01-01',
            'product_type_id' => [$this->items[0]->product_type_id], 'product_stage_id' => [$this->items[0]->product_stage_id], 'quantity' => [1], 'price' => [1], 'total' => [1]];

        $this->post(route('order.store'), $base + ['order_status' => Order::STATUS_DELIVERED])->assertSessionHasErrors('order_status');
        $this->post(route('order.store'), $base + ['order_status' => Order::STATUS_CONFIRMED])->assertSessionHasNoErrors();
    }
}
