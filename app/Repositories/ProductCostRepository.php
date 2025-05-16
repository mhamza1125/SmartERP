<?php

namespace App\Repositories;

use App\Models\ProductCost;

class ProductCostRepository implements GlobalInterface
{
    public function all()
    {
        return ProductCost::join('products', 'products.product_id', '=', 'product_costs.product_id')
            ->groupBy('product_costs.product_id')
            ->whereNotNull('product_costs.product_id')
            ->select('products.*')
            ->get();
    }

    public function get($id)
    {
        return ProductCost::where('product_costs.product_id', $id)
            ->join('heads', 'heads.head_id', '=', 'product_costs.head_id')
            ->leftJoin('employees', function ($join) {
                $join->on('employees.employee_id', '=', 'product_costs.table_id')
                    ->where('product_costs.table_name', 'employee');
            })
            ->leftJoin('vendors', function ($join) {
                $join->on('vendors.vendor_id', '=', 'product_costs.table_id')
                    ->where('product_costs.table_name', 'vendor');
            })
            ->select('product_costs.*', 'heads.name as hname', 'employees.*', 'vendors.*')
            ->addSelect(\DB::raw('COALESCE(employees.name, vendors.name) as name'))
            ->orderBy('product_costs.table_id')
            ->get();
    }

    // public function getAll123($id){
    //     // Used By ProductInfo to Display All Costs
    //     return ProductCost::where('product_types.product_id', $id)
    //     ->join('heads', 'heads.head_id', '=', 'product_costs.head_id')
    //     ->join('product_types', 'product_types.product_type_id', '=', 'product_costs.product_type_id')
    //     ->leftJoin('employees', function($join) {
    //         $join->on('employees.employee_id', '=', 'product_costs.table_id')
    //             ->where('product_costs.table_name', 'employee');
    //     })
    //     ->leftJoin('vendors', function($join) {
    //         $join->on('vendors.vendor_id', '=', 'product_costs.table_id')
    //             ->where('product_costs.table_name', 'vendor');
    //     })
    //     ->select('product_costs.*', 'heads.name as hname', 'employees.*', 'vendors.*')
    //     ->addSelect(\DB::raw("COALESCE(employees.name, vendors.name) as name"))
    //     ->orderBy('product_costs.table_id')
    //     ->get();
    // }

    public function pcost($id)
    {
        // Ajax Product Cost Used By Stock ReceiveIssuance
        return \DB::table('product_types')
            ->join('product_costs', 'product_costs.product_id', 'product_types.product_id')
            ->join('heads', 'heads.head_id', '=', 'product_costs.head_id')
            ->select('product_costs.*', 'heads.name as hname')
            ->where('product_types.product_type_id', $id)
            ->groupBy('heads.head_id')
            ->get();

        return ProductCost::where('product_costs.product_id', $id)
            ->join('heads', 'heads.head_id', '=', 'product_costs.head_id')
            ->select('product_costs.*', 'heads.name as hname')
            ->groupBy('heads.head_id')
            ->get();
    }

    // public function times123($id){
    //     // Used By ProductInfo to Display All Costs
    //     return ProductCost::where('product_types.product_id', $id)
    //     ->join('product_types', 'product_types.product_type_id', '=', 'product_costs.product_type_id')
    //     ->join('heads', 'heads.head_id', '=', 'product_types.size_id')
    //     ->groupBy('product_types.product_type_id')
    //     ->orderBy('heads.head_id')
    //     ->select('heads.name', 'product_costs.product_type_id')->get();
    // }

    public function wages($pid, $work, $tid, $tname)
    {
        // Calculating & Storing Wages At time of Receiving
        $workArray = explode('|', $work);
        $wages = '';
        foreach ($workArray as $item) {
            $result = ProductCost::join('product_types', 'product_types.product_id', '=', 'product_costs.product_id')
                ->where('product_types.product_type_id', $pid)
                ->where('product_costs.table_name', $tname)
                ->where('product_costs.table_id', $tid)
                ->where('product_costs.head_id', $item)
                ->select('amount')->first();

            if (empty($result)) {
                $result = ProductCost::join('product_types', 'product_types.product_id', '=', 'product_costs.product_id')
                    ->where('product_types.product_type_id', $pid)
                    ->where('product_costs.head_id', $item)
                    ->select('amount')->first();
            }
            if (! empty($result)) {
                $wages .= (empty($wages) ? '' : '|').$result->amount;
            }
        }

        return $wages;
    }

    public function store(array $data)
    {
        $data['created_by'] = auth()->id();
        $store = ProductCost::create($data);

        return $store->product_cost_id;
    }

    public function update($id, array $data)
    {
        $existingItems = ProductCost::where('product_id', $id)->get();
        // Assuming $data['head_id'] are arrays of IDs.
        foreach ($existingItems as $existingItem) {
            // Check if it does not exist in the provided data
            if (! in_array($existingItem->head_id, $data['head_id']) || ! in_array($existingItem->table_id, $data['table_id']) || ! in_array($existingItem->table_name, $data['table_name'])) {
                ProductCost::where('product_cost_id', $existingItem->product_cost_id)->delete();
            }
        }

        // Assuming you have an array of product_ids and head_ids indexed similarly
        foreach ($data['amount'] as $key => $amount) {
            // Assuming you have these arrays in your $data and they are indexed accordingly
            $hid = $data['head_id'][$key] ?? null;
            $tid = $data['table_id'][$key] ?? null;
            $tname = $data['table_name'][$key] ?? null;

            // Validate that $hid is not null
            if ($hid !== null) {
                $productCostItem = [
                    'product_id' => $id,
                    'head_id' => $hid,
                    'amount' => $amount,
                    'table_id' => $tid,
                    'table_name' => $tname,
                ];

                $productCost = ProductCost::where('product_id', $id)
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

    public function delete($id)
    {
    }
}
