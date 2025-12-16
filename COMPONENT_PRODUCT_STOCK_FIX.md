# Component Product Stock Calculation Fix

## Problem Summary
Component products were not being properly tracked in stock calculations. When component products were issued (stock_type=2) or received (stock_type=1), their inventory levels remained unchanged because the stock calculation methods were not including records where `component_product_type_id > 0`.

## Root Cause
The stock calculation methods in `StockItemRepository` were filtering by `material_id = 0` to get product stock, but they were NOT checking for `component_product_type_id`. This meant:

1. When a component product was issued, it was stored with:
   - `product_type_id` = main product's ID (the PTC product)
   - `material_id` = 0
   - `stage_id` = 0
   - `component_product_type_id` = the component product's product_type_id

2. The stock queries were looking for records where `product_type_id` matched the component product, but these records had the MAIN product's ID in `product_type_id`, not the component's ID.

3. Component product stock was being tracked under the wrong product_type_id.

## Solution Implemented

### 1. Updated `pStockGet()` Method (app/Repositories/StockItemRepository.php)
**Lines: 777-843**

Added a new subquery to track component product stock:
- Created `componentStockSubquery` that queries by `component_product_type_id` instead of `product_type_id`
- Component stock is tracked with `stage_id = 0` (components don't have stages when used as components)
- Stock IN (type=1) increases component stock
- Stock OUT (type=2) decreases component stock
- Excluded component product entries from regular product stock query using `whereNull('stock_items.component_product_type_id')`

### 2. Updated `pStock()` Method (app/Repositories/StockItemRepository.php)
**Lines: 427-515**

Added component product tracking to the main stock listing:
- Created `componentStockSub` subquery similar to `pStockGet()`
- Added UNION to combine regular product stock, component product stock, and purchase data
- Component products now appear in the stock list with their correct inventory levels

## How Component Product Stock Works Now

### When Component Product is Issued (Stock OUT):
1. Record created in `stock_items` with:
   - `component_product_type_id` = component product's product_type_id
   - `stock_type` = 2 (issuance/out)
2. Stock calculation queries find this record by `component_product_type_id`
3. Component product's `stockOut` increases
4. Available stock = `stockIn - stockOut` decreases ✓

### When Component Product is Received (Stock IN):
1. Record created in `stock_items` with:
   - `component_product_type_id` = component product's product_type_id
   - `stock_type` = 1 (receiving/in)
2. Stock calculation queries find this record by `component_product_type_id`
3. Component product's `stockIn` increases
4. Available stock = `stockIn - stockOut` increases ✓

## Testing Instructions

### Manual Testing Steps:

1. **Check Initial Component Product Stock**
   - Go to Stock page (`/stock`)
   - Click "Product Stock" tab
   - Find a product that is used as a component
   - Note the current stock level

2. **Issue Component Product in PTC**
   - Create a new PTC or add issuance to existing PTC
   - Add a component product with quantity (e.g., 10 units)
   - Submit the issuance

3. **Verify Stock Decreased**
   - Return to Stock page
   - Check the component product's stock
   - Stock should have decreased by the issued quantity ✓

4. **Receive Component Product Back**
   - Go to PTC receiving page
   - Receive back some component product quantity (e.g., 5 units)
   - Submit the receiving

5. **Verify Stock Increased**
   - Return to Stock page
   - Check the component product's stock
   - Stock should have increased by the received quantity ✓

### Database Verification:

```sql
-- Check component product stock transactions
SELECT 
    si.stock_item_id,
    si.component_product_type_id,
    pt.product_type_id,
    p.article_no,
    p.name,
    si.quantity,
    s.stock_type,
    s.stock_date
FROM stock_items si
JOIN stocks s ON s.stock_id = si.stock_id
LEFT JOIN product_types pt ON pt.product_type_id = si.component_product_type_id
LEFT JOIN products p ON p.product_id = pt.product_id
WHERE si.component_product_type_id IS NOT NULL
ORDER BY s.stock_date DESC;

-- Calculate component product stock for a specific product_type_id
SELECT 
    component_product_type_id,
    SUM(CASE WHEN stocks.stock_type = 1 THEN quantity ELSE 0 END) as stockIn,
    SUM(CASE WHEN stocks.stock_type = 2 THEN quantity ELSE 0 END) as stockOut,
    SUM(CASE WHEN stocks.stock_type = 1 THEN quantity ELSE 0 END) - 
    SUM(CASE WHEN stocks.stock_type = 2 THEN quantity ELSE 0 END) as available
FROM stock_items
JOIN stocks ON stocks.stock_id = stock_items.stock_id
WHERE component_product_type_id = ? -- Replace with actual product_type_id
GROUP BY component_product_type_id;
```

## Files Modified

1. **app/Repositories/StockItemRepository.php**
   - `pStock()` method (lines 427-515)
   - `pStockGet()` method (lines 777-843)

## Impact

- ✅ Component product stock now properly decreases when issued
- ✅ Component product stock now properly increases when received
- ✅ Stock page shows accurate component product inventory levels
- ✅ Component product availability checks work correctly
- ✅ No changes needed to views (stock.blade.php already handles display properly)
- ✅ Backward compatible with existing non-component product stock

## Notes

- Component products are tracked separately from regular product stock
- Component products don't have stages when used as components (stage_id = 0)
- The fix properly handles the double-entry inventory system
- Regular product stock and component product stock are combined in the final display

