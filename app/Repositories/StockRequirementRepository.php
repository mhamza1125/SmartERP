<?php

namespace App\Repositories;

use App\Models\Stock;
use Illuminate\Support\Facades\DB;

/**
 * What still has to be made per product, from batch-tracked stock.
 *
 * Every confirmed order is covered first by its own reserved stock and the work in its open
 * PTCs; only the shortfall that is left draws on free stock and on PTCs made without an order.
 * Stock reserved for one order never covers another order.
 */
class StockRequirementRepository implements GlobalInterface
{
    public function __construct(
        protected StockBatchRepository $stockBatchRepository,
        protected StockItemRepository $stockItemRepository,
        protected PtcStockRepository $ptcStockRepository,
    ) {}

    public function all()
    {
        return collect($this->requirements());
    }

    public function get($id)
    {
        return $this->requirements()[$id] ?? null;
    }

    public function store(array $data) {}

    public function update($id, array $data) {}

    public function delete($id) {}

    /**
     * Pieces of a product still in production in one PTC (issued, not yet released).
     */
    public function ptcWorkInProgress($ptcId, $productTypeId): float
    {
        return (float) ($this->ptcStockRepository->workInProgress($ptcId)[$productTypeId] ?? 0);
    }

    /**
     * Confirmed orders with the quantity of a product still to deliver (ordered - delivered + returned),
     * oldest first: order_id, order_date, fname, lname, quantity.
     */
    public function openOrderQuantities($productTypeId): \Illuminate\Support\Collection
    {
        return DB::table('orders')
            ->join('order_items', 'order_items.order_id', '=', 'orders.order_id')
            ->leftJoin('customers', 'customers.customer_id', '=', 'orders.customer_id')
            ->where('orders.order_status', 2)
            ->where('order_items.product_type_id', $productTypeId)
            ->select('orders.order_id', 'orders.order_date', 'customers.fname', 'customers.lname')
            ->distinct()
            ->orderBy('orders.order_date')
            ->get()
            ->map(function ($order) use ($productTypeId) {
                $order->quantity = round((float) $this->stockItemRepository->orderRemainingItems($order->order_id)
                    ->where('product_type_id', $productTypeId)
                    ->sum(fn ($item) => max(0, (float) $item->remaining_quantity)), 4);

                return $order;
            })
            ->filter(fn ($order) => $order->quantity > 0)
            ->values();
    }

    /**
     * Per product type:
     *   current_stock   usable general stock (rejected excluded) = reserved_stock + free_stock
     *   in_production   pieces in open PTCs (issued, not yet released), with or without an order
     *   open_orders     still to deliver on confirmed orders (ordered - delivered + returned)
     *   total_needed    sum of each confirmed order's shortfall (after its own reserved stock and
     *                   PTCs) minus free stock and free PTC work; negative = more than needed
     *
     * @return array<int, array<string, float>>
     */
    public function requirements(): array
    {
        $balances = $this->stockBatchRepository->balances()
            ->filter(fn ($row) => (int) $row->stage_id !== Stock::STAGE_REJECTED);

        $rows = [];
        $add = function ($productTypeId, string $field, float $quantity) use (&$rows) {
            $rows[$productTypeId] ??= ['current_stock' => 0, 'reserved_stock' => 0, 'free_stock' => 0, 'in_production' => 0, 'open_orders' => 0, 'shortfall' => 0];
            $rows[$productTypeId][$field] += $quantity;
        };

        foreach ($balances as $row) {
            $add($row->product_type_id, 'current_stock', (float) $row->balance);
            $add($row->product_type_id, $row->order_id === null ? 'free_stock' : 'reserved_stock', (float) $row->balance);
        }

        $reserved = $balances->whereNotNull('order_id')->groupBy('order_id');
        foreach (DB::table('orders')->where('order_status', 2)->pluck('order_id') as $orderId) {
            $workInProgress = $this->ptcStockRepository->orderWorkInProgress($orderId);
            $orderReserved = ($reserved[$orderId] ?? collect())->groupBy('product_type_id')->map(fn ($group) => $group->sum('balance'));

            foreach ($workInProgress as $productTypeId => $quantity) {
                $add($productTypeId, 'in_production', $quantity);
            }

            $remaining = $this->stockItemRepository->orderRemainingItems($orderId)
                ->groupBy('product_type_id')
                ->map(fn ($group) => $group->sum(fn ($item) => max(0, (float) $item->remaining_quantity)));

            foreach ($remaining as $productTypeId => $quantity) {
                $covered = (float) ($orderReserved[$productTypeId] ?? 0) + (float) ($workInProgress[$productTypeId] ?? 0);
                $add($productTypeId, 'open_orders', $quantity);
                $add($productTypeId, 'shortfall', max(0, $quantity - $covered));
            }
        }

        // PTCs made without an order produce free stock
        foreach ($this->ptcStockRepository->orderWorkInProgress(0) as $productTypeId => $quantity) {
            $add($productTypeId, 'in_production', $quantity);
            $add($productTypeId, 'shortfall', -$quantity);
        }

        return array_map(function ($row) {
            $row['total_needed'] = $row['shortfall'] - $row['free_stock'];
            unset($row['shortfall']);

            return array_map(fn ($value) => round($value, 4), $row);
        }, $rows);
    }
}
