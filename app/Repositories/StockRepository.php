<?php

namespace App\Repositories;

use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StockRepository implements GlobalInterface
{
    public function all()
    {
        return Stock::all();
    }

    public function issue()
    {
        // All Issuance with Issued Article
        return Stock::where('stocks.stock_type', '2')
            ->leftJoin('orders', 'orders.order_id', '=', 'stocks.order_id')
            ->leftJoin('employees', function ($join) {
                $join->on('employees.employee_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'employee');
            })
            ->leftJoin('vendors', function ($join) {
                $join->on('vendors.vendor_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'vendor');
            })
            ->join('stock_items', 'stock_items.stock_id', '=', 'stocks.stock_id')
            ->join('product_types', 'product_types.product_type_id', 'stock_items.product_type_id')
            ->join('products', 'products.product_id', 'product_types.product_id')
            ->where('stocks.stock_status', '<', '3') // Delivery / Material Issuance Excluded
            ->where('stocks.stock_id', '>', '1') // Default Entries Excluded
            ->leftJoin('heads as shead', 'shead.head_id', '=', 'stocks.issue_for')
            ->select('stocks.*', 'job_no', 'stock_date', 'employees.employee_no', 'vendors.vendor_no', 'vendors.fname', 'employees.name', 'shead.name as sname',
                DB::raw("GROUP_CONCAT(DISTINCT products.article_no SEPARATOR ', ') as articles")
            )
            ->groupBy('stocks.stock_id') // Group by stock_id to aggregate article_numbers
            ->orderBy('stocks.created_at', 'desc')
            ->get();

        // All Issuance
        return Stock::where('stocks.stock_type', '2')
            ->leftJoin('orders', 'orders.order_id', '=', 'stocks.order_id')
        // ->join('employees', 'employees.employee_id', '=', 'stocks.employee_id')
            ->leftJoin('employees', function ($join) {
                $join->on('employees.employee_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'employee');
            })
            ->leftJoin('vendors', function ($join) {
                $join->on('vendors.vendor_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'vendor');
            })
            ->where('stocks.stock_status', '<', '3') // Delivery / Material Issuance Excluded
            ->where('stocks.stock_id', '>', '1') // Default Entries Excluded
            ->leftJoin('heads as shead', 'shead.head_id', '=', 'stocks.issue_for')
            ->select('stocks.*', 'job_no', 'stock_date', 'employees.employee_no', 'vendors.vendor_no', 'vendors.fname', 'employees.name', 'shead.name as sname')
            ->orderBy('stocks.created_at', 'desc')
            ->get();
    }

    public function issueMaterial()
    {
        // All Issuance
        return Stock::where('stocks.stock_type', '2')
            ->join('employees', 'employees.employee_id', '=', 'stocks.employee_id')
            ->join('machines', 'machines.machine_id', 'stocks.machine_id')
            ->join('heads', 'heads.head_id', '=', 'machines.machine_type_id')
            ->where('stocks.stock_status', '=', '4')
            ->select('stocks.*', 'employees.employee_no', 'employees.name', 'heads.name as hname', 'machine_no')
            ->orderBy('stocks.created_at', 'desc', 'machine_no')
            ->get();
    }

    public function receiveIssue()
    {
        // All UnReceived / Partially Received Issuance
        return Stock::where('stocks.stock_type', '2')
            ->leftJoin('orders', 'orders.order_id', '=', 'stocks.order_id')
            ->join('employees', 'employees.employee_id', '=', 'stocks.employee_id')
            ->select('stock_id', 'stock_no', 'job_no', 'employee_no', 'name', 'stock_date', 'stock_status', 'issue_for')
            ->where('stocks.stock_status', '!=', '1')
            ->orderBy('stocks.created_at', 'desc')
            ->get();
    }

    public function receive()
    {
        // All Received Issuance with Articles
        return Stock::where('stocks.stock_type', '1')
            ->leftJoin('orders', 'orders.order_id', '=', 'stocks.order_id')
            ->leftJoin('stocks as issue', 'issue.stock_id', '=', 'stocks.issue_id')
            ->leftJoin('employees', function ($join) {
                $join->on('employees.employee_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'employee');
            })
            ->leftJoin('vendors', function ($join) {
                $join->on('vendors.vendor_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'vendor');
            })
            ->leftJoin('heads as shead', 'shead.head_id', '=', 'issue.issue_for')
            ->join('stock_items', 'stock_items.stock_id', '=', 'stocks.stock_id')
            ->join('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->where('stocks.stock_id', '>', '1') // Default Entries Excluded
            ->whereNotIn('stocks.table_name', ['delivery', 'delivery_returns'])
            ->select('stocks.*', 'job_no', 'employees.employee_no', 'vendors.vendor_no', 'vendors.fname', 'employees.name', 'shead.name as sname',
                DB::raw("GROUP_CONCAT(DISTINCT products.article_no SEPARATOR ', ') as articles")
            )
            ->groupBy('stocks.stock_id') // Group by stock_id to aggregate article numbers
            ->orderBy('stocks.created_at', 'desc')
            ->get();

        // All Received Issuance
        return Stock::where('stocks.stock_type', '1')
            ->leftJoin('orders', 'orders.order_id', '=', 'stocks.order_id')
            ->leftJoin('stocks as issue', 'issue.stock_id', '=', 'stocks.issue_id')
            ->leftJoin('employees', function ($join) {
                $join->on('employees.employee_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'employee');
            })
            ->leftJoin('vendors', function ($join) {
                $join->on('vendors.vendor_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'vendor');
            })
        // ->join('employees', 'employees.employee_id', '=', 'stocks.employee_id')
            ->leftJoin('heads as shead', 'shead.head_id', '=', 'issue.issue_for')
            ->where('stocks.stock_id', '>', '1') // Default Entries Excluded
            ->select('stocks.*', 'job_no', 'employees.employee_no', 'vendors.vendor_no', 'vendors.fname', 'employees.name', 'shead.name as sname')
            ->orderBy('stocks.created_at', 'desc')
            ->get();

        // Without Issue/Received For
        return Stock::where('stocks.stock_type', '1')
            ->leftJoin('orders', 'orders.order_id', '=', 'stocks.order_id')
            ->join('employees', 'employees.employee_id', '=', 'stocks.employee_id')
            ->select('stock_id', 'stock_no', 'job_no', 'employee_no', 'name', 'stock_date')
            ->orderBy('stocks.created_at', 'desc')
            ->get();
    }

    public function wagesAll()
    {
        // Grouped Wages Single record for each person
        $stocks = Stock::where('stock_type', '1') // StockIN
            ->join('stock_items', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->leftJoin('employees', function ($join) {
                $join->on('employees.employee_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'employee');
            })
            ->leftJoin('vendors', function ($join) {
                $join->on('vendors.vendor_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'vendor');
            })
            ->select(
                'stocks.stock_id', 'stocks.table_name', 'stocks.employee_id',
                'employees.employee_no', 'employees.name', 'vendors.vendor_no', 'vendors.fname',
                'stock_items.quantity', 'stock_items.work_wages'
            )
            ->where('work_wages', '!=', '0')
            ->orderBy('stocks.stock_date', 'desc')
            ->get();

        if ($stocks->isEmpty()) {
            return ['employees' => [], 'vendors' => []];
        }

        $groupedWages = [];

        foreach ($stocks as $stock) {
            $groupKey = $stock->table_name.'_'.$stock->employee_id;

            // Initialize the grouped record if not set
            if (! isset($groupedWages[$groupKey])) {
                $groupedWages[$groupKey] = [
                    'stock_id' => $stock->stock_id,
                    'table_name' => $stock->table_name,
                    'employee_id' => $stock->employee_id,
                    'employee_no' => $stock->employee_no ?? null,
                    'name' => $stock->name ?? null,
                    'vendor_no' => $stock->vendor_no ?? null,
                    'fname' => $stock->fname ?? null,
                    'total_wages' => 0,
                    'wagesDebit' => 0,
                    'advanceDebit' => 0,
                    'radvanceCredit' => 0,
                    'obDebit' => 0,
                    'obCredit' => 0,
                ];
            }

            // Calculate total wages for the stock
            $quantity = $stock->quantity;
            $workWages = explode('|', $stock->work_wages);
            $totalWages = 0;
            foreach ($workWages as $wage) {
                $totalWages += (int) $wage * $quantity;
            }

            // Add the total wages to the corresponding grouped record
            $groupedWages[$groupKey]['total_wages'] += $totalWages;
        }

        // Retrieve transactions for each person and add to grouped record
        foreach ($groupedWages as &$group) {
            $transactions = DB::table('transactions')
                ->where('transactions.payee_id', $group['employee_id'])
                ->where('transactions.transaction_to', $group['table_name'])
                ->select(
                    DB::raw('SUM(CASE WHEN transactions.transaction_type = "wages" THEN transactions.debit ELSE 0 END) AS wagesDebit'),
                    DB::raw('SUM(CASE WHEN transactions.transaction_type = "advance" THEN transactions.debit ELSE 0 END) AS advanceDebit'),
                    DB::raw('SUM(CASE WHEN transactions.transaction_type = "receiveAdvance" THEN transactions.credit ELSE 0 END) AS radvanceCredit'),
                    DB::raw('SUM(CASE WHEN transactions.transaction_type = "openingBalance" THEN transactions.debit ELSE 0 END) AS obDebit'),
                    DB::raw('SUM(CASE WHEN transactions.transaction_type = "openingBalance" THEN transactions.credit ELSE 0 END) AS obCredit')
                )
                ->first();

            $group['wagesDebit'] = $transactions->wagesDebit;
            $group['advanceDebit'] = $transactions->advanceDebit;
            $group['radvanceCredit'] = $transactions->radvanceCredit;
            $group['obDebit'] = $transactions->obDebit;
            $group['obCredit'] = $transactions->obCredit;
        }

        // Separate employee and vendor wages
        $employeeWages = array_filter($groupedWages, function ($wage) {
            return $wage['table_name'] === 'employee';
        });

        $vendorWages = array_filter($groupedWages, function ($wage) {
            return $wage['table_name'] === 'vendor';
        });

        // Reindex the arrays to start with 0
        $employeeWages = array_values($employeeWages);
        $vendorWages = array_values($vendorWages);

        return [
            'employees' => $employeeWages,
            'vendors' => $vendorWages,
        ];
    }

    public function wagesNotUsed()
    {
        // For Wages.blade.php page Monthly Wages
        $stocks = Stock::where('stock_type', '1') // StockIN
            ->join('stock_items', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->leftJoin('employees', function ($join) {
                $join->on('employees.employee_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'employee');
            })
            ->leftJoin('vendors', function ($join) {
                $join->on('vendors.vendor_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'vendor');
            })
            ->select('*', 'stocks.employee_id', 'employees.*', 'vendors.*', 'employees.name')
            ->where('work_wages', '!=', '0')
            ->orderBy('stocks.stock_date', 'desc')
            ->get();

        if ($stocks->isEmpty()) {
            return ['employees' => [], 'vendors' => []];
        }

        $employeeWages = [];
        $vendorWages = [];

        foreach ($stocks as $stock) {
            $stockDate = Carbon::parse($stock->stock_date);
            $monthYear = $stockDate->format('F Y');

            $groupKey = $monthYear.'_'.$stock->table_name.'_'.$stock->employee_id;

            // Initialize the employee or vendor record if not set
            if (! isset($employeeWages[$groupKey]) && $stock->table_name == 'employee') {
                $employeeWages[$groupKey] = [
                    'stock_id' => $stock->stock_id,
                    'month_year' => $monthYear,
                    'table_name' => $stock->table_name,
                    'employee_id' => $stock->employee_id,
                    'employee_no' => $stock->employee_no,
                    'name' => $stock->name,
                    'total_wages' => 0,
                    'records' => [],
                ];
            } elseif (! isset($vendorWages[$groupKey]) && $stock->table_name == 'vendor') {
                $vendorWages[$groupKey] = [
                    'stock_id' => $stock->stock_id,
                    'month_year' => $monthYear,
                    'table_name' => $stock->table_name,
                    'employee_id' => $stock->employee_id,
                    'vendor_no' => $stock->vendor_no,
                    'fname' => $stock->fname,
                    'total_wages' => 0,
                    'records' => [],
                ];
            }

            // Calculate total wages for the stock
            $quantity = $stock->quantity;
            $workWages = explode('|', $stock->work_wages);
            $totalWages = 0;
            foreach ($workWages as $wage) {
                $totalWages += (int) $wage * $quantity;
            }

            // Add the total wages to the corresponding employee or vendor
            if ($stock->table_name == 'employee') {
                $employeeWages[$groupKey]['total_wages'] += $totalWages;
                $employeeWages[$groupKey]['records'][] = $stock->toArray();
            } elseif ($stock->table_name == 'vendor') {
                $vendorWages[$groupKey]['total_wages'] += $totalWages;
                $vendorWages[$groupKey]['records'][] = $stock->toArray();
            }
        }

        // Reindex the arrays to start with 0
        $employeeWages = array_values($employeeWages);
        $vendorWages = array_values($vendorWages);

        return [
            'employees' => $employeeWages,
            'vendors' => $vendorWages,
        ];
    }

    public function wagesInfo($id)
    {
        // For Wagesinfo.blade.php page
        $stockDate = new \DateTime($id['stock_date']);
        $stocks = Stock::where('stocks.employee_id', $id['employee_id'])
            ->join('stock_items', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->join('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->join('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
            ->join('heads as uhead', 'uhead.head_id', '=', 'products.unit_id')
            ->join('heads as sthead', 'sthead.head_id', '=', 'stock_items.stage_id')
            ->where('work_wages', '!=', '0')
            ->where('stocks.table_name', $id['table_name'])
            ->whereYear('stocks.stock_date', '=', $stockDate->format('Y'))
            ->whereMonth('stocks.stock_date', '=', $stockDate->format('m'))
            ->select('*', 'shead.name as sname', 'sthead.name as stage', 'uhead.name as uname')
            ->get();
        foreach ($stocks as $stock) {
            // Calculate total wages for the stock
            $quantity = $stock->quantity;
            $workWages = explode('|', $stock->work_wages);
            $totalWages = 0;
            foreach ($workWages as $wage) {
                $totalWages += (int) $wage * $quantity;
            }

            // Add total_wages attribute to stock
            $stock->total_wages = $totalWages;
        }

        return $stocks;
    }

    public function wagesInfoFilter($id, $dfrom, $dto)
    {
        // For Wagesinfo.blade.php page
        $stockDate = new \DateTime($id['stock_date']);
        $stocks = Stock::where('stocks.employee_id', $id['employee_id'])
            ->join('stock_items', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->join('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->join('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
            ->join('heads as uhead', 'uhead.head_id', '=', 'products.unit_id')
            ->join('heads as sthead', 'sthead.head_id', '=', 'stock_items.stage_id')
            ->where('work_wages', '!=', '0')
            ->where('stocks.table_name', $id['table_name'])
            ->whereBetween('stocks.stock_date', [$dfrom, $dto])
            ->select('*', 'shead.name as sname', 'sthead.name as stage', 'uhead.name as uname')
            ->get();

        foreach ($stocks as $stock) {
            // Calculate total wages for the stock
            $quantity = $stock->quantity;
            $workWages = explode('|', $stock->work_wages);
            $totalWages = 0;
            foreach ($workWages as $wage) {
                $totalWages += (int) $wage * $quantity;
            }

            // Add total_wages attribute to stock
            $stock->total_wages = $totalWages;
        }

        return $stocks;
    }

    public function get($id)
    {
        return Stock::where('stocks.stock_id', $id)
            ->leftJoin('orders', 'orders.order_id', '=', 'stocks.order_id')
            ->leftJoin('stocks as rstock', 'rstock.issue_id', '=', 'stocks.stock_id')
            ->leftJoin('stocks as sdate', 'sdate.stock_id', '=', 'stocks.issue_id')
            ->leftJoin('employees', function ($join) {
                $join->on('employees.employee_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'employee');
            })
            ->leftJoin('vendors', function ($join) {
                $join->on('vendors.vendor_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'vendor');
            })
            ->leftJoin('machines', 'machines.machine_id', '=', 'stocks.machine_id')
            ->leftJoin('heads', 'heads.head_id', '=', 'employees.department_id')
            ->leftJoin('heads as shead', 'shead.head_id', '=', 'stocks.issue_for')
            ->leftJoin('heads as mhead', 'mhead.head_id', '=', 'machines.machine_type_id')
            ->select(
                'stocks.*', 'sdate.stock_date as sdate', 'order_no', 'job_no',
                'employees.employee_no', 'employees.phone1 as employee_phone', 'employees.address as employee_address',
                'vendors.vendor_no', 'vendors.fname', 'vendors.phone1 as vendor_phone', 'vendors.address as vendor_address',
                'employees.name', 'heads.name as hname', 'shead.name as sname', 'machines.*', 'mhead.name as mname', 'stocks.employee_id',
                DB::raw('CASE WHEN rstock.stock_id IS NOT NULL THEN 1 ELSE 0 END AS has_received'))
            ->first();
    }

    public function refNo()
    {
        // For Issuance
        $yearMonth = Carbon::now()->format('ym');
        $count = Stock::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->where('stock_type', '2')->count();
        $fourDigitNumber = str_pad($count + 1, 4, '0', STR_PAD_LEFT);

        return 'I'.$yearMonth.$fourDigitNumber;
    }

    public function refNo2($id)
    {
        // For Receive Issuance
        $yearMonth = Carbon::now()->format('ym');
        $count = Stock::where('issue_id', $id)->count();

        return 'R'.$count + 1;
    }

    /**
     * Generate PTC Number in format: YYMMNNN (e.g., 2512001)
     * Stores only the numeric portion - display adds 'PTC-' prefix
     */
    public function ptcRefNo()
    {
        $yearMonth = Carbon::now()->format('ym');
        $count = Stock::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->where('is_ptc_master', 1)
            ->count();
        $threeDigitNumber = str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        return $yearMonth . $threeDigitNumber;
    }

    /**
     * Generate Issuance Number relative to PTC
     * Format: {ptcNo}-{seq} (e.g., 2609004-001, 2609004-002)
     * stocks.stock_no is unique table-wide, so the per-PTC sequence is prefixed
     * with the (unique) PTC number. Display strips the prefix and adds 'I'.
     */
    public function issueRefNo($ptcId)
    {
        $ptcNo = Stock::where('stock_id', $ptcId)->value('stock_no');

        // Count existing issuances for this PTC (excluding the initial master issuance)
        $seq = Stock::where('ptc_id', $ptcId)
            ->where('stock_type', 2)
            ->count() + 1;

        do {
            $issueNo = $ptcNo . '-' . str_pad($seq++, 3, '0', STR_PAD_LEFT);
        } while (Stock::where('stock_no', $issueNo)->exists());

        return $issueNo;
    }

    /**
     * Generate Receiving Number relative to Issuance
     * Format: R{seq}-I{ptcNo} for the PTC master issuance (e.g., R1-I2609004)
     *         {ptcNo}-R{seq}-I{issuanceSeq} for other issuances (e.g., 2609004-R1-I002)
     * Display strips the '{ptcNo}-' prefix.
     */
    public function receiveRefNo($issueId)
    {
        $issuance = Stock::find($issueId);

        if (! $issuance || $issuance->is_ptc_master || ! $issuance->ptc_id) {
            $prefix = '';
            $issuanceNo = $issuance ? $issuance->stock_no : $issueId;
        } else {
            $ptcNo = Stock::where('stock_id', $issuance->ptc_id)->value('stock_no');
            $prefix = $ptcNo . '-';
            $issuanceNo = Stock::ptcDisplayNo($issuance->stock_no, $ptcNo);
        }

        // Count existing receivings for this issuance
        $seq = Stock::where('issue_id', $issueId)
            ->where('stock_type', 1)
            ->count() + 1;

        do {
            $receiveNo = $prefix . 'R' . $seq++ . '-I' . $issuanceNo;
        } while (Stock::where('stock_no', $receiveNo)->exists());

        return $receiveNo;
    }

    /**
     * Store a PTC issuance with a generated issuance number.
     * The PTC master row is locked so concurrent requests for the same PTC
     * cannot generate the same number.
     */
    public function storePtcIssuance(array $data)
    {
        return DB::transaction(function () use ($data) {
            Stock::where('stock_id', $data['ptc_id'])->lockForUpdate()->first();
            $data['stock_no'] = $this->issueRefNo($data['ptc_id']);

            return $this->store($data);
        });
    }

    /**
     * Store a PTC receiving with a generated receiving number (see storePtcIssuance).
     */
    public function storePtcReceiving(array $data)
    {
        return DB::transaction(function () use ($data) {
            Stock::where('stock_id', $data['ptc_id'])->lockForUpdate()->first();
            $data['stock_no'] = $this->receiveRefNo($data['issue_id']);

            return $this->store($data);
        });
    }

    /**
     * Get Issuance sequence number from PTC for a specific issuance
     * Used for display purposes to show R1-I001 format
     */
    public function getIssuanceSeqNo($ptcId, $issuanceId)
    {
        // Get all issuances for this PTC ordered by creation date
        $issuances = Stock::where(function($query) use ($ptcId) {
                $query->where('ptc_id', $ptcId)
                      ->orWhere('stock_id', $ptcId);
            })
            ->where('stock_type', 2)
            ->orderBy('created_at')
            ->pluck('stock_id')
            ->toArray();

        $position = array_search($issuanceId, $issuances);
        return $position !== false ? str_pad($position + 1, 3, '0', STR_PAD_LEFT) : '001';
    }

    public function receivingIssue($id, $empId)
    {
        // Add / Edit Receiving Issuance
        return Stock::where('order_id', $id)->where('employee_id', $empId)
            ->join('stock_items', 'stock_items.stock_id', '=', 'stocks.stock_id')
            ->join('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->join('heads', 'heads.head_id', '=', 'product_types.size_id')
            ->groupBy('stock_items.product_type_id')
            ->get();
    }

    public function store(array $data)
    {
        $data['created_by'] = auth()->id();
        $store = Stock::create($data);

        return $store->stock_id;
    }

    public function update($id, array $data)
    {
        $update = Stock::findOrFail($id);
        $update->update($data);

        return $update->stock_id;
    }

    public function updateStatus($id, array $data)
    {
        // Might Not be used
        $update = Stock::findOrFail($id);
        $update->update($data);

        return $update->stock_id;
    }

    public function delete($id)
    {
    }
}
