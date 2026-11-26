# SmartERP status

## 2026-11-26: cleanup pass (removal of approved dead / unused items)

Branch `surgical-fix`. Only items approved by the owner from the cleanup audit of 2026-11-26 were removed;
no behaviour change intended. Tests run on `smarterp_test` only (docs/TESTING.md).

**Test result after the cleanup:** 161 tests, 154 passed, 7 failed (1840 assertions). The 7 failures are the
known ones in docs/TESTING.md (AttendanceMachineTest x2, BankChargesTransactionTest x3, PayrollTest x1,
RoleLinkCrawlTest x1). Before the cleanup: 160 tests, 153 passed, the same 7 failed. The count changed by:
-1 skeleton unit test removed, +1 new `CustomerOrdersGuestTest`, +1 test added by commit `a1adf23`
(employee deletion, made separately on the same day).

**Routes:** 437 → 434 (`bank.show`, `bank.edit`, `company.destroy` removed; nothing else changed).

**Removed**
- 10 empty stub controllers (HeadType, IGroupItem, OrderItem, ProductType, PurchaseItem, ReceiveMaterial,
  ReturnMaterial, Role, Salary, StockItem), `ReceiveMaterialRequest.php` (declared a misnamed class),
  `ContractorPolicy` (never registered).
- Unused repository / model / controller methods (`gStock`, `orderDeliveryOld`, `orderDeliveryTemp`,
  `wagesNotUsed`, `createCashBalance`, `ajaxGetCustomerCurrency`, empty resource methods, ...), unused
  injected repositories and imports, `User::ROLE_MANAGER` / `ROLE_ACCOUNTANT`.
- Routes `/bank/{id}`, `/editBank/{id}` (empty actions) and `DELETE /company/{id}`.
- The redundant `['web', 'auth']` wrapper around `ajax.customer-orders` (the controller enforces `auth`;
  covered by `CustomerOrdersGuestTest`).
- Views `editMProcessOld`, `headOld`, `print/order-invoice`, `print/production-order-invoice`.
- Dead JavaScript: the old client-side packing-list / invoice print code in `deliveryInfo.blade.php`
  (with its console.log), `loadAllProducts` and `updateAvailableStockOld` in custom.js.
- Commented-out code: 32 blocks in views, the listed PHP / JS blocks.
- `resources/views/Template/` (theme demo copy, 898 files), unused theme bundles / page scripts / demo images
  in `public/assets` (689 files), `public/project.html`, the 4 Laravel-skeleton GitHub workflows.
- `ALLOW_DATA_RESET` leftovers in `.env.example` / `.env.testing`; `.env.example` now lists
  `ATTENDANCE_DB_PATH` and `ATTENDANCE_DB_ALLOWED_DIRS` with the config defaults.

**Kept on purpose** (not approved, or still referenced): `app/Models/Attendance.php` (its name is used for
the Attendance policy binding), `CompanyRepository::delete` (required by `GlobalInterface`), 13 demo images
whose file names also occur inside stored upload names, delivery-return Edit (Q26), print.js (Q29).

**Owner follow-ups:** run `composer dump-autoload` once (11 classmap entries still point at deleted files;
harmless until then). Open questions: docs/AUDIT_QUESTIONS.md.
