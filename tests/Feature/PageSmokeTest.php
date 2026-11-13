<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Every listing / detail page with permission-gated buttons renders (admin and a view-only role).
 */
class PageSmokeTest extends TestCase
{
    use DatabaseTransactions;

    private function pages(): array
    {
        $first = fn ($table, $key, $where = null) => DB::table($table)->when($where, $where)->orderByDesc($key)->value($key);
        $pages = [
            route('customer'), route('employee'), route('vendor'), route('contractor'), route('material'), route('machine'),
            route('product'), route('order'), route('delivery'), route('delivery-return'), route('purchase'), route('receive'),
            route('return'), route('ptc'), route('issue'), route('receiveIssue'), route('missue'), route('mprocess'),
            route('transaction'), route('expense'), route('generalVoucher'), route('ePayment'), route('vPayment'), route('cPayment'),
            route('oPayment'), route('cashBalance'), route('bank'), route('asset'), route('payroll.index'), route('wages'),
            route('attendance'), route('attendance.summary'), route('workTime'), route('workHoliday'), route('user'), route('role'),
            route('head'), route('category'), route('packingList'), route('igroup'), route('productCost'), route('productMaterial'),
        ];
        $info = [
            'customer.show' => $first('customers', 'customer_id'), 'employee.show' => $first('employees', 'employee_id'),
            'vendor.show' => $first('vendors', 'vendor_id', fn ($q) => $q->where('vendor_type', '0')),
            'vendor.show2' => $first('vendors', 'vendor_id', fn ($q) => $q->where('vendor_type', '1')),
            'material.show' => $first('materials', 'material_id'), 'machine.show' => $first('machines', 'machine_id'),
            'product.show' => $first('products', 'product_id'), 'order.show' => $first('orders', 'order_id'),
            'delivery.show' => $first('deliveries', 'delivery_id'), 'purchase.show' => $first('purchases', 'purchase_id'),
            'receive.show' => $first('receives', 'receive_id'), 'return.show' => $first('returns', 'return_id'),
            'ptc.show' => $first('stocks', 'stock_id', fn ($q) => $q->where('is_ptc_master', 1)),
            'payroll.info' => $first('payrolls', 'month'), 'packingList.show' => $first('packing_lists', 'packing_list_id'),
            'company.edit' => $first('company', 'id'),
        ];
        foreach ($info as $route => $id) {
            if ($id !== null) {
                $pages[] = route($route, $id);
            }
        }

        return $pages;
    }

    public function test_pages_render_for_admin(): void
    {
        $this->actingAs(User::where('role_id', User::ROLE_ADMIN)->firstOrFail());
        foreach ($this->pages() as $url) {
            $response = $this->get($url);
            $this->assertContains($response->status(), [200, 302], $url.' -> '.$response->status().' '.substr(strip_tags((string) $response->getContent()), 0, 300));
        }
    }

    public function test_pages_render_or_refuse_cleanly_for_view_only_role(): void
    {
        $role = Role::create(['name' => 'Viewer '.uniqid()]);
        $ids = DB::table('permissions')->where(fn ($q) => $q->where('name', 'like', '%\_access')->orWhere('name', 'like', '%\_show'))->pluck('id');
        foreach ($ids as $id) {
            DB::table('permission_role')->insert(['role_id' => $role->id, 'permission_id' => $id]);
        }
        $viewer = User::create(['name' => 'Viewer', 'email' => uniqid().'@test.local', 'password' => 'secret123', 'role_id' => $role->id]);
        $this->actingAs($viewer);

        foreach ($this->pages() as $url) {
            $response = $this->get($url);
            $this->assertContains($response->status(), [200, 302, 403], $url.' -> '.$response->status());
            if ($response->status() === 200) {
                $this->assertStringNotContainsString('js-delete-record', (string) $response->getContent(), $url.' shows a delete button to a view-only role');
            }
        }
    }
}
