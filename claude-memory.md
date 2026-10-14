# SmartERP — Project Memory

## Overview
Manufacturing/trading ERP built on Laravel 10 + Blade for a single company. Handles the full business cycle: orders → production (PTC) → delivery/packing → procurement → inventory → finance → payroll/HR.

---

## Tech Stack
- **Backend:** Laravel 10.10, PHP 8.1+, MySQL (`smarterp` DB, localhost)
- **Auth:** Session-based; Sanctum for API; full RBAC via Role/Permission models + Policies
- **Frontend:** Blade + jQuery/vanilla JS; Bootstrap, DataTables, ApexCharts, AmCharts, CKEditor
- **Build:** Vite 5 (laravel-vite-plugin), Axios
- **Packages:** spatie/laravel-activitylog, guzzlehttp/guzzle
- **Drivers:** Session=DB, Cache=DB, Queue=DB, Mail=Log, Broadcast=Log
- **Dev URL:** http://05_smarterp.test (XAMPP)

---

## Folder Structure
```
app/
  Console/             Artisan commands
  Exceptions/          Custom exception handler
  Helpers/             NumberHelper.php (currency/number formatting)
  Http/
    Controllers/       42 controllers (thin — delegate to repositories)
    Middleware/        is_admin, all, auth, guest + framework middleware
    Requests/          24 FormRequest classes (validation + authorization)
    View/Composers/    PrintComposer.php
  Models/              46 Eloquent models
  Policies/            17 Gate policies
  Providers/           RouteServiceProvider, EventServiceProvider, etc.
  Repositories/        38 repos all implementing GlobalInterface
database/migrations/   33+ migration files
resources/views/       171 Blade files
  Template/            Shared layouts (header, sidebar, footer, bundled assets)
  print/               Self-contained print templates
  reports/             Report-specific views
routes/
  web.php              ~475 routes grouped by module
  api.php              Minimal — Sanctum auth only
```

---

## RBAC & Auth System

### User Roles (hardcoded constants on User model)
| Constant | Value | Role |
|---|---|---|
| `User::ROLE_ADMIN` | 1 | Full access |
| `User::ROLE_MANAGER` | 2 | Operational access |
| `User::ROLE_ACCOUNTANT` | 3 | Finance access |

### Permission Flow
1. `permissions` table stores named permissions
2. `permission_role` pivot links permissions to roles
3. `User::hasPermission(string $name): bool` — queries `permissions` joined with `permission_role` via user's `role_id`
4. `User::isAdmin(): bool` — checks `role_id === 1`

### Middleware Aliases (Kernel.php)
| Alias | Class | Guards |
|---|---|---|
| `is_admin` | Admin | Requires `role_id == 1`; else redirect to login |
| `all` | All | Requires `role_id == 1` OR any role assigned; else redirect to login |
| `auth` | Authenticate | Standard Laravel auth; redirects unauthenticated to login |
| `guest` | RedirectIfAuthenticated | Redirects authenticated users to home |

### Route Protection Pattern
- Admin-only routes: `middleware('is_admin')`
- All authenticated users: `middleware('all')`
- Policies handle per-resource authorization inside controllers via `$this->authorize()`

---

## Core Modules
| Module | Controllers | Notes |
|---|---|---|
| Auth/Admin | AuthController, AdminController | Login, user & role management |
| Orders | OrderController | Job numbers (SLE-O001-25 format), stage tracking |
| Delivery | DeliveryController, PackingListController | Packing cartons, commercial invoices |
| Purchase | PurchaseController, ReceiveController, ReturnController | POs, receiving, returns |
| Inventory/Stock | StockController | Daily issuance, PTC, wages ledger |
| Products | ProductController, ProductCostController, ProductMaterialController | BOM-style materials |
| Finance | TransactionController, BankController, AssetController | Multi-currency, dual ledger |
| HR | AttendanceController, PayrollController | External MDB DB for attendance data |
| Config | CategoryController, HeadController, CompanyController, RoleController | Lookup tables |
| Reports | ReportController | Stock requirements, ledger analysis |

---

## Key Models (by domain)
- **Auth:** `User` (role_id FK), `Role`, `Permission`, `PermissionRole`
- **Parties:** `Customer`, `Vendor`, `Employee`, `Company`
- **Catalog:** `Product`, `ProductMaterial`, `ProductCost`, `ProductType`, `Material`, `Machine`, `Category`, `Head`, `HeadType`
- **Sales:** `Order`, `OrderItem`, `Delivery`, `DeliveryReturn`, `DeliveryReturnItem`
- **Packing:** `PackingList`, `PackingCarton`, `PackingCartonItem`
- **Procurement:** `Purchase`, `PurchaseItem`, `Receive`, `ReceiveMaterial`, `Returns`, `ReturnMaterial`
- **Inventory:** `Stock`, `StockItem`, `IGroup`, `IGroupItem`, `MProcess`
- **Finance:** `Transaction`, `Bank`, `Asset`
- **HR:** `Salary`, `Attendance`, `WorkTime`, `WorkHoliday`
- **System:** `Image`, `Settings` (dummy — policy binding only, no DB table)

### Model Notes
- **No Eloquent relationship methods are defined on models** — joins and related data are handled inside repository query methods via raw joins or separate queries. Do not expect `$order->items` to work; use the repository instead.
- **IGroup** (table: `igroups`, PK: `igroup_id`) — Issuance Group; groups stock issuances linked to an order. Fields: `igroup_no`, `order_id`, `igroup_date`, `igroup_status`, `description`.
- **MProcess** (table: `mprocess`, PK: `mprocess_id`) — Material Processing record; tracks PTC-related material movement between purchase items and stock items. Fields: `purchase_id`, `purchase_item_id`, `stock_item_id`, `before_mid`, `before_qty`.
- **Settings** — No DB table. Exists solely as a target for `SettingsPolicy` binding.

---

## GlobalInterface Contract
All 38 repositories implement this interface (`app/Repositories/GlobalInterface.php`):
```php
public function all();
public function get($id);
public function store(array $data);
public function update($id, array $data);
public function delete($id);
```
Additional domain-specific methods are added per repository beyond these five.

---

## Key Business Logic

### Order Flow
- Order created → `job_no` auto-generated (`Order::generateOrderNo()` → format `SLE-O001-25`)
- Order has stages tracked via PTC (Process Travel Card)

### PTC (Process Travel Card) Flow
- Manufacturing tracking system; stock records carry `ptc_id`, `current_stage_id`, `next_stage_id`, `is_ptc_master`
- The PTC master row is itself the initial issuance (I001); later issuances have `ptc_id` = master, `stock_type` 2; receipts have `ptc_id` + `issue_id` = the issuance they are received against (always required)
- PTC master status: 6 In Progress, 7 Completed, 9 Closed Early. "Finished" checks must use `!= STATUS_PTC_IN_PROGRESS`
- `stocks.end_stage_id` = planned last stage (NULL on old PTCs -> product's last stage). One finish action (`ptc.close` -> `PtcStockRepository::finish()`) stores Completed if the end stage is fully received, else Closed Early
- All PTC stock logic lives in `PtcStockRepository` (locks the master row, validates, throws `PtcStockException`)
- **Stage workflow (user-confirmed 2026-10-12): the PTC start stage is the stock it starts from (e.g. Stage 1 = unfinished product bought in), NOT a work stage. Each issuance moves product from stage X to its Issue For stage Y, and it is received into Y.** Stage 1 -> 2, receive in 2; issue 2 -> 3, receive in 3; PTC 1..3 complete
- `stocks.start_stage_id` (master) stores the start stage; the master's `issue_for` = the stage after it (= `current_stage_id` at creation). Legacy masters (start_stage_id NULL) stored the start stage as `issue_for`; `startStageId()` falls back to that. `php artisan ptc:backfill-start-stage [--dry-run]` corrects them (stage labels only, no stock change) - user had NOT run it as of 2026-10-12
- Stage Progress: start stage shows status `start` ("Starting stock") when no work was issued for it
- Stage display: `stocks.current_stage_id` on issuances/receipts/releases is NOT the movement stage. Show movements via `PtcStockRepository::movementStages()` + `ptcStageMove` partial: issuance = product line stage(s) -> `issue_for`; receipt = its issuance's from-stage(s) -> received item stage(s); release = item stage -> General Stock
- `nextStageId()` (stage after a given stage, capped at the end stage) is the default Issue For of a new issuance (after the current stage, or after the PTC-stock stage being transferred)
- Issue For must be one of the PTC's work stages (`issueStages()`: stage after start .. end). Issuing for a later stage advances the master's `current_stage_id`/`next_stage_id` (never backwards)
- Receive Into defaults to the issuance's `issue_for` and lists it + Rejected; a "Show all" checkbox lists every product stage for manual flows. Server accepts any of the product's stages or Rejected
- Display references come from `PtcStockRepository::references()` (I001 = master, R{n}-I{seq}, REL{n}); stored `stock_no` numbers skip the master, so never display them directly

### PTC Virtual Stock (exists only inside /ptc)
- `stock_items.ptc_virtual = 1` marks movements internal to a PTC; **every general product-stock query in `StockItemRepository` filters `ptc_virtual = 0`**
- Product received on a PTC receipt -> `ptc_virtual = 1` (PTC stock, not general stock). Materials/components returned -> `ptc_virtual = 0` (back to general stock)
- Transfer to another stage = PTC issuance line with `ptc_virtual = 1` (source "This PTC's Stock")
- Release to general stock = `stocks` row `stock_type` 1, `stock_status` 8 (`STATUS_PTC_RELEASED`), `table_name` `ptc_release`, `ptc_id` = master; its items are ordinary `ptc_virtual = 0` stock-in
- available(product, stage) = received - transferred - released
- Products on an issuance must be received back in full (outstanding = issued - received, any stage incl. Rejected 105); materials may vary; components capped at issued
- Close is blocked while product is outstanding or PTC stock remains; closing changes only the status (stages keep their actual status, no stock movement)
- "No component" must be stored as NULL, never 0 (general stock uses `component_product_type_id IS NULL` for product lines)

### Batch-Tracked Product Stock (2026-10-14)
- `stock_batches`: one lot of one product type. `source_type` is one of ptc | purchase | issuance | opening | split | legacy, with `source_id`. `order_id` = reserved for that order; NULL = free.
- Batch numbers:
  - PTC → `PTC-{no}`
  - purchase → `{purchase_no}` (one batch per purchase item)
  - issuance → the issuance `stock_no`
  - opening → `OPEN-{product_type_id}`
  - a `-2`, `-3` suffix is added when a document makes several batches
- `stock_item_batches` (stock_item_id, batch_id, quantity) records which batches a product line moved. A stock-out may draw from several batches; `SUM` per line = `stock_items.quantity`. Lines are never split, so every product line stays one row.
- Purchase receipts: `receive_materials.batch_id` is set in `ReceiveMaterialRepository`; returns reduce the batch through `receive_material_id`.
- A product line is `material_id = 0 OR component_product_type_id IS NOT NULL`, the same definition `pStock()` uses (`StockBatchRepository::whereProductLine`). Components count under the component product type.
- **Stock-ins get their batch automatically** through `StockItemObserver` → `StockBatchRepository::syncLine()` / `stockInAllocation()`:
  - PTC receipt or release → the PTC batch
  - normal issuance receipt → the issuance batch
  - opening stock (stock_id 1) → OPEN
  - delivery return → the batches the delivered line came from
  - anything else → its own batch

  PTC stage transfers (`ptc_virtual = 1`) → the PTC batch.
- Batch order follows its source: `StockObserver` handles issuance and PTC order edits; `PurchaseRepository::update` handles the purchase order.
- **General stock-outs** are allocated after their lines are written with `StockBatchRepository::allocateStockOut($stockId, $orderIds, $pins)`, or `allocateRecord($stockId, $pins)` for records with a single order:
  - the order's own batches first, then free stock, FIFO; never another order's batch
  - the record's own lines are excluded, so create and edit behave the same
  - a shortfall throws `StockBatchException` and the whole save is rolled back. The PTC repository rethrows it as `PtcStockException`.
  - Callers: DeliveryController store/update, StockController store/gstore/update/ptcStore/ptcUpdate, PtcStockRepository issue/updateIssuance.
- Batch pins: `batch_pin["{pt}_{stage}"] = batch_id` (partial `batchPicker.blade.php`, currently on the delivery add/edit forms). Empty = auto.
- Deliveries:
  - `stock_items.order_id` records the order each line was delivered for (`markLineOrders`; for multi-order deliveries, the order whose reserved batch the line took)
  - `orderDelivery($orderId, $excludeStockId, $orderIds)`: stockIn = allowed batch stock, stockOut = 0, plus `batches` for the picker
- /stock shows Free / Reserved per product and stage, plus a Batches table in the stage modal (`StockBatchRepository::stockByProduct()`).
- **Batches screen** (`StockBatchController`, routes `batch.index` / `batch.show` / `batch.reserve` / `batch.split` under /stock/batches, views `batches`, `batchInfo`):
  - list of batches; detail page with stage balances, "Made From" (batches the source PTC/issuance consumed), splits and full movement history
  - permissions: `StockBatchPolicy` reuses stocks_access/show for viewing, and stocks_edit for changing
- **Changing ownership:** reserve/reassign/free updates `stock_batches.order_id`. A split creates a child batch `{batch}-S{n}` (`source_type` split, `parent_batch_id`) plus a `stock_batch_transfers` row (from, to, stage, qty).
  - Transfers move ownership only; they never create stock rows, so pooled totals and issuance listings are untouched.
  - `balanceQuery()` unions them in. Both models log activity.
  - `stock:backfill-batches --fresh` must not be used after manual reservations or splits.
- **Order status "To Make"** = remaining − reserved at the line's stage − reserved at other stages − in the order's open PTCs (`StockBatchRepository::orderProgress`, `PtcStockRepository::orderWorkInProgress` / `workInProgress` = outstanding issued + PTC stock).
- `orderRemainingItems` counts delivered/returned per line order (`stock_items.order_id`, falling back to the header order).
- **Requirements report** (`StockRequirementRepository`):
  - each confirmed order's shortfall after its own reserved stock and PTCs, minus free stock and PTCs without an order
  - PTCs count their actual pieces in production, not the planned `[QTY:n]`
  - orders count what is still to deliver
  - the ledger report uses the same figures
- **PTC create / issue forms:**
  - `pstock` is `allowedStock(order)` (own + free)
  - the forms include `batchChoices` (one optional batch picker per stage)
  - normal issuance forms stay on Auto
- Balances: `StockBatchRepository::balanceQuery()`. The sum over batches equals the pooled `pStock()` figures. `php artisan stock:backfill-batches --check` reports missing or stale allocations and any total mismatch.

### Stock Status Constants (on `Stock` model)
```
STATUS_NOT_RECEIVED       = 0
STATUS_COMPLETELY_RECEIVED = 1
STATUS_PARTIALLY_RECEIVED  = 2
STATUS_DELIVERY            = 3
STATUS_MACHINE_MATERIAL    = 4
STATUS_MATERIAL_PROCESSING = 5
STATUS_PTC_IN_PROGRESS     = 6
STATUS_PTC_COMPLETED       = 7   (PTC completed: end stage fully received)
STATUS_PTC_RELEASED        = 8   (PTC stock released to general stock)
STATUS_PTC_CLOSED          = 9   (PTC closed early, before its end stage)
```

### Purchase → Stock Flow
`Purchase` → `Receive` (ReceiveMaterial items) → **approved qty** (not ordered qty) added to stock → `Returns` / `ReturnMaterial` deduct from stock

### Sales → Delivery Flow
`Order` → `Delivery` (delivery_no: `SLE-D001-26` format) → `PackingList` → `PackingCarton` → `PackingCartonItem`

### Finance / Dual Ledger
- All money flows through `Transaction` model
- `transaction_to`: destination (bank/cash/vendor/customer/employee)
- `debit` / `credit`: double-entry amounts
- `cc_amount`, `fb_charges`, `db_charges`: multi-currency fields (foreign currency charges)
- `ledger_flag`: distinguishes ledger entry type
- `payee_id` + `payee_bank_id`: recipient; `bank_id`: source bank
- `transfer_pair_id`: links two transaction records for bank transfers

### Vendor Payments
Only created against purchases — not standalone transactions.

### Payroll
`Salary` model linked to `Attendance`; bank balance fetched via external API call (guzzle).

### Approved Qty Rule
When receiving purchase items, the **approved qty** (not ordered/delivered qty) is the quantity added to stock.

---

## ViewComposers
- **`PrintComposer`** — shares `$company` (first `Company` model record) to all print views automatically. Print templates do not need to pass company data manually.

---

## Date Display
- Display dates as DD-MM-YYYY with `formatDate($date, $default = '-')` / `formatDateTime()` (`app/helpers.php`); null/empty/0000-00-00 safe
- Never format form values, date inputs, date pickers or stored dates — those stay `Y-m-d`

## Print System
- 28 dedicated print routes; all return Blade views (no PDF generation)
- Print views in `resources/views/print/` — self-contained with inline CSS
- `$company` is always available in print views via `PrintComposer`
- Default: portrait; landscape only for wide-column tables

---

## Key DB Tables
```
users, roles, permissions, permission_role
customers, vendors, employees, companies
orders, order_items
deliveries, delivery_returns, delivery_return_items
packing_lists, packing_cartons, packing_carton_items
purchases, purchase_items
receives, receive_materials (ReceiveMaterial)
returns, return_materials
stocks, stock_items, igroups, igroup_items, mprocess
products, product_materials, product_costs, product_types
materials, machines, categories, heads, head_types
transactions, banks, assets
salaries, attendances, work_times, work_holidays
images, sessions, cache, jobs
```

---

## Dev Environment
- **URL:** http://05_smarterp.test (XAMPP local)
- **DB:** MySQL, database `smarterp`
- **Debug:** Enabled (local)
- **Timezone:** UTC
- **Git:** `main` is stable; use feature branches for changes
