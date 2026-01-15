<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Repositories\HeadRepository;
use App\Repositories\ImageRepository;
use App\Http\Requests\EmployeeRequest;
use App\Repositories\SalaryRepository;
use App\Repositories\EmployeeRepository;
use App\Repositories\TransactionRepository;

class EmployeeController extends Controller
{
    protected $headRepository;

    protected $salaryRepository;

    protected $imageRepository;

    protected $employeeRepository;

    protected $transactionRepository;

    public function __construct(
        HeadRepository $headRepository,
        SalaryRepository $salaryRepository,
        ImageRepository $imageRepository,
        EmployeeRepository $employeeRepository,
        TransactionRepository $transactionRepository,
    ) {
        $this->middleware(['auth', 'all']);
        $this->headRepository = $headRepository;
        $this->salaryRepository = $salaryRepository;
        $this->imageRepository = $imageRepository;
        $this->employeeRepository = $employeeRepository;
        $this->transactionRepository = $transactionRepository;
        // $this->authorizeResource(Employee::class, 'employee');
    }

    public function index()
    {
        $this->authorize('access', Employee::class);
        $employee = $this->employeeRepository->all();

        return view('employee', [
            'employee' => $employee,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Employee::class);
        $department = $this->headRepository->get('3');
        $employeeType = $this->headRepository->get('9');
        $city = $this->headRepository->get('8');
        $count = $this->employeeRepository->refNo();

        return view('addEmployee', [
            'department' => $department,
            'employeeType' => $employeeType,
            'city' => $city,
            'count' => $count,
        ]);
    }

    public function store(EmployeeRequest $request)
    {
        $validatedData = $request->validated();

        // Process JSON array fields - filter out empty entries
        if ($request->has('children_details')) {
            $children = array_filter($request->input('children_details', []), function ($child) {
                return !empty($child['name']);
            });
            $validatedData['children_details'] = !empty($children) ? array_values($children) : null;
        }

        if ($request->has('education')) {
            $education = array_filter($request->input('education', []), function ($edu) {
                return !empty($edu['institution_name']);
            });
            $validatedData['education'] = !empty($education) ? array_values($education) : null;
        }

        if ($request->has('employment_history')) {
            $history = array_filter($request->input('employment_history', []), function ($emp) {
                return !empty($emp['company_name']);
            });
            $validatedData['employment_history'] = !empty($history) ? array_values($history) : null;
        }

        $getId = $this->employeeRepository->store($validatedData);
        $salary = ['employee_id' => $getId, 'amount' => $request->input('salary')];
        $this->salaryRepository->store($salary);
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $this->storeImage($file, 'employee', 'employees', $getId);
            }
        }
        if ($request->input('balance_type') == 'debit') {
            $debit = $request->input('credit');
            $request->merge(['debit' => $debit, 'credit' => null]);
        }
        $transaction = [
            'transaction_to' => 'employee',
            'transaction_type' => 'openingBalance',
            'bank_id' => '0',
            'payee_id' => $getId,
            'debit' => $request->input('debit') ?? null,
            'credit' => $request->input('credit') ?? null,
            'transaction_date' => date('Y-m-d'),
            'payee_bank_id' => '0',
        ];
        $this->transactionRepository->store($transaction);

        return redirect()->route('employee.show', $getId)->with('success', 'Record Inserted Successfully');
    }

    public function show($id)
    {
        $this->authorize('show', Employee::class);
        $employee = $this->employeeRepository->get($id);
        $image = $this->imageRepository->image('employees', $id);

        return view('employeeInfo', [
            'employee' => $employee,
            'image' => $image,
        ]);
    }

    /**
     * Print employee information
     */
    public function printEmployee($id)
    {
        $this->authorize('show', Employee::class);
        $employee = $this->employeeRepository->get($id);
        $image = $this->imageRepository->image('employees', $id);

        return view('print.employee', [
            'employee' => $employee,
            'image' => $image,
        ]);
    }

    /**
     * Print employee ledger
     */
    public function printEmployeeLedger(Request $request, $id)
    {
        $this->authorize('show', Employee::class);
        $this->authorize('show', Transaction::class);
        $employee = $this->employeeRepository->get($id);
        $dfrom = $request->input('dfrom');
        $dto = $request->input('dto');
        $oBalance = 0; // Opening Balance
        $cBalance = 0; // Closing Balance

        // Get transaction data
        if (! empty($dfrom) && ! empty($dto)) {
            $all = $this->transactionRepository->eDetailFilter($id, $dfrom, $dto);
            $detail = $all['transactions'];
            $oBalance = $all['opening_balance'];
            $cBalance = $all['closing_balance'];
        } else {
            $detail = $this->transactionRepository->eDetail($id);
        }

        // Get wages data for this employee
        $wages = $this->getEmployeeWages($id, $dfrom, $dto);

        // Merge wages with transactions and sort by date
        $detail = $this->mergeWagesWithTransactions($detail, $wages);

        // For employee ledger (liability account):
        // DB debit (displayed as Credit) = work done, increases liability = ADD to balance
        // DB credit (displayed as Debit) = payments made, decreases liability = SUBTRACT from balance
        // Include ALL transaction types - no filtering
        $totalDebit = $detail->sum('debit') ?? 0;
        $totalCredit = $detail->sum('credit') ?? 0;
        $balance = $oBalance + $totalDebit - $totalCredit;

        return view('print.employee-ledger', [
            'employee' => $employee,
            'detail' => $detail,
            'balance' => $balance,
            'oBalance' => $oBalance,
            'cBalance' => $cBalance,
            'dfrom' => $dfrom,
            'dto' => $dto,
        ]);
    }

    public function detail(Request $request, $id)
    {
        $this->authorize('show', Employee::class);
        $this->authorize('show', Transaction::class);
        $employee = $this->employeeRepository->get($id);
        $dfrom = $request->input('dfrom');
        $dto = $request->input('dto');
        $oBalance = 0; // Opening Balance
        $cBalance = 0; // Closing Balance

        // Get transaction data
        if (! empty($dfrom) && ! empty($dto)) {
            $all = $this->transactionRepository->eDetailFilter($id, $dfrom, $dto);
            $detail = $all['transactions'];
            $oBalance = $all['opening_balance'];
            $cBalance = $all['closing_balance'];
        } else {
            $detail = $this->transactionRepository->eDetail($id);
        }

        // Get wages data for this employee
        $wages = $this->getEmployeeWages($id, $dfrom, $dto);

        // Merge wages with transactions and sort by date
        $detail = $this->mergeWagesWithTransactions($detail, $wages);

        // For employee ledger (liability account):
        // DB debit (displayed as Credit) = work done, increases liability = ADD to balance
        // DB credit (displayed as Debit) = payments made, decreases liability = SUBTRACT from balance
        // Include ALL transaction types - no filtering
        $totalDebit = $detail->where('transaction_type', '!=', 'salary')->sum('debit');
        $totalCredit = $detail->where('transaction_type', '!=', 'salary')->sum('credit');
        $balance = $oBalance + $totalDebit - $totalCredit;

        return view('employeeDetail', [
            'employee' => $employee,
            'detail' => $detail,
            'balance' => $balance,
            'oBalance' => $oBalance,
            'cBalance' => $cBalance,
            'dfrom' => $dfrom,
            'dto' => $dto,
        ]);
    }

    /**
     * Get wages data for an employee
     */
    private function getEmployeeWages($employeeId, $dfrom = null, $dto = null)
    {
        $query = \DB::table('stocks')
            ->join('stock_items', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->join('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->join('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
            ->join('heads as sthead', 'sthead.head_id', '=', 'stock_items.stage_id')
            ->where('stocks.employee_id', $employeeId)
            ->where('stocks.table_name', 'employee')
            ->where('stocks.stock_type', '1') // StockIN
            ->where('stock_items.work_wages', '!=', '0')
            ->select(
                'stocks.stock_id',
                'stocks.stock_no',
                'stocks.stock_date as transaction_date',
                'stocks.created_at',
                'stock_items.quantity',
                'stock_items.work_wages',
                'products.article_no',
                'shead.name as size_name',
                'sthead.name as stage_name'
            );

        if (!empty($dfrom) && !empty($dto)) {
            $query->whereBetween('stocks.stock_date', [$dfrom, $dto]);
        }

        $wagesData = $query->orderBy('stocks.stock_date')->get();

        // Group wages by stock_no (receive/issuance number) and calculate totals
        $groupedWages = $wagesData->groupBy('stock_no')->map(function ($wageGroup, $stockNo) {
            $totalWages = 0;
            $wageDetails = [];
            $firstWage = $wageGroup->first();

            foreach ($wageGroup as $wage) {
                $workWages = explode('|', $wage->work_wages);
                $itemWages = 0;
                foreach ($workWages as $wageAmount) {
                    $itemWages += (int) $wageAmount * $wage->quantity;
                }
                $totalWages += $itemWages;

                // Store individual wage details for modal display
                $wageDetails[] = [
                    'article_no' => $wage->article_no,
                    'size_name' => $wage->size_name,
                    'stage_name' => $wage->stage_name,
                    'quantity' => $wage->quantity,
                    'wages' => $itemWages,
                ];
            }

            return (object) [
                'transaction_id' => null,
                'transaction_type' => 'wages',
                'transaction_date' => $firstWage->transaction_date,
                'created_at' => $firstWage->created_at,
                'timestamp' => $firstWage->created_at,
                'debit' => $totalWages, // Total wages for this receive/issuance
                'credit' => null,
                'description' => "Wages for {$stockNo} (" . count($wageGroup) . " items)",
                'payee_id' => null,
                'bank_id' => null,
                'order_id' => null,
                // Additional fields for enhanced display
                'stock_id' => $firstWage->stock_id,
                'stock_no' => $stockNo,
                'wage_details' => $wageDetails, // For modal popup
                'item_count' => count($wageGroup),
            ];
        });

        return $groupedWages->values(); // Reset array keys
    }

    /**
     * Merge wages data with transaction data and sort chronologically
     */
    private function mergeWagesWithTransactions($transactions, $wages)
    {
        // Convert transactions to collection if it isn't already
        if (!$transactions instanceof \Illuminate\Support\Collection) {
            $transactions = collect($transactions);
        }

        // Merge and sort by timestamp/created_at
        $merged = $transactions->concat($wages)->sortBy(function ($item) {
            return $item->timestamp ?? $item->created_at;
        });

        return $merged;
    }

    public function edit(Employee $id)
    {
        $this->authorize('edit', Employee::class);
        $department = $this->headRepository->get('3');
        $employeeType = $this->headRepository->get('9');
        $city = $this->headRepository->get('8');

        // Fetch opening balance transaction
        $openingBalance = $this->transactionRepository->getOB($id->employee_id, 'employee');

        return view('editEmployee', [
            'employee' => $id,
            'department' => $department,
            'employeeType' => $employeeType,
            'city' => $city,
            'openingBalance' => $openingBalance,
        ]);
    }

    public function update(Request $request, $id)
    {
        $updateData = $request->input();

        // Process JSON array fields - filter out empty entries
        if ($request->has('children_details')) {
            $children = array_filter($request->input('children_details', []), function ($child) {
                return !empty($child['name']);
            });
            $updateData['children_details'] = !empty($children) ? array_values($children) : null;
        }

        if ($request->has('education')) {
            $education = array_filter($request->input('education', []), function ($edu) {
                return !empty($edu['institution_name']);
            });
            $updateData['education'] = !empty($education) ? array_values($education) : null;
        }

        if ($request->has('employment_history')) {
            $history = array_filter($request->input('employment_history', []), function ($emp) {
                return !empty($emp['company_name']);
            });
            $updateData['employment_history'] = !empty($history) ? array_values($history) : null;
        }

        $getId = $this->employeeRepository->update($id, $updateData);
        $salary = ['employee_id' => $id, 'amount' => $request->input('salary')];
        $this->salaryRepository->update($id, $salary);
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $this->storeImage($file, 'employee', 'employees', $getId);
            }
        }
        if ($request->input('balance_type') == 'debit') {
            $debit = $request->input('credit');
            $request->merge(['debit' => $debit, 'credit' => null]);
        }
        $transaction = [
            'transaction_to' => 'employee',
            'transaction_type' => 'openingBalance',
            'bank_id' => '0',
            'payee_id' => $getId,
            'debit' => $request->input('debit') ?? null,
            'credit' => $request->input('credit') ?? null,
            'transaction_date' => date('Y-m-d'),
            'payee_bank_id' => '0',
        ];
        $this->transactionRepository->updateOB($getId, 'employee', $transaction);

        return redirect()->route('employee.show', $id)->with('success', 'Record Updated Successfully');
    }

    public function destroy(Employee $employee)
    {
        $this->authorize('delete', Employee::class);
    }
}
