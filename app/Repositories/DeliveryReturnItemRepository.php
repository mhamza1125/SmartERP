<?php

namespace App\Repositories;

use App\Models\DeliveryReturnItem;
use Illuminate\Support\Facades\DB;

class DeliveryReturnItemRepository implements GlobalInterface
{
    public function all()
    {
        return DeliveryReturnItem::all();
    }

    public function get($returnId)
    {
        return DeliveryReturnItem::where('delivery_return_items.delivery_return_id', $returnId)
            ->join('stock_items', 'stock_items.stock_item_id', '=', 'delivery_return_items.stock_item_id')
            ->leftJoin('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
            ->leftJoin('products', 'products.product_id', '=', 'product_types.product_id')
            ->leftJoin('materials', 'materials.material_id', '=', 'stock_items.material_id')
            ->leftJoin('heads as shead', 'shead.head_id', '=', 'product_types.size_id')
            ->leftJoin('heads as puhead', 'puhead.head_id', '=', 'products.unit_id')
            ->leftJoin('heads as muhead', 'muhead.head_id', '=', 'materials.unit_id')
            ->leftJoin('heads as sthead', 'sthead.head_id', '=', 'stock_items.stage_id')
            ->select(
                'delivery_return_items.*',
                'stock_items.quantity as original_quantity',
                'products.name as product_name',
                'products.article_no',
                'materials.name as material_name',
                'shead.name as size_name',
                'puhead.name as product_unit',
                'muhead.name as material_unit',
                'sthead.name as stage_name'
            )
            ->get();
    }

    public function getByDelivery($deliveryId)
    {
        return DB::table('delivery_return_items')
            ->join('delivery_returns', 'delivery_returns.delivery_return_id', '=', 'delivery_return_items.delivery_return_id')
            ->where('delivery_returns.delivery_id', $deliveryId)
            ->select('delivery_return_items.*')
            ->get();
    }

    public function store(array $data)
    {
        $data['created_by'] = auth()->id();
        $store = DeliveryReturnItem::create($data);

        return $store->delivery_return_item_id;
    }

    public function update($returnId, array $data)
    {
        // Delete existing items
        DeliveryReturnItem::where('delivery_return_id', $returnId)->delete();

        // Insert new items
        $stockItemIds = $data['stock_item_id'] ?? [];
        $quantities = $data['return_quantity'] ?? [];
        $reasons = $data['reason'] ?? [];

        foreach ($quantities as $key => $quantity) {
            if ($quantity > 0) {
                $stockItemId = $stockItemIds[$key] ?? null;
                $reason = $reasons[$key] ?? null;
                
                $itemData = [
                    'delivery_return_id' => $returnId,
                    'stock_item_id' => $stockItemId,
                    'quantity' => $quantity,
                    'reason' => $reason,
                    'created_by' => auth()->id(),
                ];
                
                DeliveryReturnItem::create($itemData);
            }
        }
    }

    public function delete($id)
    {
        $delete = DeliveryReturnItem::findOrFail($id);
        $delete->delete();
    }
}
