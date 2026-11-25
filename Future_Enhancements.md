# SmartERP: Recommended Future Enhancements

Prepared: 25 November 2026
Basis: the SmartERP system audit of the same date.

## 1. Purpose and how to read this

The current work fixes and stabilizes what SmartERP already does. This document lists new capabilities that the audit found missing. Each item is optional, separately scoped, and can be quoted on its own.

- **Effort** is a planning estimate in working days. It is not a quotation and must be confirmed before pricing. It assumes the current fix work is finished.
- **Priority:** *Needed* = the business is exposed without it. *Useful* = clear benefit. *Optional* = only if the business wants it.
- **Price** is left blank for you to fill in.
- Items marked "decision" depend on a business answer that has not been given yet.

## 2. What is not in this list

The following are corrections to existing functions. They are part of the current fix work and are not billed as new features:

- validation of order, purchase, receipt, payment and delivery input
- protection of stock figures (one correct stock formula, no negative stock, locks on documents already used by later documents)
- upload safety, login throttling, session handling
- unique document numbers
- saving documents all-or-nothing
- wrong dashboard figures (Low Stock, Expense This Month, Running PTCs)
- missing database indexes
- security patches within the current framework version
- a safe test setup and a written backup procedure
- scripts for correcting existing wrong data (run by the owner)

## 3. Recommended enhancements

### Package A: Financial control

| # | Enhancement | What it gives the business | Why (audit evidence) | Effort (days) | Needs | Priority | Price |
|---|---|---|---|---|---|---|---|
| A1 | Stored sales invoices and credit notes | An invoice is fixed at delivery and keeps its numbers. Customer ledger reads stored amounts. Returned goods produce a credit note. | Invoices are printed from the current order price, so past totals change if an order is edited. Delivery returns restore stock but give no customer credit. | 6 to 10 | Numbering format decision | Needed | |
| A2 | Period close (posting lock) | Closed months cannot be changed by later edits. | No date lock exists; past months can change. | 3 to 5 | A1 | Needed | |
| A3 | Receivables and payables ageing | Who owes what and for how long; collection follow-up. | Only party ledgers exist. | 2 to 4 | Better after A1 | Needed | |
| A4 | Receipt, vendor invoice and payment matching | Pay vendors only for what was received and invoiced. | Vendor bills are journal entries not linked to receipts. | 6 to 10 | Decision on rejected quantity | Needed | |
| A5 | Order and purchase order status lifecycle, PO close | Open orders and open POs reports become reliable. | Statuses are manual; old POs stay open. | 3 to 5 | Decision on status rules | Needed | |

### Package B: Inventory and costing

| # | Enhancement | What it gives the business | Why (audit evidence) | Effort (days) | Needs | Priority | Price |
|---|---|---|---|---|---|---|---|
| B1 | Stock valuation with weighted-average cost (materials and products) | Value of stock on hand, tied to receipts. | Cost is the last PO price only; products have no valuation. | 5 to 8 | Decision on cost method | Needed | |
| B2 | Stock count (cycle count) document | Count sheet, variance, approved adjustment. | Adjustments are made one by one with no count record. | 5 to 8 | | Needed | |
| B3 | Production costing per PTC and margin per order line | Actual cost of material and wages against the bill of materials. | No PTC cost is recorded. | 10 to 15 | B1 | Needed | |
| B4 | Reorder levels and low-stock alerts per item | Buy before stock runs out. | The current Low Stock card uses one fixed rule. | 3 to 5 | Levels per item from the business | Useful | |

### Package C: Planning and purchasing

| # | Enhancement | What it gives the business | Why (audit evidence) | Effort (days) | Needs | Priority | Price |
|---|---|---|---|---|---|---|---|
| C1 | Purchase suggestions from order shortages | The shortage report turns into suggested purchase quantities. | The Stock Requirements report is read by hand. | 10 to 15 | Correct stock, good BOM data | Useful | |
| C2 | Purchase order approval step | A second person approves large purchases. | The same user can raise, receive and pay. | 3 to 5 | Approval limits from the business | Useful | |
| C3 | Management reports: supplier performance, on-time delivery, open orders against delivered, stage yield and rejection, material use against BOM | Data for decisions that is already stored. | The data exists; no reports use it. | 4 to 8 | | Useful | |

### Package D: Controls and security

| # | Enhancement | What it gives the business | Why (audit evidence) | Effort (days) | Needs | Priority | Price |
|---|---|---|---|---|---|---|---|
| D1 | Change history on orders, purchases, receipts, stock documents and payments | Who changed what and when. | Only four record types are logged today. | 3 to 5 | | Needed | |
| D2 | User deactivation, password reset, optional second login step | Safe handling of staff leaving and lost passwords. | There is no deactivation flag and no reset flow. | 3 to 6 | | Needed | |
| D3 | Automated backups with off-machine copy and restore test | Recovery from disk failure or mistakes. | No backup tooling exists. | 2 to 4 | Backup location | Needed | |

### Package E: Platform and performance

| # | Enhancement | What it gives the business | Why (audit evidence) | Effort (days) | Needs | Priority | Price |
|---|---|---|---|---|---|---|---|
| E1 | Upgrade from Laravel 10 to a supported version | Continued security fixes and package support. | Laravel 10 is out of security support (verify the current dates). | 10 to 20 | Full regression testing | Needed | |
| E2 | Exact number types for money and quantities, with before and after reconciliation | No rounding drift in totals. | Prices, totals and quantities are stored as floating-point numbers. | 4 to 7 | Backup, reconciliation sign-off | Useful | |
| E3 | Server-side paging for large lists | Fast screens as data grows. | Lists load every row in the browser. | 5 to 10 | | Useful | |
| E4 | Private storage and authorized download for product files and images | Files are not reachable by link. | Files live in the public web folder. | 3 to 5 | | Useful | |
| E5 | Automated test run on every change | Faster, safer releases. | The sample test workflow does not fit this project. | 2 to 4 | Hosting for the runner | Optional | |

### Package F: Sales and documents

| # | Enhancement | What it gives the business | Why (audit evidence) | Effort (days) | Needs | Priority | Price |
|---|---|---|---|---|---|---|---|
| F1 | Quotations that convert to orders | Quote, revise, accept, order, all in one system. | Quotations are done outside the system. | 8 to 12 | | Optional | |
| F2 | Sending documents by email or WhatsApp, shipment tracking | Less manual work after dispatch. | Documents are printed and sent by hand. | 4 to 8 | Provider account | Optional | |
| F3 | Unit-of-measure conversions | Buy in one unit, stock in another. | Each material has a single unit. | 5 to 8 | Confirm the need | Optional | |

### Package G: Specialized, only if the business needs them

| # | Enhancement | What it gives the business | Why (audit evidence) | Effort (days) | Needs | Priority | Price |
|---|---|---|---|---|---|---|---|
| G1 | Barcode or QR labels for batches and process travel cards | Faster, error-free store and shop-floor entry. | Paper cards and manual entry today. | 5 to 10 | Label printer, scanners | Optional | |
| G2 | Machine maintenance schedule and downtime log | Planned maintenance, downtime history. | Only a machine register exists. | 8 to 12 | Confirm machine-heavy work | Optional | |
| G3 | Tax codes and reports, general ledger with trial balance | The ERP can produce statutory books. | Accounting is assumed to be done elsewhere. | 20 to 40 or more | Accountant's requirements; current local tax rules must be verified | Optional until confirmed | |

## 4. Suggested order

1. **Financial control first:** A1, then A2 and A3. These protect money figures and depend on the fixes already done.
2. **Inventory and costing:** B1 and B2, then B3. B3 only makes sense after B1.
3. **Controls:** D1, D2 and D3 can be done at any time and are low-risk.
4. **Platform:** E1 should be planned within the next year because of the framework support dates. Do E2 only with a signed-off before-and-after reconciliation.
5. **Planning and sales (Packages C and F):** when the business wants to grow the system further.
6. **Package G:** only after the business confirms the need.

## 5. How to price (suggestion)

- Fixed price per item or per package, based on the effort estimate multiplied by a day rate, plus a contingency for items that need a business decision.
- Agree written acceptance criteria and a test list per item before starting.
- Items that depend on a decision (A4, A5, B1, C2, G3) should be quoted only after the decision is made.

## 6. Assumptions and limits

- Effort figures are rough planning estimates, not measured.
- They assume the stabilization work is complete, so the stock formula, document locks and numbering are already reliable.
- Anything involving tax, statutory accounting or legal requirements must be checked with an accountant against current official rules before work starts.
- Effort for E1 (framework upgrade) depends heavily on how much of the code is affected. Get the upgrade impact checked before quoting.
