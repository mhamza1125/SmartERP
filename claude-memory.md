# SmartERP — Project Memory

## Overview
Manufacturing/trading ERP built on Laravel 10 + Blade for a single company. Handles the full business cycle: orders → production (PTC) → delivery/packing → procurement → inventory → finance → payroll/HR.

---

## Tech Stack
- **Backend:** Laravel 10.10, PHP 8.1+, MySQL (`smarterp_erp` DB per .env, localhost; an older `smarterp` DB also exists)
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
| Finance | TransactionController, BankController, AssetController, LiabilityController | Multi-currency, dual ledger, asset / liability registers |
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
- **Finance:** `Transaction`, `Bank`, `Asset`, `Liability` (same `assets` table, `kind` column)
- **HR:** `Salary`, `Attendance` (WorkTime / WorkHoliday removed 2026-11-21; tables kept, unused)
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
- `stocks.start_from` (master, 2026-11-12): `stock` (default; NULL on older PTCs = same) = the start stage is stock already held, first issuance for the stage after it; `materials` = started from raw materials, the start stage IS the first work stage (master issue_for/current = start stage, received there, no "Starting stock" stage status, first movement shown as "Raw Materials"). One rule: `PtcStockRepository::firstWorkIndex()` (used by ptcStore, issueStages, stageSummary); `Stock::startsFromMaterials()`. Chosen on the Create PTC form ("Start From")
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
- Component returns (2026-11-12): on the PTC receive form each returned component is Unused (back to general stock at the stage it was issued from) or Damaged (posted r_stage_id = 105 -> component's Rejected stage, outside usable stock). No new column: damaged = received component lines at stage 105. issuanceBalances components carry issued / received / unused / damaged / returnable; consumption components carry unused / damaged; unused + damaged together capped at issued
- "No component" must be stored as NULL, never 0 (general stock uses `component_product_type_id IS NULL` for product lines)
- Component lines (another product used to make the PTC product) are recorded at the COMPONENT's own stage(s), never the PTC product's stage: `StockBatchRepository::componentStageSplit()` splits the quantity over the component's stages holding stock the order may use (own + free batches, rejected excluded, component's final stage first). PTC create/edit/issue all use it; forms show `componentStock()->sum()` (2026-11-11)
- PTC page lists issuances newest first by sequence (stock_id desc), not by date
- Gate pass print (2026-11-11): `ptc.movement.print` (`/ptc/{id}/movement/{movementId}/print`, `print/ptc-movement.blade.php`) prints one issuance (Outward), receipt (Inward) or release (Release Note) with signature lines; linked from the View/Items modal footer and each receipt card in the Receivings modal. Data via `PtcStockRepository::movementRecord()` / `movementItems()` (resolves work_logs ids to names)

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
- Batch quantities: `batch_qty["{pt}_{stage}"][batch_id] = qty` (partial `batchPicker.blade.php` on delivery add/edit and, through `batchChoices.blade.php`, on PTC create/issue). Picked quantities are taken first, the rest on Auto; picks beyond the issued quantity or for a stage not issued throw. A plain batch id (old pin) still means "everything from that batch". `recordPicks($stockId)` pre-fills edit forms.
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
  - the forms include `batchChoices` (batch quantity boxes per stage); the PTC edit form too, with `allowedStock`/`batchChoices` excluding the PTC's own lines and `recordPicks` pre-filling
  - normal issuance forms stay on Auto
- Balances: `StockBatchRepository::balanceQuery()`. The sum over batches equals the pooled `pStock()` figures. `php artisan stock:backfill-batches --check` reports missing or stale allocations and any total mismatch.

### Rejection stage, returns from any stage, stock adjustments (2026-11-22)
- `stock_items.rejected_stage_id`: on Rejection lines (stage 105) the work stage the pieces were rejected at; NULL = not recorded. PTC receipts into Rejection (product or damaged component) take the issuance's Issue For stage; PTC releases of Rejection are split per rejected-at stage (`RejectionRepository::ptcBuckets` + `split`, recorded stages first). `php artisan stock:backfill-rejected-stage [--dry-run]` tags older PTC receipt lines (run on the local DB? NO as of 2026-11-22 - dry run only, 3 lines)
- `RejectionRepository`: buckets = balance per product size + rejected-at stage (general stock, ptc_virtual 0). Movements without a stage are settled against the recorded stages (lowest id first; a recorded stage short takes from "not recorded" first), so buckets always sum to the Rejection stage total. /stock stage modal shows "Rejection by Stage"
- Purchase returns: product lines have Return From (any product stage or Rejection + Rejected At, partial `returnFromStage`); stored on `return_materials.stage_id` / `rejected_stage_id` (NULL = purchased stage). The vendor is still credited qty x purchase price (unchanged ledger). Because every stock query deducts returns at the purchased stage, `ReturnRepository::syncStageCorrections` posts a hidden pair per such line: stocks `table_name` purchase_returns (`Stock::TABLE_PURCHASE_RETURN`, status 11, source_id = return_material_id): in at the purchased stage (batch = the receipt's batch, none if the receipt has none) + out of the real stage (`allocateStockOut`, purchase's order then free). Purged / rebuilt on edit (purge BEFORE the lines change) and delete. Rejection returns are capped by the rejected-at bucket
- Stock Adjustments (Production menu, `StockAdjustmentController`, `stock_adjustments_access/show/create/delete`, model `StockAdjustment extends Stock` = policy target + list scope): stocks `table_name` stock_adjustments, stock_type 1 increase / 2 decrease, status 10, `ADJ-yymmNNN`. Materials or products at any stage / Rejection + rejected-at. Increase = own batch; decrease = chosen order's reserved stock then free (batches), materials may not go below 0, Rejection capped per bucket. No edit (delete + re-enter); deleting an increase is blocked once its stock was used
- Both new tables are excluded from the receivings list and the stock document delete flow; product ledger / batch history label them
- NOTE 2026-11-22: on smarterp_erp `stock:backfill-batches --check` reports 24 product/stage totals where batches are 0 (older purchase receipts without batch_id). Decreases / returns from another stage / deliveries are refused for those (batch stock 0). Plain `stock:backfill-batches` does nothing once batches exist; only `--fresh` rebuilds (drops manual reservations / splits) - reported to the user, not fixed

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
STATUS_ADJUSTMENT          = 10  (stock adjustment, table_name stock_adjustments)
STATUS_RETURN_STAGE        = 11  (purchase return stage-correction pair, table_name purchase_returns)
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

### Transaction display & loans (2026-11-14)
- "General Voucher" is shown as **Journal Voucher** everywhere; the stored value stays `generalVoucher` (routes/views keep their names). Show type / party values with `transactionLabel($value)` (helpers.php, uses `TransactionRepository::TYPE_LABELS`), never `ucfirst()`
- View buttons in Transactions / Cash Book / Bank Ledger come from the `transactionViewButton` partial (one transaction_to -> show-route map; `admin` = bank opening balance -> BRS page). BRS and Transfer view pages share `transactionVoucherInfo`
- Loans: `TransactionRepository::loanSummary($party, $id, $excludeId)` = Give Loan (advance, credit) minus Receive Loan (receiveAdvance, debit, payroll deductions included); employees split fixed (NULL = fixed) / temporary. `ajaxLoan` + `loanSummary` partial on the Pay Employee/Vendor/Contractor add/edit forms; Receive Loan is capped at the outstanding loan (`loanRepaymentError`)
- Receive Loan amount: employee form posts it as credit, vendor/contractor forms as debit - store()/update() take whichever is set (vendor/contractor repayments used to be saved as 0)
- Batch half-page prints: `print.layout-voucher` with `sheetOnly` renders one sheet; `print.voucher-batch` + `$sheets` prints many (payroll "Print All Payment Vouchers" / "Print All Salary Slips")

### Vendor Payments
Linked to a purchase (`order_id` = purchase_id), a General Payment (`order_id` = 0, chosen on the Pay Vendor form; changed 2026-11-14 at the user's request), or a **journal voucher** (2026-11-19: Payment For posts `jv:{id}` -> `voucher_id` = the JV, `order_id` 0). Same debit/credit handling in every case; `describe()` labels them "General Payment" / "Journal Voucher #id".
- Pay Vendor lists the vendor's credit vouchers (amount in `debit` = payable up) with unpaid = amount - SUM(credit of payments with that voucher_id) (`TransactionRepository::vendorVouchers`); `linkVoucherPayment()` refuses more than the unpaid amount and copies the JV's `expense_head_id` onto the payment. A JV with payments cannot be deleted.
- Dashboard (2026-11-24): cards link to Orders / PTCs / Expense / Product Stock. Order: Orders in Progress (ALL confirmed orders, `OrderRepository::inProgress()`, due date asc) -> Open Purchase Orders (`PurchaseRepository::openPurchases()`: non-mprocess purchases with balance > 0, received = receive qty - returned qty per line capped at ordered; Pending / Partially Received; required date asc) -> Running PTCs (latest 10) -> price changes. Both first tables: DataTables `.js-dashboard-table`, no paging, ColReorder (bundled cr-1.4.1), stateSave keeps only column order. Purchases have no closed status, so an old never-completed PO stays listed
- Expense reporting = Expense page (`expenseRows()`: expense + bankCharges + vendor/contractor payments with voucher_id AND expense_head_id, head joined by payee_id / expense_head_id) and the dashboard "Expense This Month" card.

### Journal vouchers are non-cash (user decision 2026-11-19)
- An invoice is posted as a JV (vendor Credit Voucher): it changes only the party's payable, never cash / bank. Cash / bank move only when Pay Vendor pays it.
- Every cash / bank query in `TransactionRepository` filters `ledger_flag != 0` AND `transaction_type != 'generalVoucher'` (vendor / contractor / employee JVs are still saved with `ledger_flag` 1, so the type filter is what keeps them out; old rows need no data fix). JVs skip the "Insufficient Balance" check on save
- Party ledgers (vendor / customer / employee) do not filter ledger_flag, so JVs stay in them

### Ledger date filters (fixed 2026-11-19)
- Cash book / bank ledger (`bookForRange`): opening = debit - credit before Date From, rows in date order (`transaction_date`, `transaction_id`, unfiltered too), closing = opening + the range (the balance at Date To). A positive opening is shown in the Debit column; the Closing Balance row is always shown when filtered
- Customer ledger: opening / closing run the view's own row rule (`customerLedgerAmount()`: JV +/- cc_amount, payment + cc_amount|debit, delivery/other - debit + credit) over the rows before / in the range
- Payment balance check reads a bank as debit - credit (was inverted in `TransactionController::store`)

### Payroll (reworked 2026-11-13)
- `payrolls` (one per month Y-m: total_working_days, hours_per_day, overtime_multiplier, late_multiplier, payment_date, bank_id) + `payroll_items` (per employee: designation, gross, working/actual days, salary_for_days, overtime hours/rate/amount, late_minutes/deduction, temp_advance, fixed_advance, fixed_advance_deduction, remaining_fixed_advance, net_salary, ids of the 3 posted transactions). All logic in `PayrollRepository` (calculate / sheet / store)
- Formula: hourly = gross / employee's total days / employee's hours per day; salary for days = gross x (actual + leaves) / employee's total days (2026-11-21: the row's own Total days, not the header's); overtime rate = hourly x overtime multiplier; late deduction = late minutes/60 x hourly x late multiplier; net = salary for days + overtime - late - temp advance - fixed instalment (negative net is refused)
- Defaults in Settings > Company: `overtime_multiplier` (2), `late_multiplier` (1); default working days = month days minus Sundays. `company.work_hours_per_day` was REMOVED 2026-11-25 (client: no manual hours)
- Hours per day (2026-11-25): department Work In -> Work Out MINUS `heads.break_minutes` (`HeadRepository::workingHours()`, break entered as hours + minutes `break_hours` / `break_mins` on Settings > Heads > Departments, validated in HeadRequest: both times or none, break < shift; `breakLabel()`). No fallback: a department without times -> hours null, red "No working hours" on the sheet, no hourly wage; saving overtime / late hours > 0 for such an employee is refused. The break does NOT change late time / overtime. Migration 2026_11_25_000001 (NOT run by Claude: blocked, user runs it)
- Hours per day (2026-11-21, superseded above for the calculation): no header field any more. `PayrollRepository::hoursPerDay($employees)` = the employee's department Work In -> Work Out (heads.work_start_time / work_end_time, end <= start = night shift), else the Company default; computed server side on save and stored per line in `payroll_items.hours_per_day` (`payrolls.hours_per_day` dropped). The sheet shows it under each name (amber = default; red when a saved line used other hours)
- Attendance (2026-11-21): the Work Hours (/workTime) and Non Working Days (/workHoliday) pages are gone. Late time / overtime use only the department's Work In / Out (no grace time); employees of a department without them get a warning and no late / overtime. Only Sundays are non-working days
- Late time (user decision 2026-11-22) = late check-in (after Work In) + early check-out (before Work Out), e.g. 9-6 dept, in 9:15 out 17:00 = 1.25 h. `dailyAttendance` rows: `LATE_VALUE` (total min, used by payroll `monthTotals`), `LATE_IN_VALUE`, `EARLY_OUT_VALUE`; the attendance page shows the split under the late figure. Only on days with both punches
- Employee Give / Receive Loan entries carry `transactions.advance_type` = `temporary` | `fixed` (NULL on older entries = fixed loan). Sheet pre-fills temp advance = this month's temporary advances, fixed advance = open fixed loan (`openFixedLoan`), instalment = last payroll's
- 2026-11-19: each employee row has its own **Total days** (default the header's; since 2026-11-21 the salary is divided by it, so it sets that employee's daily / hourly rate), **Leaves** (`paid_leaves`, paid, not deducted: salary for days = gross x (worked + leaves) / own total days) and **late time in hours** (`late_hours`, 2 decimals; `late_minutes` still saved = hours x 60). All numeric inputs take 2 decimals (`decimal:0,2`). Only active employees (`employee_status` 1) are listed, plus inactive ones already in the month's saved payroll. The Edit sheet's Month can be changed: `PayrollRepository::update()` moves the payroll (old month + its postings removed, new month must be free)
- Overtime / late hours default from the attendance machine (user decision 2026-11-19): `PayrollRepository::sheet()` calls `AttendanceRepository::monthTotals()` (the month's dailyAttendance rows, department working hours) for employees not saved in the month's payroll; saved rows keep their values and show the machine figure ("Att. x") with a "Use attendance figures" button. Since 2026-11-19 also worked days = total days - machine absents (absent = working day, not Sunday / holiday, without any punch; single-punch day = worked, shown as "single"); counted only up to today AND the machine's last punch date (last download); no punch at all in the month = not synced -> full attendance + warning; an employee with no punches while others have them -> 0 worked + red "No punches". Leaves and advances are NOT read from attendance. Machine unreadable -> warning, manual entry. The attendance calculation lives in `AttendanceRepository::dailyAttendance()` (moved from AttendanceController)
- Saving posts: salary transaction (net, from the payroll bank) + Receive Loan deductions (temporary / fixed) with `bank_id` 0 and `ledger_flag` 0 (no effect on bank / cash balances). Re-saving deletes the payroll's own transactions and re-posts. Months saved before this (salary transactions only) show as "No breakdown" and are replaced when saved
- Prints: `payroll.print` (month salary sheet, A4 landscape), `payroll.slip` (one employee, A5). Test: tests/Feature/PayrollTest.php

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
- `$company` and `$printContacts` (phone/email/website) are always available in print views via `PrintComposer`
- Default: portrait; landscape only for wide-column tables
- **No footer.** Standard header (`print/header.blade.php`): logo left (46% column, 72px), title middle (13px), phone/email/website right. Each view sets `@section('doc_title', '...')`; never put a title inside the body. `doc_no` is no longer shown in the header (2026-11-11) - every print shows its record code in the body instead
- Print table columns size to content: `.print-table` cells ignore static `width` styles (`width: auto !important`); tables that must keep widths (blank forms filled by hand: PTC card, PTC template, gate pass, payroll sheet) carry `data-fixed-widths`. Column headings 10.5px (voucher 9.5px); keep headings short (Product, Material, Stock, Avail. QTY)
- **Standard print header = boxed grid** (2026-11-11, every print incl. vouchers): `.meta-band.meta-band--grid` of `.meta-field` boxes (`.meta-label` without colon + `.meta-value`), 3 per row (`--cols-4` for 4, `.meta-field--span2` / `--wide` for long values; the layouts' `widenLongMetaFields()` adds them automatically by value length). Customer/vendor details go in a `.party-block` (`--spaced` before a table), as on the Order and Purchase prints. `.meta-group` (display: contents) only groups fields in markup. The old two-column `document-info` / `info-section` / `info-row` header is gone; `info-section` is still used as a plain body-section wrapper (heading + table)
- Amount in words: 13px sans, semibold. Certification line ("CERTIFIED TO BE TRUE AND CORRECT "COMPANY"") stays on one line
- Settings > Company has no listing: `company` route redirects to `company.edit` of the single company
- Table titles: `<div class="table-title">`; sign-off lines: `.sign-row` / `.sign-row__field` / `.sign-row__line`
- Column titles are short (QTY, Price, Total). Numeric columns are right-aligned (`class="num"` on th and td); the layout script `alignPrintTables()` also right-aligns columns whose header looks numeric and copies each column's data alignment to its `<th>`
- Logo URL: `printLogoUrl($company)` (app/helpers.php) - company logo if the file exists, else assets/print-logo.png; built with asset() so it follows the request's base URL (app may run under a sub-folder, e.g. http://host:8080/ERP/public)
- Page JS must never use root-relative URLs (`'/company/data'`, `origin + '/assets'`): use `appUrl('path')` / `window.SmartERP` (baseUrl, logo, companyName, contacts), set in index.blade.php by `PrintComposer@layout`
- DataTables exports (`#tableExport`, public/assets/js/custom.js + datatable-print.js): every button exports cleaned cell text (`dtExportCell`: drops .dropdown-menu / .no-export / forms, takes the selected option); a cell can set `data-export="..."`; columns titled Action or with .no-export are skipped. Print = Blade-style header repeated on every page, no footer
- Transaction narration: `TransactionRepository::describe($ids)` -> type, party, narration, summary, cheque_no, note, details[label=>value] (cheque parsed from "Check No: x" in the description - there is no cheque column). Used by payment/expense/transaction lists, vouchers and transfer info. People ledger prints use `ledgerDescriptions($detail)` (purchases / returns / deliveries with items)
- Vouchers (`print.payment`, layout-voucher) print on a portrait A4 sheet, filling its top half (210 x 148.5 mm, `.half-sheet`, dashed cut line) so two fit on one sheet - NOT A5 landscape (user, 2026-11-11); payment, receipt, expense, adjustment, transfer (`transfer.print`) and the payroll slip use it
- Invoice bank details: `BankRepository::printDetails($bankId)` (includes `bank_name` from heads) + partial `print/partials/bank-details`; modal options from `bankOptions.blade.php`

## Freight Charges (2026-11-10)
- `deliveries.freight_charges` (order currency, default 0) entered on the delivery add/edit form
- Customer ledger delivery debit = items (order price × delivered qty) + that delivery's freight (`TransactionRepository::cDetail/cDetailFilter`, `customerFreight()`)
- Commercial invoice shows Items Total, Freight Charges, and Grand Total including freight
- `company.fi_no` = default FI No (Settings > Company); a delivery's own `fi_no` overrides it on the commercial invoice

## Commercial Invoice (2026-11-11)
- `delivery.commercial` (print of one delivery), options from the modal on deliveryInfo via query string, validated by `CommercialInvoiceRequest`: hs_code, selling_type, uom, bank_id, statement_of_origin, `fi_no`, `payment_percent`
- FI No priority: modal `fi_no` > delivery's `fi_no` > `company.fi_no` (resolved in `DeliveryController::printCommercial`)
- `payment_percent` (0 < x <= 100, empty = 100): items stay at full price; totals show Invoice Value + "Payment Due (x% of Invoice Value)" incl. freight. 100% = the original layout. Proforma invoice (`order.proforma`) is separate and untouched
- Combined invoice = a multi-order delivery. **Multi-order delivery lines are one row per order** (add/edit forms post `line_order_id[]` aligned with `quantity[]`; container rows post ""), stored in `stock_items.order_id`. Same article on 2 orders = 2 lines. `allocateStockOut` limits such a line to its order's batches + free stock; `markLineOrders` keeps a valid explicit line order
- Line price = `order_items` of `COALESCE(stock_items.order_id, stocks.order_id)` in `StockItemRepository::delivery()` (also returns `line_order_no`) and in customer ledger (`TransactionRepository::cDetail/cDetailFilter`). Invoice shows an Order No column when lines span >1 order
- Legacy multi-order lines with NULL order_id show on the first order's row in the edit form and take that order on save

## Assets & Liabilities (2026-11-21)
- One table `assets` for both registers: `kind` = 'asset' | 'liability' (global scope on `Asset` / `Liability extends Asset`, set on create). Entries with the same `asset_name` = one asset / liability; balance = SUM(debit) - SUM(credit) (debit = increase, credit = decrease). Entry types + sign + money direction: `Asset::TYPES` / `Liability::TYPES` (`money` 'out' / 'in' / null = can never move money)
- Optional money effect ("Affects cash / bank"): `bank_id` (0 = cash, NULL = no money) and `transaction_id` -> a transaction with transaction_to = transaction_type = the kind, payee_id = the entry, debit = received / credit = paid; it shows in the cash book / bank ledger. Editing the entry updates / removes it, deleting the entry deletes it; the transaction itself cannot be deleted from Transactions (blocked in `TransactionRepository::deletionImpact`). Its View button goes to `asset.entry` / `liability.entry` (redirect to the ledger)
- Guards in `AssetRepository`: a decrease cannot take the balance below 0; a payment cannot exceed the cash / bank balance. Same repository for both (`LiabilityRepository extends AssetRepository`), screens in `AssetEntryController` (AssetController / LiabilityController only give labels), shared views asset / assetInfo / addAsset / editAsset + partial assetForm, `AssetRequest` (kind from the route name). Ledger URL: `asset.show` / `liability.show` with `?name=`. Permissions `liabilities_*` (LiabilityPolicy)
- Prints (2026-11-22, need the register's show): `asset.print` / `liability.print` (`print/asset-ledger`; `?name=` = one ledger, none = every name grouped with its own running balance + grand total; button on the list and ledger pages) and `asset.entry.print` / `liability.entry.print` (`print/asset-entry`, half-page voucher like print.payment: Payment / Receipt Voucher when cash / bank moved, else Journal Voucher; voucher no `AST-0001` / `LIA-0001`; double entry asset up = Dr asset, liability up = Cr liability, counter = cash / bank account or the entry type; print button on each ledger row)

## Material price field removed (2026-11-22)
- Add / Edit Material no longer have a price input (user request): prices are set per vendor (party prices). `materials.cprice` stays = last purchase / processing price (purchases and MProcess still write it; 0 for a new material); shown as "Last Purchase Price" and still used for BOM material cost and stock value. `updateFromForm` never changes it; old opening / edit rows stay in material_price_logs

## Customer prices (2026-11-21)
- The customer page (customer.show) lists the prices the customer bought at: per product size (last price + order, lowest, highest, orders, qty) and the full order-line history (`OrderItemRepository::customerPriceHistory / customerPriceSummary`, non-cancelled orders). The product page's Price History tab no longer has the old Sale Price History table; since 2026-11-21 it shows the customer price register instead (see "Vendor / customer prices")

## Vendor / customer prices (party prices, 2026-11-21)
- `party_prices` = current price of one item for one vendor (`party_type` vendor, buying) or customer (customer, selling, in the customer's currency). Item = `material_id`, or `product_type_id` + `stage_id` (unused keys 0; unique per party + item). `party_price_logs` = every change (old_price NULL = first price, price NULL = removed; source manual | purchase | order | backfill | removed; doc_id = purchase / order). All logic in `PartyPriceRepository`
- Set by hand on /material/{id} and /product/{id} (Price History tab, VENDOR prices only), partial `partyPrices` (current table + history table). Customer (selling) prices are set / shown ONLY on the customer page (user decision 2026-11-22, same as 2026-11-21: no selling prices on the product page). Vendor section only with purchases access; setting / removing (`partyPrice.store` / `partyPrice.destroy`, PartyPriceController) needs Purchase create|edit (vendor) or Order create|edit (customer)
- Documents move the price: `PurchaseController` store/update -> `syncPurchase()`, `OrderController` store/update -> `syncOrder()`. Any different price (higher OR lower) becomes current. On edits only lines that are new, changed price or moved to another party count (`snapshot()` before the save), so re-saving an old document never overwrites a newer price. Material-processing lines are ignored. Sync failures are logged, never undo the document
- Purchase / order add+edit forms: read-only "Vendor Price" / "Customer Price" box (partial `partyPriceField`, AJAX `partyPrice.lookup`) with a "Use" link that copies it into Price; for products it waits for the stage (ajaxPS) and lists other stages' prices when the chosen stage has none
- Deleting a vendor / customer / material / product deletes its prices + history (cascade in their deletionImpact)
- 2026-11-22: also entered / listed per party: vendor page "Buying Prices" (purchases access) and customer page "Selling Prices" (partial `partyPriceList`, `partySection()`), with Set Price + Price Change History (partial `priceChangesTable`, `changes()`). Dashboard: "Recent Purchase Price Changes" (purchases access) / "Recent Selling Price Changes" (customers access), latest 10. As of 2026-11-22 party_prices was still EMPTY (prices:backfill not run)
- 2026-11-24 (client feedback): vendor page Set Price lists ONLY the vendor's items (`vendorItemIds()`: vendors.material_id / product_id '|' lists from Edit Vendor + materials.vendor_id = vendor + items already priced); no linked items -> hint to Edit Vendor. Saving an item that already has a price shows a confirm pop-up with the last price (client-side, `$priceMap` in partyPriceList / partyPrices); each price row has Edit (pre-fills the form). The server still accepts any item (purchases set prices for any item)
- Price list print: `/print/party-prices/{vendor|customer}/{id}` (`partyPrice.print`, view print/party-prices, `printData()`); customer = show Customer, vendor = show Vendor + access Purchase. Button "Print Price List" on the party page
- Price Change History page: `/reports/price-changes/{vendor|customer}` (`partyPrice.changes`, view priceChanges, `changeLog()` paginate 50, filters item (product:/material:), party_id, user_id, dfrom/dto via PriceChangeFilterRequest; options = only values present in logs, `changeFilterOptions()`). Access: vendor = access Purchase, customer = access Customer. Linked from Reports menu, dashboard "View All", party pages. History tables on party / material / product pages also have in-browser filters (`.js-table-filter` in custom.js, `priceChangesTable` param `filters`)
- Existing data: `php artisan prices:backfill [--dry-run]` replays purchases / non-cancelled orders oldest first (history dated by document date) and adds an 'opening' wage history row per product_costs row; skips items / wages already present

## Product wage history (2026-11-21)
- `product_cost_logs`: every change of a product_costs row (general / employee / vendor = contractor wage): old_amount, amount, action added | changed | removed | opening. Written by `ProductCostRepository::store/update` (update re-saves every row; only real amount changes are logged)
- Employee page and contractor page (partial `personWages`): own wage per product + work head vs the general wage, last change date, and the person's wage change history (`personWages()`, `wageHistory()`). Product Costing / Wages tab shows the product's wage history. `product_cost_logs.table_name` uses utf8mb4_general_ci to match product_costs
- Deleting an employee / contractor deletes their own wage rates + history

## Vendor balance (2026-11-21)
- /vendor Balance = the ledger's (vendor.detail): received purchases - purchase returns + SUM(debit - credit) of all vendor / contractor transactions of the vendor (`VendorRepository::vendorWithBalance`). It used to add credits and drop wages / contractor rows

## Deletion (2026-11-15)
- Every module deletes through one flow: `GET {module}/{id}/delete-check` (JSON) -> confirmation dialog -> `DELETE {module}/{id}` with `confirmed=1`. Routes are named `{module}.deleteCheck` / `{module}.destroy` (stock documents: `stock.deleteCheck` / `stock.destroy` on `/stock-record/{id}`; users / roles: `user.*`, `role.*`; payroll by month; work hours: `work.destroy` `/work/{time|holiday}/{id}`)
- Repository `deletionImpact($id): App\Support\DeletionImpact` lists **blockers** (other records that use it: delete is refused) and **cascades** (its own rows deleted with it: items, images, its opening balance entry, linked transfer half...). `delete($id)` performs it. Controllers: `$this->authorize('delete', ...)` then `deletionCheckResponse()` / `runDeletion()` (trait `HandlesDeletion` on the base Controller: refuses when blocked or not confirmed, wraps the delete in a DB transaction, writes an activity-log entry `log_name=delete`)
- Rule of thumb: master data (customers, vendors, products, heads...) is deletable only while nothing references it; documents take their own lines with them but are refused while a later document depends on them (order -> deliveries / PTCs / purchases / payments; delivery -> returns; purchase -> receivings / payments; receiving -> returns; issuance -> receivings; PTC -> receivings / releases)
- Stock-ins (receivings, delivery returns, issuance receivings) are refused when removing them would make material, product (per size + stage) or batch stock negative: `StockItemRepository::stockInRemovalShortfalls($stockIds, $receiveMaterialIds)`, checked again inside the delete (throws `DeletionBlockedException`, rolled back)
- Helpers: `StockRepository::purge($stockIds)` (lines + batch allocations + images + rows; refuses stocks row 1 = opening stock), `StockBatchRepository::deleteUnused($sourceType, $sourceIds)`, `ImageRepository::deleteFor($table, $ids)`, trait `Repositories\Concerns\ChecksReferences` (`refCount`, `refExamples`)
- Never deletable: stocks row 1, system heads (`heads.action = 1` and `HeadRepository::SYSTEM_HEAD_IDS` 39, 40, 61, 96, 101, 105, 585, 814), the Admin role, your own user, the last admin, a user who created delivery returns / packing lists (their `created_by` FKs are ON DELETE CASCADE), payroll-posted transactions, automatic bank charges and delivery expenses (deleted with their parent)
- UI: `@include('deleteButton', ['url' => ..., 'check' => ..., 'label' => ...])` inside `@can('delete', Model::class)`; `custom.js` handles `.js-delete-record` with SweetAlert and submits `#deleteRecordForm` (index.blade.php)

## Permissions (audited 2026-11-16)
- Only policies decide. The single role rule: `Gate::before` in AuthServiceProvider lets the system Admin role (id 1) pass everything; policies themselves have no role checks. The Admin role is locked to all permissions in the role editor
- 114 permissions, `{module}_{action}`, actions access (list + menu) / show (view, print, export) / create / edit / delete. Labels, sections and descriptions: `App\Support\PermissionCatalog` (also used by the role editor partial `rolePermissions.blade.php` and the roles list summary)
- Dependency rule applied on every role save (`AdminRepository::syncPermissions` -> `PermissionCatalog::normalize`): any permission of a module adds its `_access`; `_edit` adds `_show`. The editor ticks them live. Migration 2026_11_16_000001 applied it to existing roles and removed 11 dead permissions (stocks_create/delete, reports_show/create/edit/delete, attendance_show, heads_show, categories_show, users_show, roles_show)
- Cross-module needs: party ledgers (customer / vendor / contractor / employee) need the party's View AND transactions_show; Cash Balance and bank ledgers need transactions; prints / Excel exports need the module's show
- Views: every link / action is wrapped in the check of its target route (`@can` or `@if(auth()->user()->can(...))`); `tests/Feature/RoleLinkCrawlTest.php` crawls the app as each real role and fails on any visible link answering 403 / 500
- Only an administrator can give the Admin role, edit or delete an administrator account (AuthController::guardAdminRole, UserRepository::deletionImpact)
- Known limitation: FormRequest validation runs before the controller's authorize(), so an unauthorized request with invalid input gets a validation redirect instead of 403 (nothing is saved either way)

## Attendance machine (2026-11-15)
- ATT2000.mdb read via PDO ODBC (Microsoft Access driver) in `AttendanceRepository::machinePunches()`. Path = `company.attendance_db_path` (Settings > Company > Attendance Machine), default `config('attendance.default_path')` = `public/resources/att2000.mdb`; relative paths are under the project root. Must be an existing readable .mdb / .accdb; optional allow-list `ATTENDANCE_DB_ALLOWED_DIRS` (";"-separated) in .env
- Employee `attendance_id` = the device's machine number = `USERINFO.Badgenumber` (joined to `CHECKINOUT.USERID`), compared without leading zeros
- Day pairing: first punch = check-in, last punch = check-out (CHECKTYPE is unreliable: staff press IN in the evening); punches within 120 s count once; a lone punch is in or out by CHECKTYPE. Late time only on days with both punches (existing rule). Employees without a machine number and numbers shared by several employees are listed as warnings; the employee filter uses employee_id
- Only salaried employees (employee_type_id 39), as before
- Department working hours (2026-11-19): `heads.work_start_time` / `work_end_time` on department heads (type 3, Settings > Heads, `HeadRepository::DEPARTMENT_TYPE`). Late time = check-in after the department start + Work Hours grace; overtime = check-out after the department end (end <= start = next day). A department without hours falls back to Payroll > Work Hours for that date. Attendance and summary show late and overtime in hours (2 decimals)

## Product Purchase Ledger (2026-11-19)
- `product.ledger` (`/productLedger`, Reports menu, `ProductController::ledger`, view `productLedger`): like the Material Ledger, for products. Rows from `ProductRepository::purchaseLedger()`: purchase receipts (approved qty), purchase returns, general-stock product / component lines (ptc_virtual 0; stock_type 1 in / 2 out, labelled Opening / Delivery / Delivery Return / PTC Release / PTC Issuance / Issuance / Receive Issuance). With one size selected: opening (before Date From) + running balance, which ends at the `pStock()` figure

## Journal Voucher Expense Head (2026-11-15)
- `transactions.expense_head_id` (nullable FK to heads, SET NULL), only on journal vouchers, must be a head of type 7 (Expenses). A label only: never changes debit / credit / cc_amount / ledger_flag. Shown on the JV listing, detail page and voucher narration (`describe()` details 'Expense Head')

## Invoice Excel export (2026-11-15)
- `delivery.commercial.excel` / `order.proforma.excel` (same query options as the prints), buttons in the commercial / proforma modals. Built by `App\Exports\InvoiceSpreadsheet` (phpoffice/phpspreadsheet) from the same data method the print uses (`DeliveryController::commercialInvoiceData`, `OrderController::proformaData`)
- Workbook header (2026-11-19): logo + contact lines + title only; the company name and address are not written (user request); rows 1-3 keep their height
- All invoice figures come from `App\Support\InvoiceTotals` (print views destructure `$totals`); the workbook uses live formulas (line = QTY x Price, sums, grand total, payment due) whose values equal those figures

---

## Reports menu names / URLs (renamed 2026-11-22; route names unchanged, old URLs 301-redirect with query)
| Menu / heading | URL | route name | shows |
|---|---|---|---|
| Issuance Report | /reports/issuance (+/print) | stock.daily | stock issued out, by date / worker / order (today by default) |
| Receiving Report | /reports/receiving (+/print) | rstock.daily | products received back, by date / worker / order |
| Material Stock Ledger | /reports/material-ledger (+/print) | material.detail | material purchases, returns, stock in / out |
| Product Stock Movements | /reports/product-movements | product.ledger | every product stock movement with running balance (was "Product Purchase Ledger") |
| Purchase Price History | /reports/purchase-prices | purchase.ledger | vendor price comparison + every purchase line of one material / product (was "Purchase Ledger", /pLedger) |
| Stock Requirements | /reports/stock-requirements | reports.product.stock.requirements | shortfall of confirmed orders |
| Product Stock Forecast | /reports/product-stock-forecast | reports.product.stock.ledger | current stock + PTCs in progress - open orders (was "Product Stock Ledger") |

## Purchase Ledger by item (2026-11-19)
- Reports > Purchase Ledger = `/pLedger` (`purchase.ledger`, GET `?item=material:{id}|product:{id}&dfrom&dto`, searches on the Search button only; `ReportController::purchaseHistory`, view `reports/purchase-history`; access: purchases access OR reports_access): vendor price comparison + every purchase line of the item. It replaced the old Purchase Ledger (all purchases / returns / vendor payments with a running balance; PurchaseController::purchaseLedger / printPurchaseLedger, TransactionRepository::pLedger / pLedgerFilter, views purchaseLedger + print/purchase-ledger) at the user's request 2026-11-19
- Data = the same queries as the Price History on the item pages: `PurchaseItemRepository::materialPriceHistory` / `productPriceHistory` (optional dates, `received_qty` = SUM approved_qty) and `vendorComparison()` (per vendor: purchases, qty, received, lowest / highest / qty-weighted average / last price + date; processing lines left out; cheapest last price first). Partial `purchaseVendorComparison` is shown on the report, /material/{id} and /product/{id} (Price History tab)
- The Vendors page only lists materials / products assigned on the vendor profile, not purchases

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
salaries, attendances, work_times, work_holidays (last two unused since 2026-11-21)
images, sessions, cache, jobs
```

---

## Dev Environment
- **URL:** http://05_smarterp.test (XAMPP local)
- **DB:** MySQL, database `smarterp_erp` (DB_DATABASE in .env)
- **Debug:** Enabled (local)
- **Timezone:** UTC
- **Git:** `main` is stable; use feature branches for changes
