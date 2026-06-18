<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Voucher')</title>
    <link rel="stylesheet" href="{{ asset('assets/css/print.css') }}">
    <style>
        /* A4 portrait – voucher occupies the top portion of the page */
        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        body {
            font-size: 9px;
            padding: 0;
            margin: 0;
        }

        .voucher-container {
            border: 2px solid #000;
            padding: 6px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
        }

        /* Voucher-specific overrides */
        .document-title {
            font-size: 11px;
            margin-bottom: 6px;
            padding-bottom: 4px;
        }

        .document-info {
            margin-bottom: 6px;
        }

        .info-row { margin-bottom: 2px; font-size: 8px; }
        .info-label { width: 100px; font-size: 8px; }
        .info-value { font-size: 8px; }

        .print-table { font-size: 8px; margin-bottom: 4px; }
        .print-table th, .print-table td { padding: 2px 3px; }

        .amount-section {
            margin-top: 4px;
            padding: 3px;
            border: 1px solid #000;
            background-color: #f9f9f9;
            font-size: 8px;
        }

        .totals-section {
            margin-top: 4px;
            padding-top: 4px;
            border-top: 1px solid #333;
        }

        .total-row { font-size: 8px; margin-bottom: 2px; }
        .grand-total { font-size: 9px; }

        .amount-words {
            margin-top: 6px;
            padding: 4px 6px;
            font-size: 8px;
        }

        .signatures {
            margin-top: 10px;
            padding-top: 4px;
        }

        .signature-box { width: 22%; }
        .signature-line { height: 16px; }
        .signature-label { font-size: 9px; }

        /* Screen preview */
        @media screen {
            body { background: #e0e0e0; }
            .voucher-container {
                max-width: 190mm;
                margin: 20px auto;
                background: white;
                box-shadow: 0 0 12px rgba(0,0,0,0.12);
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">Print Voucher</button>

    <div class="voucher-container">
        @yield('content')
    </div>

    @stack('scripts')
</body>
</html>
