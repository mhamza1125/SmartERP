<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * End-to-end role scenarios: an admin saves a role in Settings > Roles & Permissions (the real
 * form), then a non-admin user of that role works with the application.
 * Everything is rolled back after each test.
 */
class RolePermissionScenariosTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::where('role_id', User::ROLE_ADMIN)->orderBy('id')->firstOrFail();
    }

    private function ids(array $names): array
    {
        $ids = DB::table('permissions')->whereIn('name', $names)->pluck('id')->all();
        $this->assertCount(count($names), $ids, 'unknown permission in '.implode(',', $names));

        return $ids;
    }

    /** Creates a role through the Add Role form, as an admin would. */
    private function roleWith(array $permissions, ?string $name = null): Role
    {
        $name ??= 'Scenario '.uniqid();
        $this->actingAs($this->admin())
            ->post(route('role.store'), ['name' => $name, 'permission_id' => $this->ids($permissions)])
            ->assertSessionHasNoErrors();

        return Role::where('name', $name)->firstOrFail();
    }

    /** Changes a role through the Edit Role form. */
    private function saveRole(Role $role, array $permissions): void
    {
        $this->actingAs($this->admin())
            ->post(route('role.update', $role->id), ['name' => $role->name, 'permission_id' => $this->ids($permissions)])
            ->assertSessionHasNoErrors();
    }

    private function userOf(Role $role): User
    {
        return User::create(['name' => 'Scenario User', 'email' => uniqid().'@test.local', 'password' => 'secret123', 'role_id' => $role->id]);
    }

    private function permsOf(Role $role): array
    {
        return DB::table('permission_role')->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
            ->where('role_id', $role->id)->pluck('name')->sort()->values()->all();
    }

    private function customerForm(array $extra = []): array
    {
        return $extra + [
            'customer_no' => 'CUSCN', 'fname' => 'Scenario', 'lname' => 'Customer', 'email' => 'scenario@test.local',
            'phone' => '1', 'fax' => '1', 'port_no' => 'KHI', 'address' => '-', 'description' => '-',
            'country_id' => DB::table('heads')->where('head_type_id', 15)->value('head_id'),
            'currency_id' => DB::table('heads')->where('head_type_id', 16)->value('head_id'),
        ];
    }

    // ------------------------------------------------------------------------------------------

    public function test_saved_permissions_are_stored_with_their_dependencies(): void
    {
        // Create only: Access is added; Edit: View is added
        $role = $this->roleWith(['orders_create', 'customers_edit']);
        $this->assertSame(['customers_access', 'customers_edit', 'customers_show', 'orders_access', 'orders_create'], $this->permsOf($role));

        // The edit screen shows exactly what is stored
        $page = $this->actingAs($this->admin())->get(route('role.edit', $role->id))->assertOk();
        foreach ($this->ids(['orders_access', 'orders_create', 'customers_show']) as $id) {
            $this->assertMatchesRegularExpression('/id="perm_'.$id.'"[^>]*checked/s', $page->getContent());
        }
        $page->assertSee('PTC &amp; Issuances', false)->assertSee('Purchase Receivings');
    }

    public function test_a_view_only_role_can_open_but_not_change(): void
    {
        $user = $this->userOf($this->roleWith(['customers_access', 'customers_show']));
        $customer = Customer::query()->firstOrFail();
        $this->actingAs($user);

        $this->get(route('customer'))->assertOk()->assertDontSee(route('customer.add'), false)->assertDontSee('js-delete-record', false);
        $this->get(route('customer.show', $customer->customer_id))->assertOk()->assertDontSee(route('customer.edit', $customer->customer_id), false);
        $this->get(route('customer.print', $customer->customer_id))->assertOk(); // View includes printing

        $this->get(route('customer.add'))->assertForbidden();
        $this->post(route('customer.store'), $this->customerForm())->assertForbidden();
        $this->get(route('customer.edit', $customer->customer_id))->assertForbidden();
        $this->post(route('customer.update', $customer->customer_id), $this->customerForm())->assertForbidden();
        $this->delete(route('customer.destroy', $customer->customer_id), ['confirmed' => 1])->assertForbidden();
        // Ledger needs Transactions: View as well, so its button is hidden and the page refused
        $this->get(route('customer'))->assertDontSee(route('customer.detail', $customer->customer_id), false);
        $this->get(route('customer.detail', $customer->customer_id))->assertForbidden();
    }

    public function test_b_operational_role_creates_and_edits_but_cannot_delete(): void
    {
        $user = $this->userOf($this->roleWith(['customers_access', 'customers_show', 'customers_create', 'customers_edit']));
        $this->actingAs($user);

        $this->get(route('customer.add'))->assertOk();
        $this->post(route('customer.store'), $this->customerForm())->assertRedirect();
        $customer = Customer::where('customer_no', 'CUSCN')->firstOrFail();

        $this->get(route('customer.edit', $customer->customer_id))->assertOk();
        $this->post(route('customer.update', $customer->customer_id), $this->customerForm(['lname' => 'Updated']))->assertRedirect();
        $this->assertSame('Updated', $customer->fresh()->lname);

        $this->getJson(route('customer.deleteCheck', $customer->customer_id))->assertForbidden();
        $this->delete(route('customer.destroy', $customer->customer_id), ['confirmed' => 1])->assertForbidden();
        $this->assertNotNull($customer->fresh());
    }

    public function test_c_full_module_access_has_no_permission_errors(): void
    {
        $user = $this->userOf($this->roleWith(['customers_access', 'customers_show', 'customers_create', 'customers_edit', 'customers_delete', 'transactions_show']));
        $this->actingAs($user);

        $this->post(route('customer.store'), $this->customerForm())->assertRedirect();
        $customer = Customer::where('customer_no', 'CUSCN')->firstOrFail();
        foreach ([route('customer'), route('customer.show', $customer->customer_id), route('customer.edit', $customer->customer_id),
            route('customer.print', $customer->customer_id), route('customer.detail', $customer->customer_id), route('customer.ledger.print', $customer->customer_id)] as $url) {
            $this->get($url)->assertOk();
        }
        $this->getJson(route('customer.deleteCheck', $customer->customer_id))->assertOk()->assertJsonPath('data.can_delete', true);
        $this->delete(route('customer.destroy', $customer->customer_id), ['confirmed' => 1])->assertSessionHas('success');
        $this->assertNull($customer->fresh());
    }

    public function test_d_and_e_granting_and_revoking_takes_effect_on_the_next_request(): void
    {
        $role = $this->roleWith(['orders_access']);
        $user = $this->userOf($role);

        $this->actingAs($user)->get(route('order.add'))->assertForbidden();

        $this->saveRole($role, ['orders_access', 'orders_create']);       // D: granted
        $this->actingAs($user->fresh())->get(route('order.add'))->assertOk();
        $this->getJson(route('ajaxPS', ['productId' => DB::table('products')->value('product_id')]))->assertOk(); // form lookups work too

        $this->saveRole($role, ['orders_access']);                        // E: revoked
        $this->actingAs($user->fresh())->get(route('order.add'))->assertForbidden();
        $this->get(route('order'))->assertOk()->assertDontSee(route('order.add'), false);
    }

    public function test_f_formerly_admin_only_features_work_for_a_non_admin_role(): void
    {
        $user = $this->userOf($this->roleWith([
            'users_access', 'users_create', 'users_edit', 'roles_access', 'roles_edit',
            'receives_access', 'returns_access', 'heads_access', 'categories_access', 'settings_access', 'settings_edit',
            'attendance_access', 'payroll_access', 'payroll_show', 'reports_access',
        ]));
        $this->assertFalse($user->isAdmin());
        $this->actingAs($user);

        foreach (['user', 'role', 'receive', 'return', 'head', 'category', 'attendance', 'workTime', 'payroll.index', 'stock.daily'] as $route) {
            $this->get(route($route))->assertOk();
        }
        $this->get(route('company.edit', DB::table('company')->value('id')))->assertOk(); // attendance file location lives here

        // Manage user accounts ...
        $otherRole = Role::where('id', '!=', User::ROLE_ADMIN)->value('id');
        $this->post(route('user.store'), ['name' => 'Made by manager', 'email' => 'by-manager@test.local', 'role_id' => $otherRole,
            'password' => 'secret123', 'password_confirmation' => 'secret123'])->assertSessionHas('success');
        $this->assertDatabaseHas('users', ['email' => 'by-manager@test.local', 'role_id' => $otherRole]);
        // ... but never hand out the Admin role or change an administrator (system restriction)
        $this->post(route('user.store'), ['name' => 'Sneaky', 'email' => 'sneaky@test.local', 'role_id' => User::ROLE_ADMIN,
            'password' => 'secret123', 'password_confirmation' => 'secret123'])->assertForbidden();
        $this->post(route('user.update', $user->id), ['name' => $user->name, 'email' => $user->email, 'role_id' => User::ROLE_ADMIN])->assertForbidden();
        $this->assertFalse($user->fresh()->isAdmin());

        // Edit roles
        $roleToEdit = $this->roleWith(['orders_access']);
        $this->actingAs($user)->post(route('role.update', $roleToEdit->id), ['name' => $roleToEdit->name, 'permission_id' => $this->ids(['orders_access', 'orders_show'])])->assertRedirect();
        $this->assertContains('orders_show', $this->permsOf($roleToEdit));
    }

    public function test_g_direct_requests_without_permission_are_refused(): void
    {
        $order = DB::table('orders')->value('order_id');
        $customer = DB::table('customers')->value('customer_id');

        // Not signed in
        $this->get(route('order'))->assertRedirect(route('login'));
        $this->post(route('customer.store'), $this->customerForm())->assertRedirect(route('login'));
        $this->getJson(route('ajaxBalance', ['bank_id' => 0]))->assertUnauthorized();

        // Signed in with an unrelated permission
        $this->actingAs($this->userOf($this->roleWith(['materials_access'])));
        $this->get(route('order.show', $order))->assertForbidden();
        $this->get(route('order.proforma.excel', $order))->assertForbidden();
        $this->post(route('order.updateStatus', $order), ['order_status' => 1])->assertForbidden();
        $this->delete(route('order.destroy', $order), ['confirmed' => 1])->assertForbidden();
        $this->getJson(route('ajaxBalance', ['bank_id' => 0]))->assertForbidden();
        $this->getJson(route('ajax.customer-orders', $customer))->assertForbidden();
        $this->post(route('role.update', User::ROLE_ADMIN), ['name' => 'x', 'permission_id' => []])->assertForbidden();
        // Invalid form: validation answers first (redirect); either way nothing is saved
        $pathBefore = DB::table('company')->value('attendance_db_path');
        $this->post(route('company.update', DB::table('company')->value('id')), ['attendance_db_path' => 'C:\\x.mdb'])->assertStatus(302);
        $this->assertSame($pathBefore, DB::table('company')->value('attendance_db_path'));
        // Valid form: refused by the permission check
        $this->post(route('company.update', DB::table('company')->value('id')), ['name' => 'X', 'ceo' => 'X', 'phone' => '1', 'email' => 'x@test.local'])->assertForbidden();
        $this->assertNotSame('X', DB::table('company')->value('name'));
        $this->get(route('dashboard'))->assertOk()->assertDontSee('Expense This Month')->assertDontSee('Orders in Progress');
    }

    public function test_admin_role_is_locked_to_every_permission(): void
    {
        $this->actingAs($this->admin())->get(route('role.edit', User::ROLE_ADMIN))->assertOk()->assertSee('always has every permission');
        // Trying to strip the Admin role keeps all permissions
        $this->post(route('role.update', User::ROLE_ADMIN), ['name' => 'Admin', 'permission_id' => $this->ids(['orders_access'])]);
        $this->assertSame(DB::table('permissions')->count(), DB::table('permission_role')->where('role_id', User::ROLE_ADMIN)->count());
    }

    public function test_real_login_of_a_non_admin_user(): void
    {
        $role = $this->roleWith(['orders_access', 'orders_show']);
        User::create(['name' => 'Login User', 'email' => 'login-scenario@test.local', 'password' => Hash::make('secret123'), 'role_id' => $role->id]);
        auth()->logout();

        $this->post(route('login.store'), ['email' => 'login-scenario@test.local', 'password' => 'secret123'])->assertRedirect(route('dashboard'));
        $this->get(route('dashboard'))->assertOk()->assertSee('Orders in Progress')->assertSee(route('order'), false)->assertDontSee(route('customer'), false);
        $this->get(route('order'))->assertOk();
        $this->get(route('customer'))->assertForbidden();
    }
}
