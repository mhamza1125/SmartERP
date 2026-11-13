<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Permissions assigned to a role work for non-admin users, and everything else is refused,
 * both on the server and in the UI. Rolled back after each test.
 */
class PermissionEnforcementTest extends TestCase
{
    use DatabaseTransactions;

    private function userWith(array $permissions): User
    {
        $role = Role::create(['name' => 'Perm test '.uniqid()]);
        foreach (DB::table('permissions')->whereIn('name', $permissions)->pluck('id') as $id) {
            DB::table('permission_role')->insert(['role_id' => $role->id, 'permission_id' => $id]);
        }
        $this->assertCount(count($permissions), DB::table('permission_role')->where('role_id', $role->id)->get(), 'all permission names exist');

        return User::create(['name' => 'Perm Tester', 'email' => uniqid().'@test.local', 'password' => 'secret123', 'role_id' => $role->id]);
    }

    public function test_user_management_endpoints_require_login_and_permission(): void
    {
        $roleId = Role::where('id', '!=', User::ROLE_ADMIN)->value('id');
        $payload = ['name' => 'Intruder', 'email' => 'intruder@test.local', 'role_id' => User::ROLE_ADMIN, 'password' => 'x12345678', 'password_confirmation' => 'x12345678'];

        // Not signed in: refused before anything is saved
        $this->post(route('user.store'), $payload)->assertRedirect(route('login'));
        $this->assertDatabaseMissing('users', ['email' => 'intruder@test.local']);

        // Signed in without users_create
        $this->actingAs($this->userWith(['customers_access']))->post(route('user.store'), $payload)->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'intruder@test.local']);

        // With users_create (not admin): allowed
        $this->actingAs($this->userWith(['users_access', 'users_create']))
            ->post(route('user.store'), ['role_id' => $roleId] + $payload)->assertSessionHas('success');
        $this->assertDatabaseHas('users', ['email' => 'intruder@test.local', 'role_id' => $roleId]);
    }

    public function test_create_and_edit_are_enforced_on_the_server(): void
    {
        $customer = DB::table('customers')->first();
        $form = [
            'customer_no' => $customer->customer_no, 'fname' => $customer->fname, 'lname' => 'Changed By Test',
            'email' => $customer->email ?: 'x@test.local', 'phone' => $customer->phone ?: '0', 'fax' => $customer->fax ?: '0',
            'country_id' => $customer->country_id, 'currency_id' => $customer->currency_id,
        ];

        $this->actingAs($this->userWith(['customers_access', 'customers_show']))
            ->post(route('customer.update', $customer->customer_id), $form)->assertForbidden();
        $this->assertDatabaseMissing('customers', ['customer_id' => $customer->customer_id, 'lname' => 'Changed By Test']);

        $this->actingAs($this->userWith(['customers_access', 'customers_edit']))
            ->post(route('customer.update', $customer->customer_id), $form)->assertRedirect();
        $this->assertDatabaseHas('customers', ['customer_id' => $customer->customer_id, 'lname' => 'Changed By Test']);
    }

    public function test_buttons_follow_the_users_permissions(): void
    {
        $viewer = $this->userWith(['customers_access', 'customers_show']);
        $page = $this->actingAs($viewer)->get(route('customer'))->assertOk();
        $page->assertDontSee(route('customer.add'), false);
        $page->assertDontSee('js-delete-record', false);
        $page->assertDontSee('>Edit</a>', false);

        $editor = $this->userWith(['customers_access', 'customers_create', 'customers_edit', 'customers_delete']);
        $page = $this->actingAs($editor)->get(route('customer'))->assertOk();
        $page->assertSee(route('customer.add'), false);
        $page->assertSee('js-delete-record', false);
    }

    public function test_menu_shows_only_permitted_links(): void
    {
        $settingsOnly = $this->userWith(['settings_access', 'settings_show', 'settings_edit']);
        $page = $this->actingAs($settingsOnly)->get(route('company.edit', DB::table('company')->value('id')))->assertOk();
        $page->assertSee(route('company'), false);
        $page->assertDontSee('href="'.route('user').'"', false);
        $page->assertDontSee('href="'.route('role').'"', false);
        $this->get(route('user'))->assertForbidden();

        // Heads / Category sit in the Settings menu and accept settings_* (2026-11-12 decision)
        $this->get(route('head'))->assertOk();
    }

    public function test_reports_menu_pages_open_with_reports_permission(): void
    {
        $reports = $this->userWith(['reports_access']);
        $this->actingAs($reports);
        $this->get(route('stock.daily'))->assertOk();
        $this->get(route('rstock.daily'))->assertOk();
        $this->get(route('material.detail'))->assertOk();
        $this->get(route('purchase.ledger'))->assertOk();
        $this->get(route('reports.product.stock.requirements'))->assertOk();
        // but not the modules themselves
        $this->get(route('purchase'))->assertForbidden();
    }

    public function test_payroll_uses_payroll_permissions(): void
    {
        $this->actingAs($this->userWith(['payroll_access', 'payroll_show']))->get(route('payroll.index'))->assertOk();
        $this->actingAs($this->userWith(['transactions_access']))->get(route('payroll.index'))->assertForbidden();
    }

    public function test_ajax_and_image_endpoints_are_protected(): void
    {
        $bankId = DB::table('banks')->value('bank_id');
        $this->getJson(route('ajaxBalance', ['bank_id' => $bankId]))->assertUnauthorized();
        $this->actingAs($this->userWith(['customers_access']))->getJson(route('ajaxBalance', ['bank_id' => $bankId]))->assertForbidden();

        $image = DB::table('images')->where('table_name', 'products')->first();
        if ($image) {
            $this->actingAs($this->userWith(['products_access', 'products_show']))
                ->post(route('image.delete', ['id' => $image->image_id, 'dir' => 'product']))->assertForbidden();
            $this->assertDatabaseHas('images', ['image_id' => $image->image_id]);
        }
    }

    public function test_permission_changes_apply_without_code_changes(): void
    {
        $user = $this->userWith(['orders_access']);
        $this->actingAs($user)->get(route('order.add'))->assertForbidden();

        // Grant orders_create through the role editor's table; the next request sees it
        DB::table('permission_role')->insert([
            'role_id' => $user->role_id,
            'permission_id' => DB::table('permissions')->where('name', 'orders_create')->value('id'),
        ]);
        $this->actingAs($user->fresh())->get(route('order.add'))->assertOk();
    }
}
