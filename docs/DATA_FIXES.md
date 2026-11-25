# SmartERP data fixes (for the owner to run)

Written 2026-11-25 with the stabilisation work on branch `surgical-fix`. **Nothing here was run on
`smarterp_erp`.** Fixes A and C were rehearsed on `smarterp_scratch` (a mysqldump copy of
`smarterp_erp` taken 2026-11-25); the results quoted below come from that rehearsal.

## Before you run anything

1. Take a backup of the database and of `public/resources` (see [BACKUP.md](BACKUP.md)).
2. Run each fix on a copy first if you can (restore the backup into a scratch database, set
   `DB_DATABASE` to it, run the fix, look at the result), then on the real database.
3. Run the fixes **in the order below**. Each one lists: preconditions, a "before" SELECT, the fix,
   an "after" SELECT with the expected numbers. If a "before" number differs from the one written
   here, stop: the data has changed since this was written; re-check before going on.
4. Use the MySQL client of XAMPP (verify the path on this machine), e.g.
   `E:\XAMPP\mysql\bin\mysql.exe -u <user> -p smarterp_erp`.

Order: **A** (hidden product lines) → **B** (negative 407 / stage 146, a decision) → **C** (batch resync)
→ **D** (orphan vendor entry) → **E** (over-received line: review only) → **F** (stock_no duplicates,
only after question Q20 is answered).

---

## A. F-13: 21 product lines stored with component 0 instead of NULL (395 units missing from /stock)

General product stock counts a product line only when `component_product_type_id IS NULL`; 21 lines
created on 2026-09-29/30 have `0`, so `/stock` does not show them. New saves can no longer write 0
(`StockItem` model now stores NULL).

Preconditions: backup taken.

```sql
-- before: expect 21 rows, qty_in 690, qty_out 295
SELECT COUNT(*) AS n_rows,
       SUM(CASE WHEN s.stock_type = 1 THEN si.quantity ELSE 0 END) AS qty_in,
       SUM(CASE WHEN s.stock_type = 2 THEN si.quantity ELSE 0 END) AS qty_out
FROM stock_items si JOIN stocks s ON s.stock_id = si.stock_id
WHERE si.component_product_type_id = 0;

START TRANSACTION;
UPDATE stock_items SET component_product_type_id = NULL WHERE component_product_type_id = 0;
-- expect: 21 rows affected
COMMIT;

-- after: expect 0
SELECT COUNT(*) FROM stock_items WHERE component_product_type_id = 0;
```

Effect measured on the scratch copy (`/stock`, product size_stage: before → after):

| Product size _ stage | Before | After |
|---|---|---|
| 733_644 | (not shown) | 390 |
| 730_644 | (not shown) | 40 |
| 734_354 | (not shown) | 30 |
| 407_132 | (not shown) | 30 |
| 734_637 | 20 | 135 |
| 733_355 | 210 | 60 |
| 730_355 | 60 | 20 |
| 407_146 | **-40** | **-60** (see B) |

Total of all product stock: 4,918 → 5,313 (+395). `stock:backfill-batches --check` then reports 28
product/stage totals without batches instead of 24: run **C** after this fix.

## B. F-14: product type 407 (Beaker SLE-3007-7, 5000x210x180MM) at stage 146 "Semi Finish Product" is negative

Findings (read-only queries on `smarterp_erp`, 2026-11-25):

- Four PTCs started at stage 146 and took 60 pieces out of it: PTC 2609001 (10), 2609002 (10),
  2610003 (20), 2610004 (20).
- **Nothing was ever put in at stage 146**: no opening stock, no purchase receipt, no issuance receipt.
  The product's routing is 151 → 146 → 132 → …; the PTCs started from stage 146 stock that was never recorded.
- `/stock` shows -40 today because the two 2026-09-29 lines are hidden by A; after A it shows -60.
- PTC 2609001 was issued 10 but received 20 at stage 132 (receipts R1-I2609001 and R2-I2609001, 10 each,
  written before PTC virtual stock existed).

Proposed correction (needs your decision, no SQL):

1. If the 60 pieces really existed at stage 146 before those PTCs, record them with the existing screen
   **Production > Stock Adjustments > Increase**: product 407, stage 146, quantity **60** (after fix A;
   40 if you do this before A), reason "opening stock at Semi Finish Product not recorded before PTCs
   2609001/2609002/2610003/2610004". The adjustment gets its own batch, so C then has nothing to repair here.
2. Check R2-I2609001 (10 pieces at stage 132): if it was entered twice by mistake, delete it with the
   normal Delete button (the app refuses if those pieces were used since).
3. If neither is true (the PTCs were started wrongly), tell the developer before changing anything.

## C. F-15: batch layer out of sync with /stock

`php artisan stock:backfill-batches --check` (reads only) on `smarterp_erp`, 2026-11-25:

- 84 product lines with a missing or stale batch allocation;
- 24 product/stage totals where `/stock` ≠ batches (batches 0), e.g. 134_115 600 vs 0, 127_146 1500 vs 0,
  733_355 210 vs 0, 407_146 -40 vs 0; 10 product purchase receipt lines have no `batch_id`.

Until this is fixed, deliveries, decreases and returns from another stage are refused for those items
(batch stock 0).

Two options:

| | Option 1: `php artisan stock:backfill-batches --fresh` | Option 2: targeted backfill |
|---|---|---|
| What it does | Deletes every batch and allocation and rebuilds them from the history (receipts, PTCs, issuances, opening stock), oldest first | Only creates batches for the receipts / lines that have none and keeps every existing batch |
| What is lost | Manual reservations, re-assignments and splits made on the Batches screen | Nothing |
| Exists today | Yes | **No**: it would have to be written (a new command) |

Checked on `smarterp_erp`: 26 batches, 3 reserved to an order (set automatically from their PTC / issuance
order, the rebuild sets them again), **0 splits, 0 transfers**, and the activity log shows only `created`
entries for batches (no manual reservation changes). So option 1 loses nothing today.

**Recommendation: option 1, right after A**, before anyone reserves or splits batches by hand:

```text
php artisan stock:backfill-batches --check      # before: 84 lines / 24 (28 after A) totals differ
php artisan stock:backfill-batches --fresh
php artisan stock:backfill-batches --check      # after: expect "0 product line(s)..." and "All product/stage totals match /stock."
```

Rehearsed on the scratch copy (A then `--fresh`): `--check` afterwards reports 0 unallocated lines and all
totals matching. The rebuild flags 8 stock-outs that had no stock of their own:

- 4 × product 407 / stage 146 (PTCs 2609001, 2609002, 2610003, 2610004) → charged to batch LEGACY-407, which
  ends at **-60** (that is B; doing B first makes them normal);
- 3 × product 733 / stage 355 (PTC 2609005 and its issuances 001 / 002) → took stock of purchase P2609001
  reserved for another order (CROSS-ORDER);
- 1 × product 225 / stage 115, delivery 23 dated 2026-11-07 → LEGACY-225 at -10, because the stock it used
  arrived at stage 115 with a later date. `/stock` is 0 there (10 in, 10 out); only the dates are out of
  order. Correct the delivery date if it was entered wrongly; otherwise it can stay.

If you made manual reservations or splits after 2026-11-25, do **not** use `--fresh`; ask for option 2.

## D. F-29: transaction 50 is the opening balance of a deleted vendor

Transaction 50: `transaction_to` vendor, type openingBalance, payee 14, debit NULL, credit 0.00, dated
2024-05-23. Vendor 14 no longer exists; it is the only vendor / contractor entry without its vendor.
Amount 0, so no balance changes.

```sql
-- before: expect 1 row, vendor_exists 0, credit 0.00
SELECT t.transaction_id, t.credit, t.debit,
       (SELECT COUNT(*) FROM vendors v WHERE v.vendor_id = t.payee_id) AS vendor_exists
FROM transactions t WHERE t.transaction_id = 50;

START TRANSACTION;
DELETE FROM transactions
WHERE transaction_id = 50 AND transaction_to = 'vendor' AND transaction_type = 'openingBalance'
  AND payee_id = 14 AND COALESCE(debit, 0) = 0 AND COALESCE(credit, 0) = 0
  AND NOT EXISTS (SELECT 1 FROM vendors v WHERE v.vendor_id = 14);
-- expect: 1 row affected
COMMIT;
```

## E. D2: purchase lines received above the ordered quantity (review only, never rewritten)

Receiving more than ordered is now refused for new and edited receipts; existing rows stay as they are.
List on `smarterp_erp` (2026-11-25):

| Purchase | Line | Item | Ordered | Received | Receipts |
|---|---|---|---|---|---|
| P2611002 | 500 | ALUMINIUM | 100 | 125 | R1-P2611002, R2-P2611002 |

To re-list later:

```sql
SELECT p.purchase_no, pi.purchase_item_id, pi.quantity AS ordered, SUM(rm.quantity) AS received
FROM purchase_items pi JOIN purchases p ON p.purchase_id = pi.purchase_id
JOIN receive_materials rm ON rm.purchase_item_id = pi.purchase_item_id
GROUP BY pi.purchase_item_id HAVING received > ordered;
```

If 125 is right, the purchase can be edited to order 125 (allowed: not below what was received; the
price cannot change). If 125 is wrong, an admin must correct the receipt.

## F. F-16 / D8: the 10 duplicate stock numbers (read Q20 in AUDIT_QUESTIONS.md first)

The migration `2026_11_25_100004_add_unique_index_to_stocks_stock_no` refuses to run while these exist
(it lists them). They are legacy issuances / receipts of three PTCs from 2026-09-30, stored before PTC
numbers got their PTC prefix:

| stock_id | stock_no now | PTC | Proposed stock_no |
|---|---|---|---|
| 10086 | 001 | 2609005 | 2609005-001 |
| 10087 | R1-I001 | 2609005 | 2609005-R1-I001 |
| 10088 | 002 | 2609005 | 2609005-002 |
| 10089 | R1-I002 | 2609005 | 2609005-R1-I002 |
| 10092 | 001 | 2609006 | 2609006-001 |
| 10093 | R1-I001 | 2609006 | 2609006-R1-I001 |
| 10097 | 001 | 2609007 | 2609007-001 |
| 10098 | R1-I001 | 2609007 | 2609007-R1-I001 |
| 10099 | 002 | 2609007 | 2609007-002 |
| 10100 | R1-I002 | 2609007 | 2609007-R1-I002 |

This is the format every newer PTC uses (`{ptcNo}-{NNN}`, `{ptcNo}-R{n}-I{NNN}`); the PTC pages strip the
prefix (`Stock::ptcDisplayNo`), so the numbers users see do not change. None of the proposed numbers exists.

**Do not run this before Q20 is answered**: once the 10 duplicates are gone, the next `php artisan migrate`
creates the UNIQUE index, and three save paths that repeat stock numbers by design would then fail (see Q20).

```sql
-- before: expect 10 rows
SELECT s.stock_id, s.stock_no, m.stock_no AS ptc_no FROM stocks s JOIN stocks m ON m.stock_id = s.ptc_id
WHERE s.stock_no IN ('001', '002', 'R1-I001', 'R1-I002');

START TRANSACTION;
UPDATE stocks s JOIN stocks m ON m.stock_id = s.ptc_id
SET s.stock_no = CONCAT(m.stock_no, '-', s.stock_no)
WHERE s.stock_no IN ('001', '002', 'R1-I001', 'R1-I002') AND s.ptc_id IN (10084, 10090, 10094);
-- expect: 10 rows affected
COMMIT;

-- after: expect no row
SELECT stock_no, COUNT(*) FROM stocks GROUP BY stock_no HAVING COUNT(*) > 1;
```
