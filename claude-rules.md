# SmartERP — Claude Working Rules

## 1. Architecture Rules
- Follow the **Repository Pattern** strictly — controllers call repositories only, never Eloquent directly
- Repositories implement `GlobalInterface`; any new repository must define all 5 contract methods
- Use **FormRequests** for all input validation — never validate inside controllers
- Use **Policies** for authorization — never hardcode permission checks in controllers
- Business logic belongs in repositories (or dedicated service methods), not in controllers
- **Models have no Eloquent relationships defined** — all joins and related-data fetching happen inside repository methods; do not add `hasMany`/`belongsTo` calls without explicit instruction

## 2. GlobalInterface — Required Methods
Every repository must implement these five methods:
```php
public function all();
public function get($id);
public function store(array $data);
public function update($id, array $data);
public function delete($id);
```
Add domain-specific methods beyond these five as needed in each concrete repository.

## 3. Controller Conventions
- Controllers are thin: validate (FormRequest) → authorize (Policy) → call repository → return view/redirect
- Use `$this->authorize('action', $model)` with the relevant Policy — never inline permission checks
- Flash messages: use `->with('success', '...')` or `->with('error', '...')` on redirects
- Always redirect using named routes: `redirect()->route('order.index')`
- Keep controllers under 200 lines; extract excess logic to the repository

## 4. Repository Conventions
- Repos live in `app/Repositories/`; named `{Entity}Repository`
- Inject via constructor or resolve from the container — never `new SomeRepository()` inline
- Return Eloquent models or collections — never raw arrays from `DB::select()`
- Use local model scopes for reusable query filters (e.g., `scopeActive`, `scopeByStatus`)
- Handle all joins and related-data fetching here (since models have no relationship methods)

## 5. Model Conventions
- Models live in `app/Models/`; singular PascalCase names (`OrderItem`, not `OrderItems`)
- Always define `$fillable` — never leave both `$fillable` and `$guarded` empty
- Do not define Eloquent relationship methods on models unless explicitly instructed
- Soft deletes only if the table has a `deleted_at` column; never add it without a migration
- Custom primary keys (non-`id`) must be declared: `protected $primaryKey = 'igroup_id';`

## 6. View / Blade Conventions
- Views in `resources/views/`; flat CRUD naming: `add{Entity}.blade.php`, `edit{Entity}.blade.php`, `{entity}Info.blade.php`, `{entity}Detail.blade.php`
- Always extend a `Template/` layout — never duplicate header/sidebar/footer HTML
- Do not create duplicate templates — extend or `@include` existing partials
- JS goes at the bottom of each blade file in the appropriate `@push('scripts')` section
- Use DataTables for all listing/index tables; enable server-side processing when dataset exceeds ~500 rows
- No inline `<style>` blocks in regular views — exceptions allowed only in print views

## 7. ViewComposer Usage
- `PrintComposer` automatically injects `$company` into all print views — do not pass it manually
- Add a new ViewComposer only for data that must be shared across many unrelated views
- Register new composers in the relevant ServiceProvider — never in controllers or route files

## 8. Print / Report Rules
- Print views go in `resources/views/print/` with their own minimal self-contained layout
- Print views may use inline CSS — this is the only context where it is acceptable
- Default orientation: portrait; use `@page { size: landscape; }` only for wide-column tables
- `$company` is always available in print views via `PrintComposer` — no need to fetch it
- Always use `asset('path')` for logo and image paths — never hardcode absolute paths
- Format all monetary values via `NumberHelper` — never format currency manually
- Report views go in `resources/views/reports/`

## 9. Route Conventions
- All routes in `routes/web.php`; group by module using `Route::prefix('module')->group()`
- Named routes are required for all routes; follow `module.action` pattern (e.g., `order.index`, `order.store`)
- Print routes: `/print/{entity}/{id}` pattern, return a Blade view (not a PDF or download)
- API routes: add only if consumed by an external client; keep `api.php` minimal

## 10. Database / Migration Rules
- Always write reversible migrations with both `up()` and `down()`
- Use `unsignedBigInteger` for foreign key columns; declare with `->constrained()` or explicit `foreign()`
- Column names: `snake_case`
- No raw `DB::statement()` in migrations unless unavoidable (e.g., DB views)
- Never modify production data inside a migration — use seeders or Artisan commands

## 11. Coding Standards
- PSR-12 code style throughout
- PHP 8.1+ features are allowed: `match`, named arguments, `readonly`, enums
- Use `Carbon` for all date/time handling — never `date()`, `strtotime()`, or `mktime()`
- Be explicit with column names in queries — never `SELECT *` in complex queries
- No `dd()`, `dump()`, or `var_dump()` in committed code

## 12. Error Handling
- Wrap repository operations that can fail in `try/catch` — catch `\Exception` or specific DB exceptions
- On failure: log the error with `\Log::error()`, flash a user-friendly error message, redirect back
- Do not expose raw exception messages to the user view
- The global exception handler is in `app/Exceptions/Handler.php` — add custom renderers there for new exception types

## 13. Security Rules
- All input must pass through FormRequest `->validated()` — never use `$request->all()` or `$request->input()` directly for mass assignment
- No raw SQL with user input — use Eloquent query builder bindings or `DB::select('...', [$param])`
- File uploads: validate MIME type and size in FormRequest; store via `Storage` facade
- Enforce permissions at two layers: route middleware (`is_admin`, `all`) AND controller Policy check
- Log all create/update/delete operations on critical models via Spatie activity log

## 14. Frontend / AJAX Rules
- Bootstrap for layout — do not introduce new CSS frameworks
- AJAX via Axios (pre-configured); all JSON responses must follow this shape:
  ```json
  { "success": true|false, "message": "...", "data": {...} }
  ```
- Do not add SPA frameworks (React, Vue, Alpine) without an explicit project-level decision
- Vite handles all asset bundling — do not add separate webpack or rollup configs

## 15. Pagination Rules
- Paginate any query that could return more than 50 records in a listing view
- Use Laravel's `->paginate($perPage)` — default `$perPage = 25` unless the view specifies otherwise
- Pass the paginator to the view and render with `$results->links()` using the Bootstrap preset

## 16. Testing Rules
- PHPUnit is installed but no test coverage is currently expected for routine feature work
- If a bug is fixed that previously had no test, consider adding a regression test in `tests/Feature/`
- Do not write unit tests for repository methods that only wrap Eloquent calls

## 17. Artisan Command Rules
- Add Artisan commands in `app/Console/Commands/` only for scheduled tasks, batch jobs, or one-off data operations
- Do not duplicate logic already in repositories inside commands — call the repository from the command
- Register commands in `app/Console/Kernel.php`

## 18. Quick-Reference Don'ts
- Don't call Eloquent directly in controllers — always go through a repository
- Don't add middleware without registering it in `app/Http/Kernel.php`
- Don't create new helper files without adding them to `composer.json` `autoload.files`
- Don't use `Session::put()` for flash data — use `->with()` on redirects
- Don't hardcode company name, currency symbol, or logo path — read from `Company` model or config
- Don't create vendor payments as standalone transactions — they must be linked to a purchase
- Don't use the ordered qty for stock updates — always use the **approved qty** from the receive record
