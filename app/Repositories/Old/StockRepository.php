<?php

namespace App\Repositories\Operator;

use App\Models\Stock;
use App\Models\Cost;
use Illuminate\Support\Facades\DB;

class StockRepository implements GlobalInterface {
    
    public function all(){
        return Stock::join('products', 'products.product_id', '=', 'stocks.product_id')
            ->select('products.product_id', 'products.name', 'products.quantity', DB::raw('SUM(stocks.quantity) as total_quantity'))
            ->groupBy('products.product_id', 'products.name', 'products.quantity')
            ->get();
    }
    
    public function all2(){
        return Stock::leftJoin('order_items', function ($join) {
            $join->on('order_items.product_id', '=', 'stocks.product_id')
                 ->on('order_items.cost_id', '=', 'stocks.cost_id');
        })
        ->join('costs', 'costs.cost_id', '=', 'stocks.cost_id')
        ->join('products', 'products.product_id', '=', 'stocks.product_id')
        ->join('category', 'category.category_id', '=', 'products.category_id')
        ->select(
            'stocks.*',
            'products.name',
            'category.name as cname',
            DB::raw('CASE WHEN order_items.product_id IS NOT NULL THEN 1 ELSE 0 END as del')
        )
        ->orderBy('stocks.created_at', 'desc')
        ->distinct()  // Ensure distinct records
        // ->groupBy('stocks.stock_id')  // Group by to handle duplicates
        ->get();


        return Stock::join('products', 'products.product_id', '=', 'stocks.product_id')
            ->join('category', 'category.category_id', '=', 'products.category_id')
            ->select('stocks.*', 'products.name', 'category.name as cname')
            ->orderBy('stocks.created_at', 'desc')
            ->get();
    }

    public function get($id){
        $data = DB::table('cost_items')
        ->join('heads', 'heads.head_id', '=', 'cost_items.head_id')
        ->where('cost_items.cost_id', $id)
        ->get();
        return $data;
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        Stock::create($data);
    }

    public function update($id, array $data) {
        $stock = Stock::findOrFail($id);
        $stock->update($data);
    }

    public function delete($id){
        Stock::destroy($id);
    }
}
