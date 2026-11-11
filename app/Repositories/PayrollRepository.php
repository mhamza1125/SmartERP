<?php

namespace App\Repositories;

use App\Models\Company;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Monthly payroll with a full salary breakdown per employee.
 *
 *   hourly wage            = gross / total working days / hours per day
 *   salary for days        = gross x actual working days / total working days
 *   overtime rate          = hourly wage x overtime multiplier (Settings > Company)
 *   late deduction         = late minutes / 60 x hourly wage x late multiplier
 *   remaining fixed advance = fixed advance - fixed advance deduction
 *   total salary           = salary for days + overtime - late deduction - temporary advance - fixed advance deduction
 *
 * Saving posts to the employee ledger: the salary payment (total salary, from the payroll's
 * bank / cash) and the temporary advance and fixed advance deduction as Receive Loan entries.
 * The deductions move no money, so they carry ledger_flag 0 (left out of bank / cash balances).
 */
class PayrollRepository implements GlobalInterface
{
    private const SALARY_EMPLOYEE_TYPE = 39;

    /**
     * Payroll months, newest first: payrolls saved with a breakdown, plus older months that
     * only have salary transactions (legacy = true).
     */
    public function all()
    {
        $payrolls = Payroll::leftJoin('payroll_items', 'payroll_items.payroll_id', '=', 'payrolls.payroll_id')
            ->groupBy('payrolls.payroll_id', 'payrolls.month', 'payrolls.payment_date', 'payrolls.total_working_days')
            ->select('payrolls.payroll_id', 'payrolls.month', 'payrolls.payment_date', 'payrolls.total_working_days',
                DB::raw('COUNT(payroll_items.payroll_item_id) as employee_count'),
                DB::raw('COALESCE(SUM(payroll_items.net_salary), 0) as total_amount'))
            ->get()
            ->map(fn ($row) => (object) array_merge($row->toArray(), ['legacy' => false]));

        $legacy = Transaction::where('transaction_type', 'salary')
            ->where('transaction_to', 'employee')
            ->whereNotIn('transaction_id', $this->postedTransactionIds())
            ->select(
                DB::raw("DATE_FORMAT(transaction_date, '%Y-%m') as month"),
                DB::raw('MIN(transaction_date) as payment_date'),
                DB::raw('COUNT(DISTINCT payee_id) as employee_count'),
                DB::raw('SUM(COALESCE(credit, debit)) as total_amount')
            )
            ->groupBy(DB::raw("DATE_FORMAT(transaction_date, '%Y-%m')"))
            ->get()
            ->reject(fn ($row) => $payrolls->contains('month', $row->month))
            ->map(fn ($row) => (object) array_merge($row->toArray(), ['payroll_id' => null, 'total_working_days' => null, 'legacy' => true]));

        return $payrolls->concat($legacy)->sortByDesc('month')->values();
    }

    /** The payroll saved for a month (Y-m), or null. */
    public function get($month)
    {
        return Payroll::where('month', $month)->first();
    }

    /**
     * Save a month's payroll (new, or replacing a saved one) and post its transactions.
     *
     * $data: month, total_working_days, hours_per_day, overtime_multiplier, late_multiplier,
     *        payment_date, bank_id, description, employees[employee_id => [actual_working_days,
     *        overtime_hours, late_minutes, temp_advance, fixed_advance, fixed_advance_deduction]]
     *
     * @throws ValidationException when deductions exceed an employee's salary
     */
    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $payroll = Payroll::where('month', $data['month'])->lockForUpdate()->first();
            if ($payroll) {
                $this->removePostings($payroll);
            } else {
                // Months processed before the breakdown existed only have transactions
                $this->removeLegacyPostings($data['month']);
            }

            $period = $this->period($data);
            $payroll = Payroll::updateOrCreate(['month' => $data['month']], array_merge($period, [
                'payment_date' => $data['payment_date'],
                'bank_id' => (int) $data['bank_id'],
                'description' => $data['description'] ?? null,
                'created_by' => $payroll->created_by ?? auth()->id(),
            ]));

            $monthLabel = Carbon::createFromFormat('Y-m', $data['month'])->format('F Y');
            $employees = Employee::whereIn('employee_id', array_keys($data['employees']))->get()->keyBy('employee_id');
            $errors = [];

            foreach ($data['employees'] as $employeeId => $input) {
                $employee = $employees->get($employeeId);
                if (! $employee) {
                    continue;
                }
                $item = $this->calculate((float) $employee->salary, $input, $period);
                if ($item['net_salary'] < 0) {
                    $errors["employees.{$employeeId}.fixed_advance_deduction"] = "{$employee->name}: deductions ("
                        .number_format($item['temp_advance'] + $item['fixed_advance_deduction'] + $item['late_deduction'], 2)
                        .') are more than the salary earned ('.number_format($item['salary_for_days'] + $item['overtime_amount'], 2).').';

                    continue;
                }

                $item += $this->post($employeeId, $item, $payroll, $monthLabel);
                PayrollItem::create(array_merge($item, [
                    'payroll_id' => $payroll->payroll_id,
                    'employee_id' => $employeeId,
                    'designation' => $employee->designation,
                ]));
            }

            if ($errors) {
                throw ValidationException::withMessages($errors);
            }

            return $payroll->payroll_id;
        });
    }

    /** Same as store(): a month has one payroll, saving it again replaces it. */
    public function update($month, array $data)
    {
        return $this->store(array_merge($data, ['month' => $month]));
    }

    /** Remove a month's payroll and the transactions it posted. */
    public function delete($month)
    {
        DB::transaction(function () use ($month) {
            $payroll = Payroll::where('month', $month)->first();
            if ($payroll) {
                $this->removePostings($payroll);
                $payroll->delete();
            }
        });
    }

    /** Payroll defaults from Settings > Company. */
    public function settings(): array
    {
        $company = Company::first();

        return [
            'hours_per_day' => (float) ($company->work_hours_per_day ?? 8) ?: 8,
            'overtime_multiplier' => (float) ($company->overtime_multiplier ?? 2),
            'late_multiplier' => (float) ($company->late_multiplier ?? 1),
        ];
    }

    /**
     * One employee's salary breakdown.
     *
     * @param  array  $input  actual_working_days, overtime_hours, late_minutes, temp_advance, fixed_advance, fixed_advance_deduction
     * @param  array  $period  total_working_days, hours_per_day, overtime_multiplier, late_multiplier
     */
    public function calculate(float $gross, array $input, array $period): array
    {
        $totalDays = (float) $period['total_working_days'];
        $hoursPerDay = (float) $period['hours_per_day'] ?: 8;
        $actualDays = (float) ($input['actual_working_days'] ?? $totalDays);
        $hourly = $totalDays > 0 ? $gross / $totalDays / $hoursPerDay : 0;

        $salaryForDays = $totalDays > 0 ? $gross * $actualDays / $totalDays : 0;
        $overtimeHours = (float) ($input['overtime_hours'] ?? 0);
        $overtimeRate = $hourly * (float) $period['overtime_multiplier'];
        $lateMinutes = (int) round((float) ($input['late_minutes'] ?? 0));
        $lateDeduction = $lateMinutes / 60 * $hourly * (float) $period['late_multiplier'];
        $tempAdvance = (float) ($input['temp_advance'] ?? 0);
        $fixedAdvance = (float) ($input['fixed_advance'] ?? 0);
        $fixedDeduction = min((float) ($input['fixed_advance_deduction'] ?? 0), $fixedAdvance);

        $round = fn ($value) => round($value, 2);
        $salaryForDays = $round($salaryForDays);
        $overtimeAmount = $round($overtimeHours * $overtimeRate);
        $lateDeduction = $round($lateDeduction);

        return [
            'gross_salary' => $round($gross),
            'total_working_days' => $totalDays,
            'actual_working_days' => $actualDays,
            'salary_for_days' => $salaryForDays,
            'overtime_hours' => $overtimeHours,
            'overtime_rate' => $round($overtimeRate),
            'overtime_amount' => $overtimeAmount,
            'late_minutes' => $lateMinutes,
            'late_deduction' => $lateDeduction,
            'temp_advance' => $round($tempAdvance),
            'fixed_advance' => $round($fixedAdvance),
            'fixed_advance_deduction' => $round($fixedDeduction),
            'remaining_fixed_advance' => $round($fixedAdvance - $fixedDeduction),
            'net_salary' => $round($salaryForDays + $overtimeAmount - $lateDeduction - $tempAdvance - $fixedDeduction),
        ];
    }

    /**
     * The payroll sheet for a month: every salary employee with their saved values, or
     * (new payroll / employee not in it) defaults: full attendance, no overtime / late time,
     * this month's temporary advances, the open fixed-loan balance and last month's instalment.
     */
    public function sheet(string $month): array
    {
        $payroll = $this->get($month);
        $period = $payroll ? $this->period($payroll->toArray()) : null;
        $items = $payroll ? PayrollItem::where('payroll_id', $payroll->payroll_id)->get()->keyBy('employee_id') : collect();
        $ownDeductions = $items->pluck('fixed_deduction_transaction_id')->filter()->all();
        $previous = $this->previousItems($month);

        $rows = $this->employees()->map(function ($employee) use ($month, $items, $ownDeductions, $previous) {
            $saved = $items->get($employee->employee_id);
            $openLoan = $this->openFixedLoan($employee->employee_id, $ownDeductions);
            $lastInstalment = (float) ($previous[$employee->employee_id]->fixed_advance_deduction ?? 0);

            return (object) [
                'employee' => $employee,
                'included' => $saved !== null || ! $items->count(),
                'actual_working_days' => $saved?->actual_working_days,
                'overtime_hours' => (float) ($saved->overtime_hours ?? 0),
                'late_minutes' => (int) ($saved->late_minutes ?? 0),
                'temp_advance' => (float) ($saved->temp_advance ?? $this->temporaryAdvance($employee->employee_id, $month)),
                'fixed_advance' => (float) ($saved->fixed_advance ?? $openLoan),
                'fixed_advance_deduction' => (float) ($saved->fixed_advance_deduction ?? min($lastInstalment, $openLoan)),
            ];
        });

        return ['payroll' => $payroll, 'period' => $period ?? array_merge(['total_working_days' => $this->defaultWorkingDays($month)], $this->settings()), 'rows' => $rows];
    }

    /** A saved payroll's items with employee details, in sheet order. */
    public function items(string $month): Collection
    {
        $payroll = $this->get($month);
        if (! $payroll) {
            return collect();
        }

        return PayrollItem::where('payroll_items.payroll_id', $payroll->payroll_id)
            ->join('employees', 'employees.employee_id', '=', 'payroll_items.employee_id')
            ->select('payroll_items.*', 'employees.employee_no', 'employees.name', 'employees.cnic')
            ->orderBy('employees.name')
            ->get();
    }

    /** One saved item with its employee and payroll. */
    public function item(int $payrollItemId)
    {
        return PayrollItem::where('payroll_items.payroll_item_id', $payrollItemId)
            ->join('employees', 'employees.employee_id', '=', 'payroll_items.employee_id')
            ->join('payrolls', 'payrolls.payroll_id', '=', 'payroll_items.payroll_id')
            ->leftJoin('heads as dhead', 'dhead.head_id', '=', 'employees.department_id')
            ->select('payroll_items.*', 'employees.employee_no', 'employees.name', 'employees.cnic', 'dhead.name as department',
                'payrolls.month', 'payrolls.payment_date', 'payrolls.bank_id', 'payrolls.hours_per_day',
                'payrolls.overtime_multiplier', 'payrolls.late_multiplier')
            ->first();
    }

    /** Salary employees (employee type 39). */
    public function employees(): Collection
    {
        return Employee::where('employee_type_id', self::SALARY_EMPLOYEE_TYPE)
            ->orderBy('name')
            ->get(['employee_id', 'employee_no', 'name', 'designation', 'salary']);
    }

    /** Temporary advances paid to an employee within the month. */
    public function temporaryAdvance(int $employeeId, string $month): float
    {
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();

        return (float) Transaction::where('payee_id', $employeeId)
            ->where('transaction_to', 'employee')
            ->where('transaction_type', 'advance')
            ->where('advance_type', Transaction::ADVANCE_TEMPORARY)
            ->whereBetween('transaction_date', [$start->toDateString(), $start->copy()->endOfMonth()->toDateString()])
            ->sum('credit');
    }

    /**
     * Open long-term loan of an employee: loans given minus repaid. Give / Receive Loan entries
     * without a type (made before loans were typed) count as fixed loans.
     *
     * @param  array  $excludeTransactionIds  repayments to leave out (a payroll being edited)
     */
    public function openFixedLoan(int $employeeId, array $excludeTransactionIds = []): float
    {
        $sums = Transaction::where('payee_id', $employeeId)
            ->where('transaction_to', 'employee')
            ->whereIn('transaction_type', ['advance', 'receiveAdvance'])
            ->where(fn ($query) => $query->whereNull('advance_type')->orWhere('advance_type', Transaction::ADVANCE_FIXED))
            ->when($excludeTransactionIds, fn ($query) => $query->whereNotIn('transaction_id', $excludeTransactionIds))
            ->selectRaw("COALESCE(SUM(CASE WHEN transaction_type = 'advance' THEN credit ELSE 0 END), 0) as given")
            ->selectRaw("COALESCE(SUM(CASE WHEN transaction_type = 'receiveAdvance' THEN debit ELSE 0 END), 0) as repaid")
            ->first();

        return max(0, round((float) $sums->given - (float) $sums->repaid, 2));
    }

    /** Working days of a month by default: calendar days minus Sundays. */
    public function defaultWorkingDays(string $month): int
    {
        $day = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = $day->copy()->endOfMonth();
        $days = 0;
        for (; $day->lte($end); $day->addDay()) {
            if (! $day->isSunday()) {
                $days++;
            }
        }

        return $days;
    }

    private function period(array $data): array
    {
        $settings = $this->settings();

        return [
            'total_working_days' => (float) $data['total_working_days'],
            'hours_per_day' => (float) ($data['hours_per_day'] ?? $settings['hours_per_day']) ?: 8,
            'overtime_multiplier' => (float) ($data['overtime_multiplier'] ?? $settings['overtime_multiplier']),
            'late_multiplier' => (float) ($data['late_multiplier'] ?? $settings['late_multiplier']),
        ];
    }

    /** The latest earlier payroll's items, keyed by employee_id. */
    private function previousItems(string $month): Collection
    {
        $previous = Payroll::where('month', '<', $month)->orderByDesc('month')->first();

        return $previous ? PayrollItem::where('payroll_id', $previous->payroll_id)->get()->keyBy('employee_id') : collect();
    }

    /** Post an employee's salary and deductions; returns the transaction ids. */
    private function post(int $employeeId, array $item, Payroll $payroll, string $monthLabel): array
    {
        $base = [
            'payee_id' => $employeeId,
            'transaction_to' => 'employee',
            'transaction_date' => $payroll->payment_date,
            'payee_bank_id' => 0,
            'created_by' => auth()->id(),
        ];
        $ids = ['salary_transaction_id' => null, 'temp_deduction_transaction_id' => null, 'fixed_deduction_transaction_id' => null];

        if ($item['net_salary'] > 0) {
            $ids['salary_transaction_id'] = Transaction::create(array_merge($base, [
                'bank_id' => $payroll->bank_id,
                'transaction_type' => 'salary',
                'credit' => $item['net_salary'],
                'debit' => 0,
                'description' => 'Monthly Salary - '.$monthLabel,
            ]))->transaction_id;
        }
        // Deductions settle advances against the salary: no money moves, so not in bank / cash balances
        foreach ([
            'temp_deduction_transaction_id' => [$item['temp_advance'], Transaction::ADVANCE_TEMPORARY, 'Temporary Advance Deduction - '],
            'fixed_deduction_transaction_id' => [$item['fixed_advance_deduction'], Transaction::ADVANCE_FIXED, 'Loan Repayment - '],
        ] as $key => [$amount, $type, $label]) {
            if ($amount > 0) {
                $ids[$key] = Transaction::create(array_merge($base, [
                    'bank_id' => 0,
                    'ledger_flag' => 0,
                    'transaction_type' => 'receiveAdvance',
                    'advance_type' => $type,
                    'debit' => $amount,
                    'credit' => null,
                    'description' => $label.$monthLabel,
                ]))->transaction_id;
            }
        }

        return $ids;
    }

    /** Delete a saved payroll's items and the transactions they posted. */
    private function removePostings(Payroll $payroll): void
    {
        $items = PayrollItem::where('payroll_id', $payroll->payroll_id)->get();
        $ids = $items->flatMap(fn ($item) => [$item->salary_transaction_id, $item->temp_deduction_transaction_id, $item->fixed_deduction_transaction_id])->filter();
        if ($ids->isNotEmpty()) {
            Transaction::whereIn('transaction_id', $ids)->delete();
        }
        PayrollItem::where('payroll_id', $payroll->payroll_id)->delete();
    }

    /** Every transaction a saved payroll posted (subquery). */
    private function postedTransactionIds()
    {
        return DB::table('payroll_items')->whereNotNull('salary_transaction_id')->select('salary_transaction_id')
            ->union(DB::table('payroll_items')->whereNotNull('temp_deduction_transaction_id')->select('temp_deduction_transaction_id'))
            ->union(DB::table('payroll_items')->whereNotNull('fixed_deduction_transaction_id')->select('fixed_deduction_transaction_id'));
    }

    /** A month processed before payroll breakdowns: its salary and payroll loan-repayment transactions. */
    private function removeLegacyPostings(string $month): void
    {
        $date = Carbon::createFromFormat('Y-m', $month);
        Transaction::where('transaction_to', 'employee')
            // A saved payroll paid in this month (e.g. last month's salaries) is not legacy
            ->whereNotIn('transaction_id', $this->postedTransactionIds())
            ->whereYear('transaction_date', $date->year)
            ->whereMonth('transaction_date', $date->month)
            ->where(function ($query) {
                $query->where('transaction_type', 'salary')
                    ->orWhere(fn ($q) => $q->where('transaction_type', 'receiveAdvance')->where('description', 'like', 'Loan Repayment%'));
            })
            ->delete();
    }
}
