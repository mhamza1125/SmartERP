<?php

namespace App\Repositories;

use App\Models\ProductCost;

class ProductCostRepository implements GlobalInterface {
    
    public function all(){
        return ProductCost::join('product_types', 'product_types.product_type_id', 'product_costs.product_type_id')
        ->join('heads', 'heads.head_id', 'product_types.size_id')
        ->join('products', 'products.product_id', '=', 'product_types.product_id')
        ->groupBy('product_costs.product_type_id')
        ->whereNotNull('product_costs.product_type_id')
        ->select('products.*', 'product_types.product_type_id', 'heads.name as hname')
        ->get();
    }

    public function get($id){
        return ProductCost::where('product_costs.product_type_id', $id)
            ->join('heads', 'heads.head_id', '=', 'product_costs.head_id')
            ->leftJoin('employees', function($join) {
                $join->on('employees.employee_id', '=', 'product_costs.table_id')
                    ->where('product_costs.table_name', 'employee');
            })
            ->leftJoin('vendors', function($join) {
                $join->on('vendors.vendor_id', '=', 'product_costs.table_id')
                    ->where('product_costs.table_name', 'vendor');
            })
            ->select('product_costs.*', 'heads.name as hname', 'employees.*', 'vendors.*')
            ->addSelect(\DB::raw("COALESCE(employees.name, vendors.name) as name"))
            ->orderBy('product_costs.table_id')
            ->get();
    }

    public function getAll($id){
        return ProductCost::where('product_types.product_id', $id)
        ->join('heads', 'heads.head_id', '=', 'product_costs.head_id')
        ->join('product_types', 'product_types.product_type_id', '=', 'product_costs.product_type_id')
        ->leftJoin('employees', function($join) {
            $join->on('employees.employee_id', '=', 'product_costs.table_id')
                ->where('product_costs.table_name', 'employee');
        })
        ->leftJoin('vendors', function($join) {
            $join->on('vendors.vendor_id', '=', 'product_costs.table_id')
                ->where('product_costs.table_name', 'vendor');
        })
        ->select('product_costs.*', 'heads.name as hname', 'employees.*', 'vendors.*')
        ->addSelect(\DB::raw("COALESCE(employees.name, vendors.name) as name"))
        ->orderBy('product_costs.table_id')
        ->get();
        
        return ProductCost::where('product_types.product_id', $id)
        ->join('heads', 'heads.head_id', '=', 'product_costs.head_id')
        ->join('product_types', 'product_types.product_type_id', '=', 'product_costs.product_type_id')
        ->select('product_costs.*', 'heads.name as hname')
        ->get();
    }

    public function times($id){
        return ProductCost::where('product_types.product_id', $id)
        ->join('product_types', 'product_types.product_type_id', '=', 'product_costs.product_type_id')
        ->join('heads', 'heads.head_id', '=', 'product_types.size_id')
        ->groupBy('product_types.product_type_id')
        ->orderBy('heads.head_id')
        ->select('heads.name', 'product_costs.product_type_id')->get();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $store = ProductCost::create($data);
        return $store->product_cost_id;
    }

    public function update($id, array $data) {
        $existingItems = ProductCost::where('product_type_id', $id)->get();
        // Assuming $data['head_id'] are arrays of IDs.
        foreach ($existingItems as $existingItem) {
            // Check if it does not exist in the provided data
            if (!in_array($existingItem->head_id, $data['head_id']) || !in_array($existingItem->table_id, $data['table_id']) || !in_array($existingItem->table_name, $data['table_name'])) {
                ProductCost::where('product_cost_id', $existingItem->product_cost_id)->delete();
            }
        }
        
        // Assuming you have an array of product_type_ids and head_ids indexed similarly
        foreach ($data['amount'] as $key => $amount) {
            // Assuming you have these arrays in your $data and they are indexed accordingly
            $hid = $data['head_id'][$key] ?? null;
            $tid = $data['table_id'][$key] ?? null;
            $tname = $data['table_name'][$key] ?? null;

            // Validate that $hid is not null
            if ($hid !== null) {
                $productCostItem = [
                    'product_type_id' => $id,
                    'head_id' => $hid,
                    'amount' => $amount,
                    'table_id' => $tid,
                    'table_name' => $tname,
                ];
                
                $productCost = ProductCost::where('product_type_id', $id)
                    ->where('head_id', $hid)
                    ->where('table_id', $tid)
                    ->where('table_name', $tname)
                    ->first();
                if ($productCost) {
                    $productCost->update($productCostItem);
                } else {
                    $productCostItem['created_by'] = auth()->id();
                    ProductCost::create($productCostItem);
                }
            }
        }
    }

    public function delete($id){}
}
