# SmartERP stabilisation, 2026-11-25

What was changed to fix the defects found in the audit of 2026-11-25 (its report, AUDIT_2026-11-25.md, is not kept in the repository).
No new features: only validation, guards, locks, transactions, bug fixes, security, indexes, tests and docs.

- Branch `surgical-fix`, based on commit `aae6bf9`.
- The live database `smarterp_erp` was only read, never changed. Data corrections are written up for the owner in [DATA_FIXES.md](DATA_FIXES.md).
- Tests: 81 before (74 pass), 159 after (152 pass). The same 7 fail before and after; they depend on data or the environment, not on this work ([TESTING.md](TESTING.md)).

## Owner decisions applied

| | Decision |
|---|---|
| D1 | Vendors are billed for the approved quantity only |
| D2 | Receiving more than was ordered is refused; existing over-received lines are kept and listed |
| D3 | Stock may never go negative |
| D4 | Documents that later documents depend on are locked (see "Locked once used") |
| D5 | Order status follows fixed transitions (table below) |
| D6 | Costing, tax and general ledger are out of scope |
| D7 | Document numbers are unique; gaps are allowed |
| D8 | Existing data is never renamed to make an index fit |
| D9 | Login and permission checks stay in the controllers' constructors |
| D10 | Reset Test Data exists only when `ALLOW_DATA_RESET=true` (REPLACED 2026-11-25: always available to the Admin role, no .env switch) |
| D11-D13 | Production settings, git history and backups are the owner's job (documented) |

## What changed

### Tests
- Tests run only against the database `smarterp_test`. `tests/TestCase.php` stops the run if it points anywhere else.
- `database/schema/mysql-schema.sql` holds the structure of the core tables.
- 12 new test files cover the fixes below.

### Uploads
- Images (jpg, png, gif, 5 MB) and product files (pdf, images, Word, Excel, 10 MB) are checked before anything is saved. SVG, disguised scripts and double extensions are refused.
- Files are stored under a random name with the extension of their real content (`App\Support\Uploads`).
- `public/resources/.htaccess` stops scripts from running in the upload folders. `.mdb` and `.sql` files cannot be downloaded.
- New `.sql` dumps in `database/` are ignored by git.

### Values the browser can no longer set
- The server decides stock direction, status and document type on issuances and deliveries.
- Order edits ignore any posted status, number, creator or totals; line totals are price × quantity.
- A delivery edit changes only its own stock document.
- Payments need an allowed payee/type pair, an amount above 0 and an existing bank (or cash).
- The delivery and receipt status buttons send a form (POST) and accept only valid statuses.

### Stock figures
- One shared stock formula in `StockItemRepository` replaces about 15 copies.
- A receipt line with two partial returns used to be counted twice: 100 − 10 − 5 showed 185, now 85.
- "No component" is always stored as NULL, so new lines cannot disappear from `/stock`.
- A material cannot go below zero through an issuance, machine issue, PTC, material processing or adjustment. The check runs under a row lock, so two saves at the same moment cannot both pass.

### Locked once used
- **Receipts:**
  - approved + rejected + pending must equal the received quantity;
  - receiving above the ordered quantity is refused;
  - a line with returns, or whose stock was already used, cannot be lowered or removed.
- **Purchases:** once something is received on a line, its item and price are fixed and its quantity cannot go below what was received. A material-processing order with receipts keeps its lines.
- **Orders:** once a line is delivered, its price and the customer are fixed, it cannot be removed, and its quantity cannot go below what was delivered. Delivering more than was ordered is refused by the server.
- Refusals end with "To correct it, ask an admin." Dates, remarks and descriptions stay editable.

### Order status

| From | To | Condition |
|---|---|---|
| Draft | Confirmed, Cancelled | Cancel needs no delivery and no reserved stock |
| Confirmed | Dispatched, Delivered | At least one delivery exists |
| Confirmed | Cancelled | No delivery and no reserved stock |
| Dispatched | Delivered | — |
| Any status | An earlier status (re-open) | Admin only; the target's conditions still apply |

A new order starts as Draft or Confirmed. The same rules apply on the delivery form.

### Money
- Vendor balance and vendor ledger use the approved quantity.
- A payment with its bank charges, and a transfer with both its halves, are saved together or not at all.
- The cash/bank balance check runs under a lock, also when a payment is edited.
- Edits cannot change `ledger_flag`, `transfer_pair_id` or `created_by`.

### All-or-nothing saves
Orders, purchases, receipts, material processing, payments and transfers each save in one database transaction.

### Document numbers
- Job, delivery, issuance, PTC and adjustment numbers are the highest existing number + 1, taken inside the save (`App\Support\DocumentNumbers`).
- A deleted number never comes back, and two saves at once cannot get the same number.
- Year and month are taken in Pakistan time (Asia/Karachi). Formats are unchanged.
- Migrations add UNIQUE indexes on order no, job no and delivery no.
- The stock-number migration stops and lists the 10 old duplicates instead of changing them (question Q20).

### Security
- Login allows 5 failed attempts per minute per email and IP.
- Logout ends the session completely.
- Reset Test Data routes exist only when `ALLOW_DATA_RESET=true` (replaced 2026-11-25: admin only, always available).

### Reports
- Low Stock card: uses the real stock figures (now 19, was 34).
- Expense This Month: expenses minus reversals.
- Running PTCs: each PTC once, with its order (job no, or "Default PTC") and a search box like the other dashboard tables.
- Receipt print: shows the real receiving status.
- Excel invoice: address and free-text cells cannot become formulas.
- New indexes on the stock and transaction tables speed up stock and ledger lookups.

## New and changed files

| Area | Files |
|---|---|
| New support classes | `app/Support/Uploads.php`, `DocumentNumbers.php`, `UniqueIndexGuard.php` |
| New exceptions | `app/Exceptions/DocumentLockedException.php`, `MaterialStockException.php` |
| New requests | `StockUpdateRequest`, `DeliveryUpdateRequest`, `OrderUpdateRequest`, `ReceiveUpdateRequest`, `TransactionUpdateRequest` |
| New migrations | `2026_11_25_100000` (lookup indexes), `100001`-`100004` (unique numbers) |
| Test setup | `.env.testing`, `phpunit.xml`, `tests/TestCase.php` |
| Changed | Controllers, repositories, requests, models and views of stock, delivery, order, purchase, receipt, material processing, transactions, dashboard and auth; `routes/web.php`; `config/app.php`; `public/.htaccess`; `.gitignore`; `.env.example` |

## Not done, waiting for the owner

- **Dependency updates (Q23):** checked, not installed. Both possible updates also touch packages outside the vulnerable list.
- **Data corrections:** written in [DATA_FIXES.md](DATA_FIXES.md), not run. They include the 395 hidden units on `/stock`, the negative product 407 at stage 146, the batch rebuild and an orphan vendor entry.
- **Stock-number index (Q20):** don't remove the 10 duplicate stock numbers until Q20 is answered.
- **Other open questions:** Q19-Q24 (texts not kept in the repository); later questions in [AUDIT_QUESTIONS.md](AUDIT_QUESTIONS.md).
- **Production steps:** in [OWNER_ACTIONS.md](OWNER_ACTIONS.md). Backups: [BACKUP.md](BACKUP.md).
