<?php

namespace App\Repositories;

use App\Support\DeletionImpact;
use App\Repositories\Concerns\ChecksReferences;
use App\Models\Head;
use DB;

class HeadRepository implements GlobalInterface
{
    use ChecksReferences;

    public function all()
    {
        return Head::join('head_types', 'head_types.head_type_id', '=', 'heads.head_type_id')
            ->select('heads.*', 'head_types.name as htname')
            ->orderBy('head_types.name')
            ->orderBy('heads.name', 'asc')
            // Exclude Product Costing
            ->where('heads.head_type_id', '!=', '14')
            ->get();
    }

    public function headType()
    {
        return \DB::table('head_types')
            ->get();
    }

    public function get($id)
    {
        return Head::where('heads.head_type_id', $id)
            ->where('heads.head_status', '1')
            ->orderBy('heads.name', 'asc')
            ->get();
    }

    public function getById($id)
    {
        return Head::where('heads.head_id', $id)
            ->first();
    }

    public function duplicate(array $data)
    {
        return Head::where('head_type_id', $data['head_type_id'])
            ->where('name', $data['name'])
            ->exists();
    }

    public function getStage($id)
    {
        // Product Stages Used by Product Controller
        $stageIds = explode('|', $id);

        // Order by the position in the original string to maintain the order they were added
        $stages = Head::whereIn('heads.head_id', $stageIds)->get();

        // Sort the collection based on the order in the original pipe-separated string
        $orderedStages = collect();
        foreach ($stageIds as $stageId) {
            $stage = $stages->where('head_id', $stageId)->first();
            if ($stage) {
                $orderedStages->push($stage);
            }
        }

        return $orderedStages;
    }

    public function getStageAjax($id)
    {
        // Product Stages Used by Product Controller
        $product = DB::table('product_types')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->where('product_type_id', $id)
            ->first();
        $product = $product ? (array) $product : [];
        $stageIds = explode('|', $product['stage_ids']);

        // Order by the position in the original string to maintain the order they were added
        $stages = Head::whereIn('heads.head_id', $stageIds)->get();

        // Sort the collection based on the order in the original pipe-separated string
        $orderedStages = collect();
        foreach ($stageIds as $stageId) {
            $stage = $stages->where('head_id', $stageId)->first();
            if ($stage) {
                $orderedStages->push($stage);
            }
        }

        return $orderedStages;
    }

    /**
     * Get heads by array of IDs, maintaining order
     */
    public function getByIds(array $ids)
    {
        if (empty($ids)) {
            return collect();
        }

        $heads = Head::whereIn('heads.head_id', $ids)->get();

        // Maintain order from input array
        $orderedHeads = collect();
        foreach ($ids as $id) {
            $head = $heads->where('head_id', $id)->first();
            if ($head) {
                $orderedHeads->push($head);
            }
        }

        return $orderedHeads;
    }

    public function store(array $data)
    {
        $data['created_by'] = auth()->id();
        $store = Head::create($data);

        return $store->head_id;
    }

    public function update($id, array $data)
    {
        $update = Head::findOrFail($id);
        $update->update($data);
    }

    /**
     * Heads the code relies on by id (employee types, material types, the Rejected stage,
     * expense heads used by automatic entries): never deletable, like heads with action = 1.
     */
    public const SYSTEM_HEAD_IDS = [39, 40, 61, 96, 101, 105, 585, 814];

    /**
     * Every column that stores a head id, with a readable name for the "used by" message.
     *
     * @return array<int, array{0: string, 1: string, 2: string, 3?: callable}>
     */
    private function headReferences(): array
    {
        return [
            ['customers', 'country_id', 'customer (country)'],
            ['customers', 'currency_id', 'customer (currency)'],
            ['employees', 'department_id', 'employee (department)'],
            ['employees', 'employee_type_id', 'employee (employee type)'],
            ['employees', 'city_id', 'employee (city)'],
            ['vendors', 'city_id', 'vendor / contractor (city)'],
            ['vendors', 'vendor_type_id', 'vendor / contractor (vendor type)'],
            ['banks', 'head_id', 'bank account (bank)'],
            ['materials', 'material_type_id', 'material (material type)'],
            ['materials', 'unit_id', 'material (unit)'],
            ['products', 'unit_id', 'product (unit)'],
            ['machines', 'machine_type_id', 'machine (machine type)'],
            ['product_types', 'size_id', 'product size'],
            ['product_types', 'color_id', 'product size (colour)'],
            ['product_costs', 'head_id', 'product cost line'],
            ['order_items', 'product_stage_id', 'order line (stage)'],
            ['purchase_items', 'product_stage_id', 'purchase line (stage)'],
            ['stock_items', 'stage_id', 'stock movement line (stage)'],
            ['igroup_items', 'stage_id', 'issuance group line (stage)'],
            ['stock_batch_transfers', 'stage_id', 'batch transfer (stage)'],
            ['stocks', 'current_stage_id', 'issuance / PTC (stage)'],
            ['stocks', 'start_stage_id', 'PTC (start stage)'],
            ['stocks', 'next_stage_id', 'issuance / PTC (next stage)'],
            ['stocks', 'end_stage_id', 'PTC (end stage)'],
            ['stocks', 'issue_for', 'issuance / PTC (issue for)'],
            ['transactions', 'payee_id', 'expense transaction', fn ($q) => $q->where('transaction_to', 'expense')],
            ['transactions', 'expense_head_id', 'journal voucher (expense head)'],
        ];
    }

    /**
     * A head can be deleted only while nothing stores it (and it is not a system head).
     */
    public function deletionImpact($id): DeletionImpact
    {
        $head = Head::findOrFail($id);
        $impact = DeletionImpact::for('Head "'.$head->name.'"');

        if ((int) $head->action === 1 || in_array((int) $head->head_id, self::SYSTEM_HEAD_IDS, true)) {
            return $impact->block('it is a system head the software relies on');
        }

        foreach ($this->headReferences() as $reference) {
            [$table, $column, $label] = $reference;
            // "employee (department)" -> "employees (department)"
            $plural = preg_replace_callback('/(\w+)( \(|$)/', fn ($m) => \Illuminate\Support\Str::plural($m[1]).$m[2], $label, 1);
            $impact->blockIf($this->refCount($table, $column, $id, $reference[3] ?? null), $label, $plural);
        }
        // Product stage lists are stored as pipe-separated head ids (151|146|...)
        $impact->blockIf(
            DB::table('products')->whereRaw('FIND_IN_SET(?, REPLACE(REPLACE(stage_ids, " ", ""), "|", ","))', [(string) $id])->count(),
            'product (stage list)', 'products (stage list)'
        );

        return $impact;
    }

    public function delete($id)
    {
        return Head::findOrFail($id)->delete();
    }
}
