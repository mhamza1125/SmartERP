# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Required Context Files

Before starting ANY task involving design decisions, architecture, or code changes, read:

1. `claude-memory.md` — compressed project knowledge (architecture, modules, models, business flows)
2. `claude-rules.md` — coding standards, patterns, and architectural rules

These files are authoritative. If they conflict with general assumptions, the files win. Update them when new decisions or patterns emerge.

---

## Common Commands

```bash
# Start Vite dev server (assets)
npm run dev

# Build assets for production
npm run build

# Run all tests (safe: only ever against the database smarterp_test, see docs/TESTING.md)
php artisan test

# Run a single test file
php artisan test tests/Feature/ExampleTest.php

# Code style (Laravel Pint)
./vendor/bin/pint

# Fix style for a single file
./vendor/bin/pint app/Http/Controllers/OrderController.php

# Clear and rebuild caches
php artisan optimize:clear

# Run a specific seeder
php artisan db:seed --class=PermissionRoleSeeder

# Generate a new policy
php artisan make:policy ProductPolicy --model=Product
```

**Dev URL:** `http://05_smarterp.test` (XAMPP)  
**Database:** MySQL, database `smarterp_erp` (see `.env`)

---

## Architecture Overview

This is a manufacturing/trading ERP on **Laravel 10 + Blade**. The stack is:
- Backend: Laravel 10, PHP 8.1+, MySQL
- Frontend: Blade + jQuery/Bootstrap + DataTables + ApexCharts (editor: Summernote)
- Build: Vite 5
- Auth: Session-based; Sanctum for API
- Key packages: `spatie/laravel-activitylog`, `guzzlehttp/guzzle`

### Layered Architecture

```
Route → Middleware → Controller → Repository → Model/DB
                          ↓
                     FormRequest (validation)
                     Policy (authorization)
```

**Controllers are thin.** The canonical pattern is:
1. `$this->authorize('action', ModelClass::class)` via Policy
2. `$request->validated()` via FormRequest
3. Call repository method
4. Return view or redirect with `->with('success'/'error', '...')`

**Repositories hold all business logic and DB queries.** The repositories (46 of the 49 classes in `app/Repositories/` as of 2026-11-25) implement `GlobalInterface` (`app/Repositories/GlobalInterface.php`) with 5 required methods: `all()`, `get($id)`, `store(array $data)`, `update($id, array $data)`, `delete($id)`.

**Models have no Eloquent relationship methods.** All joins and related-data fetching are done inside repository methods. Do not add `hasMany`/`belongsTo` without explicit instruction.

### Key Structural Facts

- `app/helpers.php` (autoloaded in `composer.json`) — global helpers delegating to `NumberHelper`; format currency with `NumberHelper`, never manually
- `app/Http/View/Composers/PrintComposer.php` — automatically injects `$company` into all print views; never pass it manually
- Static vendor assets (theme CSS/JS, DataTables, ApexCharts, Bootstrap bundles) live in `public/assets/`; the main layout `resources/views/index.blade.php` loads them. The theme's demo copy `resources/views/Template/` was removed 2026-11-26 (the original theme zip is kept outside git)
- Print views: `resources/views/print/` — self-contained with inline CSS; portrait by default, `@page { size: landscape }` only for wide tables
- Report views: `resources/views/reports/`

### RBAC

Roles and their permissions are data (`roles`, `permission_role`; editable in Settings > Roles). Only the Admin role is special: `User::ROLE_ADMIN = 1` (`Gate::before` lets it pass everything). Other role ids carry no meaning in code (in the test copy of the data: 2 Store Incharge, 3 Accountant, 4 Export, 5 Director, 6 Manager).

Route middleware:
- `is_admin` — role_id == 1 only
- `all` — any authenticated user with a role
- `auth` — standard Laravel auth

Authorization is two-layered: **middleware applied in each controller's constructor** (`$this->middleware(['auth', 'all'])`; routes in `web.php` carry none, owner decision D9, 2026-11-25; the one exception is the Settings > Reset Test Data group, which also has `auth` + `is_admin` on the routes) + **Policy `$this->authorize()`** (fine-grained) in every action. Both must be present. Policies are registered in `app/Providers/AuthServiceProvider.php`.

### View Naming Convention

| File | Purpose |
|---|---|
| `{entity}.blade.php` | Index/listing |
| `add{Entity}.blade.php` | Create form |
| `edit{Entity}.blade.php` | Edit form |
| `{entity}Info.blade.php` | Detail/show |
| `{entity}Detail.blade.php` | Sub-detail |

Screens extend `@extends('index')` (`resources/views/index.blade.php`); prints extend `print.layout` or `print.layout-voucher`. JS goes in `@push('scripts')` at the bottom.

### Route Conventions

All routes in `routes/web.php`, grouped by module with `Route::prefix()`. Named routes follow `module.action` (e.g., `order.index`, `order.store`). Print routes follow `/print/{entity}/{id}`.

### Critical Business Rules

- **Approved qty, not ordered qty** — stock is updated using approved qty from the receive record, and the vendor is billed for approved qty only (vendor balance and vendor ledger, D1 2026-11-25)
- **Vendor payments** — linked to a purchase (`order_id` = purchase_id), or a **General Payment** (`order_id` = 0) when not for a specific purchase (user decision 2026-11-14)
- **Job number format** — `{customer_no}/J{YY}-{NNN}` (e.g., `CU0001/J25-001`)
- **Delivery number format** — `SLE-D001-26`
- **Document numbers** — highest existing + 1, taken under a lock inside the save (`App\Support\DocumentNumbers`), year/month in Asia/Karachi; unique, gaps allowed (D7). Never COUNT + 1
- **Stock figures** — one formula in `StockItemRepository` (`materialStock`, `pStock`, `productStockAggregate`, receipt totals with returns summed per receipt line first); never copy it. Negative stock is never allowed (D3)
- **Locks once used (D4)** — delivered order lines, received purchase lines and returned / used receipt lines cannot be changed in price / party / item or lowered below what was delivered / received / used; refusals end with "ask an admin" (`DocumentLockedException`)
- **Stock statuses** — defined as constants on the `Stock` model (0–7); see `claude-memory.md` for the full table
- **Finance** — all money flows through the `Transaction` model using double-entry (`debit`/`credit`); `transfer_pair_id` links bank transfer pairs
