<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * The figures of a commercial / proforma invoice, in one place: the print views and the Excel
 * export both use these, so the two always show the same totals.
 */
final class InvoiceTotals
{
    /**
     * Commercial invoice of a delivery: items at their order price, plus the delivery's freight;
     * a partial payment request (payment_percent < 100) adds the amount due now.
     *
     * @param  Collection  $items  StockItemRepository::delivery() lines (quantity, price, cname)
     * @param  array|object  $delivery  DeliveryRepository::get() (freight_charges, payment_terms)
     * @param  array  $relatedOrders  orders of a combined (multi-order) delivery (payment_terms)
     */
    public static function commercial(Collection $items, $delivery, ?string $sellingType, float $paymentPercent, bool $isMultiOrder = false, array $relatedOrders = []): array
    {
        $delivery = self::toArray($delivery);
        $itemsTotal = (float) $items->sum(fn ($item) => self::lineTotal($item));
        $freight = (float) ($delivery['freight_charges'] ?? 0);
        $grandTotal = $itemsTotal + $freight;
        $paymentPercent = round($paymentPercent ?: 100, 2);

        $paymentTerms = ($isMultiOrder && ! empty($relatedOrders))
            ? collect($relatedOrders)->map(fn ($order) => trim((string) (((array) $order)['payment_terms'] ?? '')))->filter()->unique()->implode('; ')
            : trim((string) ($delivery['payment_terms'] ?? ''));

        return [
            'totalQuantity' => (float) $items->sum('quantity'),
            'itemsTotal' => $itemsTotal,
            'freightCharges' => $freight,
            'grandTotal' => $grandTotal,
            'currencyName' => $items->first()->cname ?? 'PKR',
            'totalLabel' => self::totalLabel($sellingType),
            'paymentPercent' => $paymentPercent,
            'isPartialPayment' => $paymentPercent < 100,
            'percentLabel' => rtrim(rtrim(number_format($paymentPercent, 2), '0'), '.').'%',
            'amountDue' => round($grandTotal * $paymentPercent / 100, 2),
            'paymentTerms' => $paymentTerms,
        ];
    }

    /**
     * Proforma invoice of an order: the order lines at their price.
     *
     * @param  Collection  $items  OrderItemRepository::get() lines (quantity, price, cname)
     */
    public static function proforma(Collection $items, ?string $sellingType): array
    {
        $total = (float) $items->sum(fn ($item) => self::lineTotal($item));

        return [
            'totalQuantity' => (float) $items->sum('quantity'),
            'itemsTotal' => $total,
            'grandTotal' => $total,
            'currencyName' => $items->first()->cname ?? 'PKR',
            'totalLabel' => self::totalLabel($sellingType),
        ];
    }

    /**
     * Attributes of a model / object / array as an array (repositories return Eloquent models).
     */
    public static function toArray($record): array
    {
        if ($record === null) {
            return [];
        }
        if (is_object($record) && method_exists($record, 'toArray')) {
            return $record->toArray();
        }

        return (array) $record;
    }

    public static function lineTotal($item): float
    {
        return (float) $item->quantity * (float) ($item->price ?? 0);
    }

    private static function totalLabel(?string $sellingType): string
    {
        return ! empty($sellingType) ? 'Total '.$sellingType.' Amount:' : 'Grand Total:';
    }
}
