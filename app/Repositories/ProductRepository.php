<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductRepository implements GlobalInterface
{
    public function all()
    {
        return Product::join('categories', 'categories.category_id', '=', 'products.category_id')
            ->select('products.*', 'categories.name as cname')
            ->orderBy('products.created_at', 'desc')->get();
    }

    public function active()
    {
        // Used By Product Material
        return Product::where('products.product_status', '1')
            ->join('categories', 'categories.category_id', '=', 'products.category_id')
            ->select('products.*', 'categories.name as cname')
            ->orderBy('products.created_at', 'desc')->get();
    }

    public function activeTypes()
    {
        // Used By Add/Edit Order
        return Product::join('product_types', 'product_types.product_id', 'products.product_id')
            ->join('heads', 'heads.head_id', 'product_types.size_id')
            ->where('products.product_status', '1')
            ->where('product_types.product_type_status', '1')
            ->select('products.*', 'product_types.product_type_id', 'product_types.size_id', 'heads.name as hname')
            ->get();
    }

    public function cost()
    {
        // Used By Product Cost
        return Product::where('products.product_status', '1')
            ->leftJoin('product_costs', 'product_costs.product_id', 'products.product_id')
            ->whereNull('product_costs.product_id')
            ->select('products.*')
            ->get();
    }

    public function material($id)
    {
        // Used By Product Material
        return Product::where('product_types.product_id', $id)
            ->join('product_types', 'product_types.product_id', 'products.product_id')
            ->join('heads', 'heads.head_id', 'product_types.size_id')
            ->leftJoin('product_materials', 'product_materials.product_type_id', '=', 'product_types.product_type_id')
            ->whereNull('product_materials.product_type_id')
            ->select('products.*', 'product_types.product_type_id', 'heads.name as hname')
            ->get();
    }

    public function get($id)
    {
        return Product::where('products.product_id', $id)
            ->join('categories', 'categories.category_id', '=', 'products.category_id')
            ->join('heads', 'heads.head_id', '=', 'products.unit_id')
            ->leftJoin('product_types', function ($join) {
                $join->on('product_types.product_id', '=', 'products.product_id')
                    ->where('product_types.product_type_status', '=', '1')
                    ->orderBy('product_types.product_type_id');
            })
            ->leftJoin('stock_items', function ($join) {
                $join->on('stock_items.product_type_id', '=', 'product_types.product_type_id')
                    ->where('stock_items.stock_id', '=', '1')
                    ->where('stock_items.material_id', '=', '0');
            })
            ->select('products.*', 'categories.name as cname', 'heads.name as hname', 'stock_items.quantity as opening_stock')
            ->first();
    }

    public function getOpeningStock($productId)
    {
        // Get all opening stock entries for a product (size and stage-specific)
        return DB::table('stock_items')
            ->join('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
            ->join('heads as stage_heads', 'stage_heads.head_id', '=', 'stock_items.stage_id')
            ->join('heads as size_heads', 'size_heads.head_id', '=', 'product_types.size_id')
            ->where('product_types.product_id', $productId)
            ->where('stock_items.stock_id', '1') // Opening stock
            ->where('stock_items.material_id', '0')
            ->select(
                'stock_items.stage_id',
                'stock_items.quantity',
                'stage_heads.name as stage_name',
                'product_types.size_id',
                'size_heads.name as size_name'
            )
            ->get();
    }

    public function getProduct($id)
    {
        // Vendor Associated Products
        if ($id === '0' || empty($id)) {
            return collect([]);
        }

        $productIds = explode('|', $id);

        return Product::whereIn('products.product_id', $productIds)
            ->join('categories', 'categories.category_id', '=', 'products.category_id')
            ->select('products.*', 'categories.name as cname')
            ->get();
    }

    public function store(array $data)
    {
        $data['created_by'] = auth()->id();
        $store = Product::create($data);

        return $store->product_id;
    }

    public function update($id, array $data)
    {
        $update = Product::findOrFail($id);
        $update->update($data);

        return $update->product_id;
    }

    public function delete($id)
    {
    }
}
