<?php

namespace App\Repositories;

use App\Models\ProductMaterial;
use Illuminate\Support\Facades\DB;

class ProductMaterialRepository implements GlobalInterface {

    public function all(){
        return ProductMaterial::
        join('product_types', 'product_types.product_type_id', '=', 'product_materials.product_type_id')
        ->join('products', 'products.product_id', '=', 'product_types.product_id')
        ->join('heads', 'heads.head_id', '=', 'product_types.size_id')
        ->groupBy('product_materials.product_type_id')
        ->select('product_materials.created_at', 'product_types.product_type_id', 
                'heads.name as hname', 'products.article_no', 'products.name')
        ->orderBy('product_materials.created_at', 'desc')
        ->get();
    }

    public function get($id){
        return ProductMaterial::where('product_materials.product_type_id', $id)
        ->join('materials', 'materials.material_id', '=', 'product_materials.material_id')
        ->join('heads', 'heads.head_id', '=', 'materials.unit_id')
        ->select('product_materials.*', 'materials.*', 'heads.name as hname')
        ->get();
    }

    public function store(array $data){
        $data['created_by'] = auth()->id();
        $store = ProductMaterial::create($data);
        return $store->product_material_id;
    }

    public function update($id, array $data) {}

    public function delete($id){
        ProductMaterial::where('product_type_id', $id)->delete();
    }
}
