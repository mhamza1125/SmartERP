<?php

namespace App\Repositories;

use App\Models\PartyPrice;
use App\Models\PartyPriceLog;
use Illuminate\Support\Facades\DB;

/**
 * Vendor (buying) and customer (selling) prices per item.
 *
 * An item is a material (material_id) or a product size at a stage (product_type_id + stage_id);
 * unused keys are stored as 0. party_prices holds the current price, party_price_logs every change.
 *
 * Prices are set by hand on the material / product page (source manual) and follow the documents:
 * a purchase line updates its vendor's price, an order line its customer's price, whenever the
 * line uses a price other than the current one (higher or lower). On edits only lines that are new,
 * changed price or moved to another party count, so re-saving an old document does not overwrite
 * a newer price. Material processing lines are never used.
 */
class PartyPriceRepository implements GlobalInterface
{
    public function all()
    {
        return PartyPrice::orderBy('party_type')->orderBy('party_id')->get();
    }

    public function get($id)
    {
        return PartyPrice::findOrFail($id);
    }

    /**
     * Manual price from the material / product page.
     * $data: party_type, party_id, material_id | product_type_id + stage_id, price.
     */
    public function store(array $data)
    {
        $this->set(
            $data['party_type'],
            (int) $data['party_id'],
            $this->itemKey($data['material_id'] ?? 0, $data['product_type_id'] ?? 0, $data['stage_id'] ?? 0),
            (float) $data['price'],
            PartyPrice::SOURCE_MANUAL
        );

        return $this->current($data['party_type'], (int) $data['party_id'], $data['material_id'] ?? 0, $data['product_type_id'] ?? 0, $data['stage_id'] ?? 0)?->party_price_id;
    }

    /** Manual change of an existing price (only the price can change). */
    public function update($id, array $data)
    {
        $row = PartyPrice::findOrFail($id);
        $this->set($row->party_type, (int) $row->party_id, $this->keyOf($row), (float) $data['price'], PartyPrice::SOURCE_MANUAL);

        return $row->party_price_id;
    }

    /** Removes a price; the removal is kept in the history. */
    public function delete($id)
    {
        return DB::transaction(function () use ($id) {
            $row = PartyPrice::lockForUpdate()->findOrFail($id);
            $this->log($row->party_type, (int) $row->party_id, $this->keyOf($row), (float) $row->price, null, PartyPrice::SOURCE_REMOVED, null);

            return $row->delete();
        });
    }

    /**
     * Current price row of one item for one party, or null.
     */
    public function current(string $partyType, int $partyId, $materialId = 0, $productTypeId = 0, $stageId = 0): ?PartyPrice
    {
        $key = $this->itemKey($materialId, $productTypeId, $stageId);

        return PartyPrice::where('party_type', $partyType)->where('party_id', $partyId)
            ->where('material_id', $key['material_id'])
            ->where('product_type_id', $key['product_type_id'])
            ->where('stage_id', $key['stage_id'])
            ->first();
    }

    /**
     * Lookup for the purchase / order forms: the party's price, plus the other stages of the
     * same product size when the exact stage has none (so a price is never hidden by the stage).
     */
    public function lookup(string $partyType, int $partyId, $materialId = 0, $productTypeId = 0, $stageId = 0): array
    {
        $row = $this->current($partyType, $partyId, $materialId, $productTypeId, $stageId);
        $others = collect();
        if (! $row && (int) $productTypeId > 0) {
            $others = PartyPrice::where('party_prices.party_type', $partyType)->where('party_prices.party_id', $partyId)
                ->where('party_prices.product_type_id', (int) $productTypeId)
                ->leftJoin('heads as stage', 'stage.head_id', '=', 'party_prices.stage_id')
                ->select('party_prices.price', 'party_prices.updated_at', 'stage.name as stage_name')
                ->get()
                ->map(fn ($r) => ['price' => (float) $r->price, 'stage' => $r->stage_name, 'date' => formatDate($r->updated_at)]);
        }

        return [
            'found' => (bool) $row,
            'price' => $row ? (float) $row->price : null,
            'date' => $row ? formatDate($row->updated_at) : null,
            'source' => $row?->source,
            'other_stages' => $others->values()->all(),
        ];
    }

    /**
     * Sets a price. Creates the row or changes it, logging the change; nothing happens when the
     * price is unchanged. Returns true when something changed.
     */
    public function set(string $partyType, int $partyId, array $key, float $price, string $source, ?int $docId = null, $at = null): bool
    {
        if ($partyId <= 0 || ($key['material_id'] <= 0 && $key['product_type_id'] <= 0)) {
            return false;
        }

        return DB::transaction(function () use ($partyType, $partyId, $key, $price, $source, $docId, $at) {
            $row = PartyPrice::where('party_type', $partyType)->where('party_id', $partyId)
                ->where($key)->lockForUpdate()->first();
            $old = $row ? (float) $row->price : null;
            if ($row && round($old, 3) === round($price, 3)) {
                return false;
            }

            $values = ['price' => $price, 'source' => $source, 'doc_id' => $docId, 'updated_by' => auth()->id()];
            if ($row) {
                $row->fill($values);
                if ($at) {
                    $row->updated_at = $at;
                }
                $row->save();
            } else {
                $row = new PartyPrice(array_merge(['party_type' => $partyType, 'party_id' => $partyId], $key, $values));
                if ($at) {
                    $row->created_at = $at;
                    $row->updated_at = $at;
                }
                $row->save();
            }
            $this->log($partyType, $partyId, $key, $old, $price, $source, $docId, $at);

            return true;
        });
    }

    /**
     * State of a purchase / order before an edit: party + line prices by item. Passed to
     * syncPurchase() / syncOrder() so only changed lines move the party price.
     */
    public function snapshot(string $document, int $docId): array
    {
        return $document === 'purchase' ? $this->purchaseLines($docId) : $this->orderLines($docId);
    }

    /** Applies a saved purchase's line prices to its vendor's prices. */
    public function syncPurchase(int $purchaseId, ?array $before = null): void
    {
        $this->syncDocument(PartyPrice::PARTY_VENDOR, PartyPrice::SOURCE_PURCHASE, $purchaseId, $this->purchaseLines($purchaseId), $before);
    }

    /** Applies a saved order's line prices to its customer's prices. */
    public function syncOrder(int $orderId, ?array $before = null): void
    {
        $this->syncDocument(PartyPrice::PARTY_CUSTOMER, PartyPrice::SOURCE_ORDER, $orderId, $this->orderLines($orderId), $before);
    }

    private function syncDocument(string $partyType, string $source, int $docId, array $after, ?array $before): void
    {
        if (! $after['party_id']) {
            return;
        }
        $sameParty = $before && (int) $before['party_id'] === (int) $after['party_id'];
        foreach ($after['lines'] as $id => $line) {
            if ($line['price'] <= 0) {
                continue;
            }
            $previous = $sameParty ? ($before['lines'][$id]['price'] ?? null) : null;
            if ($previous !== null && round($previous, 3) === round($line['price'], 3)) {
                continue; // unchanged line of an edited document
            }
            $this->set($partyType, (int) $after['party_id'], $line['key'], $line['price'], $source, $docId);
        }
    }

    private function purchaseLines(int $purchaseId): array
    {
        $partyId = (int) DB::table('purchases')->where('purchase_id', $purchaseId)->value('vendor_id');
        $lines = DB::table('purchase_items')
            ->leftJoin('mprocess', 'mprocess.purchase_item_id', '=', 'purchase_items.purchase_item_id')
            ->where('purchase_items.purchase_id', $purchaseId)
            ->whereNull('mprocess.purchase_item_id')
            ->orderBy('purchase_items.purchase_item_id')
            ->get(['purchase_items.material_id', 'purchase_items.product_type_id', 'purchase_items.product_stage_id', 'purchase_items.price']);

        return ['party_id' => $partyId, 'lines' => $this->keyLines($lines, 'product_stage_id')];
    }

    private function orderLines(int $orderId): array
    {
        $partyId = (int) DB::table('orders')->where('order_id', $orderId)->value('customer_id');
        $lines = DB::table('order_items')->where('order_id', $orderId)->orderBy('order_item_id')
            ->get(['product_type_id', 'product_stage_id', 'price']);

        return ['party_id' => $partyId, 'lines' => $this->keyLines($lines, 'product_stage_id')];
    }

    /** Lines keyed by item; a repeated item keeps its last line. */
    private function keyLines($lines, string $stageColumn): array
    {
        $out = [];
        foreach ($lines as $line) {
            $key = $this->itemKey($line->material_id ?? 0, $line->product_type_id ?? 0, $line->{$stageColumn} ?? 0);
            $out[implode('-', $key)] = ['key' => $key, 'price' => (float) $line->price];
        }

        return $out;
    }

    /** Normalised item key: a material has no product / stage, a product no material. */
    public function itemKey($materialId, $productTypeId, $stageId): array
    {
        $materialId = (int) $materialId;
        if ($materialId > 0) {
            return ['material_id' => $materialId, 'product_type_id' => 0, 'stage_id' => 0];
        }

        return ['material_id' => 0, 'product_type_id' => (int) $productTypeId, 'stage_id' => (int) $stageId];
    }

    private function keyOf($row): array
    {
        return $this->itemKey($row->material_id, $row->product_type_id, $row->stage_id);
    }

    private function log(string $partyType, int $partyId, array $key, ?float $old, ?float $price, string $source, ?int $docId, $at = null): void
    {
        $log = new PartyPriceLog(array_merge([
            'party_type' => $partyType,
            'party_id' => $partyId,
            'old_price' => $old,
            'price' => $price,
            'source' => $source,
            'doc_id' => $docId,
            'created_by' => auth()->id(),
        ], $key));
        if ($at) {
            $log->created_at = $at;
            $log->updated_at = $at;
        }
        $log->save();
    }

    // ---------------------------------------------------------------- deletion of the owner

    /**
     * Prices (current rows) belonging to a vendor / customer, material or product being deleted.
     * $owner: ['party', type, id] | ['material', id] | ['product', product_id].
     */
    public function countFor(array $owner): int
    {
        return $this->ownedBy(DB::table('party_prices'), $owner)->count();
    }

    /** Deletes the owner's prices and their history (called inside the owner's delete). */
    public function deleteFor(array $owner): void
    {
        $this->ownedBy(DB::table('party_price_logs'), $owner)->delete();
        $this->ownedBy(DB::table('party_prices'), $owner)->delete();
    }

    private function ownedBy($query, array $owner)
    {
        return match ($owner[0]) {
            'party' => $query->where('party_type', $owner[1])->where('party_id', $owner[2]),
            'material' => $query->where('material_id', $owner[1])->where('material_id', '>', 0),
            'product' => $query->whereIn('product_type_id', DB::table('product_types')->where('product_id', $owner[1])->select('product_type_id')),
        };
    }

    // ---------------------------------------------------------------- display

    /**
     * Data of the partyPriceList partial (vendor / customer page): the party's current prices, its
     * price changes and the items for the Set Price form (materials for vendors only).
     */
    public function partySection(string $partyType, int $partyId): array
    {
        $productTypes = app(ProductRepository::class)->activeTypes();
        $materials = collect();

        // A vendor's Set Price form lists only the vendor's own items (client request 2026-11-24)
        if ($partyType === PartyPrice::PARTY_VENDOR) {
            $items = $this->vendorItemIds($partyId);
            $materials = app(MaterialRepository::class)->all()
                ->filter(fn ($m) => in_array((int) $m->material_id, $items['material_ids'], true))
                ->sortBy('material_no')->values();
            $productTypes = $productTypes
                ->filter(fn ($t) => in_array((int) $t->product_id, $items['product_ids'], true)
                    || in_array((int) $t->product_type_id, $items['product_type_ids'], true))
                ->values();
        }

        return [
            'partyType' => $partyType,
            'partyId' => $partyId,
            'current' => $this->currentForParty($partyType, $partyId),
            'changes' => $this->changes($partyType, $partyId),
            'materials' => $materials,
            'productTypes' => $productTypes,
            'stagesByType' => app(RejectionRepository::class)->stagesForTypes($productTypes),
        ];
    }

    /**
     * Items a vendor supplies: the materials / products chosen on the vendor (vendors.material_id /
     * product_id, '|' separated), materials whose main vendor it is, and items it already has a price for.
     */
    public function vendorItemIds(int $vendorId): array
    {
        $vendor = DB::table('vendors')->where('vendor_id', $vendorId)->first(['material_id', 'product_id']);
        $ids = fn ($list) => array_values(array_filter(array_map('intval', explode('|', (string) $list))));
        $priced = DB::table('party_prices')->where('party_type', PartyPrice::PARTY_VENDOR)->where('party_id', $vendorId)
            ->get(['material_id', 'product_type_id']);

        return [
            'material_ids' => array_values(array_unique(array_merge(
                $ids($vendor->material_id ?? ''),
                DB::table('materials')->where('vendor_id', $vendorId)->pluck('material_id')->map(fn ($id) => (int) $id)->all(),
                $priced->where('material_id', '>', 0)->pluck('material_id')->map(fn ($id) => (int) $id)->all()
            ))),
            'product_ids' => $ids($vendor->product_id ?? ''),
            'product_type_ids' => $priced->where('product_type_id', '>', 0)->pluck('product_type_id')->map(fn ($id) => (int) $id)->values()->all(),
        ];
    }

    /**
     * Data of the price list print (print/party-prices): the vendor / customer and its current prices.
     */
    public function printData(string $partyType, int $partyId): array
    {
        $party = $partyType === PartyPrice::PARTY_VENDOR
            ? DB::table('vendors')->leftJoin('heads as city', 'city.head_id', '=', 'vendors.city_id')
                ->where('vendor_id', $partyId)
                ->select('vendors.vendor_no as party_no', 'vendors.fname as party_name', 'vendors.phone1 as phone', 'vendors.address', 'city.name as place', DB::raw('NULL as currency_name'))
                ->first()
            : DB::table('customers')->leftJoin('heads as country', 'country.head_id', '=', 'customers.country_id')
                ->leftJoin('heads as currency', 'currency.head_id', '=', 'customers.currency_id')
                ->where('customer_id', $partyId)
                ->select('customers.customer_no as party_no', DB::raw("TRIM(CONCAT(COALESCE(customers.fname, ''), ' ', COALESCE(customers.lname, ''))) as party_name"),
                    'customers.phone', 'customers.address', 'country.name as place', 'currency.name as currency_name')
                ->first();
        abort_unless($party, 404);

        return [
            'partyType' => $partyType,
            'party' => $party,
            'prices' => $this->currentForParty($partyType, $partyId)
                ->sortBy(fn ($r) => [$r->item_type === 'material' ? 0 : 1, $r->item_name, $r->size_name, $r->stage_name])
                ->values(),
        ];
    }

    /**
     * Current prices of one vendor / customer, every item (materials and product sizes), with names.
     */
    public function currentForParty(string $partyType, int $partyId)
    {
        return $this->withItemNames(PartyPrice::query(), 'party_prices', $partyType)
            ->where('party_prices.party_id', $partyId)
            ->addSelect('party_prices.party_price_id', 'party_prices.price', 'party_prices.source', 'party_prices.doc_id', 'party_prices.updated_at')
            ->orderBy('item_name')
            ->get();
    }

    /**
     * Price changes, newest first: of one vendor / customer ($partyId), or of every vendor / customer
     * (dashboard, $limit rows).
     */
    public function changes(string $partyType, ?int $partyId = null, ?int $limit = null)
    {
        return $this->changesQuery($partyType, ['party_id' => $partyId])
            ->when($limit, fn ($q) => $q->limit($limit))
            ->get();
    }

    /**
     * Price Change History page: changes of every vendor / customer, newest first, $perPage a page.
     * $filters: item ('material:{id}' | 'product:{product_id}', every size / stage), party_id,
     * user_id (who made the change), dfrom + dto (Y-m-d).
     */
    public function changeLog(string $partyType, array $filters, int $perPage = 50)
    {
        return $this->changesQuery($partyType, $filters)->paginate($perPage)->withQueryString();
    }

    private function changesQuery(string $partyType, array $filters)
    {
        [$itemType, $itemId] = array_pad(explode(':', (string) ($filters['item'] ?? ''), 2), 2, 0);
        $itemId = (int) $itemId;

        return $this->withItemNames(PartyPriceLog::query(), 'party_price_logs', $partyType)
            ->when(! empty($filters['party_id']), fn ($q) => $q->where('party_price_logs.party_id', (int) $filters['party_id']))
            ->when(! empty($filters['user_id']), fn ($q) => $q->where('party_price_logs.created_by', (int) $filters['user_id']))
            ->when($itemType === 'material' && $itemId, fn ($q) => $q->where('party_price_logs.material_id', $itemId))
            ->when($itemType === 'product' && $itemId, fn ($q) => $q->where('party_price_logs.product_type_id', '>', 0)->where('product_types.product_id', $itemId))
            ->when(! empty($filters['dfrom']), fn ($q) => $q->where('party_price_logs.created_at', '>=', $filters['dfrom'].' 00:00:00'))
            ->when(! empty($filters['dto']), fn ($q) => $q->where('party_price_logs.created_at', '<=', $filters['dto'].' 23:59:59'))
            ->leftJoin('users', 'users.id', '=', 'party_price_logs.created_by')
            ->addSelect('party_price_logs.party_price_log_id', 'party_price_logs.old_price', 'party_price_logs.price',
                'party_price_logs.source', 'party_price_logs.doc_id', 'party_price_logs.created_at', 'users.name as user_name')
            ->orderBy('party_price_logs.created_at', 'desc')
            ->orderBy('party_price_logs.party_price_log_id', 'desc');
    }

    /**
     * Choices of the Price Change History filters: only the products / materials, vendors / customers
     * and users that appear in the changes. Each a collection of {value, label}.
     */
    public function changeFilterOptions(string $partyType): array
    {
        $logs = fn () => DB::table('party_price_logs')->where('party_price_logs.party_type', $partyType);

        $products = $logs()->where('party_price_logs.product_type_id', '>', 0)
            ->join('product_types', 'product_types.product_type_id', '=', 'party_price_logs.product_type_id')
            ->join('products', 'products.product_id', '=', 'product_types.product_id')
            ->distinct()
            ->orderBy('products.article_no')
            ->get(['products.product_id', 'products.article_no', 'products.name'])
            ->map(fn ($r) => (object) ['value' => 'product:'.$r->product_id, 'label' => $r->article_no.' - '.$r->name]);
        $materials = $logs()->where('party_price_logs.material_id', '>', 0)
            ->join('materials', 'materials.material_id', '=', 'party_price_logs.material_id')
            ->distinct()
            ->orderBy('materials.material_no')
            ->get(['materials.material_id', 'materials.material_no', 'materials.name'])
            ->map(fn ($r) => (object) ['value' => 'material:'.$r->material_id, 'label' => $r->material_no.' - '.$r->name]);

        $parties = $partyType === PartyPrice::PARTY_VENDOR
            ? $logs()->join('vendors', 'vendors.vendor_id', '=', 'party_price_logs.party_id')->distinct()
                ->orderBy('vendors.vendor_no')->get(['vendors.vendor_id as id', 'vendors.vendor_no as no', 'vendors.fname as name'])
            : $logs()->join('customers', 'customers.customer_id', '=', 'party_price_logs.party_id')->distinct()
                ->orderBy('customers.customer_no')
                ->get(['customers.customer_id as id', 'customers.customer_no as no', DB::raw("TRIM(CONCAT(COALESCE(customers.fname, ''), ' ', COALESCE(customers.lname, ''))) as name")]);

        return [
            'products' => $products,
            'materials' => $materials,
            'parties' => $parties->map(fn ($r) => (object) ['value' => $r->id, 'label' => $r->no.($r->name ? ' - '.$r->name : '')]),
            'users' => $logs()->join('users', 'users.id', '=', 'party_price_logs.created_by')->distinct()
                ->orderBy('users.name')->get(['users.id', 'users.name'])
                ->map(fn ($r) => (object) ['value' => $r->id, 'label' => $r->name]),
        ];
    }

    /**
     * Party / item / document names for prices of any item: item_type (material | product), item_id
     * (material_id / product_id), item_name, size_name, stage_name, party_no, party_name, doc_no.
     */
    private function withItemNames($query, string $table, string $partyType)
    {
        $query->where("$table.party_type", $partyType)
            ->leftJoin('materials', function ($join) use ($table) {
                $join->on('materials.material_id', '=', "$table.material_id")->where("$table.material_id", '>', 0);
            })
            ->leftJoin('heads as munit', 'munit.head_id', '=', 'materials.unit_id')
            ->leftJoin('product_types', 'product_types.product_type_id', '=', "$table.product_type_id")
            ->leftJoin('products', 'products.product_id', '=', 'product_types.product_id')
            ->leftJoin('heads as size', 'size.head_id', '=', 'product_types.size_id')
            ->leftJoin('heads as stage', 'stage.head_id', '=', "$table.stage_id")
            ->select("$table.party_id", "$table.material_id", "$table.product_type_id", "$table.stage_id",
                'size.name as size_name', 'stage.name as stage_name', 'munit.name as unit_name',
                DB::raw("CASE WHEN $table.material_id > 0 THEN 'material' ELSE 'product' END as item_type"),
                DB::raw("CASE WHEN $table.material_id > 0 THEN materials.material_id ELSE products.product_id END as item_id"),
                DB::raw("CASE WHEN $table.material_id > 0 THEN CONCAT(materials.material_no, ' - ', materials.name)
                    ELSE CONCAT(products.article_no, ' - ', products.name) END as item_name"));

        if ($partyType === PartyPrice::PARTY_VENDOR) {
            $query->leftJoin('vendors', 'vendors.vendor_id', '=', "$table.party_id")
                ->leftJoin('purchases', function ($join) use ($table) {
                    $join->on('purchases.purchase_id', '=', "$table.doc_id")->where("$table.source", PartyPrice::SOURCE_PURCHASE);
                })
                ->addSelect('vendors.vendor_no as party_no', 'vendors.fname as party_name', 'purchases.purchase_no as doc_no');
        } else {
            $query->leftJoin('customers', 'customers.customer_id', '=', "$table.party_id")
                ->leftJoin('heads as currency', 'currency.head_id', '=', 'customers.currency_id')
                ->leftJoin('orders', function ($join) use ($table) {
                    $join->on('orders.order_id', '=', "$table.doc_id")->where("$table.source", PartyPrice::SOURCE_ORDER);
                })
                ->addSelect('customers.customer_no as party_no', DB::raw("TRIM(CONCAT(COALESCE(customers.fname, ''), ' ', COALESCE(customers.lname, ''))) as party_name"),
                    'currency.name as currency_name', 'orders.job_no as doc_no');
        }

        return $query;
    }

    /**
     * Data of the partyPrices partial: current prices, history and the vendors / customers for
     * the Set Price form.
     */
    public function section(string $partyType, string $itemType, int $itemId): array
    {
        return [
            'partyType' => $partyType,
            'itemType' => $itemType,
            'itemId' => $itemId,
            'current' => $this->currentFor($partyType, $itemType, $itemId),
            'history' => $this->historyFor($partyType, $itemType, $itemId),
            'parties' => $partyType === PartyPrice::PARTY_VENDOR
                ? app(VendorRepository::class)->vendor()
                : app(CustomerRepository::class)->all(),
        ];
    }

    /**
     * Current prices of a material (vendors) or of a product's sizes (vendors or customers),
     * with party / size / stage names and the document that set them. Lowest price first per item.
     */
    public function currentFor(string $partyType, string $itemType, int $itemId)
    {
        return $this->withNames(PartyPrice::query(), 'party_prices', $partyType, $itemType, $itemId)
            ->addSelect('party_prices.party_price_id', 'party_prices.price', 'party_prices.source', 'party_prices.doc_id', 'party_prices.updated_at')
            ->orderBy('size.name')
            ->orderBy('stage.name')
            ->orderBy('party_prices.price')
            ->get();
    }

    /** Change history of a material's / product's prices for vendors or customers, newest first. */
    public function historyFor(string $partyType, string $itemType, int $itemId)
    {
        return $this->withNames(PartyPriceLog::query(), 'party_price_logs', $partyType, $itemType, $itemId)
            ->leftJoin('users', 'users.id', '=', 'party_price_logs.created_by')
            ->addSelect('party_price_logs.party_price_log_id', 'party_price_logs.old_price', 'party_price_logs.price',
                'party_price_logs.source', 'party_price_logs.doc_id', 'party_price_logs.created_at', 'users.name as user_name')
            ->orderBy('party_price_logs.created_at', 'desc')
            ->orderBy('party_price_logs.party_price_log_id', 'desc')
            ->get();
    }

    private function withNames($query, string $table, string $partyType, string $itemType, int $itemId)
    {
        $query->where("$table.party_type", $partyType)
            ->leftJoin('product_types', 'product_types.product_type_id', '=', "$table.product_type_id")
            ->leftJoin('heads as size', 'size.head_id', '=', 'product_types.size_id')
            ->leftJoin('heads as stage', 'stage.head_id', '=', "$table.stage_id")
            ->select("$table.party_id", "$table.product_type_id", "$table.stage_id", 'size.name as size_name', 'stage.name as stage_name');

        if ($itemType === 'material') {
            $query->where("$table.material_id", $itemId);
        } else {
            $query->where("$table.product_type_id", '>', 0)->where('product_types.product_id', $itemId);
        }

        if ($partyType === PartyPrice::PARTY_VENDOR) {
            $query->leftJoin('vendors', 'vendors.vendor_id', '=', "$table.party_id")
                ->leftJoin('purchases', function ($join) use ($table) {
                    $join->on('purchases.purchase_id', '=', "$table.doc_id")
                        ->where("$table.source", PartyPrice::SOURCE_PURCHASE);
                })
                ->addSelect('vendors.vendor_no as party_no', 'vendors.fname as party_name', 'purchases.purchase_no as doc_no');
        } else {
            $query->leftJoin('customers', 'customers.customer_id', '=', "$table.party_id")
                ->leftJoin('heads as currency', 'currency.head_id', '=', 'customers.currency_id')
                ->leftJoin('orders', function ($join) use ($table) {
                    $join->on('orders.order_id', '=', "$table.doc_id")
                        ->where("$table.source", PartyPrice::SOURCE_ORDER);
                })
                ->addSelect('customers.customer_no as party_no', DB::raw("TRIM(CONCAT(COALESCE(customers.fname, ''), ' ', COALESCE(customers.lname, ''))) as party_name"),
                    'currency.name as currency_name', 'orders.job_no as doc_no');
        }

        return $query;
    }
}
