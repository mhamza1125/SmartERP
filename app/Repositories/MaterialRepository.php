<?php

namespace App\Repositories;

use App\Models\Material;
use Illuminate\Support\Facades\DB;

class MaterialRepository implements GlobalInterface
{
    public function all()
    {
        return Material::join('heads as mthead', 'mthead.head_id', '=', 'materials.material_type_id')
            ->join('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
            ->join('vendors', 'vendors.vendor_id', '=', 'materials.vendor_id')
            ->select('materials.*', 'mthead.name as mtname', 'uhead.name as uname', 'vendors.fname', 'vendor_no')
            ->orderBy('materials.created_at', 'desc')
            ->get();
    }

    public function machine()
    {
        // Machine Material
        return Material::join('heads as mthead', 'mthead.head_id', '=', 'materials.material_type_id')
            ->join('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
            ->join('vendors', 'vendors.vendor_id', '=', 'materials.vendor_id')
            ->select('materials.*', 'mthead.name as mtname', 'uhead.name as uname', 'vendors.fname', 'vendor_no')
            ->where('materials.material_type_id', '=', '101')
            ->orderBy('materials.created_at', 'desc')
            ->get();
    }

    public function get($id)
    {
        return Material::where('materials.material_id', $id)
            ->join('heads as mthead', 'mthead.head_id', '=', 'materials.material_type_id')
            ->join('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
            ->join('vendors', 'vendors.vendor_id', '=', 'materials.vendor_id')
            ->leftJoin('stock_items', function ($join) {
                $join->on('stock_items.material_id', '=', 'materials.material_id')
                    ->where('stock_items.stock_id', '=', '1');
            })
            ->select('materials.*', 'stock_items.quantity', 'mthead.name as mtname', 'uhead.name as uname', 'vendors.fname', 'vendor_no')
            ->first();

        // Without Opening Stock
        return Material::where('materials.material_id', $id)
            ->join('heads as mthead', 'mthead.head_id', '=', 'materials.material_type_id')
            ->join('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
            ->join('vendors', 'vendors.vendor_id', '=', 'materials.vendor_id')
            ->select('materials.*', 'mthead.name as mtname', 'uhead.name as uname', 'vendors.fname', 'vendor_no')
            ->first();
    }

    public function refNo()
    {
        $lastMaterial = Material::all()->sortByDesc(function ($material) {
            return intval(substr($material->material_no, 1));
        })->first();

        $lastNumber = $lastMaterial ? intval(substr($lastMaterial->material_no, 1)) : 0;

        return 'M'.str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    public function getMaterial($id)
    {
        // Vendor Selling  & Product Raw Materials
        $materialIds = explode('|', $id);

        return Material::whereIn('materials.material_id', $materialIds)
            ->join('heads as mthead', 'mthead.head_id', '=', 'materials.material_type_id')
            ->join('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
            ->join('vendors', 'vendors.vendor_id', '=', 'materials.vendor_id')
            ->select('materials.*', 'mthead.name as mtname', 'uhead.name as uname', 'vendors.fname', 'vendor_no')
            ->get();
    }

    public function getBox()
    {
        // Product Boxes
        return Material::where('mthead.head_id', '61')
            ->join('heads as mthead', 'mthead.head_id', '=', 'materials.material_type_id')
            ->join('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
            ->join('vendors', 'vendors.vendor_id', '=', 'materials.vendor_id')
            ->select('materials.*', 'mthead.name as mtname', 'uhead.name as uname', 'vendors.fname', 'vendor_no')
            ->orderBy('materials.created_at', 'desc')
            ->get();
    }

    public function ledger()
    {
        // Material Ledger
        $stockIn = DB::table('purchase_items')
            ->join('materials', 'materials.material_id', '=', 'purchase_items.material_id')
            ->join('receive_materials', 'receive_materials.purchase_item_id', '=', 'purchase_items.purchase_item_id')
            // ->leftJoin('return_materials', 'return_materials.receive_material_id', '=', 'receive_materials.receive_material_id')
            ->leftJoin('heads as mthead', 'mthead.head_id', '=', 'materials.material_type_id')
            ->leftJoin('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
            ->select('*', 'mthead.name as mtname', 'uhead.name as uname',
                'receive_materials.created_at as timestamp', 'materials.name')
            ->selectRaw('SUM(receive_materials.quantity) as total_received')
            // ->selectRaw('SUM(return_materials.quantity) as total_returned')
            // ->groupBy('purchase_items.purchase_item_id', 'receive_materials.purchase_item_id', 'return_materials.receive_material_id')
            ->groupBy('purchase_items.purchase_item_id', 'receive_materials.purchase_item_id')
            ->get();

        $stockReturn = DB::table('purchase_items')
            ->join('materials', 'materials.material_id', '=', 'purchase_items.material_id')
            ->join('receive_materials', 'receive_materials.purchase_item_id', '=', 'purchase_items.purchase_item_id')
            ->join('return_materials', 'return_materials.receive_material_id', '=', 'receive_materials.receive_material_id')
            ->leftJoin('heads as mthead', 'mthead.head_id', '=', 'materials.material_type_id')
            ->leftJoin('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
            ->where('return_materials.quantity', '!=', '0')
            ->select('*', 'mthead.name as mtname', 'uhead.name as uname',
                'receive_materials.created_at as timestamp', 'materials.name')
            // ->selectRaw('SUM(receive_materials.quantity) as total_received')
            ->selectRaw('SUM(return_materials.quantity) as total_returned')
            ->groupBy('purchase_items.purchase_item_id', 'receive_materials.purchase_item_id', 'return_materials.receive_material_id')
            ->get();

        $stockOut = DB::table('stock_items')
            ->join('materials', 'materials.material_id', 'stock_items.material_id')
            ->leftJoin('heads as mthead', 'mthead.head_id', '=', 'materials.material_type_id')
            ->leftJoin('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
            ->leftJoin('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->select('*', 'mthead.name as mtname', 'uhead.name as uname',
                'stock_items.created_at as timestamp', 'materials.name')
            ->get();

        $return = $stockIn->concat($stockOut)->concat($stockReturn);
        $sorted = $return->sortBy('timestamp');

        return $sorted;
    }

    public function ledgerFilter($dfrom, $dto, $mid)
    {
        // Material Ledger
        $stockIn = DB::table('purchase_items')
            ->join('materials', 'materials.material_id', '=', 'purchase_items.material_id')
            ->join('purchases', 'purchases.purchase_id', '=', 'purchase_items.purchase_id')
            ->join('receive_materials', 'receive_materials.purchase_item_id', '=', 'purchase_items.purchase_item_id')
            // ->leftJoin('return_materials', 'return_materials.receive_material_id', '=', 'receive_materials.receive_material_id')
            ->leftJoin('heads as mthead', 'mthead.head_id', '=', 'materials.material_type_id')
            ->leftJoin('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
            ->whereBetween('purchases.purchase_date', [$dfrom, $dto])
            ->select('*', 'mthead.name as mtname', 'uhead.name as uname',
                'receive_materials.created_at as timestamp', 'materials.name')
            ->selectRaw('SUM(receive_materials.quantity) as total_received')
            // ->selectRaw('SUM(return_materials.quantity) as total_returned')
            // ->groupBy('purchase_items.purchase_item_id', 'receive_materials.purchase_item_id', 'return_materials.receive_material_id')
            ->groupBy('purchase_items.purchase_item_id', 'receive_materials.purchase_item_id');
        // ->get();

        $stockReturn = DB::table('purchase_items')
            ->join('materials', 'materials.material_id', '=', 'purchase_items.material_id')
            ->join('receive_materials', 'receive_materials.purchase_item_id', '=', 'purchase_items.purchase_item_id')
            ->join('return_materials', 'return_materials.receive_material_id', '=', 'receive_materials.receive_material_id')
            ->join('returns', 'returns.return_id', '=', 'return_materials.return_id')
            ->leftJoin('heads as mthead', 'mthead.head_id', '=', 'materials.material_type_id')
            ->leftJoin('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
            ->whereBetween('returns.return_date', [$dfrom, $dto])
            ->where('return_materials.quantity', '!=', '0')
            ->select('*', 'mthead.name as mtname', 'uhead.name as uname',
                'receive_materials.created_at as timestamp', 'materials.name')
            // ->selectRaw('SUM(receive_materials.quantity) as total_received')
            ->selectRaw('SUM(return_materials.quantity) as total_returned')
            ->groupBy('purchase_items.purchase_item_id', 'receive_materials.purchase_item_id', 'return_materials.receive_material_id');
        // ->get();

        $stockOut = DB::table('stock_items')
            ->join('materials', 'materials.material_id', 'stock_items.material_id')
            ->leftJoin('heads as mthead', 'mthead.head_id', '=', 'materials.material_type_id')
            ->leftJoin('heads as uhead', 'uhead.head_id', '=', 'materials.unit_id')
            ->leftJoin('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->whereBetween('stocks.stock_date', [$dfrom, $dto])
            ->select('*', 'mthead.name as mtname', 'uhead.name as uname',
                'stock_items.created_at as timestamp', 'materials.name');
        // ->get();

        if ($mid > 0) {
            $stockIn->where('materials.material_id', $mid);
            $stockOut->where('materials.material_id', $mid);
            $stockReturn->where('materials.material_id', $mid);
        }

        $stockInResults = $stockIn->get();
        $stockOutResults = $stockOut->get();
        $returnResults = $stockReturn->get();

        $return = $stockInResults->merge($stockOutResults)->merge($returnResults);
        $sorted = $return->sortBy('timestamp');

        return $sorted;
    }

    public function store(array $data)
    {
        $data['created_by'] = auth()->id();
        $store = Material::create($data);

        return $store->material_id;
    }

    public function update($id, array $data)
    {
        $update = Material::findOrFail($id);
        $update->update($data);

        return $update->material_id;
    }

    public function delete($id)
    {
    }
}
