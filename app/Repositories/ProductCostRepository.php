<?php

namespace App\Repositories;

use App\Models\ProductCost;
use App\Models\ProductCostLog;
use Illuminate\Support\Facades\DB;

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
        $this->logWage($store, null, $store->amount, ProductCostLog::ACTION_ADDED);

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
                $this->logWage($existingItem, $existingItem->amount, null, ProductCostLog::ACTION_REMOVED);
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
                    $oldAmount = $productCost->amount;
                    $productCost->update($productCostItem);
                    if (round((float) $oldAmount, 3) !== round((float) $amount, 3)) {
                        $this->logWage($productCost, $oldAmount, $amount, ProductCostLog::ACTION_CHANGED);
                    }
                } else {
                    $productCostItem['created_by'] = auth()->id();
                    $created = ProductCost::create($productCostItem);
                    $this->logWage($created, null, $amount, ProductCostLog::ACTION_ADDED);
                }
            }
        }
    }

    public function delete($id)
    {
    }

    /** Own wage rates of an employee (employee) / contractor (vendor) being deleted. */
    public function countForPerson(string $tableName, int $id): int
    {
        return ProductCost::where('table_name', $tableName)->where('table_id', $id)->count();
    }

    /** Deletes a deleted person's own wage rates and their history. */
    public function deleteForPerson(string $tableName, int $id): void
    {
        ProductCostLog::where('table_name', $tableName)->where('table_id', $id)->delete();
        ProductCost::where('table_name', $tableName)->where('table_id', $id)->delete();
    }

    /** Records one wage change in product_cost_logs. */
    private function logWage($cost, $oldAmount, $amount, string $action): void
    {
        ProductCostLog::create([
            'product_id' => $cost->product_id,
            'head_id' => $cost->head_id,
            'table_name' => $cost->table_name ?: 'general',
            'table_id' => (int) $cost->table_id,
            'old_amount' => $oldAmount,
            'amount' => $amount,
            'action' => $action,
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Wages set for one employee (table_name employee) or contractor (vendor) per product and
     * work head, next to the general wage of the same product / head.
     */
    public function personWages(string $tableName, int $id)
    {
        return ProductCost::where('product_costs.table_name', $tableName)
            ->where('product_costs.table_id', $id)
            ->join('products', 'products.product_id', '=', 'product_costs.product_id')
            ->leftJoin('heads', 'heads.head_id', '=', 'product_costs.head_id')
            ->leftJoin('product_costs as general', function ($join) {
                $join->on('general.product_id', '=', 'product_costs.product_id')
                    ->on('general.head_id', '=', 'product_costs.head_id')
                    ->where('general.table_name', 'general');
            })
            ->select('product_costs.product_cost_id', 'product_costs.product_id', 'product_costs.head_id', 'product_costs.amount',
                'product_costs.created_at', 'product_costs.updated_at',
                'products.article_no', 'products.name as product_name', 'heads.name as head_name', 'general.amount as general_amount')
            ->selectSub($this->lastChange('product_costs'), 'last_changed_at')
            ->orderBy('products.article_no')
            ->orderBy('heads.name')
            ->get();
    }

    /** Date of the latest logged change of a wage row, as a sub-select. */
    private function lastChange(string $alias)
    {
        return DB::table('product_cost_logs')
            ->whereColumn('product_cost_logs.product_id', "$alias.product_id")
            ->whereColumn('product_cost_logs.head_id', "$alias.head_id")
            ->whereColumn('product_cost_logs.table_name', "$alias.table_name")
            ->whereColumn('product_cost_logs.table_id', "$alias.table_id")
            ->selectRaw('MAX(product_cost_logs.created_at)');
    }

    /**
     * Wage change history, newest first: of one employee / contractor ($tableName + $id), or of
     * one product ($productId, every person and the general wage).
     */
    public function wageHistory(?string $tableName = null, ?int $id = null, ?int $productId = null)
    {
        return ProductCostLog::query()
            ->when($tableName, fn ($q) => $q->where('product_cost_logs.table_name', $tableName)->where('product_cost_logs.table_id', $id))
            ->when($productId, fn ($q) => $q->where('product_cost_logs.product_id', $productId))
            ->leftJoin('products', 'products.product_id', '=', 'product_cost_logs.product_id')
            ->leftJoin('heads', 'heads.head_id', '=', 'product_cost_logs.head_id')
            ->leftJoin('employees', function ($join) {
                $join->on('employees.employee_id', '=', 'product_cost_logs.table_id')
                    ->where('product_cost_logs.table_name', 'employee');
            })
            ->leftJoin('vendors', function ($join) {
                $join->on('vendors.vendor_id', '=', 'product_cost_logs.table_id')
                    ->where('product_cost_logs.table_name', 'vendor');
            })
            ->leftJoin('users', 'users.id', '=', 'product_cost_logs.created_by')
            ->select('product_cost_logs.*', 'products.article_no', 'products.name as product_name', 'heads.name as head_name',
                'users.name as user_name',
                DB::raw("CASE product_cost_logs.table_name WHEN 'employee' THEN CONCAT(employees.employee_no, ' - ', employees.name)
                    WHEN 'vendor' THEN CONCAT(vendors.vendor_no, ' - ', COALESCE(vendors.fname, vendors.name)) ELSE 'General Wage' END as person_name"))
            ->orderBy('product_cost_logs.created_at', 'desc')
            ->orderBy('product_cost_logs.product_cost_log_id', 'desc')
            ->get();
    }
}
