<?php

namespace App\Repositories;

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
        ->leftJoin('stocks as sdate', 'sdate.stock_id', '=', 'stocks.receive_issue_id')
        ->join('employees', 'employees.employee_id', '=', 'stocks.employee_id')
        ->join('heads', 'heads.head_id', '=', 'employees.department_id')
        ->select('stocks.stock_id', 'stocks.stock_no', 'stocks.stock_date', 'stocks.order_id', 'sdate.stock_date as sdate', 'order_no', 'job_no', 'employee_no', 'employees.name','heads.name as hname', 'stocks.description', 'stocks.employee_id')
        ->first();
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
