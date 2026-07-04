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

# Run all tests
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
**Database:** MySQL, database `smarterp`

---

## Architecture Overview

This is a manufacturing/trading ERP on **Laravel 10 + Blade**. The stack is:
- Backend: Laravel 10, PHP 8.1+, MySQL
- Frontend: Blade + jQuery/Bootstrap + DataTables + ApexCharts/AmCharts
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

**Repositories hold all business logic and DB queries.** All 38 repositories implement `GlobalInterface` (`app/Repositories/GlobalInterface.php`) with 5 required methods: `all()`, `get($id)`, `store(array $data)`, `update($id, array $data)`, `delete($id)`.

**Models have no Eloquent relationship methods.** All joins and related-data fetching are done inside repository methods. Do not add `hasMany`/`belongsTo` without explicit instruction.

### Key Structural Facts

- `app/helpers.php` (autoloaded in `composer.json`) — global helpers delegating to `NumberHelper`; format currency with `NumberHelper`, never manually
- `app/Http/View/Composers/PrintComposer.php` — automatically injects `$company` into all print views; never pass it manually
- `resources/views/Template/` — contains static vendor assets (DataTables, ApexCharts, Bootstrap bundles); do not modify
- Print views: `resources/views/print/` — self-contained with inline CSS; portrait by default, `@page { size: landscape }` only for wide tables
- Report views: `resources/views/reports/`

### RBAC

Three hardcoded roles on `User`: `ROLE_ADMIN=1`, `ROLE_MANAGER=2`, `ROLE_ACCOUNTANT=3`.

Route middleware:
- `is_admin` — role_id == 1 only
- `all` — any authenticated user with a role
- `auth` — standard Laravel auth

Authorization is two-layered: **route middleware** (coarse) + **Policy `$this->authorize()`** (fine-grained). Both must be present. Policies are registered in `app/Providers/AuthServiceProvider.php`.

### View Naming Convention

| File | Purpose |
|---|---|
| `{entity}.blade.php` | Index/listing |
| `add{Entity}.blade.php` | Create form |
| `edit{Entity}.blade.php` | Edit form |
| `{entity}Info.blade.php` | Detail/show |
| `{entity}Detail.blade.php` | Sub-detail |

All views extend a layout from `resources/views/` root (e.g., `@extends('layouts.app')`). JS goes in `@push('scripts')` at the bottom.

### Route Conventions

All routes in `routes/web.php`, grouped by module with `Route::prefix()`. Named routes follow `module.action` (e.g., `order.index`, `order.store`). Print routes follow `/print/{entity}/{id}`.

### Critical Business Rules

- **Approved qty, not ordered qty** — stock is updated using approved qty from the receive record
- **Vendor payments** — must be linked to a purchase; never created as standalone transactions
- **Job number format** — `{customer_no}/J{YY}-{NNN}` (e.g., `CU0001/J25-001`)
- **Delivery number format** — `SLE-D001-26`
- **Stock statuses** — defined as constants on the `Stock` model (0–7); see `claude-memory.md` for the full table
- **Finance** — all money flows through the `Transaction` model using double-entry (`debit`/`credit`); `transfer_pair_id` links bank transfer pairs
