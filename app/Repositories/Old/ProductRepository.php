<?php

namespace App\Repositories\Operator;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductRepository implements GlobalInterface {
    
    public function all(){
        return Product::join('category', 'category.category_id', '=', 'products.category_id')
            ->select('products.*', 'category.name AS cname')
            ->get();
    }

    // public function batchProduct(){
    //     return Product::join('stocks', 'stocks.product_id', '=', 'products.product_id')
    //         ->leftJoin('batches', 'batches.batch_id', '=', 'stocks.batch_id')
    //         ->select('products.product_id', 'batches.batch_id', 'batches.batch_no', 'products.name', 'batches.per_liter', 'products.quantity')
    //         ->selectRaw('SUM(stocks.quantity) AS tqty')
    //         ->groupBy('products.product_id', 'batches.batch_id', 'batches.batch_no', 'products.name', 'batches.per_liter', 'products.quantity')
    //         ->havingRaw('SUM(stocks.quantity) > 0')
    //         ->get();
    // }

    public function batchProduct(){
        return Product::join('stocks', 'stocks.product_id', '=', 'products.product_id')
            ->join('costs', 'costs.cost_id', '=', 'stocks.cost_id')
            ->select('products.product_id', 'products.name', 'products.quantity', 'costs.cost_id', 'costs.cost_no', 'costs.per_liter')
            ->selectRaw('SUM(stocks.quantity) AS tqty')
            ->groupBy('products.product_id', 'products.name', 'products.quantity', 'costs.cost_id', 'costs.cost_no', 'costs.per_liter')
            ->havingRaw('SUM(stocks.quantity) > 0')
            ->get();
    }

    public function orderProduct($id){
        return DB::table('order_items')
    ->join('costs', 'costs.cost_id', '=', 'order_items.cost_id')
    ->join('stocks', 'stocks.product_id', '=', 'order_items.product_id')
    ->join('products', 'products.product_id', '=', 'order_items.product_id')
    ->where('order_items.order_id', $id)
    ->select('products.product_id', 
    'products.name', 
    'products.quantity as pqty', 
    'costs.cost_id', 
    'costs.cost_no', 
    'costs.per_liter', 
    'order_items.product_id', 
    'order_items.quantity', 
    'order_items.price',)
    ->groupBy(
        'products.product_id', 
        'products.name', 
        'products.quantity', 
        'costs.cost_id', 
        'costs.cost_no', 
        'costs.per_liter', 
        'order_items.product_id', 
        'order_items.quantity', 
        'order_items.price',
    )
    ->get();


        return DB::table('products')
            ->join('stocks', 'stocks.product_id', '=', 'products.product_id')
            ->join('costs', 'costs.cost_id', '=', 'stocks.cost_id')
            ->join('order_items', 'order_items.product_id', '=', 'products.product_id')
            ->leftJoin(DB::raw('(SELECT product_id, SUM(quantity) as total_quantity FROM stocks GROUP BY product_id) as stock_sum'), 'stock_sum.product_id', '=', 'products.product_id')
            ->where('order_items.order_id', '=', $id)
            ->select(
                'products.product_id', 
                'products.name', 
                'products.quantity', 
                'costs.cost_id', 
                'costs.cost_no', 
                'costs.per_liter', 
                'order_items.product_id as pid', 
                'order_items.quantity as qty',
                'order_items.price as price',
                'stock_sum.total_quantity as tqty'
            )
            ->groupBy(
                'products.product_id', 
                'products.name', 
                'products.quantity', 
                'costs.cost_id', 
                'costs.cost_no', 
                'costs.per_liter', 
                'order_items.product_id', 
                'order_items.quantity', 
                'order_items.price',
                'stock_sum.total_quantity'
            )
            ->havingRaw('stock_sum.total_quantity > 0')
            ->get();
    }    

    public function get($id){
        return Product::find($id);
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        Product::create($data);
    }

    public function update($id, array $data) {
        $update = Product::findOrFail($id);
        $update->update($data);
    }

    public function delete($id){
        // Logic to delete a WeighBridge entry with ID $id
    }
}
