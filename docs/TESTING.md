# Running the tests safely

The test suite runs **only** against the database `smarterp_test`. `tests/TestCase.php` checks the
configured and the connected database name before any test query and aborts the whole run otherwise.
`.env.testing` and `phpunit.xml` point at `smarterp_test`. Never point tests at `smarterp_erp`.

Every test file uses `DatabaseTransactions` (each test is rolled back). Do not use `RefreshDatabase`:
it would empty `smarterp_test`, and most tests need its master data (an admin user, products with 3+
stages, customers, roles).

## Building `smarterp_test` (once, or to refresh it)

`smarterp_test` = the table structure from `database/schema/mysql-schema.sql` + a copy of the data of the
development database. The copy only reads `smarterp_erp`. Paths are XAMPP's; verify on your machine.

```bash
B=/e/XAMPP/mysql/bin
$B/mysql.exe -h127.0.0.1 -uroot -e "DROP DATABASE IF EXISTS smarterp_test; CREATE DATABASE smarterp_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
$B/mysql.exe -h127.0.0.1 -uroot smarterp_test < database/schema/mysql-schema.sql
$B/mysqldump.exe -h127.0.0.1 -uroot --single-transaction --skip-lock-tables --hex-blob --no-create-info \
  --default-character-set=utf8mb4 --ignore-table=smarterp_erp.migrations smarterp_erp > /tmp/erp_data.sql
$B/mysql.exe -h127.0.0.1 -uroot --default-character-set=utf8mb4 smarterp_test < /tmp/erp_data.sql
php artisan migrate --env=testing --force     # applies migrations newer than the schema dump, to smarterp_test only
```

`2026_11_25_100004` (UNIQUE `stocks.stock_no`) stops on the 10 known duplicates; that is expected
(docs/DATA_FIXES.md F) and does not affect the tests.

## Running

```bash
php artisan test                                   # whole suite, smarterp_test only
php artisan test tests/Feature/StockFormulaTest.php
```

Known failures that come from the data or the environment, not from the code (2026-11-25):
AttendanceMachineTest ×2 (the `att2000.mdb` in the repo has no punches for user 24 in March 2024),
BankChargesTransactionTest ×3 (`User::factory()` sets no `role_id`, which has no default), PayrollTest ×1
(the employee's department has no Work In / Work Out times), RoleLinkCrawlTest ×1 (9 Edit links on purchases
answer 403 for the Accountant / Director / Manager roles).

## Rebuilding the schema dump

`php artisan schema:dump` (without `--prune`) reads the structure of the `.env` database and rewrites
`database/schema/mysql-schema.sql`. On MariaDB it retries without `--column-statistics`; that message is
harmless. It is used when migrating an empty database; it is not a backup (docs/BACKUP.md).
