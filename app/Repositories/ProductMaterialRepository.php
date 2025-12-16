<?php

namespace App\Repositories;

use App\Models\ProductMaterial;
use Illuminate\Support\Facades\DB;

class ProductMaterialRepository implements GlobalInterface
{
    public function all()
    {
        return ProductMaterial::join('product_types', 'product_types.product_type_id', '=', 'product_materials.product_type_id')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->join('heads', 'heads.head_id', '=', 'product_types.size_id')
            ->groupBy('product_materials.product_type_id')
            ->select('product_materials.created_at', 'product_types.product_type_id',
                'heads.name as hname', 'products.article_no', 'products.name')
            ->orderBy('product_materials.created_at', 'desc')
            ->get();
    }

    public function get($id)
    {
        $productMaterialIds = ProductMaterial::where('product_materials.product_type_id', $id)
            ->pluck('material_id')
            ->toArray();

        $productMaterialIdsFromProducts = DB::table('products')
            ->join('product_types', 'product_types.product_id', '=', 'products.product_id')
            ->where('product_types.product_type_id', $id)
            ->pluck('products.material_id')
            ->toArray();

        $allMaterialIds = array_unique(array_merge(
            $productMaterialIds,
            array_filter(explode('|', implode('|', $productMaterialIdsFromProducts)))
        ));

        $materials = DB::table('materials')
            ->leftJoin('product_materials as pm', function ($join) use ($id) {
                $join->on('pm.material_id', '=', 'materials.material_id')
                    ->where('pm.product_type_id', '=', $id);
            })
            ->leftJoin('heads', 'heads.head_id', '=', 'materials.unit_id')
            ->select('pm.*', 'materials.*', 'heads.name as hname',
                DB::raw('COALESCE(pm.quantity, 0) as quantity')
            )
            ->whereIn('materials.material_id', $allMaterialIds)
            ->groupBy('materials.material_id', 'materials.name', 'heads.name')
            ->get();

        return $materials;

        // Not showing newly added materials
        return ProductMaterial::where('product_materials.product_type_id', $id)
            ->join('materials', 'materials.material_id', '=', 'product_materials.material_id')
            ->join('heads', 'heads.head_id', '=', 'materials.unit_id')
            ->select('product_materials.*', 'materials.*', 'heads.name as hname')
            ->get();

        return ProductMaterial::where('product_materials.product_type_id', $id)
            ->join('materials', 'materials.material_id', '=', 'product_materials.material_id')
            ->join('heads', 'heads.head_id', '=', 'materials.unit_id')
            ->select('product_materials.*', 'materials.*', 'heads.name as hname')
            ->where('materials.material_type_id', '!=', '61') // Not Getting Boxes
            ->get();
    }

    public function getAll($id)
    {
        // Used By ProductInfo
        return ProductMaterial::where('product_types.product_id', $id)
            ->join('materials', 'materials.material_id', '=', 'product_materials.material_id')
            ->join('product_types', 'product_types.product_type_id', '=', 'product_materials.product_type_id')
            ->join('heads', 'heads.head_id', '=', 'materials.unit_id')
            ->select('product_materials.*', 'materials.*', 'heads.name as hname')
            ->get();
    }

    public function times($id)
    {
        // Used By ProductInfo
        return ProductMaterial::where('product_types.product_id', $id)
            ->join('product_types', 'product_types.product_type_id', '=', 'product_materials.product_type_id')
            ->join('products', 'products.product_id', 'product_types.product_id')
            ->join('heads', 'heads.head_id', '=', 'product_types.size_id')
            ->select('heads.name', 'product_materials.product_type_id', 'article_no', 'products.name as pname')
            ->groupBy('product_types.product_type_id')
            ->orderBy('heads.head_id')->get();
    }

    public function store(array $data)
    {
        $data['created_by'] = auth()->id();
        $store = ProductMaterial::create($data);

        return $store->product_material_id;
    }

    public function update($id, array $data)
    {
        $existingItems = ProductMaterial::where('product_type_id', $id)->get();
        foreach ($existingItems as $existingItem) {
            if (! in_array($existingItem->material_id, $data['material_id'])) {
                $existingItem->delete();
            }
        }
        foreach ($data['quantity'] as $key => $quantity) {
            $material = $data['material_id'][$key] ?? null;
            $productMaterial = [
                'product_type_id' => $id,
                'material_id' => $material,
                'quantity' => $quantity,
            ];
            $product = ProductMaterial::where('product_type_id', $id)
                ->where('material_id', $material)
                ->first();
            if ($product) {
                $product->update($productMaterial);
            } else {
                $productMaterial['created_by'] = auth()->id();
                $store = ProductMaterial::create($productMaterial);
            }
        }
    }

    public function updateMaterial($id, array $data)
    {
        // Fetch the existing product materials
        $materials = ProductMaterial::where('product_id', $id)
            ->join('product_types', 'product_types.product_type_id', '=', 'product_materials.product_type_id')
            ->join('materials', 'materials.material_id', 'product_materials.material_id')
            ->where('materials.material_type_id', '!=', '61') // Not Getting Boxes
            ->get();

        // Loop through existing materials and delete those not in the $materialIds array
        foreach ($materials as $material) {
            if (! in_array($material->material_id, $data)) {
                $material->delete();
            }
        }
    }

    public function delete($id)
    {
    }

    /**
     * Get material components for a product type
     */
    public function getMaterialComponents($productTypeId)
    {
        return ProductMaterial::where('product_materials.product_type_id', $productTypeId)
            ->where(function($query) {
                $query->where('component_type', 'material')
                      ->orWhereNull('component_type');
            })
            ->join('materials', 'materials.material_id', '=', 'product_materials.material_id')
            ->join('heads', 'heads.head_id', '=', 'materials.unit_id')
            ->select('product_materials.*', 'materials.*', 'heads.name as hname')
            ->get();
    }

    /**
     * Get product components for a product type
     */
    public function getProductComponents($productTypeId)
    {
        return ProductMaterial::where('product_materials.product_type_id', $productTypeId)
            ->where('component_type', 'product')
            ->join('product_types', 'product_types.product_type_id', '=', 'product_materials.component_product_type_id')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->join('heads', 'heads.head_id', '=', 'product_types.size_id')
            ->select('product_materials.*', 'product_types.product_type_id as component_pt_id',
                'products.article_no', 'products.name as product_name', 'heads.name as size_name')
            ->get();
    }

    /**
     * Store a product component
     */
    public function storeProductComponent(array $data)
    {
        $data['component_type'] = 'product';
        $data['created_by'] = auth()->id();
        $store = ProductMaterial::create($data);
        return $store->product_material_id;
    }

    /**
     * Update product components for a product type
     */
    public function updateProductComponents($productTypeId, array $productIds, array $quantities)
    {
        // Get existing product components
        $existingItems = ProductMaterial::where('product_type_id', $productTypeId)
            ->where('component_type', 'product')
            ->get();

        // Delete items not in the new list
        foreach ($existingItems as $existingItem) {
            if (!in_array($existingItem->component_product_type_id, $productIds)) {
                $existingItem->delete();
            }
        }

        // Update or create new items
        foreach ($productIds as $key => $componentProductTypeId) {
            if (empty($componentProductTypeId)) continue;

            $quantity = $quantities[$key] ?? 1;
            $productMaterial = [
                'product_type_id' => $productTypeId,
                'component_type' => 'product',
                'component_product_type_id' => $componentProductTypeId,
                'quantity' => $quantity,
            ];

            $existing = ProductMaterial::where('product_type_id', $productTypeId)
                ->where('component_type', 'product')
                ->where('component_product_type_id', $componentProductTypeId)
                ->first();

            if ($existing) {
                $existing->update($productMaterial);
            } else {
                $productMaterial['created_by'] = auth()->id();
                ProductMaterial::create($productMaterial);
            }
        }
    }

    /**
     * Delete all product components for a product type
     */
    public function deleteProductComponents($productTypeId)
    {
        ProductMaterial::where('product_type_id', $productTypeId)
            ->where('component_type', 'product')
            ->delete();
    }
}
