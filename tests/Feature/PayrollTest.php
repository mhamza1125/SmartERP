<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\PayrollRepository;
use App\Repositories\TransactionRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Payroll with salary breakdown: calculation, pre-filled advances, ledger posting, edit, prints.
 * Runs inside a transaction that is rolled back.
 */
class PayrollTest extends TestCase
{
    use DatabaseTransactions;

    private Employee $employee;

    private string $month = '2031-03'; // a month no real payroll uses

    protected function setUp(): void
    {
        parent::setUp();

        $admin = User::where('role_id', User::ROLE_ADMIN)->first();
        $employee = Employee::where('employee_type_id', 39)->first();
        if (! $admin || ! $employee) {
            $this->markTestSkipped('Needs an admin user and a salary employee.');
        }
        $this->actingAs($admin);
        $employee->update(['salary' => 10000]);
        // Enough cash for the test loans (the loan form refuses more than the balance)
        Transaction::create([
            'transaction_to' => 'brs', 'transaction_type' => 'brs', 'bank_id' => 0, 'debit' => 1000000,
            'transaction_date' => '2031-01-01', 'payee_id' => 0, 'payee_bank_id' => 0, 'description' => 'Test cash', 'created_by' => $admin->id,
        ]);
        $this->employee = $employee->fresh();
    }

    public function test_salary_breakdown_is_calculated_and_posted_to_the_ledger(): void
    {
        $id = $this->employee->employee_id;
        $openLoanBefore = app(PayrollRepository::class)->openFixedLoan($id);

        // A temporary advance this month and a long-term loan, given on the Give Loan form
        $this->giveLoan(2000, Transaction::ADVANCE_TEMPORARY, $this->month.'-10');
        $this->giveLoan(100000, Transaction::ADVANCE_FIXED, '2031-01-15');

        // The sheet pre-fills them
        $row = collect(app(PayrollRepository::class)->sheet($this->month)['rows'])->firstWhere('employee.employee_id', $id);
        $this->assertEqualsWithDelta(2000, $row->temp_advance, 0.001);
        $this->assertEqualsWithDelta($openLoanBefore + 100000, $row->fixed_advance, 0.001);
        $this->get(route('payroll.create', ['month' => $this->month]))->assertOk()->assertSee('Total Working Days');

        $cashBefore = app(TransactionRepository::class)->cashBalance();
        $this->post(route('payroll.store'), $this->sheet([
            'actual_working_days' => 24, 'overtime_hours' => 5, 'late_minutes' => 90,
            'temp_advance' => 2000, 'fixed_advance' => 100000, 'fixed_advance_deduction' => 1500,
        ]))->assertSessionHasNoErrors()->assertRedirect(route('payroll.info', $this->month));

        // gross 10,000 / 26 days / 8 h = 48.077 per hour; overtime 2x, late 1x
        $item = $this->item();
        $this->assertEqualsWithDelta(9230.77, $item->salary_for_days, 0.001);
        $this->assertEqualsWithDelta(96.15, $item->overtime_rate, 0.001);
        $this->assertEqualsWithDelta(480.77, $item->overtime_amount, 0.001);
        $this->assertEqualsWithDelta(72.12, $item->late_deduction, 0.001);
        $this->assertEqualsWithDelta(98500, $item->remaining_fixed_advance, 0.001);
        $this->assertEqualsWithDelta(6139.42, $item->net_salary, 0.001);
        $this->assertSame($this->employee->designation, $item->designation);

        // Ledger: salary paid from cash, both deductions as Receive Loan entries outside cash balances
        $salary = Transaction::find($item->salary_transaction_id);
        $this->assertSame('salary', $salary->transaction_type);
        $this->assertEqualsWithDelta(6139.42, $salary->credit, 0.001);
        $temp = Transaction::find($item->temp_deduction_transaction_id);
        $fixed = Transaction::find($item->fixed_deduction_transaction_id);
        $this->assertSame(['receiveAdvance', Transaction::ADVANCE_TEMPORARY, 0], [$temp->transaction_type, $temp->advance_type, (int) $temp->ledger_flag]);
        $this->assertSame(['receiveAdvance', Transaction::ADVANCE_FIXED, 0], [$fixed->transaction_type, $fixed->advance_type, (int) $fixed->ledger_flag]);
        $this->assertEqualsWithDelta($cashBefore - 6139.42, app(TransactionRepository::class)->cashBalance(), 0.001, 'only the salary leaves cash');
        $this->assertEqualsWithDelta($openLoanBefore + 98500, app(PayrollRepository::class)->openFixedLoan($id), 0.001);

        // Pages and prints
        $this->get(route('payroll.index'))->assertOk()->assertSee('March 2031');
        $this->get(route('payroll.info', $this->month))->assertOk()->assertSee($this->employee->name);
        $this->get(route('payroll.print', $this->month))->assertOk()->assertSee('Salary Sheet')->assertSee('6,139.42');
        $this->get(route('payroll.slip', $item->payroll_item_id))->assertOk()->assertSee('Salary Slip')->assertSee('98,500.00');
        $this->get(route('payroll.edit', $this->month))->assertOk()->assertSee('Update Payroll');

        // Editing replaces the payroll's transactions instead of adding more
        $oldIds = [$item->salary_transaction_id, $item->temp_deduction_transaction_id, $item->fixed_deduction_transaction_id];
        $this->put(route('payroll.update', $this->month), $this->sheet([
            'actual_working_days' => 26, 'overtime_hours' => 0, 'late_minutes' => 0,
            'temp_advance' => 2000, 'fixed_advance' => 100000, 'fixed_advance_deduction' => 1000,
        ]))->assertSessionHasNoErrors();
        $this->assertSame(0, Transaction::whereIn('transaction_id', $oldIds)->count());
        $item = $this->item();
        $this->assertEqualsWithDelta(7000, $item->net_salary, 0.001);
        $this->assertSame(1, PayrollItem::where('payroll_id', $item->payroll_id)->count());
        $this->assertEqualsWithDelta($openLoanBefore + 99000, app(PayrollRepository::class)->openFixedLoan($id), 0.001);
        // On the edit sheet the loan shown is the balance before this payroll's own instalment
        $row = collect(app(PayrollRepository::class)->sheet($this->month)['rows'])->firstWhere('employee.employee_id', $id);
        $this->assertEqualsWithDelta(100000, $row->fixed_advance, 0.001);
    }

    public function test_deductions_larger_than_the_salary_are_refused(): void
    {
        $this->post(route('payroll.store'), $this->sheet([
            'actual_working_days' => 26, 'overtime_hours' => 0, 'late_minutes' => 0,
            'temp_advance' => 12000, 'fixed_advance' => 0, 'fixed_advance_deduction' => 0,
        ]))->assertSessionHasErrors();

        $this->assertNull(Payroll::where('month', $this->month)->first());
    }

    private function giveLoan(float $amount, string $type, string $date): void
    {
        $this->post(route('transaction.store'), [
            'transaction_to' => 'employee', 'transaction_type' => 'advance', 'advance_type' => $type,
            'payee_id' => $this->employee->employee_id, 'bank_id' => 0, 'payee_bank_id' => 0,
            'transaction_date' => $date, 'credit' => $amount, 'description' => 'Test loan',
        ]);
        $this->assertTrue(Transaction::where('payee_id', $this->employee->employee_id)->where('advance_type', $type)->where('credit', $amount)->exists(), "{$type} loan saved with its type");
    }

    private function sheet(array $row): array
    {
        return [
            'month' => $this->month, 'total_working_days' => 26, 'hours_per_day' => 8,
            'overtime_multiplier' => 2, 'late_multiplier' => 1, 'payment_date' => $this->month.'-28', 'bank_id' => 0,
            'employee_ids' => [$this->employee->employee_id],
            'employees' => [$this->employee->employee_id => $row],
        ];
    }

    private function item(): PayrollItem
    {
        return PayrollItem::join('payrolls', 'payrolls.payroll_id', '=', 'payroll_items.payroll_id')
            ->where('payrolls.month', $this->month)
            ->where('payroll_items.employee_id', $this->employee->employee_id)
            ->select('payroll_items.*')
            ->firstOrFail();
    }
}
