<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;

class StockRepository implements GlobalInterface {
    
    public function all(){
        return Stock::all();
    }

    public function issue(){
        // All Issuance
        return Stock::where('stocks.stock_type', '2')
        ->leftJoin('orders', 'orders.order_id', '=', 'stocks.order_id')
        // ->join('employees', 'employees.employee_id', '=', 'stocks.employee_id')
        ->leftJoin('employees', function($join) {
            $join->on('employees.employee_id', '=', 'stocks.employee_id')
                ->where('stocks.table_name', 'employee');
        })
        ->leftJoin('vendors', function($join) {
            $join->on('vendors.vendor_id', '=', 'stocks.employee_id')
                ->where('stocks.table_name', 'vendor');
        })
        ->where('stocks.stock_status', '<', '3') // Delivery / Material Issuance Excluded
        ->leftJoin('heads as shead', 'shead.head_id', '=', 'stocks.issue_for')
        ->select('stocks.*', 'job_no', 'stock_date', 'employees.employee_no', 'vendors.vendor_no', 'vendors.fname', 'employees.name', 'shead.name as sname')
        ->orderBy('stocks.created_at', 'desc')
        ->get();
    }

    public function issueMaterial(){
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

    public function receiveIssue(){
        // All UnReceived / Partially Received Issuance
        return Stock::where('stocks.stock_type', '2')
        ->leftJoin('orders', 'orders.order_id', '=', 'stocks.order_id')
        ->join('employees', 'employees.employee_id', '=', 'stocks.employee_id')
        ->select('stock_id', 'stock_no', 'job_no', 'employee_no', 'name', 'stock_date', 'stock_status', 'issue_for')
        ->where('stocks.stock_status', '!=', '1')
        ->orderBy('stocks.created_at', 'desc')
        ->get();
    }

    public function receive(){
        // All Received Issuance
        return Stock::where('stocks.stock_type', '1')
        ->leftJoin('orders', 'orders.order_id', '=', 'stocks.order_id')
        ->leftJoin('stocks as issue', 'issue.stock_id', '=', 'stocks.issue_id')
        ->leftJoin('employees', function($join) {
            $join->on('employees.employee_id', '=', 'stocks.employee_id')
                ->where('stocks.table_name', 'employee');
        })
        ->leftJoin('vendors', function($join) {
            $join->on('vendors.vendor_id', '=', 'stocks.employee_id')
                ->where('stocks.table_name', 'vendor');
        })
        // ->join('employees', 'employees.employee_id', '=', 'stocks.employee_id')
        ->leftJoin('heads as shead', 'shead.head_id', '=', 'issue.issue_for')
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

    public function wagesAll(){
        // Grouped Wages Single record for each person
        $stocks = Stock::where('stock_type', '1') // StockIN
            ->join('stock_items', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->leftJoin('employees', function($join) {
                $join->on('employees.employee_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'employee');
            })
            ->leftJoin('vendors', function($join) {
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
    
        if($stocks->isEmpty()) {
            return ['employees' => [], 'vendors' => []];
        }
    
        $groupedWages = [];
    
        foreach ($stocks as $stock) {
            $groupKey = $stock->table_name . '_' . $stock->employee_id;
    
            // Initialize the grouped record if not set
            if (!isset($groupedWages[$groupKey])) {
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
                $totalWages += (int)$wage * $quantity;
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
        $employeeWages = array_filter($groupedWages, function($wage) {
            return $wage['table_name'] === 'employee';
        });
    
        $vendorWages = array_filter($groupedWages, function($wage) {
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
    
      

    public function wagesNotUsed(){
        // For Wages.blade.php page Monthly Wages
        $stocks = Stock::where('stock_type', '1') // StockIN
            ->join('stock_items', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->leftJoin('employees', function($join) {
                $join->on('employees.employee_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'employee');
            })
            ->leftJoin('vendors', function($join) {
                $join->on('vendors.vendor_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'vendor');
            })
            ->select('*', 'stocks.employee_id', 'employees.*', 'vendors.*', 'employees.name')
            ->where('work_wages', '!=', '0')
            ->orderBy('stocks.stock_date', 'desc')
            ->get();
    
        if($stocks->isEmpty()) {
            return ['employees' => [], 'vendors' => []];
        }
    
        $employeeWages = [];
        $vendorWages = [];
    
        foreach ($stocks as $stock) {
            $stockDate = Carbon::parse($stock->stock_date);
            $monthYear = $stockDate->format('F Y');
            
            $groupKey = $monthYear . '_' . $stock->table_name . '_' . $stock->employee_id;
            
            // Initialize the employee or vendor record if not set
            if (!isset($employeeWages[$groupKey]) && $stock->table_name == 'employee') {
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
            } elseif (!isset($vendorWages[$groupKey]) && $stock->table_name == 'vendor') {
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
                $totalWages += (int)$wage * $quantity;
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

    public function wagesInfo($id){
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
                $totalWages += (int)$wage * $quantity;
            }
        
            // Add total_wages attribute to stock
            $stock->total_wages = $totalWages;
        }
        return $stocks;
    }

    public function wagesInfoFilter($id, $dfrom, $dto){
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
                $totalWages += (int)$wage * $quantity;
            }
        
            // Add total_wages attribute to stock
            $stock->total_wages = $totalWages;
        }
        return $stocks;
    }

    public function get($id){
        return Stock::where('stocks.stock_id', $id)
            ->leftJoin('orders', 'orders.order_id', '=', 'stocks.order_id')
            ->leftJoin('stocks as rstock', 'rstock.issue_id', '=', 'stocks.stock_id')
            ->leftJoin('stocks as sdate', 'sdate.stock_id', '=', 'stocks.issue_id')
            ->leftJoin('employees', function($join) {
                $join->on('employees.employee_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'employee');
            })
            ->leftJoin('vendors', function($join) {
                $join->on('vendors.vendor_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'vendor');
            })
            ->leftJoin('machines', 'machines.machine_id', '=', 'stocks.machine_id')
            ->leftJoin('heads', 'heads.head_id', '=', 'employees.department_id')
            ->leftJoin('heads as shead', 'shead.head_id', '=', 'stocks.issue_for')
            ->leftJoin('heads as mhead', 'mhead.head_id', '=', 'machines.machine_type_id')
            ->select(
                'stocks.*', 'sdate.stock_date as sdate', 'order_no', 'job_no', 'employees.employee_no', 'vendors.vendor_no', 'vendors.fname', 'employees.name', 'heads.name as hname', 'shead.name as sname', 'machines.*', 'mhead.name as mname', 'stocks.employee_id', 
                DB::raw('CASE WHEN rstock.stock_id IS NOT NULL THEN 1 ELSE 0 END AS has_received'))
            ->first();
    }

    public function refNo() {
        // For Issuance
        $yearMonth = Carbon::now()->format('ym');
        $count = Stock::whereMonth('stock_date', Carbon::now()->month)
            ->whereYear('stock_date', Carbon::now()->year)
            ->where('stock_type', '2')->count();
        $fourDigitNumber = str_pad($count+1, 4, '0', STR_PAD_LEFT);
        return 'I' . $yearMonth . $fourDigitNumber;
    }
    
    public function refNo2($id) {
        // For Receive Issuance
        $yearMonth = Carbon::now()->format('ym');
        $count = Stock::where('issue_id', $id)->count();
        return 'R' . $count+1;
    }

    public function receivingIssue($id, $empId){
        // Add / Edit Receiving Issuance
        return Stock::where('order_id', $id)->where('employee_id', $empId)
        ->join('stock_items', 'stock_items.stock_id', '=', 'stocks.stock_id')
        ->join('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
        ->join('products', 'products.product_id', '=', 'product_types.product_id')
        ->join('heads', 'heads.head_id', '=', 'product_types.size_id')
        ->groupBy('stock_items.product_type_id')
        ->get();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $store = Stock::create($data);
        return $store->stock_id;
    }

    public function update($id, array $data) {
        $update = Stock::findOrFail($id);
        $update->update($data);
        return $update->stock_id;
    }

    public function delete($id){}
}
