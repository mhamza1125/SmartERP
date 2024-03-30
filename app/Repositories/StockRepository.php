<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Models\Stock;

class StockRepository implements GlobalInterface {
    
    public function all(){
        return Stock::all();
    }

    public function issue(){
        return Stock::where('stocks.stock_type', '2')
        ->leftJoin('orders', 'orders.order_id', '=', 'stocks.order_id')
        ->join('employees', 'employees.employee_id', '=', 'stocks.employee_id')
        ->select('stock_id', 'stock_no', 'job_no', 'employee_no', 'name', 'stock_date')
        ->orderBy('stocks.created_at', 'desc')
        ->get();
    }

    public function receive(){
        return Stock::where('stocks.stock_type', '1')
        ->leftJoin('orders', 'orders.order_id', '=', 'stocks.order_id')
        ->join('employees', 'employees.employee_id', '=', 'stocks.employee_id')
        ->select('stock_id', 'stock_no', 'job_no', 'employee_no', 'name', 'stock_date')
        ->orderBy('stocks.created_at', 'desc')
        ->get();
    }

    public function get($id){
        return Stock::where('stocks.stock_id', $id)
            ->leftJoin('orders', 'orders.order_id', '=', 'stocks.order_id')
            ->leftJoin('stocks as sdate', 'sdate.stock_id', '=', 'stocks.issue_id')
            ->leftJoin('employees', function($join) {
                $join->on('employees.employee_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'employee');
            })
            ->leftJoin('vendors', function($join) {
                $join->on('vendors.vendor_id', '=', 'stocks.employee_id')
                    ->where('stocks.table_name', 'vendor');
            })
            ->leftJoin('heads', 'heads.head_id', '=', 'employees.department_id')
            ->select(
                'stocks.*', 'sdate.stock_date as sdate', 'order_no', 'job_no', 'employees.employee_no', 'vendors.vendor_no', 'vendors.fname', 'employees.name', 'heads.name as hname'
            )
            ->first();

        return Stock::where('stocks.stock_id', $id)
        ->leftJoin('orders', 'orders.order_id', '=', 'stocks.order_id')
        ->leftJoin('stocks as sdate', 'sdate.stock_id', '=', 'stocks.issue_id')
        ->join('employees', 'employees.employee_id', '=', 'stocks.employee_id')
        ->join('heads', 'heads.head_id', '=', 'employees.department_id')
        ->select('stocks.stock_id', 'stocks.stock_no', 'stocks.stock_date', 'stocks.order_id', 'sdate.stock_date as sdate', 'order_no', 'job_no', 'employee_no', 'employees.name','heads.name as hname', 'stocks.description', 'stocks.employee_id', 'stocks.issue_id')
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
