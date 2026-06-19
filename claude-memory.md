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
- Stages defined via `MProcess`; advance through `mprocess` records
- PTC-linked stocks use `Stock::STATUS_PTC_IN_PROGRESS` / `STATUS_PTC_COMPLETED`

### Stock Status Constants (on `Stock` model)
```
STATUS_NOT_RECEIVED       = 0
STATUS_COMPLETELY_RECEIVED = 1
STATUS_PARTIALLY_RECEIVED  = 2
STATUS_DELIVERY            = 3
STATUS_MACHINE_MATERIAL    = 4
STATUS_MATERIAL_PROCESSING = 5
STATUS_PTC_IN_PROGRESS     = 6
STATUS_PTC_COMPLETED       = 7
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
