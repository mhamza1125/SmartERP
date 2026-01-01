<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory;

    protected $primaryKey = 'delivery_id';

    protected $fillable = [
        'stock_id',
        'fshipping',
        'tshipping',
        'fport_no',
        'tport_no',
        'delivery_method',
        'delivery_status',
        'fi_no',
        'delivery_no',
        'delivery_date',
        'created_by',
        'updated_at',
    ];

    /**
     * Generate auto-generated delivery number with format: SLE-D001-26
     * SLE-D = Fixed prefix for deliveries
     * 001 = Sequential delivery number for the year (zero-padded, 3 digits)
     * 26 = Last 2 digits of the year (2026)
     */
    public static function generateDeliveryNo()
    {
        $currentYear = date('Y');
        $lastTwoDigits = substr($currentYear, -2);

        // Get the count of deliveries created this year
        $count = self::whereYear('created_at', $currentYear)->count();
        $sequentialNo = str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        return "SLE-D{$sequentialNo}-{$lastTwoDigits}";
    }

    /**
     * MULTI-ORDER DELIVERY PRICING LOGIC
     *
     * When a single delivery contains items from multiple orders, pricing should be calculated as follows:
     *
     * 1. PRODUCT AGGREGATION: Group all items by product_type_id and stage_id across all orders
     * 2. QUANTITY SUMMATION: Sum quantities for the same product across different orders
     * 3. WEIGHTED PRICING: Calculate weighted average price based on quantities from each order
     *    - For each product: (qty1 * price1 + qty2 * price2) / (qty1 + qty2)
     * 4. DELIVERY COST ALLOCATION: Distribute delivery charges proportionally by order value
     *    - Order share = Order total value / Total delivery value
     *    - Order delivery cost = Total delivery cost * Order share
     * 5. INVOICE GENERATION: Create separate line items per order showing:
     *    - Order number
     *    - Product details
     *    - Aggregated quantity
     *    - Weighted average price
     *    - Order-specific delivery cost allocation
     *
     * Example:
     * Order 1: Product A (100 units @ $10) = $1000
     * Order 2: Product A (50 units @ $12) = $600
     * Total delivery cost: $200
     *
     * Aggregated: Product A (150 units @ $10.67 weighted avg)
     * Order 1 share: $1000/$1600 = 62.5% → $125 delivery cost
     * Order 2 share: $600/$1600 = 37.5% → $75 delivery cost
     */
}
