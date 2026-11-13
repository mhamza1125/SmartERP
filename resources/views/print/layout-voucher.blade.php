{{-- sheetOnly: output just this document's half-page sheet, for printing several together --}}
@if(!empty($sheetOnly))
@include('print.partials.voucher-sheet')
@else
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Voucher')</title>
    <style>
        /* =================================================================
           SmartERP — Voucher Print Layout
           Inherits the same design tokens as layout.blade.php.
           Half-page voucher: printed on a portrait A4 sheet, filling its top half
           (210 x 148.5 mm) with a dashed cut line, so two vouchers fit on one sheet.
           ================================================================= */

        /* ── 0. Design tokens (must match layout.blade.php) ────────────── */
        :root {
            --accent:    #1f3a5f;
            --ink:       #1a1d21;
            --muted:     #6b707a;
            --hairline:  #e6e9ee;
            --row-alt:   #fafbfc;
            --sans:      "Arial", "Helvetica Neue", Helvetica, sans-serif;
            --mono:      "Courier New", Courier, monospace;
            --header-h:  18mm;
        }

        /* ── 1. Reset ───────────────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        /* ── 2. Page ────────────────────────────────────────────────────── */
        @page { size: A4 portrait; margin: 0; }

        /* ── 3. Body ────────────────────────────────────────────────────── */
        body {
            font-family: var(--sans);
            font-size: 10px;
            line-height: 1.4;
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
            padding: 2.5mm 8mm 2mm;
        }

        .doc-header__brand { min-width: 0; }  /* fills its column, so the logo is capped at the column width */

        .doc-header__logo {
            height: 34px;
            width: auto;
            max-width: 100%;
            object-fit: contain;
            display: block;
        }

        .doc-header__title { text-align: center; min-width: 0; }

        .doc-header__title-main {
            font-size: 12px;
            font-weight: 900; /* print page titles: heaviest weight */
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

        .doc-header__rule { height: 3px; background: var(--accent); }

        /* ── 6. Voucher container ────────────────────────────────────────── */
        .doc-body {
            padding: 3mm 8mm 4mm;
        }

        /* Page frame: thead/tfoot spacers repeat on every printed page so
           content never runs under the fixed header/footer on page 2+. */
        .page-frame                { width: 100%; border-collapse: collapse; }
        .page-frame > * > tr > td  { padding: 0; }
        .page-frame__header-space  { height: calc(var(--header-h) + 3mm); }
        .page-frame__footer-space  { height: 8mm; }

        .voucher-container {
            border: 1.5px solid var(--ink);
            padding: 8px 10px;
            box-sizing: border-box;
        }

        /* ── 7. Info layout (tighter for voucher) ────────────────────────── */
        .document-title {
            text-align: center;
            font-size: 12px;
            font-weight: 900;
            margin-bottom: 8px;
            text-transform: uppercase;
            color: var(--accent);
            border-bottom: 1px solid var(--accent);
            padding-bottom: 4px;
        }

        .document-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            gap: 10px;
        }

        .info-section  { flex: 1; }

        .info-row {
            display: flex;
            margin-bottom: 3px;
            font-size: 9.5px;
        }

        .info-label {
            font-weight: 600;
            width: 110px;
            flex-shrink: 0;
            color: var(--muted);
            font-size: 9px;
        }

        .info-value { flex-grow: 1; color: var(--ink); font-size: 9.5px; }

        /* ── 8. Table ────────────────────────────────────────────────────── */
        .print-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 9.5px;
        }

        .print-table thead tr { background: var(--accent); }

        .print-table thead th {
            color: #fff;
            font-family: var(--mono);
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            padding: 4px 6px;
            text-align: left;
            font-weight: 700;
        }

        .print-table thead th.text-right  { text-align: right; }
        .print-table thead th.text-center { text-align: center; }

        /* Column widths follow the content and the page width: static widths set on cells are
           ignored, so long article numbers, names and sizes use the space other columns leave.
           Tables marked data-fixed-widths (blank forms filled in by hand) keep their widths. */
        .print-table:not([data-fixed-widths]) th,
        .print-table:not([data-fixed-widths]) td { width: auto !important; }

        /* Numbers, codes and dates never break over two lines */
        .print-table td.num, .print-table td.code, .print-table td.mono,
        .print-table td.amount { white-space: nowrap; }

        .print-table tbody tr { border-bottom: 1px solid var(--hairline); }
        .print-table tbody tr:nth-child(even) { background: var(--row-alt); }

        .print-table tbody td {
            padding: 3px 6px;
            vertical-align: middle;
            color: var(--ink);
        }

        .print-table tfoot tr {
            border-top: 2px solid var(--hairline);
            background: var(--row-alt);
        }

        .print-table tfoot td { padding: 3px 6px; font-weight: 600; }

        /* ── 9. Alignment utilities ──────────────────────────────────────── */
        .text-right  { text-align: right  !important; }
        .text-center { text-align: center !important; }
        .text-left   { text-align: left   !important; }
        .amount      { text-align: right  !important; font-family: var(--mono) !important; }
        .mono        { font-family: var(--mono) !important; }

        /* ── 10. Totals ──────────────────────────────────────────────────── */
        .totals-wrap { display: flex; justify-content: flex-end; margin: 0 0 8px; }

        .totals-block { min-width: 200px; max-width: 280px; }

        .totals-block__row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 2px 6px;
            font-size: 9.5px;
            color: var(--muted);
            border-bottom: 1px solid var(--hairline);
        }

        .totals-block__row.grand {
            background: var(--accent);
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 6px;
            margin-top: 2px;
            border-bottom: none;
            border-radius: 2px;
        }

        .totals-block__row .val { font-family: var(--mono); font-weight: 700; }

        /* Legacy totals for un-refactored views */
        .totals-section {
            margin-top: 8px;
            padding-top: 6px;
            border-top: 1px solid var(--hairline);
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            font-weight: 600;
            font-size: 9.5px;
        }

        .grand-total {
            font-size: 12px;
            border-top: 1px solid var(--hairline);
            padding-top: 3px;
            margin-top: 4px;
        }

        /* ── 11. Amount in words ──────────────────────────────────────────── */
        .amount-words {
            margin-top: 6px;
            padding: 4px 8px;
            border: 1px solid var(--hairline);
            background: var(--row-alt);
            font-family: var(--sans);
            font-size: 11px;
            font-weight: 600;
            line-height: 1.5;
        }

        .amount-words-label {
            font-family: var(--mono);
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--muted);
            margin-right: 5px;
        }

        /* ── 12. Signatures ──────────────────────────────────────────────── */
        /* Signatures sit ~2 lines lower than the content above, with tall lines for a signature and stamp */
        .signatures {
            margin-top: 32px;
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .signature-box  { flex: 1; text-align: center; }

        .signature-line {
            border-bottom: 1px solid var(--ink);
            height: 40px;
            margin-bottom: 2px;
        }

        .signature-line-sm {
            border-bottom: 1px solid var(--ink);
            height: 30px;
            margin-bottom: 2px;
        }

        .signature-label { font-size: 8.5px; color: var(--muted); }

        /* ── 13. Misc ────────────────────────────────────────────────────── */
        .voucher-number { font-family: var(--mono); font-weight: 700; font-size: 11px; }

        .status-badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 2px;
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
            border: 1px solid transparent;
        }

        .status-badge-success  { background: #d4edda; color: #1e6e35; border-color: #b1dfbb; }
        .status-badge-warning  { background: #fff3cd; color: #856404; border-color: #ffeeba; }
        .status-badge-info     { background: #d1ecf1; color: #0c5460; border-color: #bee5eb; }
        .status-badge-secondary{ background: #e9ecef; color: #495057; border-color: #dee2e6; }

        .watermark-container { position: relative; z-index: 1; }

        .watermark-container::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: url('{{ asset("assets/print-logo2.png") }}');
            background-repeat: no-repeat;
            background-position: center;
            background-size: 500px;
            opacity: 0.07;
            z-index: 0;
        }

        .watermark-container > * { position: relative; z-index: 1; }

        .note-box {
            padding: 4px 8px;
            border: 1px solid var(--hairline);
            background: var(--row-alt);
            font-size: 9.5px;
            line-height: 1.4;
            margin-bottom: 6px;
        }


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

        .section-head,
        .doc-body h3 {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--accent);
            margin-bottom: 5px;
            padding-bottom: 3px;
            border-bottom: 2px solid var(--accent);
        }

        .sign-row {
            display: flex;
            justify-content: space-between;
            gap: 24px;
            margin-top: 56px;   /* ~2 blank lines above the lines: room to sign and stamp */
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

        /* ── 14. Page-break helpers ──────────────────────────────────────── */
        .page-break  { page-break-before: always; }
        .avoid-break { page-break-inside: avoid; }

        /* ── 15. Print button ────────────────────────────────────────────── */
        .print-button {
            position: fixed;
            bottom: 16px; right: 16px;
            z-index: 9999;
            padding: 7px 18px;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 11px;
            font-family: var(--sans);
        }

        .print-button:hover { opacity: .88; }

        /* ── Boxed header (same design as the A4 prints, sized for the half page) ── */
        .meta-band {
            display: flex;
            flex-wrap: wrap;
            border: 1px solid var(--hairline);
            border-radius: 2px;
            margin-bottom: 6px;
            overflow: hidden;
        }
        .meta-field { flex: 1; min-width: 80px; padding: 3px 8px; border-right: 1px solid var(--hairline); }
        .meta-field:last-child { border-right: none; }
        /* Header card titles 10px; the values in the cards 11px (same as the other prints) */
        .meta-label {
            display: block;
            font-family: var(--mono);
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--muted);
            margin-bottom: 1px;
        }
        .meta-value { display: block; font-size: 11px; font-weight: 600; color: var(--ink); }
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
        .party-block--spaced { margin-bottom: 6px; }

        /* ── 16. @media print ────────────────────────────────────────────── */
        @media print {
            body {
                padding-right:  0                !important;
                padding-left:   0                !important;
                background: #fff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print { display: none !important; }

            /* Top half of the A4 sheet: header and voucher flow together (no header fixed to
               the page top), ending at the middle of the sheet with a cut line */
            .half-sheet {
                width: 210mm;
                min-height: 148.5mm;
                border-bottom: 1px dashed #9aa0a8;
                page-break-inside: avoid;
            }
            .doc-header { position: static; }
            .page-frame__header-space,
            .page-frame__footer-space { display: none; }

            .print-table tbody tr { page-break-inside: avoid; }
            .avoid-break  { page-break-inside: avoid; }
            .signatures   { page-break-inside: avoid; }
            .totals-wrap  { page-break-inside: avoid; }
            .amount-words { page-break-inside: avoid; }

            h1, h2, h3, h4 { page-break-after: avoid; }
            a { color: var(--ink) !important; text-decoration: none !important; }
        }

        /* ── 17. Screen preview ──────────────────────────────────────────── */
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
                min-height: 128mm;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">Print</button>

    {{-- $sheets: several documents printed together (e.g. all payment vouchers of a payroll),
         each rendered with sheetOnly; two half-page sheets per A4 page --}}
    @if(isset($sheets))
        @foreach($sheets as $sheet)
            {!! $sheet !!}
        @endforeach
    @else
        @include('print.partials.voucher-sheet')
    @endif


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
@endif
