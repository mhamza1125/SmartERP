<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Voucher')</title>
    <style>
        @page {
            size: 210mm 148mm; /* A5 landscape - exact half A4 */
            margin: 10mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            margin: 0;
            padding: 0;
            line-height: 1.2;
            height: 128mm; /* Available height after margins */
            width: 190mm; /* Available width after margins */
        }

        /* Main voucher container */
        .voucher-container {
            border: 2px solid #000;
            padding: 5px;
            height: 100%;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
        }

        /* Voucher header */
        .voucher-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            border-bottom: 1px solid #000;
            padding-bottom: 3px;
        }

        .voucher-title {
            font-weight: bold;
            font-size: 10px;
        }

        .voucher-number {
            font-weight: bold;
            font-size: 9px;
        }

        .voucher-date {
            font-size: 8px;
        }

        /* Voucher body */
        .voucher-body {
            flex-grow: 1;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* Transaction details table */
        .voucher-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
            font-size: 8px;
        }

        .voucher-table th,
        .voucher-table td {
            border: 1px solid #000;
            padding: 2px 3px;
            text-align: left;
        }

        .voucher-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        .voucher-table .text-right {
            text-align: right;
        }

        .voucher-table .text-center {
            text-align: center;
        }

        /* Amount section */
        .amount-section {
            margin-top: 5px;
            padding: 3px;
            border: 1px solid #000;
            background-color: #f9f9f9;
        }

        .amount-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
            font-size: 8px;
        }

        .amount-label {
            font-weight: bold;
        }

        .amount-value {
            text-align: right;
        }

        /* Signature section */
        .signature-section {
            margin-top: auto;
            padding: 3px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
        }

        /* Print styles */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }
        }

        /* Screen-only styles */
        @media screen {
            .print-button {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 2000;
                padding: 10px 20px;
                background: #007bff;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-size: 12px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            }

            .print-button:hover {
                background: #0056b3;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Print button (visible only on screen) -->
    <button onclick="window.print()" class="print-button no-print">
        <i class="fas fa-print"></i> Print Voucher
    </button>

    <!-- Voucher Container -->
    <div class="voucher-container">
        @yield('content')
    </div>

    @stack('scripts')

    <script>
        // Auto-generate PDF filename based on voucher number
        document.addEventListener('DOMContentLoaded', function() {
            const title = document.title;
            const currentDate = new Date().toISOString().split('T')[0];
            const cleanTitle = title.replace(/[^a-zA-Z0-9\s\-_]/g, '').replace(/\s+/g, '_');
            const filename = `${cleanTitle}_${currentDate}.pdf`;
            document.title = filename.replace('.pdf', '');
            document.body.setAttribute('data-print-filename', filename);
        });
    </script>
</body>
</html>

