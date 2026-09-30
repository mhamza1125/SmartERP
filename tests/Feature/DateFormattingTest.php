<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Dates are displayed as DD-MM-YYYY; nullable dates are handled safely.
 * Read-only against existing data; runs inside a rolled-back transaction.
 */
class DateFormattingTest extends TestCase
{
    use DatabaseTransactions;

    public function test_format_date_helper(): void
    {
        $this->assertSame('28-02-2026', formatDate('2026-02-28'));
        $this->assertSame('28-02-2026', formatDate('2026-02-28 13:45:00'));
        $this->assertSame('28-02-2026', formatDate(now()->setDate(2026, 2, 28)));
        $this->assertSame('-', formatDate(null));
        $this->assertSame('-', formatDate(''));
        $this->assertSame('N/A', formatDate('0000-00-00', 'N/A'));
        $this->assertSame('N/A', formatDate('not a date', 'N/A'));
        $this->assertSame('28-02-2026 01:45 PM', formatDateTime('2026-02-28 13:45:00'));
    }

    public function test_print_views_show_dd_mm_yyyy(): void
    {
        $admin = User::where('role_id', User::ROLE_ADMIN)->first();
        if (! $admin) {
            $this->markTestSkipped('Needs an admin user.');
        }
        $this->actingAs($admin);

        $purchase = DB::table('purchases')->whereNotNull('purchase_date')->orderByDesc('purchase_id')->first();
        if ($purchase) {
            $response = $this->get(route('purchase.print', $purchase->purchase_id))->assertOk();
            $response->assertSee(formatDate($purchase->purchase_date));
            $response->assertDontSee('>'.$purchase->purchase_date.'<', false);
            if ($purchase->require_date) {
                $response->assertSee(formatDate($purchase->require_date));
            }
        }

        foreach ([
            ['igroups', 'igroup_id', 'igroup.print'],
            ['employees', 'employee_id', 'employee.print'],
        ] as [$table, $key, $route]) {
            $id = DB::table($table)->value($key);
            if ($id) {
                $this->get(route($route, $id))->assertOk();
            }
        }

        foreach (['igroup', 'order', 'workTime', 'workHoliday', 'mprocess'] as $page) {
            if (\Route::has($page)) {
                $this->get(route($page))->assertOk();
            }
        }
    }
}
