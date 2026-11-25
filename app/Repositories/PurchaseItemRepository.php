<?php

namespace App\Repositories;

use App\Models\PurchaseItem;
use Illuminate\Support\Facades\DB;

class PurchaseItemRepository implements GlobalInterface
{
    public function all()
    {
        return PurchaseItem::all();
    }

    // This is for Material Purchase
    public function get($id)
    {
        return PurchaseItem::where('purchase_id', $id)
            ->join('materials', 'materials.material_id', '=', 'purchase_items.material_id')
            ->join('heads', 'heads.head_id', '=', 'materials.unit_id')
            ->select('purchase_items.*', 'materials.name', 'materials.material_no', 'heads.name as hname')
            ->get();
    }

    // This is for Product Purchase
    public function get2($id)
    {
        return PurchaseItem::where('purchase_id', $id)
            ->join('product_types', 'product_types.product_type_id', '=', 'purchase_items.product_type_id')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->join('heads', 'heads.head_id', '=', 'product_types.size_id')
            ->join('heads as shead', 'shead.head_id', '=', 'purchase_items.product_stage_id')
            ->select('purchase_items.*', 'products.name', 'products.article_no', 'heads.name as hname', 'shead.name as sname')
            ->orderBy('product_types.product_id')
            ->orderBy('product_types.size_id')
            ->get();
    }

    /**
     * Price history of a material: every purchase / processing line for it,
     * newest first. is_process = 1 when the line belongs to a material process.
     */
    public function materialPriceHistory($materialId, ?string $dfrom = null, ?string $dto = null)
    {
        // Product lines carry no material_id; processing lines store product_stage_id = 0
        return PurchaseItem::where('purchase_items.material_id', $materialId)
            ->join('purchases', 'purchases.purchase_id', '=', 'purchase_items.purchase_id')
            ->leftJoin('vendors', 'vendors.vendor_id', '=', 'purchases.vendor_id')
            ->leftJoin('orders', 'orders.order_id', '=', 'purchases.order_id')
            ->leftJoin('mprocess', 'mprocess.purchase_item_id', '=', 'purchase_items.purchase_item_id')
            ->when($dfrom && $dto, fn ($q) => $q->whereBetween('purchases.purchase_date', [$dfrom, $dto]))
            ->select(
                'purchase_items.purchase_item_id', 'purchase_items.purchase_id', 'purchase_items.quantity', 'purchase_items.price', 'purchase_items.total',
                'purchase_items.created_at', 'purchases.purchase_no', 'purchases.purchase_date', 'orders.job_no',
                'purchases.vendor_id', 'vendors.vendor_no', 'vendors.fname',
                DB::raw('CASE WHEN mprocess.purchase_item_id IS NOT NULL THEN 1 ELSE 0 END AS is_process')
            )
            ->selectSub($this->receivedQty(), 'received_qty')
            ->orderBy('purchases.purchase_date', 'desc')
            ->orderBy('purchase_items.purchase_item_id', 'desc')
            ->get();
    }

    /**
     * Purchase price history of a product (all sizes / stages), newest first.
     */
    public function productPriceHistory($productId, ?string $dfrom = null, ?string $dto = null)
    {
        return PurchaseItem::join('product_types', 'product_types.product_type_id', '=', 'purchase_items.product_type_id')
            ->where('product_types.product_id', $productId)
            ->join('purchases', 'purchases.purchase_id', '=', 'purchase_items.purchase_id')
            ->leftJoin('vendors', 'vendors.vendor_id', '=', 'purchases.vendor_id')
            ->leftJoin('orders', 'orders.order_id', '=', 'purchases.order_id')
            ->leftJoin('heads as size', 'size.head_id', '=', 'product_types.size_id')
            ->leftJoin('heads as stage', 'stage.head_id', '=', 'purchase_items.product_stage_id')
            ->when($dfrom && $dto, fn ($q) => $q->whereBetween('purchases.purchase_date', [$dfrom, $dto]))
            ->select(
                'purchase_items.purchase_item_id', 'purchase_items.purchase_id', 'purchase_items.quantity', 'purchase_items.price', 'purchase_items.total',
                'purchases.purchase_no', 'purchases.purchase_date', 'orders.job_no',
                'purchases.vendor_id', 'vendors.vendor_no', 'vendors.fname',
                'size.name as size_name', 'stage.name as stage_name'
            )
            ->selectSub($this->receivedQty(), 'received_qty')
            ->orderBy('purchases.purchase_date', 'desc')
            ->orderBy('purchase_items.purchase_item_id', 'desc')
            ->get();
    }

    /** Approved (received into stock) quantity of a purchase line, as a sub-select. */
    private function receivedQty()
    {
        return DB::table('receive_materials')
            ->whereColumn('receive_materials.purchase_item_id', 'purchase_items.purchase_item_id')
            ->selectRaw('COALESCE(SUM(receive_materials.approved_qty), 0)');
    }

    /**
     * Vendor price comparison from purchase history lines (materialPriceHistory /
     * productPriceHistory). Processing lines (a processing charge, not a purchase price) are
     * left out. Per vendor: times purchased, total quantity, lowest / highest / average price
     * (average weighted by quantity) and the last purchase's price and date. Cheapest last
     * price first.
     */
    public function vendorComparison($lines)
    {
        return collect($lines)
            ->reject(fn ($line) => ! empty($line->is_process))
            ->groupBy('vendor_id')
            ->map(function ($vendorLines) {
                $last = $vendorLines->sortByDesc(fn ($l) => $l->purchase_date.' '.str_pad((string) $l->purchase_item_id, 12, '0', STR_PAD_LEFT))->first();
                $quantity = (float) $vendorLines->sum('quantity');

                return (object) [
                    'vendor_id' => $last->vendor_id,
                    'vendor_no' => $last->vendor_no,
                    'fname' => $last->fname,
                    'purchases' => $vendorLines->pluck('purchase_id')->unique()->count(),
                    'quantity' => $quantity,
                    'received_qty' => (float) $vendorLines->sum('received_qty'),
                    'lowest_price' => (float) $vendorLines->min('price'),
                    'highest_price' => (float) $vendorLines->max('price'),
                    'average_price' => $quantity > 0
                        ? round($vendorLines->sum(fn ($l) => (float) $l->price * (float) $l->quantity) / $quantity, 2)
                        : round((float) $vendorLines->avg('price'), 2),
                    'last_price' => (float) $last->price,
                    'last_date' => $last->purchase_date,
                ];
            })
            ->sortBy('last_price')
            ->values();
    }

    public function receive($id)
    {
        // Receive Purchase Items
        return PurchaseItem::where('purchase_id', $id)
            ->join('materials', 'materials.material_id', '=', 'purchase_items.material_id')
            ->leftJoinSub($this->receivedPerLine(), 'line_totals', 'line_totals.purchase_item_id', '=', 'purchase_items.purchase_item_id')
            ->join('heads', 'heads.head_id', '=', 'materials.unit_id')
            ->select('purchase_items.*', 'materials.name', 'materials.material_no', 'heads.name as uname')
            ->selectRaw('COALESCE(line_totals.received, 0) as received, COALESCE(line_totals.returned, 0) as returned')
            ->get();
    }

    public function receiveProducts($id)
    {
        // Receive Purchase Items
        return PurchaseItem::where('purchase_id', $id)
            ->join('product_types', 'product_types.product_type_id', '=', 'purchase_items.product_type_id')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->join('heads', 'heads.head_id', '=', 'product_types.size_id')
            ->join('heads as shead', 'shead.head_id', '=', 'purchase_items.product_stage_id')
            ->leftJoinSub($this->receivedPerLine(), 'line_totals', 'line_totals.purchase_item_id', '=', 'purchase_items.purchase_item_id')
            ->select('purchase_items.*', 'products.name', 'products.article_no', 'heads.name as hname', 'shead.name as sname')
            ->selectRaw('COALESCE(line_totals.received, 0) as received, COALESCE(line_totals.returned, 0) as returned')
            ->get();
    }

    /**
     * Per purchase line: received (gross receipt quantity, incl. rejected / pending) and returned.
     * Returns are summed per receipt line first (StockItemRepository::returnsPerReceiptLine),
     * so a receipt with several partial returns is counted once.
     */
    private function receivedPerLine(): \Illuminate\Database\Query\Builder
    {
        return DB::table('receive_materials')
            ->leftJoinSub(app(StockItemRepository::class)->returnsPerReceiptLine(), 'returned', 'returned.receive_material_id', '=', 'receive_materials.receive_material_id')
            ->groupBy('receive_materials.purchase_item_id')
            ->select('receive_materials.purchase_item_id')
            ->selectRaw('SUM(receive_materials.quantity) as received')
            ->selectRaw('IFNULL(SUM(returned.quantity), 0) as returned');
    }

    public function estimate($id)
    {
        // Material Purchase Against Order
        return PurchaseItem::where('purchases.order_id', $id)
            ->join('purchases', 'purchases.purchase_id', '=', 'purchase_items.purchase_id')
            ->join('materials', 'materials.material_id', '=', 'purchase_items.material_id')
            ->join('vendors', 'vendors.vendor_id', '=', 'materials.vendor_id')
            ->join('heads', 'heads.head_id', '=', 'materials.unit_id')
            ->select('*', 'materials.material_id', 'heads.name as hname', 'materials.name', 'vendors.fname', 'vendor_no')
            ->selectRaw('CEIL(SUM(CEIL(purchase_items.quantity))) as total_qty')
            ->orderBy('materials.vendor_id')->orderBy('materials.material_id')
            ->groupBy('materials.material_id')
            ->get();
    }

    public function estimateMaterial($orderId, $materialId)
    {
        // Material Purchase Against Order, Purchase Order
        $result = DB::table('materials')
            ->select(
                DB::raw('GREATEST(0, COALESCE(total.total_qty, 0) - COALESCE(purchase.pqty, 0)) AS balance')
            )
            ->leftJoin(DB::raw("
                (
                    SELECT
                        product_materials.material_id,
                        CEIL(SUM(CEIL(order_items.quantity * product_materials.quantity))) AS total_qty
                    FROM order_items
                    JOIN product_materials ON product_materials.product_type_id = order_items.product_type_id
                    WHERE order_items.order_id = $orderId AND product_materials.material_id = $materialId
                    GROUP BY product_materials.material_id
                ) AS total"), 'materials.material_id', '=', 'total.material_id')
            ->leftJoin(DB::raw("
                (
                    SELECT
                        purchase_items.material_id,
                        CEIL(SUM(CEIL(purchase_items.quantity))) AS pqty
                    FROM purchase_items
                    JOIN purchases ON purchases.purchase_id = purchase_items.purchase_id
                    WHERE purchases.order_id = $orderId AND purchase_items.material_id = $materialId
                    GROUP BY purchase_items.material_id
                ) AS purchase"), 'materials.material_id', '=', 'purchase.material_id')
            ->where('materials.material_id', $materialId)
            ->first();

        return $result ? $result->balance : 0;
    }

    // This is for Material Purchase
    public function editReceive($pid, $rid)
    {
        return PurchaseItem::where('purchase_id', $pid)
            ->join('materials', 'materials.material_id', '=', 'purchase_items.material_id')
            ->leftJoin('receive_materials', function ($join) use ($rid) {
                $join->on('receive_materials.purchase_item_id', '=', 'purchase_items.purchase_item_id')
                    ->where('receive_materials.receive_id', '!=', $rid);
            })
            ->join('heads', 'heads.head_id', '=', 'materials.unit_id')
            ->select('purchase_items.*', 'materials.name', 'materials.material_no', 'heads.name as uname')
            ->selectRaw('COALESCE(SUM(receive_materials.quantity), 0) as received')
            ->groupBy('purchase_items.purchase_item_id')
            ->get();
    }
    
    // This is for Product Purchase
    public function editReceive2($pid, $rid)
    {
        return PurchaseItem::where('purchase_id', $pid)
            ->join('product_types', 'product_types.product_type_id', '=', 'purchase_items.product_type_id')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->join('heads', 'heads.head_id', '=', 'product_types.size_id')
            ->join('heads as shead', 'shead.head_id', '=', 'purchase_items.product_stage_id')
            ->leftJoin('receive_materials', function ($join) use ($rid) {
                $join->on('receive_materials.purchase_item_id', '=', 'purchase_items.purchase_item_id')
                    ->where('receive_materials.receive_id', '!=', $rid);
            })
            ->select('purchase_items.*', 'products.name', 'products.article_no', 'heads.name as hname', 'shead.name as sname')
            ->selectRaw('COALESCE(SUM(receive_materials.quantity), 0) as received')
            ->groupBy('purchase_items.purchase_item_id')
            ->get();
    }

    public function store(array $data)
    {
        $data['created_by'] = auth()->id();
        $store = PurchaseItem::create($data);

        return $store->purchase_item_id;
    }

    public function update($id, array $data)
    {
        $existingItems = PurchaseItem::where('purchase_id', $id)->get();
        foreach ($existingItems as $existingItem) {
            if (!in_array([$existingItem->material_id, $existingItem->product_type_id, $existingItem->product_stage_id], array_map(null, $data['material_id'], $data['product_type_id'], $data['product_stage_id']))) {
                $existingItem->delete();
            }
        }
        foreach ($data['quantity'] as $key => $quantity) {
            $price = $data['price'][$key] ?? null;
            $material = $data['material_id'][$key] ?? null;
            $product = $data['product_type_id'][$key] ?? null;
            $stage = $data['product_stage_id'][$key] ?? null;
            $total = $price * $quantity;
            $purchaseItem = [
                'purchase_id' => $id,
                'product_type_id' => $product,
                'product_stage_id' => $stage,
                'material_id' => $material,
                'price' => $price,
                'quantity' => $quantity,
                'total' => $total,
            ];
            $purchase = PurchaseItem::where('purchase_id', $id)
                ->where('material_id', $material)
                ->where('product_type_id', $product)
                ->where('product_stage_id', $stage)
                ->first();
            if ($purchase) {
                $purchase->update($purchaseItem);
            } else {
                $purchaseItem['created_by'] = auth()->id();
                $store = PurchaseItem::create($purchaseItem);
            }
            if (!$stage) DB::table('materials')->where('material_id', $material)->update(['cprice' => $price]);
        }
    }

    public function delete($id)
    {
    }

    /** Gross received quantity (incl. rejected / pending) per line of a purchase: purchase_item_id => qty. */
    public function receivedByLine(int $purchaseId): \Illuminate\Support\Collection
    {
        return DB::table('receive_materials')
            ->join('purchase_items', 'purchase_items.purchase_item_id', '=', 'receive_materials.purchase_item_id')
            ->where('purchase_items.purchase_id', $purchaseId)
            ->groupBy('receive_materials.purchase_item_id')
            ->selectRaw('receive_materials.purchase_item_id, SUM(receive_materials.quantity) as qty')
            ->pluck('qty', 'purchase_item_id')
            ->map(fn ($qty) => (float) $qty);
    }

    /**
     * D4: once something was received on a purchase line, the line keeps its item and price, its
     * quantity cannot go below what was received, and it cannot be removed. Lines are matched
     * the way update() matches them (material + product size + stage). Call inside the save's
     * transaction, after locking the purchase's lines.
     *
     * @throws \App\Exceptions\DocumentLockedException
     */
    public function assertReceivedLinesKept(int $purchaseId, array $data): void
    {
        $received = $this->receivedByLine($purchaseId)->filter(fn ($qty) => $qty > 0.0001);
        if ($received->isEmpty()) {
            return;
        }

        $new = [];
        foreach ((array) ($data['quantity'] ?? []) as $key => $quantity) {
            $lineKey = (int) ($data['material_id'][$key] ?? 0).'|'.(int) ($data['product_type_id'][$key] ?? 0).'|'.(int) ($data['product_stage_id'][$key] ?? 0);
            $new[$lineKey] = ['quantity' => (float) $quantity, 'price' => (float) ($data['price'][$key] ?? 0)];
        }

        $existing = PurchaseItem::where('purchase_id', $purchaseId)->whereIn('purchase_item_id', $received->keys())->get();
        foreach ($existing as $item) {
            $got = $received[$item->purchase_item_id];
            $lineKey = (int) $item->material_id.'|'.(int) $item->product_type_id.'|'.(int) $item->product_stage_id;
            $label = $this->lineLabel($item);
            $line = $new[$lineKey] ?? null;
            $problem = match (true) {
                $line === null => 'cannot be removed or changed to another item',
                abs($line['price'] - (float) $item->price) > 0.0001 => 'cannot change its price ('.$this->qty((float) $item->price).')',
                $line['quantity'] < $got - 0.0001 => 'cannot be ordered below what was received',
                default => null,
            };
            if ($problem) {
                throw new \App\Exceptions\DocumentLockedException(
                    "{$label}: {$this->qty($got)} already received, so this line {$problem}.".\App\Exceptions\DocumentLockedException::ASK_ADMIN
                );
            }
        }
    }

    private function lineLabel(PurchaseItem $item): string
    {
        if ((int) $item->material_id > 0) {
            return (string) (DB::table('materials')->where('material_id', $item->material_id)->value(DB::raw("CONCAT(material_no, ' - ', name)")) ?? 'Material #'.$item->material_id);
        }

        return app(StockBatchRepository::class)->productLabel((int) $item->product_type_id, (int) $item->product_stage_id);
    }

    private function qty(float $quantity): string
    {
        return rtrim(rtrim(number_format($quantity, 2, '.', ','), '0'), '.');
    }
}
