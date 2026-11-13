<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Role;
use App\Models\User;
use App\Repositories\StockItemRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Delete flow: dependency check, confirmation, permission, cascades and integrity.
 * Runs against the local database inside a transaction that is rolled back.
 */
class DeletionTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::where('role_id', User::ROLE_ADMIN)->orderBy('id')->firstOrFail();
    }

    /** A non-admin user whose role has exactly the given permissions. */
    private function userWith(array $permissions): User
    {
        $role = Role::create(['name' => 'Test role '.uniqid()]);
        $ids = DB::table('permissions')->whereIn('name', $permissions)->pluck('id');
        foreach ($ids as $id) {
            DB::table('permission_role')->insert(['role_id' => $role->id, 'permission_id' => $id]);
        }

        return User::create(['name' => 'Tester', 'email' => uniqid().'@test.local', 'password' => 'secret123', 'role_id' => $role->id]);
    }

    private function newCustomer(): Customer
    {
        $customer = Customer::create([
            'customer_no' => 'CUTEST', 'fname' => 'Delete', 'lname' => 'Me',
            'email' => 'delete-me@test.local', 'phone' => '0', 'fax' => '0', 'address' => '-', 'port_no' => '-', 'description' => '-', 'created_by' => 1,
            'country_id' => DB::table('heads')->where('head_type_id', 15)->value('head_id'),
            'currency_id' => DB::table('heads')->where('head_type_id', 16)->value('head_id'),
        ]);
        DB::table('transactions')->insert([
            'transaction_to' => 'customer', 'transaction_type' => 'openingBalance', 'bank_id' => 0,
            'payee_id' => $customer->customer_id, 'debit' => 500, 'transaction_date' => now()->toDateString(), 'payee_bank_id' => 0, 'created_by' => 1,
        ]);

        return $customer;
    }

    public function test_independent_record_is_deleted_with_its_own_rows_after_confirmation(): void
    {
        $customer = $this->newCustomer();
        $id = $customer->customer_id;

        $check = $this->actingAs($this->admin())->getJson(route('customer.deleteCheck', $id))->assertOk();
        $this->assertTrue($check->json('data.can_delete'));
        $this->assertStringContainsString('opening balance', implode(' ', $check->json('data.cascades')));

        // Not confirmed: nothing happens
        $this->delete(route('customer.destroy', $id))->assertSessionHas('fails');
        $this->assertDatabaseHas('customers', ['customer_id' => $id]);

        $this->delete(route('customer.destroy', $id), ['confirmed' => 1])
            ->assertRedirect(route('customer'))->assertSessionHas('success');
        $this->assertDatabaseMissing('customers', ['customer_id' => $id]);
        $this->assertDatabaseMissing('transactions', ['transaction_to' => 'customer', 'payee_id' => $id]);
        $this->assertDatabaseHas('activity_log', ['log_name' => 'delete', 'description' => 'Customer CUTEST (Delete Me) deleted']);
    }

    public function test_record_with_dependents_is_refused_and_kept(): void
    {
        $customerId = DB::table('orders')->value('customer_id');
        $check = $this->actingAs($this->admin())->getJson(route('customer.deleteCheck', $customerId))->assertOk();
        $this->assertFalse($check->json('data.can_delete'));
        $this->assertStringContainsString('order', implode(' ', $check->json('data.blockers')));

        $this->delete(route('customer.destroy', $customerId), ['confirmed' => 1])->assertSessionHas('fails');
        $this->assertDatabaseHas('customers', ['customer_id' => $customerId]);

        // An order that has a delivery cannot be deleted either
        $orderId = DB::table('stocks')->where('stock_status', 3)->value('order_id');
        $blockers = $this->getJson(route('order.deleteCheck', $orderId))->json('data.blockers');
        $this->assertStringContainsString('deliver', implode(' ', $blockers));
    }

    public function test_delete_permission_is_enforced_for_non_admin_roles(): void
    {
        $customer = $this->newCustomer();

        $viewer = $this->userWith(['customers_access', 'customers_show']);
        $this->actingAs($viewer)->getJson(route('customer.deleteCheck', $customer->customer_id))->assertForbidden();
        $this->actingAs($viewer)->delete(route('customer.destroy', $customer->customer_id), ['confirmed' => 1])->assertForbidden();
        $this->assertDatabaseHas('customers', ['customer_id' => $customer->customer_id]);

        $deleter = $this->userWith(['customers_access', 'customers_delete']);
        $this->actingAs($deleter)->delete(route('customer.destroy', $customer->customer_id), ['confirmed' => 1])->assertSessionHas('success');
        $this->assertDatabaseMissing('customers', ['customer_id' => $customer->customer_id]);
    }

    public function test_delivery_delete_returns_stock_and_removes_its_rows(): void
    {
        $delivery = DB::table('deliveries')->whereNotExists(fn ($q) => $q->from('delivery_returns')->whereColumn('delivery_returns.delivery_id', 'deliveries.delivery_id'))->first();
        $this->assertNotNull($delivery, 'needs a delivery without returns');
        $line = DB::table('stock_items')->where('stock_id', $delivery->stock_id)->first();
        $batchIds = DB::table('stock_item_batches')->where('stock_item_id', $line->stock_item_id)->pluck('batch_id');
        $before = $this->stockAt($line->product_type_id, $line->stage_id);

        $check = $this->actingAs($this->admin())->getJson(route('delivery.deleteCheck', $delivery->delivery_id))->assertOk();
        $this->assertTrue($check->json('data.can_delete'));

        $this->delete(route('delivery.destroy', $delivery->delivery_id), ['confirmed' => 1])->assertSessionHas('success');
        $this->assertDatabaseMissing('deliveries', ['delivery_id' => $delivery->delivery_id]);
        $this->assertDatabaseMissing('stocks', ['stock_id' => $delivery->stock_id]);
        $this->assertSame(0, DB::table('stock_items')->where('stock_id', $delivery->stock_id)->count());
        $this->assertSame(0, DB::table('stock_item_batches')->where('stock_item_id', $line->stock_item_id)->count());
        $this->assertSame(0, DB::table('packing_lists')->where('delivery_id', $delivery->delivery_id)->count());
        $this->assertEqualsWithDelta($before + (float) $line->quantity, $this->stockAt($line->product_type_id, $line->stage_id), 0.001);
        // the batches it came from still exist
        $this->assertSame($batchIds->count(), DB::table('stock_batches')->whereIn('batch_id', $batchIds)->count());
    }

    public function test_ptc_with_receivings_is_refused_and_one_without_is_deleted_whole(): void
    {
        $withReceipts = DB::table('stocks as m')->where('m.is_ptc_master', 1)
            ->whereExists(fn ($q) => $q->from('stocks as r')->whereColumn('r.ptc_id', 'm.stock_id')->where('r.stock_type', 1))->value('m.stock_id');
        $check = $this->actingAs($this->admin())->getJson(route('stock.deleteCheck', $withReceipts))->assertOk();
        $this->assertFalse($check->json('data.can_delete'));

        $without = DB::table('stocks as m')->where('m.is_ptc_master', 1)
            ->whereNotExists(fn ($q) => $q->from('stocks as r')->whereColumn('r.ptc_id', 'm.stock_id')->where('r.stock_type', 1))->value('m.stock_id');
        $issuances = DB::table('stocks')->where('ptc_id', $without)->pluck('stock_id')->push($without);
        $this->delete(route('stock.destroy', $without), ['confirmed' => 1])->assertRedirect(route('ptc'))->assertSessionHas('success');
        $this->assertSame(0, DB::table('stocks')->whereIn('stock_id', $issuances)->count());
        $this->assertSame(0, DB::table('stock_items')->whereIn('stock_id', $issuances)->count());
    }

    public function test_transfer_is_deleted_with_its_pair_and_payroll_postings_are_protected(): void
    {
        $transfer = DB::table('transactions')->whereNotNull('transfer_pair_id')->first();
        $this->actingAs($this->admin())->delete(route('transaction.destroy', $transfer->transaction_id), ['confirmed' => 1])->assertSessionHas('success');
        $this->assertDatabaseMissing('transactions', ['transaction_id' => $transfer->transaction_id]);
        $this->assertDatabaseMissing('transactions', ['transaction_id' => $transfer->transfer_pair_id]);

        $posted = DB::table('payroll_items')->whereNotNull('salary_transaction_id')->value('salary_transaction_id');
        if ($posted) {
            $this->assertFalse($this->getJson(route('transaction.deleteCheck', $posted))->json('data.can_delete'));
        }
    }

    public function test_system_records_and_own_account_are_protected(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);
        $this->assertFalse($this->getJson(route('head.deleteCheck', 105))->json('data.can_delete'), 'Rejected stage head');
        $this->assertFalse($this->getJson(route('user.deleteCheck', $admin->id))->json('data.can_delete'), 'own account');
        $this->assertFalse($this->getJson(route('role.deleteCheck', User::ROLE_ADMIN))->json('data.can_delete'), 'Admin role');
        $usedHead = DB::table('product_types')->value('size_id');
        $this->assertFalse($this->getJson(route('head.deleteCheck', $usedHead))->json('data.can_delete'), 'size in use');
    }

    public function test_stock_in_removal_guard_reports_consumed_receipts(): void
    {
        // Every receiving either passes or names a shortfall; the guard must not error on real data
        foreach (DB::table('receives')->pluck('receive_id') as $receiveId) {
            $data = $this->actingAs($this->admin())->getJson(route('receive.deleteCheck', $receiveId))->assertOk()->json('data');
            $this->assertIsBool($data['can_delete']);
        }
        $this->assertSame([], app(StockItemRepository::class)->stockInRemovalShortfalls([]));
    }

    public function test_every_delete_check_runs_on_real_records(): void
    {
        $this->actingAs($this->admin());
        $modules = [
            'customer' => ['customers', 'customer_id'], 'employee' => ['employees', 'employee_id'],
            'vendor' => ['vendors', 'vendor_id'], 'material' => ['materials', 'material_id'],
            'machine' => ['machines', 'machine_id'], 'product' => ['products', 'product_id'],
            'bank' => ['banks', 'bank_id'], 'category' => ['categories', 'category_id'],
            'head' => ['heads', 'head_id'], 'order' => ['orders', 'order_id'],
            'delivery' => ['deliveries', 'delivery_id'], 'packingList' => ['packing_lists', 'packing_list_id'],
            'delivery-return' => ['delivery_returns', 'delivery_return_id'], 'purchase' => ['purchases', 'purchase_id'],
            'receive' => ['receives', 'receive_id'], 'return' => ['returns', 'return_id'],
            'stock' => ['stocks', 'stock_id'], 'transaction' => ['transactions', 'transaction_id'],
            'user' => ['users', 'id'], 'role' => ['roles', 'id'], 'payroll' => ['payrolls', 'month'],
        ];
        $checked = 0;
        foreach ($modules as $route => [$table, $key]) {
            foreach (DB::table($table)->orderByDesc($key)->limit(8)->pluck($key) as $id) {
                $response = $this->getJson(route($route.'.deleteCheck', $id));
                $this->assertSame(200, $response->status(), "{$route} #{$id}: ".$response->getContent());
                $this->assertIsBool($response->json('data.can_delete'), "{$route} #{$id}");
                $checked++;
            }
        }
        $this->assertGreaterThan(50, $checked);
    }

    private function stockAt($productTypeId, $stageId): float
    {
        $row = app(StockItemRepository::class)->pStockForProductTypes([$productTypeId])->get($productTypeId, collect())
            ->first(fn ($r) => (int) $r->stage_id === (int) $stageId);

        return $row ? (float) $row->stockIn - (float) $row->stockOut : 0.0;
    }
}
