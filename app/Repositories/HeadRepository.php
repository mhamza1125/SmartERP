<?php

namespace App\Repositories;

use App\Models\Head;
use DB;

class HeadRepository implements GlobalInterface
{
    public function all()
    {
        return Head::join('head_types', 'head_types.head_type_id', '=', 'heads.head_type_id')
            ->select('heads.*', 'head_types.name as htname')
            ->orderBy('head_types.name')
            ->orderBy('heads.name', 'asc')
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

    public function delete($id)
    {
    }
}
