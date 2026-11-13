<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use App\Repositories\TransactionRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Optional Expense Head on Journal Vouchers: saved, shown, editable, and without any effect on
 * the amounts or balances. Rolled back after each test.
 */
class JournalVoucherExpenseHeadTest extends TestCase
{
    use DatabaseTransactions;

    private function voucher(array $extra = []): array
    {
        return $extra + [
            'transaction_to' => 'generalVoucher', 'transaction_type' => 'generalVoucher',
            'payee_bank_id' => 0, 'bank_id' => 0, 'payee_type' => 'vendor',
            'payee_id' => DB::table('vendors')->where('vendor_type', '0')->value('vendor_id'),
            'transaction_date' => '2026-11-13', 'voucher_type' => 'debit', 'amount' => 1234.5,
            'description' => 'JV expense head test',
        ];
    }

    private function lastVoucher(): Transaction
    {
        return Transaction::where('transaction_type', 'generalVoucher')->orderByDesc('transaction_id')->firstOrFail();
    }

    public function test_voucher_with_and_without_expense_head_post_the_same_amounts(): void
    {
        $this->actingAs(User::where('role_id', User::ROLE_ADMIN)->firstOrFail());
        $head = DB::table('heads')->where('head_type_id', 7)->value('head_id');
        $cashBefore = app(TransactionRepository::class)->cashBalance();

        $this->post(route('transaction.store'), $this->voucher())->assertSessionHasNoErrors()->assertRedirect();
        $without = $this->lastVoucher();
        $this->assertNull($without->expense_head_id);
        $cashAfterWithout = app(TransactionRepository::class)->cashBalance();

        $this->post(route('transaction.store'), $this->voucher(['expense_head_id' => $head]))->assertSessionHasNoErrors()->assertRedirect();
        $with = $this->lastVoucher();
        $this->assertSame((int) $head, (int) $with->expense_head_id);
        $cashAfterWith = app(TransactionRepository::class)->cashBalance();

        foreach (['transaction_to', 'debit', 'credit', 'cc_amount', 'ledger_flag', 'bank_id', 'payee_id'] as $column) {
            $this->assertEquals($without->$column, $with->$column, "{$column} must not depend on the expense head");
        }
        // Whatever a voucher does to the balances, it does the same with or without an expense head
        $this->assertEqualsWithDelta($cashAfterWithout - $cashBefore, $cashAfterWith - $cashAfterWithout, 0.001);

        // Shown on the detail page, the listing and the voucher narration
        $name = DB::table('heads')->where('head_id', $head)->value('name');
        $this->get(route('transaction.showGeneralVoucher', $with->transaction_id))->assertOk()->assertSee('Expense Head:')->assertSee($name);
        $this->get(route('generalVoucher'))->assertOk()->assertSee($name);
        $this->assertSame($name, app(TransactionRepository::class)->describe([$with->transaction_id])->first()->details['Expense Head'] ?? null);
    }

    public function test_expense_head_can_be_changed_and_cleared_when_editing(): void
    {
        $this->actingAs(User::where('role_id', User::ROLE_ADMIN)->firstOrFail());
        [$first, $second] = DB::table('heads')->where('head_type_id', 7)->orderBy('head_id')->limit(2)->pluck('head_id')->all();
        $this->post(route('transaction.store'), $this->voucher(['expense_head_id' => $first]));
        $voucher = $this->lastVoucher();

        $this->get(route('transaction.editGeneralVoucher', $voucher->transaction_id))->assertOk()->assertSee('name="expense_head_id"', false);

        $this->post(route('transaction.update', $voucher->transaction_id), $this->voucher(['expense_head_id' => $second]))->assertRedirect();
        $this->assertSame((int) $second, (int) $voucher->fresh()->expense_head_id);
        $this->assertEquals(1234.5, (float) $voucher->fresh()->credit);

        $this->post(route('transaction.update', $voucher->transaction_id), $this->voucher(['expense_head_id' => '']))->assertRedirect();
        $this->assertNull($voucher->fresh()->expense_head_id);
    }

    public function test_only_expense_heads_are_accepted(): void
    {
        $this->actingAs(User::where('role_id', User::ROLE_ADMIN)->firstOrFail());
        $notExpense = DB::table('heads')->where('head_type_id', '!=', 7)->value('head_id');
        $count = Transaction::count();
        $this->post(route('transaction.store'), $this->voucher(['expense_head_id' => $notExpense]))->assertSessionHasErrors('expense_head_id');
        $this->assertSame($count, Transaction::count());
    }
}
