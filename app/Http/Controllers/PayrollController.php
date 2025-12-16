<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Transaction;
use App\Models\Bank;
use App\Repositories\EmployeeRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\BankRepository;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    protected $employeeRepository;
    protected $transactionRepository;
    protected $bankRepository;

    public function __construct(
        EmployeeRepository $employeeRepository,
        TransactionRepository $transactionRepository,
        BankRepository $bankRepository
    ) {
        $this->employeeRepository = $employeeRepository;
        $this->transactionRepository = $transactionRepository;
        $this->bankRepository = $bankRepository;
    }

    /**
     * Display list of all processed payroll batches (history)
     */
    public function index()
    {
        $this->authorize('access', Transaction::class);

        // Get all salary transactions grouped by month
        $payrollBatches = Transaction::where('transaction_type', 'salary')
            ->where('transaction_to', 'employee')
            ->select(
                DB::raw("DATE_FORMAT(transaction_date, '%Y-%m') as month"),
                DB::raw("DATE(MIN(created_at)) as processing_date"),
                DB::raw("COUNT(DISTINCT payee_id) as employee_count"),
                DB::raw("SUM(COALESCE(credit, debit)) as total_amount"),
                DB::raw("'Completed' as status")
            )
            ->groupBy(DB::raw("DATE_FORMAT(transaction_date, '%Y-%m')"))
            ->orderBy('month', 'desc')
            ->get();

        return view('payroll', [
            'payrollBatches' => $payrollBatches,
        ]);
    }

    /**
     * Show form to create new payroll batch
     */
    public function create(Request $request)
    {
        $this->authorize('create', Transaction::class);

        // Get selected month or default to current month
        $selectedMonth = $request->query('month', Carbon::now()->format('Y-m'));
        $selectedDate = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();

        // Check if payroll for this month already exists
        $existingPayroll = Transaction::where('transaction_type', 'salary')
            ->where('transaction_to', 'employee')
            ->whereYear('transaction_date', $selectedDate->year)
            ->whereMonth('transaction_date', $selectedDate->month)
            ->exists();

        // If payroll already exists, redirect to edit page with warning
        if ($existingPayroll) {
            return redirect()->route('payroll.edit', $selectedMonth)
                ->with('warning', "Payroll for {$selectedDate->format('F Y')} has already been processed. You can edit it below.");
        }

        // Get all salary-based employees (employee_type_id = 39)
        $employees = $this->employeeRepository->salary();

        // Generate month options (24 months back and 6 months forward)
        $monthOptions = [];
        for ($i = 24; $i >= -6; $i--) {
            $date = Carbon::now()->addMonths(-$i);
            $monthOptions[$date->format('Y-m')] = $date->format('F Y');
        }

        $payrollData = [];

        foreach ($employees as $employee) {
            $salary = $employee->salary ?? 0;

            // Calculate opening balance from transactions table
            // Opening Balance = credit - debit from openingBalance transaction
            // (credit means employee owes us, debit means we owe employee)
            $openingBalanceTransaction = Transaction::where('payee_id', $employee->employee_id)
                ->where('transaction_to', 'employee')
                ->where('transaction_type', 'openingBalance')
                ->first();

            $openingBalance = 0;
            if ($openingBalanceTransaction) {
                $openingBalance = ($openingBalanceTransaction->credit ?? 0) - ($openingBalanceTransaction->debit ?? 0);
            }

            // Loans are stored as credit in 'advance' transactions (cash outflow)
            $totalLoansGiven = Transaction::where('payee_id', $employee->employee_id)
                ->where('transaction_to', 'employee')
                ->where('transaction_type', 'advance')
                ->sum('credit');

            // Loan repayments are stored as debit in 'receiveAdvance' transactions (cash inflow, swapped)
            $loanRepaymentsMade = Transaction::where('payee_id', $employee->employee_id)
                ->where('transaction_to', 'employee')
                ->where('transaction_type', 'receiveAdvance')
                ->sum('debit');

            $loansPending = max(0, $openingBalance + $totalLoansGiven - $loanRepaymentsMade);

            $payrollData[] = [
                'employee' => $employee,
                'salary' => $salary,
                'loans_pending' => $loansPending,
                'net_salary' => $salary - $loansPending,
            ];
        }

        $bank = $this->bankRepository->self();

        return view('addPayroll', [
            'payrollData' => $payrollData,
            'selectedMonth' => $selectedMonth,
            'monthOptions' => $monthOptions,
            'bank' => $bank,
        ]);
    }

    /**
     * Store new payroll batch with editable salary amounts
     */
    public function store(Request $request)
    {
        $this->authorize('create', Transaction::class);

        $validated = $request->validate([
            'month' => 'required|date_format:Y-m',
            'bank_id' => 'required',
            'payment_date' => 'required|date',
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,employee_id',
            'salary_amounts' => 'required|array',
            'salary_amounts.*' => 'numeric|min:0',
            'loan_deductions' => 'nullable|array',
            'loan_deductions.*' => 'numeric|min:0',
        ]);

        // Cast bank_id to integer
        $bankId = (int) $validated['bank_id'];

        // Validate bank_id: either 0 (cash) or exists in banks table
        if ($bankId !== 0 && !Bank::where('bank_id', $bankId)->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'bank_id' => ['The selected bank/cash account is invalid.'],
            ]);
        }
        $paymentDate = $validated['payment_date'];
        $month = $validated['month'];
        $monthLabel = Carbon::createFromFormat('Y-m', $month)->format('F Y');

        $employees = Employee::whereIn('employee_id', $validated['employee_ids'])->get();
        $transactionCount = 0;
        $totalAmount = 0;

        foreach ($employees as $employee) {
            $salaryAmount = (float) ($validated['salary_amounts'][$employee->employee_id] ?? 0);
            $loanDeduction = (float) ($validated['loan_deductions'][$employee->employee_id] ?? 0);

            if ($salaryAmount > 0) {
                $transactionData = [
                    'payee_id' => $employee->employee_id,
                    'bank_id' => $bankId,
                    'transaction_to' => 'employee',
                    'transaction_type' => 'salary',
                    'transaction_date' => $paymentDate,
                    'credit' => $salaryAmount,
                    'debit' => 0,
                    'payee_bank_id' => 0,
                    'description' => 'Monthly Salary - ' . $monthLabel,
                ];

                $this->transactionRepository->store($transactionData);
                $transactionCount++;
                $totalAmount += $salaryAmount;
            }

            // Process loan deduction
            if ($loanDeduction > 0) {
                $loanDeductionData = [
                    'payee_id' => $employee->employee_id,
                    'bank_id' => $bankId,
                    'transaction_to' => 'employee',
                    'transaction_type' => 'receiveAdvance',
                    'transaction_date' => $paymentDate,
                    'debit' => $loanDeduction,  // receiveAdvance stores as debit (cash inflow)
                    'credit' => null,
                    'payee_bank_id' => 0,
                    'description' => 'Loan Repayment - ' . $monthLabel,
                ];

                $this->transactionRepository->store($loanDeductionData);
            }
        }

        return redirect()->route('payroll.index')
            ->with('success', "Payroll for {$monthLabel} processed successfully! {$transactionCount} employee(s) paid. Total amount: " . number_format($totalAmount, 2));
    }

    /**
     * Show form to edit existing payroll batch
     */
    public function edit($month)
    {
        $this->authorize('edit', Transaction::class);

        // Parse month format
        try {
            $selectedDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } catch (\Exception $e) {
            return redirect()->route('payroll.index')->with('error', 'Invalid month format');
        }

        // Get all salary transactions for this month
        $existingTransactions = Transaction::where('transaction_type', 'salary')
            ->where('transaction_to', 'employee')
            ->whereYear('transaction_date', $selectedDate->year)
            ->whereMonth('transaction_date', $selectedDate->month)
            ->get()
            ->keyBy('payee_id');

        // Get all loan deduction transactions for this month
        $existingLoanDeductions = Transaction::where('transaction_type', 'receiveAdvance')
            ->where('transaction_to', 'employee')
            ->where('description', 'like', '%Loan Repayment%')
            ->whereYear('transaction_date', $selectedDate->year)
            ->whereMonth('transaction_date', $selectedDate->month)
            ->get()
            ->keyBy('payee_id');

        // Get all salary-based employees
        $employees = $this->employeeRepository->salary();

        // Generate month options (24 months back and 6 months forward)
        $monthOptions = [];
        for ($i = 24; $i >= -6; $i--) {
            $date = Carbon::now()->addMonths(-$i);
            $monthOptions[$date->format('Y-m')] = $date->format('F Y');
        }

        $payrollData = [];

        foreach ($employees as $employee) {
            $salary = $employee->salary ?? 0;

            // Calculate opening balance from transactions table
            // Opening Balance = credit - debit from openingBalance transaction
            // (credit means employee owes us, debit means we owe employee)
            $openingBalanceTransaction = Transaction::where('payee_id', $employee->employee_id)
                ->where('transaction_to', 'employee')
                ->where('transaction_type', 'openingBalance')
                ->first();

            $openingBalance = 0;
            if ($openingBalanceTransaction) {
                $openingBalance = ($openingBalanceTransaction->credit ?? 0) - ($openingBalanceTransaction->debit ?? 0);
            }

            // Loans are stored as credit in 'advance' transactions (cash outflow)
            $totalLoansGiven = Transaction::where('payee_id', $employee->employee_id)
                ->where('transaction_to', 'employee')
                ->where('transaction_type', 'advance')
                ->sum('credit');

            // Loan repayments are stored as debit in 'receiveAdvance' transactions (cash inflow, swapped)
            $loanRepaymentsMade = Transaction::where('payee_id', $employee->employee_id)
                ->where('transaction_to', 'employee')
                ->where('transaction_type', 'receiveAdvance')
                ->sum('debit');

            $loansPending = max(0, $openingBalance + $totalLoansGiven - $loanRepaymentsMade);

            // Get existing salary transaction amount if it exists
            $paidAmount = 0;
            if (isset($existingTransactions[$employee->employee_id])) {
                $paidAmount = $existingTransactions[$employee->employee_id]->credit ?? 0;
            }

            // Get existing loan deduction amount if it exists
            $loanDeductionAmount = 0;
            if (isset($existingLoanDeductions[$employee->employee_id])) {
                $loanDeductionAmount = $existingLoanDeductions[$employee->employee_id]->debit ?? 0;
            }

            $payrollData[] = [
                'employee' => $employee,
                'salary' => $salary,
                'loans_pending' => $loansPending,
                'paid_amount' => $paidAmount,
                'is_paid' => $paidAmount > 0,
                'loan_deduction_amount' => $loanDeductionAmount,
            ];
        }

        $bank = $this->bankRepository->self();

        // Get the bank_id from the first transaction of this month
        $selectedBankId = Transaction::where('transaction_type', 'salary')
            ->where('transaction_to', 'employee')
            ->whereYear('transaction_date', $selectedDate->year)
            ->whereMonth('transaction_date', $selectedDate->month)
            ->value('bank_id') ?? '';

        return view('editPayroll', [
            'payrollData' => $payrollData,
            'selectedMonth' => $month,
            'monthOptions' => $monthOptions,
            'bank' => $bank,
            'selectedBankId' => $selectedBankId,
        ]);
    }

    /**
     * Display payroll batch details (read-only)
     */
    public function info($month)
    {
        $this->authorize('access', Transaction::class);

        // Parse month format
        try {
            $selectedDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } catch (\Exception $e) {
            return redirect()->route('payroll.index')->with('error', 'Invalid month format');
        }

        // Get all salary transactions for this month
        $transactions = Transaction::where('transaction_type', 'salary')
            ->where('transaction_to', 'employee')
            ->whereYear('transaction_date', $selectedDate->year)
            ->whereMonth('transaction_date', $selectedDate->month)
            ->get()
            ->keyBy('payee_id');

        // Get all salary-based employees
        $employees = $this->employeeRepository->salary();

        $payrollData = [];
        $totalAmount = 0;
        $processingDate = null;
        $bankId = null;
        $bankName = 'N/A';

        foreach ($employees as $employee) {
            if (isset($transactions[$employee->employee_id])) {
                $transaction = $transactions[$employee->employee_id];
                $paidAmount = $transaction->credit ?? 0;

                if (!$processingDate) {
                    $processingDate = $transaction->created_at;
                    $bankId = $transaction->bank_id;
                }

                $salary = $employee->salary ?? 0;

                // Check for salary advance in selected month
                $salaryAdvance = Transaction::where('payee_id', $employee->employee_id)
                    ->where('transaction_to', 'employee')
                    ->where('transaction_type', 'salaryAdvance')
                    ->whereYear('transaction_date', $selectedDate->year)
                    ->whereMonth('transaction_date', $selectedDate->month)
                    ->sum('debit');

                // Check for pending loans (all time)
                // Loans are stored as credit in 'advance' transactions (cash outflow)
                $totalLoansGiven = Transaction::where('payee_id', $employee->employee_id)
                    ->where('transaction_to', 'employee')
                    ->where('transaction_type', 'advance')
                    ->sum('credit');

                // Deduct loan repayments already made
                // Loan repayments are stored as debit in 'receiveAdvance' transactions (cash inflow, swapped)
                $loanRepayments = Transaction::where('payee_id', $employee->employee_id)
                    ->where('transaction_to', 'employee')
                    ->where('transaction_type', 'receiveAdvance')
                    ->sum('debit');

                $loansPending = max(0, $totalLoansGiven - $loanRepayments);

                $payrollData[] = [
                    'employee' => $employee,
                    'salary' => $salary,
                    'salary_advance' => $salaryAdvance,
                    'loans_pending' => $loansPending,
                    'net_salary' => $salary - $salaryAdvance - $loansPending,
                    'paid_amount' => $paidAmount,
                ];

                $totalAmount += $paidAmount;
            }
        }

        // Get bank name
        if ($bankId === 0) {
            $bankName = 'Cash';
        } elseif ($bankId) {
            $bank = Bank::find($bankId);
            $bankName = $bank ? $bank->account_title . ' (' . $bank->account . ')' : 'N/A';
        }

        return view('payrollInfo', [
            'payrollData' => $payrollData,
            'selectedMonth' => $month,
            'processingDate' => $processingDate,
            'bankName' => $bankName,
            'totalAmount' => $totalAmount,
        ]);
    }

    /**
     * Update existing payroll batch
     */
    public function update(Request $request, $month)
    {
        $this->authorize('edit', Transaction::class);

        $validated = $request->validate([
            'month' => 'required|date_format:Y-m',
            'bank_id' => 'required',
            'payment_date' => 'required|date',
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,employee_id',
            'salary_amounts' => 'required|array',
            'salary_amounts.*' => 'numeric|min:0',
            'loan_deductions' => 'nullable|array',
            'loan_deductions.*' => 'numeric|min:0',
        ]);

        // Cast bank_id to integer
        $bankId = (int) $validated['bank_id'];

        // Validate bank_id: either 0 (cash) or exists in banks table
        if ($bankId !== 0 && !Bank::where('bank_id', $bankId)->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'bank_id' => ['The selected bank/cash account is invalid.'],
            ]);
        }

        try {
            $selectedDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } catch (\Exception $e) {
            return redirect()->route('payroll.index')->with('error', 'Invalid month format');
        }

        // Delete existing transactions for this month (salary and deductions)
        Transaction::where('transaction_to', 'employee')
            ->whereYear('transaction_date', $selectedDate->year)
            ->whereMonth('transaction_date', $selectedDate->month)
            ->whereIn('transaction_type', ['salary', 'receiveAdvance'])
            ->delete();

        $paymentDate = $validated['payment_date'];
        $monthLabel = Carbon::createFromFormat('Y-m', $validated['month'])->format('F Y');

        $employees = Employee::whereIn('employee_id', $validated['employee_ids'])->get();
        $transactionCount = 0;
        $totalAmount = 0;

        foreach ($employees as $employee) {
            $salaryAmount = (float) ($validated['salary_amounts'][$employee->employee_id] ?? 0);
            $loanDeduction = (float) ($validated['loan_deductions'][$employee->employee_id] ?? 0);

            if ($salaryAmount > 0) {
                $transactionData = [
                    'payee_id' => $employee->employee_id,
                    'bank_id' => $bankId,
                    'transaction_to' => 'employee',
                    'transaction_type' => 'salary',
                    'transaction_date' => $paymentDate,
                    'credit' => $salaryAmount,
                    'debit' => 0,
                    'payee_bank_id' => 0,
                    'description' => 'Monthly Salary - ' . $monthLabel,
                ];

                $this->transactionRepository->store($transactionData);
                $transactionCount++;
                $totalAmount += $salaryAmount;
            }

            // Process loan deduction
            if ($loanDeduction > 0) {
                $loanDeductionData = [
                    'payee_id' => $employee->employee_id,
                    'bank_id' => $bankId,
                    'transaction_to' => 'employee',
                    'transaction_type' => 'receiveAdvance',
                    'transaction_date' => $paymentDate,
                    'debit' => $loanDeduction,  // receiveAdvance stores as debit (cash inflow)
                    'credit' => null,
                    'payee_bank_id' => 0,
                    'description' => 'Loan Repayment - ' . $monthLabel,
                ];

                $this->transactionRepository->store($loanDeductionData);
            }
        }

        return redirect()->route('payroll.index')
            ->with('success', "Payroll for {$monthLabel} updated successfully! {$transactionCount} employee(s) paid. Total amount: " . number_format($totalAmount, 2));
    }

    /**
     * Get bank balance for a specific bank account
     */
    public function getBankBalance($bankId)
    {
        if ($bankId === 0 || $bankId === '0') {
            // Cash account
            $balance = $this->transactionRepository->cashBalance();
        } else {
            // Bank account
            $bankData = $this->transactionRepository->bankBalance2($bankId);
            $balance = ($bankData->tdebit ?? 0) - ($bankData->tcredit ?? 0);
        }

        return response()->json(['balance' => $balance]);
    }
}

