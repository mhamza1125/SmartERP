# Batch-Tracked Product Stock

**Commit:** `b898e1c` on `surgical-fix` · **Date:** 2026-10-14

## What changed

Product stock is no longer one anonymous pool. Every unit belongs to a **batch**. A batch records:
- **its source:** PTC, purchase, issuance, opening stock, or split
- **its reservation (optional):** the order it is reserved for

**The rule:** stock reserved for order X can only go to order X. **Free** batches can go to any order.

Materials are not batch-tracked. They work as before.

## How batches are created (automatic)

| Stock comes in from | Batch | Reserved for |
|---|---|---|
| PTC receipt / release | `PTC-{no}` | the PTC's order |
| Purchase receipt | `{purchase_no}` (one per item) | the purchase's order (free if none) |
| Issuance receipt | issuance number | the issuance's order |
| Opening stock | `OPEN-{product}` | free |
| Delivery return | batch it was delivered from | unchanged |

Changing the order on a purchase, issuance or PTC moves its batches to the new order.

## How stock goes out

Deliveries, issuances, IGroup issuances and PTC issues take stock in this order:
1. **the order's own batches**
2. **free batches**, oldest first

They **never** take another order's batches.

- **Behaviour change:** issuing or delivering more than is available is now **rejected** and nothing is saved. Before, stock could go negative.
- **Choosing a batch:** delivery forms and PTC forms have a batch dropdown. **Auto** (the default) applies the rule above.

## Where to see it

| Page | What's new |
|---|---|
| **Stock → Product Stock** | Free / Reserved columns; batch table in **View Stages** |
| **Stock → Batches** | All batches, history (*Made From*, every movement), actions |
| **Order Status** | Ready (Reserved), In Production, **To Make** |
| **Reports → Stock Requirements / Ledger** | Each order is covered by its own reserved stock and PTCs first |

## Managing reservations (Stock → Batches → open a batch)

- **Change Reservation:** reserve the whole batch for an order, move it to another order, or make it free.
- **Split:** move part of one stage into a new batch (`{batch}-S1`) for another order or as free stock. Only ownership changes; stock totals stay the same.
- **Leftovers stay reserved** after an order is finished. Free them here.
- **Permission:** needs **stocks edit** (admin only by default). Every change is written to the activity log.

## Deploying to another environment

1. Pull `surgical-fix`.
2. Run the migrations as usual:
   ```
   php artisan migrate
   ```
   This creates the batch tables. The older migrations that were pending are now safe to run:
   - `remove_stock_no_unique_constraint` does nothing, so stock numbers stay unique.
   - `add_transfer_pair_id` and `add_source_id` skip themselves when their column already exists.
3. Convert existing stock into batches:
   ```
   php artisan stock:backfill-batches --dry-run   # review the report
   php artisan stock:backfill-batches             # write it (refuses if totals don't match /stock)
   ```
4. Check the result:
   ```
   php artisan stock:backfill-batches --check     # expect: 0 unallocated lines, totals match
   php artisan optimize:clear
   ```

The backfill can't know which order's stock historical stock-outs used. It matches them the way stock is used now (the order's own batches, then free, oldest first) and reports:
- **CROSS-ORDER:** past stock-outs that used stock built for a different order
- **LEGACY:** past stock-outs with no matching stock-in

## Do / don't

- ✅ Run `stock:backfill-batches --check` any time to verify consistency.
- ❌ Don't run `stock:backfill-batches --fresh` once reservations have been changed or batches split by hand. It rebuilds everything and loses those changes.
- ❌ Don't run the full `php artisan test`: it would wipe the live DB. Run single files only:
  ```
  php artisan test tests/Feature/StockBatchTest.php
  php artisan test tests/Feature/PtcVirtualStockTest.php
  ```

## For developers

- **All batch logic** lives in `app/Repositories/StockBatchRepository.php`.
- **New product stock-in:** write it through `StockItem` (Eloquent). The observer assigns the batch.
- **New product stock-out:** after writing the lines, call `StockBatchRepository::allocateRecord($stockId)`, or `allocateStockOut($stockId, $orderIds, $pins)`. It throws `StockBatchException` on a shortage, so wrap the write in a transaction.
- **Balances:** use `StockBatchRepository::balances()`. Its sum per product/stage equals `pStock()`.
- **Full rules:** see `claude-memory.md` → *Batch-Tracked Product Stock*.

## Known limitations

- Normal issuance forms always use Auto; they have no batch dropdown.
- PTC forms have no batch dropdown for components; components always use Auto.
- The requirements report ignores PTCs that belong to draft or dispatched orders.
