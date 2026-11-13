<?php

namespace App\Repositories;

use App\Support\DeletionImpact;
use App\Repositories\Concerns\ChecksReferences;
use App\Repositories\ImageRepository;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductRepository implements GlobalInterface
{
    use ChecksReferences;

    public function all()
    {
        // Active sizes per product as a comma-separated list (single query, no N+1)
        $sizes = DB::table('product_types')
            ->join('heads', 'heads.head_id', '=', 'product_types.size_id')
            ->whereColumn('product_types.product_id', 'products.product_id')
            ->where('product_types.product_type_status', '1')
            ->selectRaw("GROUP_CONCAT(heads.name ORDER BY product_types.product_type_id SEPARATOR ', ')");

        return Product::join('categories', 'categories.category_id', '=', 'products.category_id')
            ->select('products.*', 'categories.name as cname')
            ->selectSub($sizes, 'sizes')
            ->orderBy('products.created_at', 'desc')->get();
    }

    public function active()
    {
        // Used By Product Material
        return Product::where('products.product_status', '1')
            ->join('categories', 'categories.category_id', '=', 'products.category_id')
            ->select('products.*', 'categories.name as cname')
            ->orderBy('products.created_at', 'desc')->get();
    }

    public function activeTypes()
    {
        // Used By Add/Edit Order
        return Product::join('product_types', 'product_types.product_id', 'products.product_id')
            ->join('heads', 'heads.head_id', 'product_types.size_id')
            ->where('products.product_status', '1')
            ->where('product_types.product_type_status', '1')
            ->select('products.*', 'product_types.product_type_id', 'product_types.size_id', 'heads.name as hname')
            ->get();
    }

    public function cost()
    {
        // Used By Product Cost
        return Product::where('products.product_status', '1')
            ->leftJoin('product_costs', 'product_costs.product_id', 'products.product_id')
            ->whereNull('product_costs.product_id')
            ->select('products.*')
            ->get();
    }

    public function material($id)
    {
        // Used By Product Material
        return Product::where('product_types.product_id', $id)
            ->join('product_types', 'product_types.product_id', 'products.product_id')
            ->join('heads', 'heads.head_id', 'product_types.size_id')
            ->leftJoin('product_materials', 'product_materials.product_type_id', '=', 'product_types.product_type_id')
            ->whereNull('product_materials.product_type_id')
            ->select('products.*', 'product_types.product_type_id', 'heads.name as hname')
            ->get();
    }

    public function get($id)
    {
        return Product::where('products.product_id', $id)
            ->join('categories', 'categories.category_id', '=', 'products.category_id')
            ->join('heads', 'heads.head_id', '=', 'products.unit_id')
            ->leftJoin('product_types', function ($join) {
                $join->on('product_types.product_id', '=', 'products.product_id')
                    ->where('product_types.product_type_status', '=', '1')
                    ->orderBy('product_types.product_type_id');
            })
            ->leftJoin('stock_items', function ($join) {
                $join->on('stock_items.product_type_id', '=', 'product_types.product_type_id')
                    ->where('stock_items.stock_id', '=', '1')
                    ->where('stock_items.material_id', '=', '0');
            })
            ->select('products.*', 'categories.name as cname', 'heads.name as hname', 'stock_items.quantity as opening_stock')
            ->first();
    }

    public function getOpeningStock($productId)
    {
        // Get all opening stock entries for a product (size and stage-specific)
        return DB::table('stock_items')
            ->join('product_types', 'product_types.product_type_id', '=', 'stock_items.product_type_id')
            ->join('heads as stage_heads', 'stage_heads.head_id', '=', 'stock_items.stage_id')
            ->join('heads as size_heads', 'size_heads.head_id', '=', 'product_types.size_id')
            ->where('product_types.product_id', $productId)
            ->where('stock_items.stock_id', '1') // Opening stock
            ->where('stock_items.material_id', '0')
            ->select(
                'stock_items.stage_id',
                'stock_items.quantity',
                'stage_heads.name as stage_name',
                'product_types.size_id',
                'size_heads.name as size_name'
            )
            ->get();
    }

    public function getProduct($id)
    {
        // Vendor Associated Products
        if ($id === '0' || empty($id)) {
            return collect([]);
        }

        $productIds = explode('|', $id);

        return Product::whereIn('products.product_id', $productIds)
            ->join('categories', 'categories.category_id', '=', 'products.category_id')
            ->select('products.*', 'categories.name as cname')
            ->get();
    }

    public function store(array $data)
    {
        $data['created_by'] = auth()->id();
        $store = Product::create($data);

        return $store->product_id;
    }

    public function update($id, array $data)
    {
        $update = Product::findOrFail($id);
        $update->update($data);

        return $update->product_id;
    }

    /**
     * A product can be deleted only while none of its sizes is on an order, purchase, stock
     * movement, batch, packing list or another product's bill of materials. Its own sizes, bill
     * of materials, costing, opening stock (with its opening batches) and images go with it.
     */
    public function deletionImpact($id): DeletionImpact
    {
        $product = Product::findOrFail($id);
        $impact = DeletionImpact::for('Product '.$product->article_no.' ('.$product->name.')');
        $types = DB::table('product_types')->where('product_id', $id)->pluck('product_type_id')->all();

        if ($types) {
            $impact->blockIf(
                DB::table('order_items')->whereIn('product_type_id', $types)->distinct()->count('order_id'), 'order', null,
                DB::table('order_items')->join('orders', 'orders.order_id', '=', 'order_items.order_id')->whereIn('order_items.product_type_id', $types)->distinct()->limit(6)->pluck('orders.order_no')
            );
            $impact->blockIf(
                DB::table('purchase_items')->whereIn('product_type_id', $types)->where('material_id', 0)->distinct()->count('purchase_id'), 'purchase', null,
                DB::table('purchase_items')->join('purchases', 'purchases.purchase_id', '=', 'purchase_items.purchase_id')->whereIn('purchase_items.product_type_id', $types)->where('purchase_items.material_id', 0)->distinct()->limit(6)->pluck('purchases.purchase_no')
            );
            $impact->blockIf(
                DB::table('stock_items')->where('stock_id', '!=', 1)
                    ->where(fn ($q) => $q->whereIn('product_type_id', $types)->orWhereIn('component_product_type_id', $types))
                    ->distinct()->count('stock_id'),
                'stock record (issuance / PTC / delivery / receiving)', 'stock records (issuances / PTCs / deliveries / receivings)'
            );
            $impact->blockIf(
                DB::table('product_materials')->whereIn('component_product_type_id', $types)->whereNotIn('product_type_id', $types)->count(),
                'other product using it as a component', 'other products using it as a component'
            );
            $impact->blockIf(DB::table('igroup_items')->whereIn('product_type_id', $types)->count(), 'issuance group line');
            $impact->blockIf(
                DB::table('stock_batches')->whereIn('product_type_id', $types)->where('source_type', '!=', 'opening')->count(), 'stock batch', null,
                DB::table('stock_batches')->whereIn('product_type_id', $types)->where('source_type', '!=', 'opening')->limit(6)->pluck('batch_no')
            );
        }
        $impact->blockIf($this->refCount('packing_carton_items', 'product_id', $id), 'packing list carton line');
        $impact->blockIf($this->refCount('vendors', 'product_id', $id), 'vendor', null, $this->refExamples('vendors', 'product_id', $id, 'name'));

        $impact->cascadeIf(count($types), 'product size', 'product sizes');
        if ($types) {
            $impact->cascadeIf(DB::table('product_materials')->whereIn('product_type_id', $types)->count(), 'bill of materials line');
            $impact->cascadeIf($this->openingStockLines($types)->count(), 'opening stock line');
        }
        $impact->cascadeIf($this->refCount('product_costs', 'product_id', $id), 'costing / wages line');
        $images = app(ImageRepository::class)->countFor('products', $id);
        $impact->cascadeIf($images, 'image / file', 'images / files');

        return $impact;
    }

    /**
     * Opening stock lines (stocks row 1) of the given product sizes, as product or as component.
     */
    private function openingStockLines(array $types)
    {
        return DB::table('stock_items')->where('stock_id', 1)
            ->where(fn ($q) => $q->whereIn('product_type_id', $types)->orWhereIn('component_product_type_id', $types));
    }

    public function delete($id)
    {
        $types = DB::table('product_types')->where('product_id', $id)->pluck('product_type_id')->all();

        if ($types) {
            $openingLines = $this->openingStockLines($types)->pluck('stock_item_id')->all();
            DB::table('stock_item_batches')->whereIn('stock_item_id', $openingLines)->delete();
            DB::table('stock_items')->whereIn('stock_item_id', $openingLines)->delete();
            DB::table('stock_batches')->whereIn('product_type_id', $types)->where('source_type', 'opening')->delete();
            DB::table('product_materials')->whereIn('product_type_id', $types)->delete();
            DB::table('product_types')->whereIn('product_type_id', $types)->delete();
        }
        DB::table('product_costs')->where('product_id', $id)->delete();
        app(ImageRepository::class)->deleteFor('products', $id);

        return Product::findOrFail($id)->delete();
    }
}
