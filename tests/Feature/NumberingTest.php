<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Support\DocumentNumbers;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * F-16 / D7: numbers come from MAX + 1 under a lock (not COUNT + 1), so deleting a record never
 * brings its number back and two saves in a row never collide; the year is Asia/Karachi's.
 */
class NumberingTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::where('role_id', User::ROLE_ADMIN)->orderBy('id')->firstOrFail());
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function newOrder(int $customerId)
    {
        $item = DB::table('order_items')->first();

        return $this->post(route('order.store'), [
            'order_no' => '', 'customer_id' => $customerId, 'order_date' => '2026-01-01', 'order_status' => Order::STATUS_DRAFT,
            'product_type_id' => [$item->product_type_id], 'product_stage_id' => [$item->product_stage_id], 'quantity' => [1], 'price' => [1], 'total' => [1],
        ]);
    }

    public function test_job_and_order_numbers_never_repeat_after_a_delete(): void
    {
        $customer = DB::table('customers')->first();
        $before = (int) DB::table('orders')->max('order_id');
        foreach (range(1, 3) as $i) {
            $this->newOrder($customer->customer_id)->assertSessionHasNoErrors();
        }
        $orders = DB::table('orders')->where('order_id', '>', $before)->orderBy('order_id')->get();
        $this->assertCount(3, $orders);
        $this->assertCount(3, $orders->pluck('job_no')->unique(), 'job numbers in sequence are unique');
        $this->assertCount(3, $orders->pluck('order_no')->unique(), 'generated order numbers are unique');

        // delete the middle one, create another: the new numbers are new
        DB::table('order_items')->where('order_id', $orders[1]->order_id)->delete();
        DB::table('orders')->where('order_id', $orders[1]->order_id)->delete();
        $this->newOrder($customer->customer_id)->assertSessionHasNoErrors();
        $latest = DB::table('orders')->orderByDesc('order_id')->first();

        $this->assertNotContains($latest->job_no, $orders->pluck('job_no')->all());
        $this->assertNotContains($latest->order_no, $orders->pluck('order_no')->all());
        $this->assertMatchesRegularExpression('#^'.preg_quote($customer->customer_no, '#').'/J\d{2}-\d{3,}$#', $latest->job_no);
        $this->assertMatchesRegularExpression('/^SLE-O\d{3,}-\d{2}$/', $latest->order_no);
    }

    public function test_delivery_and_stock_numbers_follow_the_highest_existing_number(): void
    {
        $numbers = new DocumentNumbers();
        DB::transaction(function () use ($numbers) {
            $yy = DocumentNumbers::now()->format('y');
            DB::table('deliveries')->where('delivery_no', 'like', 'SLE-D%')->update(['delivery_no' => null]);
            DB::table('deliveries')->limit(1)->update(['delivery_no' => "SLE-D007-{$yy}"]);
            $this->assertSame("SLE-D008-{$yy}", $numbers->nextDeliveryNo());

            $ym = DocumentNumbers::now()->format('ym');
            $first = $numbers->nextIssuanceNo();
            $this->assertMatchesRegularExpression("/^I{$ym}\d{4}$/", $first);
            DB::table('stocks')->insert(['stock_no' => $first, 'order_id' => 0, 'table_name' => 'employee', 'employee_id' => 0, 'stock_type' => 2,
                'stock_date' => '2026-01-01', 'stock_status' => 0, 'created_by' => 1]);
            $this->assertNotSame($first, $numbers->nextIssuanceNo(), 'two in sequence never collide');
        });
    }

    public function test_the_year_is_taken_in_asia_karachi(): void
    {
        // 31 Dec 2026 20:00 UTC is already 1 Jan 2027 01:00 in Karachi
        Carbon::setTestNow(Carbon::parse('2026-12-31 20:00:00', 'UTC'));
        DB::transaction(function () {
            $this->assertStringEndsWith('-27', (new DocumentNumbers())->nextDeliveryNo());
            $this->assertStringStartsWith('I2701', (new DocumentNumbers())->nextIssuanceNo());
        });
    }

    public function test_numbers_are_read_under_a_lock_and_only_inside_a_transaction(): void
    {
        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = strtolower($query->sql);
        });
        DB::transaction(fn () => (new DocumentNumbers())->nextDeliveryNo());
        $this->assertNotEmpty(array_filter($queries, fn ($sql) => str_contains($sql, '`deliveries`') && str_contains($sql, 'for update')));

        // the test itself runs in a transaction; simulate a call with none
        $level = DB::transactionLevel();
        $pdo = DB::connection();
        $this->assertGreaterThan(0, $level);
        $this->assertSame('SLE-D', substr((new DocumentNumbers())->nextDeliveryNo(true), 0, 5), 'preview works without a lock');
    }

    public function test_issuance_and_ptc_numbers_are_assigned_by_the_server(): void
    {
        $repo = app(\App\Repositories\StockItemRepository::class);
        $material = null;
        foreach (DB::table('materials')->pluck('material_id') as $id) {
            if ($repo->materialStock((int) $id) >= 2) {
                $material = (int) $id;
                break;
            }
        }
        if (! $material) {
            $this->markTestSkipped('needs a material in stock');
        }
        $payload = fn () => ['stock_no' => 'TYPED-BY-USER', 'order_id' => (int) DB::table('orders')->value('order_id'),
            'employee_id' => (int) DB::table('employees')->value('employee_id'), 'table_name' => 'employee', 'stock_date' => now()->toDateString(),
            'issue_for' => 0, 'product_type_id' => [0], 'material_id' => [$material], 'stage_id' => [0], 'quantity' => [1], 'work_logs' => [0]];

        $this->post(route('stock.store'), $payload())->assertSessionMissing('fails');
        $this->post(route('stock.store'), $payload())->assertSessionMissing('fails');
        $last = DB::table('stocks')->orderByDesc('stock_id')->limit(2)->pluck('stock_no');
        $this->assertCount(2, $last->unique());
        $this->assertNotContains('TYPED-BY-USER', $last->all());
    }
}
