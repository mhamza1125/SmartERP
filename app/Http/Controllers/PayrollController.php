<?php

namespace App\Http\Controllers;

use App\Http\Requests\PayrollRequest;
use App\Models\Transaction;
use App\Repositories\BankRepository;
use App\Repositories\PayrollRepository;
use App\Repositories\TransactionRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Monthly payroll: attendance-based salary, overtime, late deduction, temporary advance and
 * fixed-loan instalment per employee. Calculations and ledger posting live in PayrollRepository.
 */
class PayrollController extends Controller
{
    public function __construct(
        protected PayrollRepository $payrollRepository,
        protected TransactionRepository $transactionRepository,
        protected BankRepository $bankRepository
    ) {
        $this->middleware(['auth', 'all']);
    }

    /** Payroll months, newest first. */
    public function index()
    {
        $this->authorize('access', Transaction::class);

        return view('payroll', [
            'payrollBatches' => $this->payrollRepository->all(),
        ]);
    }

    /** New payroll sheet for a month (a month that already has one opens its edit sheet). */
    public function create(Request $request)
    {
        $this->authorize('create', Transaction::class);

        $month = $this->monthOrNull($request->query('month', Carbon::now()->format('Y-m'))) ?? Carbon::now()->format('Y-m');
        if ($this->payrollRepository->get($month)) {
            return redirect()->route('payroll.edit', $month)
                ->with('warning', 'Payroll for '.$this->monthLabel($month).' has already been processed. You can edit it below.');
        }

        return view('addPayroll', $this->sheetView($month, false));
    }

    public function store(PayrollRequest $request)
    {
        $this->authorize('create', Transaction::class);
        $data = $request->payroll();
        $this->payrollRepository->store($data);

        return redirect()->route('payroll.info', $data['month'])
            ->with('success', 'Payroll for '.$this->monthLabel($data['month']).' processed: '.count($data['employees']).' employee(s).');
    }

    /** Edit sheet of a month's payroll (also opens months processed before breakdowns existed). */
    public function edit($month)
    {
        $this->authorize('edit', Transaction::class);
        if (! $this->monthOrNull($month)) {
            return redirect()->route('payroll.index')->with('error', 'Invalid month format');
        }

        return view('addPayroll', $this->sheetView($month, true));
    }

    public function update(PayrollRequest $request, $month)
    {
        $this->authorize('edit', Transaction::class);
        if (! $this->monthOrNull($month)) {
            return redirect()->route('payroll.index')->with('error', 'Invalid month format');
        }
        $data = $request->payroll();
        $this->payrollRepository->update($month, $data);

        return redirect()->route('payroll.info', $month)
            ->with('success', 'Payroll for '.$this->monthLabel($month).' updated: '.count($data['employees']).' employee(s).');
    }

    /** Saved payroll of a month with every employee's breakdown. */
    public function info($month)
    {
        $this->authorize('access', Transaction::class);
        $payroll = $this->monthOrNull($month) ? $this->payrollRepository->get($month) : null;
        if (! $payroll) {
            return redirect()->route('payroll.edit', $month)
                ->with('warning', 'This month has no salary breakdown yet. Save it once to record working days, overtime and advances.');
        }

        return view('payrollInfo', [
            'payroll' => $payroll,
            'items' => $this->payrollRepository->items($month),
            'accountName' => $this->accountName($payroll->bank_id),
        ]);
    }

    /** Printed payroll sheet of a month. */
    public function printMonth($month)
    {
        $this->authorize('access', Transaction::class);
        $payroll = $this->monthOrNull($month) ? $this->payrollRepository->get($month) : null;
        if (! $payroll) {
            return redirect()->route('payroll.index')->with('error', 'No payroll breakdown saved for this month.');
        }

        return view('print.payroll', [
            'payroll' => $payroll,
            'items' => $this->payrollRepository->items($month),
            'accountName' => $this->accountName($payroll->bank_id),
        ]);
    }

    /** Printed salary slip of one employee. */
    public function printSlip($payrollItemId)
    {
        $this->authorize('access', Transaction::class);
        $item = $this->payrollRepository->item((int) $payrollItemId);
        if (! $item) {
            return redirect()->route('payroll.index')->with('error', 'Salary record not found.');
        }

        return view('print.payroll-slip', [
            'item' => $item,
            'accountName' => $this->accountName($item->bank_id),
        ]);
    }

    /** Balance of a bank (or cash, id 0), for the "insufficient balance" check before saving. */
    public function getBankBalance($bankId)
    {
        if ((string) $bankId === '0') {
            $balance = $this->transactionRepository->cashBalance();
        } else {
            $bankData = $this->transactionRepository->bankBalance2($bankId);
            $balance = ($bankData->tdebit ?? 0) - ($bankData->tcredit ?? 0);
        }

        return response()->json(['balance' => $balance]);
    }

    private function sheetView(string $month, bool $editing): array
    {
        $sheet = $this->payrollRepository->sheet($month);
        $monthOptions = [];
        for ($i = 24; $i >= -6; $i--) {
            $date = Carbon::now()->addMonths(-$i);
            $monthOptions[$date->format('Y-m')] = $date->format('F Y');
        }

        return [
            'editing' => $editing,
            'selectedMonth' => $month,
            'monthOptions' => $monthOptions,
            'payroll' => $sheet['payroll'],
            'period' => $sheet['period'],
            'rows' => $sheet['rows'],
            'bank' => $this->bankRepository->self(),
        ];
    }

    private function accountName($bankId): string
    {
        if ((int) $bankId === 0) {
            return 'Cash';
        }
        $bank = $this->bankRepository->printDetails($bankId);

        return $bank ? trim(($bank['bank_name'] ? $bank['bank_name'].' - ' : '').$bank['account_title'].' ('.$bank['account'].')') : 'N/A';
    }

    private function monthOrNull($month): ?string
    {
        return is_string($month) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month) ? $month : null;
    }

    private function monthLabel(string $month): string
    {
        return Carbon::createFromFormat('Y-m', $month)->format('F Y');
    }
}
