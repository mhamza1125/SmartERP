<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Print Document')</title>
    <style>
        /* =================================================================
           SmartERP — Print Design System
           Self-contained: no external stylesheets, no CDN dependencies.
           System font stack only.
           ================================================================= */

        /* ── 0. Design tokens ──────────────────────────────────────────── */
        :root {
            --accent:    #1f3a5f;
            --ink:       #1a1d21;
            --muted:     #6b707a;
            --hairline:  #e6e9ee;
            --row-alt:   #fafbfc;
            --sans:      "Arial", "Helvetica Neue", Helvetica, sans-serif;
            --mono:      "Courier New", Courier, monospace;
            --header-h:  20mm;
        }

        /* ── 1. Reset & color accuracy ─────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        /* ── 2. Page ────────────────────────────────────────────────────── */
        @page { size: A4; margin: 0; }

        /* ── 3. Body ────────────────────────────────────────────────────── */
        body {
            font-family: var(--sans);
            font-size: 11px;
            line-height: 1.45;
            color: var(--ink);
            background: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ── 4. Fixed header ─────────────────────────────────────────────── */
        .doc-header {
            position: fixed;
            top: 0; left: 0; right: 0;
            background: #fff;
            z-index: 1000;
        }

        .doc-header__inner {
            display: grid;
            /* Fixed shares: a long title wraps inside its own column instead of pushing into the logo */
            grid-template-columns: minmax(0, 32fr) minmax(0, 36fr) minmax(0, 32fr);  /* equal sides keep the title on the page centre */
            align-items: center;
            gap: 12px;
            padding: 3mm 14mm 2.5mm;
        }

        .doc-header__brand { min-width: 0; }  /* fills its column, so the logo is capped at the column width */

        .doc-header__logo {
            height: 50px;
            width: auto;
            max-width: 100%;
            object-fit: contain;
            display: block;
        }

        .doc-header__title { text-align: center; min-width: 0; }

        .doc-header__title-main {
            font-size: 13px;
            font-weight: 700;
            color: var(--accent);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            line-height: 1.2;
            overflow-wrap: anywhere;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            overflow: hidden;
        }

        .doc-header__title-no {
            font-family: var(--mono);
            font-size: 11px;
            font-weight: 700;
            color: var(--ink);
            margin-top: 2px;
        }

        .doc-header__contacts {
            justify-self: end;
            text-align: right;
            font-size: 9px;
            line-height: 1.5;
            color: var(--ink);
            min-width: 0;
            overflow-wrap: anywhere;
        }

        .doc-header__contact-label {
            font-family: var(--mono);
            font-size: 7.5px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--muted);
            margin-right: 3px;
        }

        .doc-header__rule {
            height: 3px;
            background: var(--accent);
        }

        /* ── 6. Content area ─────────────────────────────────────────────── */
        .doc-body {
            padding: 5mm 14mm 8mm;
        }

        /* Page frame: thead/tfoot spacers repeat on every printed page so
           content never runs under the fixed header/footer on page 2+. */
        .page-frame                { width: 100%; border-collapse: collapse; }
        .page-frame > * > tr > td  { padding: 0; }
        .page-frame__header-space  { height: calc(var(--header-h) + 3mm); }
        .page-frame__footer-space  { height: 8mm; }

        /* ── 7. Meta band ────────────────────────────────────────────────── */
        .meta-band {
            display: flex;
            flex-wrap: wrap;
            border: 1px solid var(--hairline);
            border-radius: 2px;
            margin-bottom: 12px;
            overflow: hidden;
        }

        .meta-field {
            flex: 1;
            min-width: 90px;
            padding: 5px 10px;
            border-right: 1px solid var(--hairline);
        }

        .meta-field:last-child { border-right: none; }

        /* Header card titles 10px; the values in the cards stay 11px */
        .meta-label {
            display: block;
            font-family: var(--mono);
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--muted);
            margin-bottom: 2px;
        }

        .meta-value {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: var(--ink);
        }

        .meta-value.mono { font-family: var(--mono); }

        /* Boxed header grid (all prints): 3 boxes per row (--cols-4 for 4). Hairlines are
           drawn as shadows so a short last row leaves no grey gaps; the outer ones are clipped.
           .meta-group only groups fields in the markup (old two-column sections). */
        .meta-band--grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        .meta-band--grid.meta-band--cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .meta-band--grid .meta-field {
            min-width: 0;
            border-right: none;
            box-shadow: 1px 0 0 var(--hairline), 0 1px 0 var(--hairline);
            overflow-wrap: anywhere;
        }
        .meta-band--grid .meta-field--span2 { grid-column: span 2; }
        .meta-band--grid .meta-field--wide  { grid-column: 1 / -1; }
        .meta-group { display: contents; }
        /* Every box reads the same way: values start at the left, even ones the old layout right-aligned */
        .meta-band--grid .meta-value,
        .meta-band--grid .meta-value.text-right { text-align: left !important; }
        .party-block--spaced { margin-bottom: 12px; }

        /* ── 8. Status pills ─────────────────────────────────────────────── */
        .status-pill {
            display: inline-block;
            padding: 1px 7px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .pill-success  { background: #d4edda; color: #1e6e35; border: 1px solid #b1dfbb; }
        .pill-warning  { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .pill-danger   { background: #f8d7da; color: #842029; border: 1px solid #f5c6cb; }
        .pill-info     { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .pill-secondary{ background: #e9ecef; color: #495057; border: 1px solid #dee2e6; }

        /* ── 9. Party block ──────────────────────────────────────────────── */
        .party-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 12px;
        }

        .party-block {
            padding: 7px 10px;
            border: 1px solid var(--hairline);
            border-left: 3px solid var(--accent);
        }

        .party-label {
            font-family: var(--mono);
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--accent);
            margin-bottom: 3px;
        }

        .party-name {
            font-size: 12px;
            font-weight: 700;
            color: var(--ink);
            line-height: 1.3;
        }

        .party-detail {
            font-size: 10px;
            color: var(--muted);
            margin-top: 1px;
            line-height: 1.4;
        }

        .party-contact {
            font-family: var(--mono);
            font-size: 9.5px;
            color: var(--muted);
            margin-top: 2px;
        }

        /* ── 10. Data table ──────────────────────────────────────────────── */
        .print-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 11px;
        }

        .print-table thead tr {
            background: var(--accent);
        }

        .print-table thead th {
            color: #fff;
            font-family: var(--mono);
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            padding: 6px 8px;
            text-align: left;
            font-weight: 700;
            white-space: nowrap;
        }

        .print-table thead th.text-right  { text-align: right; }
        .print-table thead th.text-center { text-align: center; }

        /* Column widths follow the content and the page width: static widths set on cells are
           ignored, so long article numbers, names and sizes use the space other columns leave.
           Tables marked data-fixed-widths (blank forms filled in by hand) keep their widths. */
        .print-table:not([data-fixed-widths]) th,
        .print-table:not([data-fixed-widths]) td { width: auto !important; }
        /* Fixed-width blank forms have many narrow columns: their headings may wrap */
        .print-table[data-fixed-widths] thead th { white-space: normal; }

        /* Numbers, codes and dates never break over two lines */
        .print-table td.num, .print-table td.code, .print-table td.mono,
        .print-table td.amount { white-space: nowrap; }

        .print-table tbody tr {
            border-bottom: 1px solid var(--hairline);
        }

        .print-table tbody tr:nth-child(even) {
            background: var(--row-alt);
        }

        .print-table tbody td {
            padding: 5px 8px;
            vertical-align: middle;
            color: var(--ink);
        }

        .print-table tfoot tr {
            border-top: 2px solid var(--hairline);
            background: var(--row-alt);
        }

        .print-table tfoot td {
            padding: 5px 8px;
            font-weight: 600;
        }

        /* ── 11. Alignment & monospace utilities ─────────────────────────── */
        .text-right  { text-align: right  !important; }
        .text-center { text-align: center !important; }
        .text-left   { text-align: left   !important; }

        .num {
            text-align: right   !important;
            font-family: var(--mono) !important;
        }

        .code {
            text-align: center  !important;
            font-family: var(--mono) !important;
        }

        .amount {
            text-align: right   !important;
            font-family: var(--mono) !important;
        }

        .mono { font-family: var(--mono) !important; }

        /* ── 12. Totals block ────────────────────────────────────────────── */
        .totals-wrap {
            display: flex;
            justify-content: flex-end;
            margin: 0 0 14px;
        }

        .totals-block {
            min-width: 220px;
            max-width: 300px;
        }

        .totals-block__row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 3px 8px;
            font-size: 10.5px;
            color: var(--muted);
            border-bottom: 1px solid var(--hairline);
        }

        .totals-block__row.grand {
            background: var(--accent);
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            padding: 5px 8px;
            margin-top: 2px;
            border-bottom: none;
            border-radius: 2px;
        }

        .totals-block__row .val {
            font-family: var(--mono);
            font-weight: 700;
            white-space: nowrap;
        }

        /* ── 13. Amount in words ─────────────────────────────────────────── */
        .amount-words {
            margin-top: 10px;
            padding: 5px 10px;
            border: 1px solid var(--hairline);
            background: var(--row-alt);
            font-family: var(--sans);
            font-size: 11px;
            font-weight: 600;
            line-height: 1.5;
        }

        .amount-words-label {
            font-family: var(--mono);
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--muted);
            margin-right: 6px;
        }

        /* ── 14. Section headings / note boxes ───────────────────────────── */
        .section-head {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--accent);
            margin-bottom: 6px;
            padding-bottom: 3px;
            border-bottom: 2px solid var(--accent);
        }

        .note-box {
            padding: 6px 10px;
            border: 1px solid var(--hairline);
            background: var(--row-alt);
            font-size: 10.5px;
            line-height: 1.5;
            margin-bottom: 10px;
        }

        /* ── 15. Document title row (inside content) ────────────────────── */
        .doc-title-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 10px;
            padding-bottom: 6px;
            border-bottom: 2px solid var(--accent);
        }

        .doc-title-label {
            font-size: 16px;
            font-weight: 700;
            color: var(--accent);
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .doc-title-no {
            font-family: var(--mono);
            font-size: 11px;
            color: var(--muted);
        }

        /* ── 16. Signature section ───────────────────────────────────────── */
        .signatures {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            gap: 12px;
        }

        .signature-box {
            flex: 1;
            text-align: center;
        }

        .signature-line {
            border-bottom: 1px solid var(--ink);
            height: 28px;
            margin-bottom: 3px;
        }

        .signature-line-sm {
            border-bottom: 1px solid var(--ink);
            height: 18px;
            margin-bottom: 3px;
        }

        .signature-label {
            font-size: 9px;
            color: var(--muted);
        }

        /* ── 16. Legacy compatibility ────────────────────────────────────── */
        /* Keeps un-refactored views rendering correctly until bulk-apply. */

        .document-title {
            text-align: center;
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 14px;
            text-transform: uppercase;
            color: var(--accent);
            border-bottom: 2px solid var(--accent);
            padding-bottom: 6px;
        }

        .document-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 14px;
            gap: 10px;
        }

        .info-section {
            flex: 1;
            margin-bottom: 14px;
            padding-right: 10px;
        }

        .info-section:last-child { padding-right: 0; }

        .info-row {
            display: flex;
            margin-bottom: 4px;
            font-size: 11px;
        }

        .info-label {
            font-weight: 600;
            width: 130px;
            flex-shrink: 0;
            color: var(--muted);
        }

        .info-value { flex-grow: 1; color: var(--ink); }

        .items-section { margin-bottom: 14px; }

        .items-section h3,
        .info-section > h3,
        .doc-body h3 {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--accent);
            margin-bottom: 6px;
            font-weight: 700;
            border-bottom: 2px solid var(--accent);
            padding-bottom: 3px;
        }

        .receive-record {
            margin-bottom: 12px;
            border: 1px solid var(--hairline);
            padding: 8px 10px;
            background: var(--row-alt);
        }

        .receive-record h4 {
            font-family: var(--mono);
            font-size: 9px;
            color: var(--ink);
        }

        .totals-section {
            margin-top: 14px;
            padding-top: 8px;
            border-top: 2px solid var(--hairline);
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
            font-weight: 600;
            font-size: 11px;
        }

        .grand-total {
            font-size: 12px;
            border-top: 1px solid var(--hairline);
            padding-top: 4px;
            margin-top: 8px;
        }

        /* Amount in words written inside a grand-total row (proforma / commercial invoice) */
        .grand-total__words { font-size: 11px; }

        .status-badge {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 2px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            border: 1px solid transparent;
        }

        .status-badge-success  { background: #d4edda; color: #1e6e35; border-color: #b1dfbb; }
        .status-badge-warning  { background: #fff3cd; color: #856404; border-color: #ffeeba; }
        .status-badge-danger   { background: #f8d7da; color: #842029; border-color: #f5c6cb; }
        .status-badge-info     { background: #d1ecf1; color: #0c5460; border-color: #bee5eb; }
        .status-badge-secondary{ background: #e9ecef; color: #495057; border-color: #dee2e6; }
        .status-badge-primary  { background: #cce5ff; color: #004085; border-color: #b8daff; }

        .voucher-number { font-family: var(--mono); font-weight: 700; font-size: 12px; }

        .watermark-container { position: relative; z-index: 1; }

        .watermark-container::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: url('{{ asset("assets/print-logo2.png") }}');
            background-repeat: no-repeat;
            background-position: center;
            background-size: 600px;
            opacity: 0.07;
            z-index: 0;
        }

        .watermark-container > * { position: relative; z-index: 1; }

        .account-status        { padding: 8px 10px; border: 1px solid var(--hairline); font-size: 11px; }
        .account-status-credit { background: #d4edda; }
        .account-status-debit  { background: #f8d7da; }
        .account-status-zero   { background: var(--row-alt); }

        .certification-statement {
            margin-top: 24px;
            text-align: left;
            font-weight: 700;
            font-size: 11px;
        }

        .certification-statement p { white-space: nowrap; }


        /* ── Table titles & sign-off lines ───────────────────────────────── */
        .table-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--accent);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin: 6px 0 6px;
            padding-bottom: 4px;
            border-bottom: 2px solid var(--accent);
        }

        .sign-row {
            display: flex;
            justify-content: space-between;
            gap: 24px;
            margin-top: 28px;
            font-size: 11px;
            font-weight: 600;
            page-break-inside: avoid;
        }

        .sign-row--single { justify-content: flex-start; }

        .sign-row__field { white-space: nowrap; }

        .sign-row__line {
            display: inline-block;
            width: 55mm;
            border-bottom: 1px solid var(--ink);
            margin-left: 6px;
        }

        /* Numeric columns (qty, price, amount): right-aligned in head, body and foot */
        .print-table th.num, .print-table td.num { text-align: right !important; }

        /* ── 18. Page-break helpers ──────────────────────────────────────── */
        .page-break  { page-break-before: always; }
        .avoid-break { page-break-inside: avoid; }

        /* ── 19. Print button (screen only) ─────────────────────────────── */
        .print-button {
            position: fixed;
            bottom: 16px; right: 16px;
            z-index: 9999;
            padding: 8px 20px;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-family: var(--sans);
            box-shadow: 0 2px 6px rgba(0,0,0,0.18);
        }

        .print-button:hover { opacity: .88; }

        /* ── 20. @media print ───────────────────────────────────────────── */
        @media print {
            body {
                padding-right:  14mm             !important;
                padding-left:   14mm             !important;
                background: #fff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print { display: none !important; }

            .print-table tbody tr  { page-break-inside: avoid; }
            .avoid-break           { page-break-inside: avoid; }
            .signatures            { page-break-inside: avoid; }
            .totals-wrap           { page-break-inside: avoid; }
            .totals-block          { page-break-inside: avoid; }
            .totals-section        { page-break-inside: avoid; }
            .amount-words          { page-break-inside: avoid; }

            h1, h2, h3, h4 { page-break-after: avoid; }

            a { color: var(--ink) !important; text-decoration: none !important; }

            .card, .table, .panel { box-shadow: none !important; }
        }

        /* ── 21. Screen preview ─────────────────────────────────────────── */
        @media screen {
            body { background: #e6e9ef; }

            .doc-header { position: relative; background: transparent; }

            .doc-header__inner,
            .doc-header__rule { max-width: 210mm; margin: 0 auto; background: #fff; }

            .page-frame__header-space,
            .page-frame__footer-space { display: none; }

            .doc-body {
                max-width: 210mm;
                margin: 0 auto;
                background: #fff;
                box-shadow: 0 4px 20px rgba(0,0,0,0.10);
                min-height: 250mm;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">Print</button>

    <header class="doc-header">
        @include('print.header')
    </header>

    <table class="page-frame">
        <thead><tr><td><div class="page-frame__header-space"></div></td></tr></thead>
        <tbody><tr><td>
            <main class="doc-body">
                @yield('content')
            </main>
        </td></tr></tbody>
        <tfoot><tr><td><div class="page-frame__footer-space"></div></td></tr></tfoot>
    </table>


    <script>
        function applyChromeHeights() {
            var root = document.documentElement.style;
            var h = document.querySelector('.doc-header');
            if (h) root.setProperty('--header-h', h.offsetHeight + 'px');
        }
        window.addEventListener('load', applyChromeHeights);
        window.addEventListener('beforeprint', applyChromeHeights);
        setTimeout(applyChromeHeights, 600);

        // Table alignment rules for every print:
        //  1. Numeric columns (QTY, price, totals, amounts, debit/credit, balance, weight...) are
        //     right-aligned in the header, body and footer.
        //  2. Every header cell takes the alignment of the data below it, so titles and values line up.
        // Cells are mapped to columns through their colspans; tables using rowspan keep their own classes.
        var NUMERIC_HEAD = /\b(qty|quantity|price|rate|total|amount|debit|credit|balance|stock in|stock out|total stock|weight|pcs|boxes|cartons|average|avg|ordered|received|returned|remaining|finished|unfinished|work done|wages|cost|value)\b/i;
        var NOT_NUMERIC_HEAD = /\b(no|name|date|from|to|by|type|description|method|reference|details|head|stage|remarks|reason|code|status)\b/i;

        function cellsByColumn(row) {
            var map = {}, col = 0;
            Array.prototype.forEach.call(row.cells, function (cell) {
                if (cell.colSpan === 1) map[col] = cell;
                col += cell.colSpan;
            });
            return map;
        }

        function alignPrintTables() {
            document.querySelectorAll('table.print-table').forEach(function (table) {
                // data-no-align: blank forms keep their own (centred) headers
                if (!table.tHead || table.querySelector('[rowspan]') || table.hasAttribute('data-no-align')) return;
                var headRow = table.tHead.rows[table.tHead.rows.length - 1];
                var heads = cellsByColumn(headRow);
                var rows = [];
                Array.prototype.forEach.call(table.tBodies, function (b) { rows = rows.concat(Array.prototype.slice.call(b.rows)); });
                if (table.tFoot) rows = rows.concat(Array.prototype.slice.call(table.tFoot.rows));

                Object.keys(heads).forEach(function (col) {
                    var text = heads[col].textContent.replace(/\s+/g, ' ').trim();
                    if (NUMERIC_HEAD.test(text) && !NOT_NUMERIC_HEAD.test(text)) {
                        heads[col].style.setProperty('text-align', 'right', 'important');
                        rows.forEach(function (r) {
                            var cell = cellsByColumn(r)[col];
                            if (cell) cell.style.setProperty('text-align', 'right', 'important');
                        });
                        return;
                    }
                    var sample = rows.map(function (r) { return cellsByColumn(r)[col]; })
                        .filter(function (c) { return c && c.textContent.trim() !== ''; })[0];
                    if (sample) heads[col].style.setProperty('text-align', window.getComputedStyle(sample).textAlign, 'important');
                });
            });
        }
        document.addEventListener('DOMContentLoaded', alignPrintTables);

        // Boxed header grid: a long value (address, description, several numbers) gets a
        // double-width box, a very long one the whole row, so it is not squeezed into one box
        function widenLongMetaFields() {
            document.querySelectorAll('.meta-band--grid .meta-field').forEach(function (field) {
                if (field.classList.contains('meta-field--wide') || field.classList.contains('meta-field--span2')) return;
                var value = field.querySelector('.meta-value');
                var length = value ? value.textContent.replace(/\s+/g, ' ').trim().length : 0;
                if (length > 70) field.classList.add('meta-field--wide');
                else if (length > 28) field.classList.add('meta-field--span2');
            });
        }
        document.addEventListener('DOMContentLoaded', widenLongMetaFields);
    </script>

    @stack('scripts')
</body>
</html>
