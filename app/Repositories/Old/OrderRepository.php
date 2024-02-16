<?php

namespace App\Repositories\Operator;

use Carbon\Carbon;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderRepository implements GlobalInterface {
    
    public function all(){
        return Order::join('salesman', 'salesman.salesman_id', '=', 'orders.salesman_id')
            ->join('shops', 'shops.shop_id', '=', 'orders.shop_id')
            ->select('orders.*', 'salesman.name', 'shops.sname')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function fDate($id1, $id2){
        return Order::join('salesman', 'salesman.salesman_id', '=', 'orders.salesman_id')
            ->join('shops', 'shops.shop_id', '=', 'orders.shop_id')
            ->select('orders.*', 'salesman.name', 'shops.sname')
            ->orderBy('created_at', 'desc')
            ->whereBetween('orders.order_date', [$id1, $id2])
            ->get();
    }

    public function profit(){
        //Gross Profit
        return Order::select(
                DB::raw("DATE_FORMAT(order_date, '%M %Y') as month_year"),
                DB::raw('SUM(tprofit) as total_profit')
            )   
            ->groupBy('month_year')
            ->orderBy('order_date', 'desc')
            ->get();
    }
    
    public function grossDate($startDate, $endDate) {
        return Order::select(
                DB::raw("DATE_FORMAT(order_date, '%M %Y') as month_year"),
                DB::raw('SUM(tprofit) as total_profit')
            )
            ->groupBy('month_year')
            ->orderBy('order_date', 'desc')
            ->whereBetween(DB::raw('DATE(order_date)'), [$startDate, $endDate])
            ->get();
    }
    

    public function netProfit(){
        return Order::select(
            DB::raw("DATE_FORMAT(orders.order_date, '%M %Y') as month_year"),
            DB::raw('(SUM(orders.tprofit) - 
                    COALESCE((SELECT SUM(amount) FROM expenses WHERE DATE_FORMAT(expenses.expense_date, "%Y-%m") = DATE_FORMAT(orders.order_date, "%Y-%m")), 0) -
                    COALESCE((SELECT SUM(amount) FROM salary_pay WHERE DATE_FORMAT(salary_pay.date, "%Y-%m") = DATE_FORMAT(orders.order_date, "%Y-%m")), 0) -
                    COALESCE((SELECT SUM(debit) FROM vendor_payments WHERE DATE_FORMAT(vendor_payments.transaction_date, "%Y-%m") = DATE_FORMAT(orders.order_date, "%Y-%m")), 0)
                ) as total_profit')
        )
        ->groupBy('month_year', 'orders.order_date')
        ->orderBy('orders.order_date', 'desc')
        ->get();
    } 
    
    public function netDate($startDate, $endDate) {
        return Order::select(
            DB::raw("DATE_FORMAT(orders.order_date, '%M %Y') as month_year"),
            DB::raw('(SUM(orders.tprofit) - 
                    COALESCE((SELECT SUM(amount) FROM expenses WHERE DATE_FORMAT(expenses.expense_date, "%Y-%m") = DATE_FORMAT(orders.order_date, "%Y-%m")), 0) -
                    COALESCE((SELECT SUM(amount) FROM salary_pay WHERE DATE_FORMAT(salary_pay.date, "%Y-%m") = DATE_FORMAT(orders.order_date, "%Y-%m")), 0) -
                    COALESCE((SELECT SUM(debit) FROM vendor_payments WHERE DATE_FORMAT(vendor_payments.transaction_date, "%Y-%m") = DATE_FORMAT(orders.order_date, "%Y-%m")), 0)
                ) as total_profit')
        )
        ->groupBy('month_year', 'orders.order_date')
        ->orderBy('orders.order_date', 'desc')
        ->whereBetween(DB::raw('DATE(orders.order_date)'), [$startDate, $endDate])
        ->get();
    }
    

    public function get($id){
        return Order::join('salesman', 'salesman.salesman_id', '=', 'orders.salesman_id')
        ->join('shops', 'shops.shop_id', '=', 'orders.shop_id')
        ->join('areas', 'areas.area_id', '=', 'shops.area_id')
        ->join('city', 'city.city_id', '=', 'areas.city_id')
        ->select('orders.*', 'salesman.name', 'salesman.salesman_no', 'salesman.name', 'shops.*','salesman.sname as ssname', 'city.name as cname')
        ->where('orders.order_id', '=', $id)
        ->get();
    }

    public function shop($id){
        return Order::select('shop_id', 'salesman_id')
        ->where('orders.order_id', '=', $id)
        ->get();
    }

    public function count(){
        $currentDate = Carbon::now();
        $currentMonth = $currentDate->month;
        $currentYear = $currentDate->year;
    
        return Order::whereMonth('order_date', $currentMonth)
            ->whereYear('order_date', $currentYear)
            ->count();
    }
    
    public function store(array $data){
        $data['created_by'] = auth()->id();
        $order = Order::create($data);
        $orderId = $order->order_id;
        return $orderId;
    }

    public function update($id, array $data) {
        $update = Order::findOrFail($id);
        $update->update($data);
    }   

    public function delete($id){
        Order::destroy($id);
    }
}
