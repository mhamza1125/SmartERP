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

        return Head::whereIn('heads.head_id', $stageIds)
            ->get();
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

        return Head::whereIn('heads.head_id', $stageIds)
            ->get();
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
