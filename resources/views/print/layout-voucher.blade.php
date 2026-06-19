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
           Tighter sizing so the voucher fits the upper portion of an A4 page.
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
            --header-h:  26mm;
            --footer-h:  10mm;
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3mm 12mm 2mm;
        }

        .doc-header__logo {
            height: 42px;
            width: auto;
            object-fit: contain;
        }

        .doc-header__rule { height: 3px; background: var(--accent); }

        /* ── 5. Fixed footer ─────────────────────────────────────────────── */
        .doc-footer {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            height: var(--footer-h);
            background: var(--accent);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0 12mm;
            z-index: 1000;
        }

        .doc-footer__line {
            font-size: 8.5px;
            color: #fff;
            font-weight: 700;
            text-align: center;
            line-height: 1.5;
        }

        /* ── 6. Voucher container ────────────────────────────────────────── */
        .doc-body {
            padding: 8mm 12mm 8mm;
        }

        .voucher-container {
            border: 1.5px solid var(--ink);
            padding: 8px 10px;
            box-sizing: border-box;
        }

        /* ── 7. Info layout (tighter for voucher) ────────────────────────── */
        .document-title {
            text-align: center;
            font-size: 12px;
            font-weight: 700;
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
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            padding: 4px 6px;
            text-align: left;
            font-weight: 700;
        }

        .print-table thead th.text-right  { text-align: right; }
        .print-table thead th.text-center { text-align: center; }

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
            font-size: 10.5px;
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
            font-size: 11px;
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
            font-size: 9.5px;
            line-height: 1.5;
        }

        .amount-words-label {
            font-family: var(--mono);
            font-size: 7.5px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--muted);
            margin-right: 5px;
        }

        /* ── 12. Signatures ──────────────────────────────────────────────── */
        .signatures {
            margin-top: 12px;
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .signature-box  { flex: 1; text-align: center; }

        .signature-line {
            border-bottom: 1px solid var(--ink);
            height: 22px;
            margin-bottom: 2px;
        }

        .signature-line-sm {
            border-bottom: 1px solid var(--ink);
            height: 14px;
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

        /* ── 14. Page-break helpers ──────────────────────────────────────── */
        .page-break  { page-break-before: always; }
        .avoid-break { page-break-inside: avoid; }

        /* ── 15. Print button ────────────────────────────────────────────── */
        .print-button {
            position: fixed;
            top: 16px; right: 16px;
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

        /* ── 16. @media print ────────────────────────────────────────────── */
        @media print {
            body {
                padding-top:    var(--header-h)  !important;
                padding-right:  12mm             !important;
                padding-bottom: var(--footer-h)  !important;
                padding-left:   12mm             !important;
                background: #fff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print { display: none !important; }

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

            .doc-header,
            .doc-footer { position: relative; }

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

    <main class="doc-body">
        <div class="voucher-container">
            @yield('content')
        </div>
    </main>

    <footer class="doc-footer">
        @include('print.footer')
    </footer>

    <script>
        function applyHeaderHeight() {
            var h = document.querySelector('.doc-header');
            if (h) {
                document.documentElement.style.setProperty('--header-h', h.offsetHeight + 'px');
            }
        }
        window.addEventListener('load', applyHeaderHeight);
        setTimeout(applyHeaderHeight, 600);
    </script>

    @stack('scripts')
</body>
</html>
