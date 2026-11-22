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
        $impact->cascadeIf(app(PartyPriceRepository::class)->countFor(['product', $id]), 'vendor / customer price');
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
        app(PartyPriceRepository::class)->deleteFor(['product', $id]); // before product_types go
        DB::table('product_cost_logs')->where('product_id', $id)->delete();

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

    /**
     * Product Purchase Ledger: how product stock came in and went out, like the Material Ledger.
     * The rows are the same sources product stock is counted from (StockItemRepository::pStock):
     *   - purchase receipts: approved qty of received product purchase lines (stock in)
     *   - purchase returns: returned qty of those lines (stock out)
     *   - general stock lines (ptc_virtual 0) of the product, or of it used as a component:
     *     stock_type 1 = in (opening stock, receive issuance, PTC release, delivery return...),
     *     stock_type 2 = out (issuance, PTC issuance, delivery...)
     *
     * With a size (product type) selected, rows carry a running balance and the opening balance
     * is the stock before $dfrom; for all products only in / out are listed.
     *
     * @return array{rows: \Illuminate\Support\Collection, opening: ?float}
     */
    public function purchaseLedger(?string $dfrom, ?string $dto, $productTypeId = null): array
    {
        $productTypeId = (int) $productTypeId ?: null;
        $hasRange = ! empty($dfrom) && ! empty($dto);
        $inRange = fn ($query, $column) => $hasRange ? $query->whereBetween($column, [$dfrom, $dto]) : $query;
        $sources = fn (callable $dates) => $this->ledgerReceipts($dates, $productTypeId)
            ->concat($this->ledgerReturns($dates, $productTypeId))
            ->concat($this->ledgerStockLines($dates, $productTypeId));

        $rows = $sources($inRange)
            ->sortBy(fn ($row) => substr((string) $row->date, 0, 10).' '.$row->stamp)
            ->values();

        $opening = null;
        if ($productTypeId) {
            $opening = $hasRange
                ? (float) $sources(fn ($query, $column) => $query->where($column, '<', $dfrom))->sum(fn ($row) => $row->qty_in - $row->qty_out)
                : 0.0;
            $balance = $opening;
            $rows = $rows->map(function ($row) use (&$balance) {
                $balance += $row->qty_in - $row->qty_out;
                $row->balance = $balance;

                return $row;
            });
        }

        return ['rows' => $rows, 'opening' => $opening];
    }

    /** Every product size for the Product Purchase Ledger filter. */
    public function ledgerProductTypes()
    {
        return DB::table('product_types')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->leftJoin('heads as size', 'size.head_id', '=', 'product_types.size_id')
            ->select('product_types.product_type_id', 'products.article_no', 'products.name as product_name', 'size.name as size_name')
            ->orderBy('products.article_no')->orderBy('size.name')
            ->get();
    }

    /** Product, size and stage names for a ledger query whose product type is $typeColumn. */
    private function ledgerProductJoins($query, string $typeColumn)
    {
        return $query->join('product_types', 'product_types.product_type_id', '=', DB::raw($typeColumn))
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->leftJoin('heads as size', 'size.head_id', '=', 'product_types.size_id');
    }

    private const LEDGER_COLUMNS = ['products.article_no', 'products.name as product_name', 'size.name as size_name', 'stage.name as stage_name'];

    /** Received product purchase lines (approved qty = what entered stock). */
    private function ledgerReceipts(callable $dates, ?int $productTypeId)
    {
        $query = DB::table('receive_materials')
            ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
            ->join('receives', 'receives.receive_id', '=', 'receive_materials.receive_id')
            ->join('purchases', 'purchases.purchase_id', '=', 'purchase_items.purchase_id')
            ->leftJoin('heads as stage', 'stage.head_id', '=', 'purchase_items.product_stage_id')
            ->where('purchase_items.material_id', 0)
            ->when($productTypeId, fn ($q) => $q->where('purchase_items.product_type_id', $productTypeId));
        $this->ledgerProductJoins($query, 'purchase_items.product_type_id');

        return $dates($query, 'receives.receive_date')->select(array_merge(self::LEDGER_COLUMNS, [
            'receives.receive_date as date', 'receive_materials.created_at as stamp',
            DB::raw("'Purchase Receipt' as movement"),
            DB::raw("CONCAT(purchases.purchase_no, ' / ', receives.receive_no) as reference"),
            DB::raw("'receive.show' as link_route"), 'receives.receive_id as link_id',
            DB::raw('COALESCE(receive_materials.approved_qty, 0) as qty_in'), DB::raw('0 as qty_out'),
        ]))->get();
    }

    /** Product purchase returns. */
    private function ledgerReturns(callable $dates, ?int $productTypeId)
    {
        $query = DB::table('return_materials')
            ->join('returns', 'returns.return_id', '=', 'return_materials.return_id')
            ->join('receive_materials', 'receive_materials.receive_material_id', '=', 'return_materials.receive_material_id')
            ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
            ->join('purchases', 'purchases.purchase_id', '=', 'purchase_items.purchase_id')
            ->leftJoin('heads as stage', 'stage.head_id', '=', 'purchase_items.product_stage_id')
            ->where('purchase_items.material_id', 0)
            ->where('return_materials.quantity', '!=', 0)
            ->when($productTypeId, fn ($q) => $q->where('purchase_items.product_type_id', $productTypeId));
        $this->ledgerProductJoins($query, 'purchase_items.product_type_id');

        return $dates($query, 'returns.return_date')->select(array_merge(self::LEDGER_COLUMNS, [
            'returns.return_date as date', 'return_materials.created_at as stamp',
            DB::raw("'Purchase Return' as movement"),
            DB::raw("CONCAT(purchases.purchase_no, ' / ', returns.return_no) as reference"),
            DB::raw("'return.show' as link_route"), 'returns.return_id as link_id',
            DB::raw('0 as qty_in'), 'return_materials.quantity as qty_out',
        ]))->get();
    }

    /** General-stock lines of the product (or of it used as a component), labelled by document. */
    private function ledgerStockLines(callable $dates, ?int $productTypeId)
    {
        $typeColumn = 'COALESCE(stock_items.component_product_type_id, stock_items.product_type_id)';
        $query = DB::table('stock_items')
            ->join('stocks', 'stocks.stock_id', '=', 'stock_items.stock_id')
            ->leftJoin('heads as stage', 'stage.head_id', '=', 'stock_items.stage_id')
            ->leftJoin('deliveries', 'deliveries.stock_id', '=', 'stocks.stock_id')
            ->where('stock_items.ptc_virtual', 0) // PTC virtual stock is not general stock
            ->whereIn('stocks.stock_type', [1, 2])
            ->where(fn ($q) => $q->where(fn ($q) => $q->where('stock_items.material_id', 0)->whereNull('stock_items.component_product_type_id'))
                ->orWhereNotNull('stock_items.component_product_type_id'))
            ->when($productTypeId, fn ($q) => $q->whereRaw($typeColumn.' = ?', [$productTypeId]));
        $this->ledgerProductJoins($query, $typeColumn);

        return $dates($query, 'stocks.stock_date')->select(array_merge(self::LEDGER_COLUMNS, [
            'stocks.stock_date as date', 'stock_items.created_at as stamp',
            'stocks.stock_id', 'stocks.stock_type', 'stocks.table_name', 'stocks.ptc_id', 'stocks.is_ptc_master',
            'stocks.source_id', 'stocks.stock_no', 'deliveries.delivery_id', 'deliveries.delivery_no',
            DB::raw('stock_items.component_product_type_id IS NOT NULL as as_component'),
            DB::raw('CASE WHEN stocks.stock_type = 1 THEN stock_items.quantity ELSE 0 END as qty_in'),
            DB::raw('CASE WHEN stocks.stock_type = 2 THEN stock_items.quantity ELSE 0 END as qty_out'),
        ]))->get()->map(function ($row) {
            [$row->movement, $row->link_route, $row->link_id] = match (true) {
                (int) $row->stock_id === 1 => ['Opening Stock', null, null],
                ! empty($row->delivery_id) => ['Delivery', 'delivery.show', $row->delivery_id],
                $row->table_name === 'delivery_returns' => ['Delivery Return', 'delivery-return.show', $row->source_id],
                $row->table_name === \App\Models\Stock::TABLE_PTC_RELEASE => ['PTC Release', 'ptc.show', $row->ptc_id],
                $row->table_name === \App\Models\Stock::TABLE_ADJUSTMENT => [(int) $row->stock_type === 1 ? 'Stock Adjustment (+)' : 'Stock Adjustment (-)', 'stockAdjustment.show', $row->stock_id],
                $row->table_name === \App\Models\Stock::TABLE_PURCHASE_RETURN => [(int) $row->stock_type === 1 ? 'Purchase Return (purchased stage)' : 'Purchase Return (stage returned from)', null, null],
                (bool) $row->is_ptc_master || ! empty($row->ptc_id) => [(int) $row->stock_type === 2 ? 'PTC Issuance' : 'PTC Receipt', 'ptc.show', $row->ptc_id ?: $row->stock_id],
                (int) $row->stock_type === 2 => ['Issuance', 'stock.show', $row->stock_id],
                default => ['Receive Issuance', 'rstock.show', $row->stock_id],
            };
            if ($row->as_component) {
                $row->movement .= ' (as component)';
            }
            $row->reference = $row->delivery_no ?: $row->stock_no;

            return $row;
        });
    }
}
