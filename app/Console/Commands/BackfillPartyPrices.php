<?php

namespace App\Console\Commands;

use App\Models\PartyPrice;
use App\Models\ProductCostLog;
use App\Repositories\PartyPriceRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * One-off fill of the vendor / customer price register and the wage history from existing data:
 *  - vendor prices: every purchase line (material processing left out), oldest first; each change of
 *    a vendor's price for an item is logged with the purchase date, the last one becomes current
 *  - customer prices: the same from non-cancelled order lines
 *  - wages: one 'opening' history row per product_costs row that has no history yet
 * Safe to run again: items that already have a price, and wages that already have history, are skipped.
 */
class BackfillPartyPrices extends Command
{
    protected $signature = 'prices:backfill {--dry-run : Only count what would be written}';

    protected $description = 'Fill vendor / customer prices and wage history from existing purchases, orders and product wages';

    public function handle(PartyPriceRepository $prices): int
    {
        $dry = (bool) $this->option('dry-run');
        activity()->disableLogging(); // the history tables are the record; no audit entry per replayed line

        $purchaseLines = DB::table('purchase_items')
            ->join('purchases', 'purchases.purchase_id', '=', 'purchase_items.purchase_id')
            ->leftJoin('mprocess', 'mprocess.purchase_item_id', '=', 'purchase_items.purchase_item_id')
            ->whereNull('mprocess.purchase_item_id')
            ->where('purchases.vendor_id', '>', 0)
            ->where('purchase_items.price', '>', 0)
            ->orderBy('purchases.purchase_date')->orderBy('purchase_items.purchase_item_id')
            ->get(['purchases.vendor_id as party_id', 'purchases.purchase_id as doc_id', 'purchases.purchase_date as doc_date',
                'purchase_items.material_id', 'purchase_items.product_type_id', 'purchase_items.product_stage_id as stage_id', 'purchase_items.price']);

        $orderLines = DB::table('order_items')
            ->join('orders', 'orders.order_id', '=', 'order_items.order_id')
            ->where('orders.order_status', '!=', \App\Models\Order::STATUS_CANCELLED)
            ->where('orders.customer_id', '>', 0)
            ->where('order_items.price', '>', 0)
            ->orderBy('orders.order_date')->orderBy('order_items.order_item_id')
            ->get(['orders.customer_id as party_id', 'orders.order_id as doc_id', 'orders.order_date as doc_date',
                'order_items.product_type_id', 'order_items.product_stage_id as stage_id', 'order_items.price']);

        $vendor = $this->fill($prices, PartyPrice::PARTY_VENDOR, PartyPrice::SOURCE_PURCHASE, $purchaseLines, $dry);
        $customer = $this->fill($prices, PartyPrice::PARTY_CUSTOMER, PartyPrice::SOURCE_ORDER, $orderLines, $dry);
        $wages = $this->wageOpening($dry);

        $this->table(['What', 'Items', 'History rows'], [
            ['Vendor prices', $vendor[0], $vendor[1]],
            ['Customer prices', $customer[0], $customer[1]],
            ['Wage history (opening)', '-', $wages],
        ]);
        $this->info($dry ? 'Dry run: nothing was written.' : 'Done.');

        return self::SUCCESS;
    }

    /** Replays the lines oldest first per party + item. Returns [items, history rows]. */
    private function fill(PartyPriceRepository $prices, string $partyType, string $source, $lines, bool $dry): array
    {
        $existing = DB::table('party_prices')->where('party_type', $partyType)
            ->get(['party_id', 'material_id', 'product_type_id', 'stage_id'])
            ->map(fn ($r) => $r->party_id.'|'.$r->material_id.'-'.$r->product_type_id.'-'.$r->stage_id)
            ->flip();

        $items = 0;
        $logs = 0;
        $groups = collect($lines)->groupBy(function ($l) use ($prices) {
            return $l->party_id.'|'.implode('-', $prices->itemKey($l->material_id ?? 0, $l->product_type_id ?? 0, $l->stage_id ?? 0));
        });

        DB::transaction(function () use ($groups, $existing, $prices, $partyType, $source, $dry, &$items, &$logs) {
            foreach ($groups as $groupKey => $groupLines) {
                if ($existing->has($groupKey)) {
                    continue;
                }
                $first = $groupLines->first();
                $key = $prices->itemKey($first->material_id ?? 0, $first->product_type_id ?? 0, $first->stage_id ?? 0);
                if ($key['material_id'] <= 0 && $key['product_type_id'] <= 0) {
                    continue;
                }
                $items++;
                $last = null;
                foreach ($groupLines as $line) {
                    $price = round((float) $line->price, 3);
                    if ($last !== null && $price === $last) {
                        continue;
                    }
                    $logs++;
                    $last = $price;
                    if (! $dry) {
                        $prices->set($partyType, (int) $line->party_id, $key, $price, $source, (int) $line->doc_id,
                            Carbon::parse($line->doc_date)->startOfDay());
                    }
                }
            }
        });

        return [$items, $logs];
    }

    private function wageOpening(bool $dry): int
    {
        $rows = DB::table('product_costs')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))->from('product_cost_logs')
                    ->whereColumn('product_cost_logs.product_id', 'product_costs.product_id')
                    ->whereColumn('product_cost_logs.head_id', 'product_costs.head_id')
                    ->whereRaw("product_cost_logs.table_name = COALESCE(NULLIF(product_costs.table_name, ''), 'general')")
                    ->whereRaw('product_cost_logs.table_id = COALESCE(product_costs.table_id, 0)');
            })
            ->whereNotNull('product_costs.product_id')
            ->get();

        if (! $dry) {
            foreach ($rows as $row) {
                $at = $row->updated_at ?? $row->created_at;
                ProductCostLog::create([
                    'product_id' => $row->product_id,
                    'head_id' => $row->head_id,
                    'table_name' => $row->table_name ?: 'general',
                    'table_id' => (int) $row->table_id,
                    'old_amount' => null,
                    'amount' => $row->amount,
                    'action' => ProductCostLog::ACTION_OPENING,
                    'created_by' => $row->created_by,
                    'created_at' => $at,
                    'updated_at' => $at,
                ]);
            }
        }

        return $rows->count();
    }
}
