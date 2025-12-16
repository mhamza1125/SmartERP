<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Packing List</title>
    <style>
        @page {
            size: A4;
            margin: 20mm 15mm 25mm 15mm; /* top, right, bottom, left - extra bottom for footer */
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            color: #000;
            background: white;
        }

        /* Print Header - Fixed at top */
        .print-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 80px;
            background: white;
            border-bottom: 2px solid #333;
            padding: 10px 15mm;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .company-logo {
            height: 60px;
            width: auto;
        }

        .company-info {
            text-align: center;
            flex-grow: 1;
            margin: 0 20px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .company-tagline {
            font-size: 14px;
            color: #666;
            font-style: italic;
        }

        .company-reg {
            font-size: 10px;
            color: #888;
            margin-top: 3px;
        }

        /* Print Footer - Fixed at bottom */
        .print-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: white;
            border-top: 1px solid #333;
            padding: 8px 15mm;
            z-index: 1000;
            font-size: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .footer-left {
            text-align: left;
        }

        .footer-center {
            text-align: center;
            flex-grow: 1;
            margin: 0 20px;
        }

        .footer-right {
            text-align: right;
        }

        /* Main content area */
        .print-content {
            margin-top: 100px; /* Space for header */
            margin-bottom: 80px; /* Space for footer */
            padding: 0 15mm;
        }

        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .header { margin-bottom: 20px; }
        .header h2 { margin: 0 0 10px 0; }
        .header-info { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .header-info div { width: 48%; }
        .carton-group { margin-bottom: 20px; }
        .carton-title { background-color: #e8e8e8; padding: 10px; font-weight: bold; margin-bottom: 10px; }
        .group-total { background-color: #f9f9f9; font-weight: bold; }
        .grand-total { background-color: #e8e8e8; font-weight: bold; margin-top: 20px; padding: 10px; }
        .summary { margin-top: 20px; padding: 15px; background-color: #f0f0f0; }

        /* Print-specific styles */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }

            .page-break {
                page-break-before: always;
            }

            .avoid-break {
                page-break-inside: avoid;
            }

            /* Prevent header/footer overlap on page breaks */
            .print-header {
                position: static;
                margin-bottom: 20px;
            }

            .print-footer {
                position: static;
                margin-top: 20px;
                page-break-after: always;
            }

            .print-content {
                margin-top: 0;
                margin-bottom: 0;
            }

            /* Prevent table rows from breaking awkwardly */
            table tbody tr {
                page-break-inside: avoid;
            }

            /* Prevent carton groups from breaking */
            .carton-group {
                page-break-inside: avoid;
            }
        }

        /* Screen-only styles */
        @media screen {
            .print-header {
                position: relative;
            }

            .print-footer {
                position: relative;
            }

            .print-content {
                margin-top: 20px;
                margin-bottom: 20px;
            }

            .print-button {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 2000;
                padding: 12px 24px;
                background: #007bff;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-size: 14px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            }

            .print-button:hover {
                background: #0056b3;
            }
        }
    </style>
</head>
<body>
    <!-- Print button (visible only on screen) -->
    <button onclick="window.print()" class="print-button no-print">
        <i class="fas fa-print"></i> Print Document
    </button>

    <!-- Print Header -->
    <div class="print-header">
        @include('print.header')
    </div>

    <!-- Main Content -->
    <div class="print-content">
    <!-- Header -->
    <div class="header">
        <h2>PACKING LIST</h2>
        <div class="header-info">
            <div>
                <p><strong>Stock No:</strong> {{ $packingList->stock_no }}</p>
                <p><strong>Order No:</strong> {{ $packingList->order_no }}</p>
                <p><strong>Job No:</strong> {{ $packingList->job_no }}</p>
            </div>
            <div>
                <p><strong>Customer:</strong> {{ $packingList->fname }} {{ $packingList->lname }}</p>
                <p><strong>Customer No:</strong> {{ $packingList->customer_no }}</p>
                <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($packingList->created_at)->format('d M Y') }}</p>
            </div>
        </div>
    </div>

    <!-- Carton Groups -->
    @php
        $grandTotalPieces = 0;
    @endphp

    @foreach($cartons as $carton)
    <div class="carton-group">
        <div class="carton-title">
            @if($carton->carton_from == $carton->carton_to)
                Carton #{{ $carton->carton_from }}
            @else
                Carton #{{ $carton->carton_from }} to {{ $carton->carton_to }}
            @endif
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 30%;">Description of Goods</th>
                    <th style="width: 20%;" class="text-center">Pcs</th>
                    <th style="width: 20%;" class="text-right">Total Pcs</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $cartonTotal = 0;
                @endphp
                @foreach($carton->items as $item)
                <tr>
                    <td>{{ $item->name }}</td>
                    <td class="text-center">{{ $item->pcs_each_carton }} Each</td>
                    <td class="text-right">{{ $item->total_pcs }}</td>
                </tr>
                @php
                    $cartonTotal += $item->total_pcs;
                    $grandTotalPieces += $item->total_pcs;
                @endphp
                @endforeach
            </tbody>
            <tfoot>
                <tr class="group-total">
                    <td colspan="2" class="text-right">Group Total:</td>
                    <td class="text-right">{{ $cartonTotal }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endforeach

    <!-- Grand Total Summary -->
    <div class="grand-total">
        <div style="display: flex; justify-content: space-between;">
            <span>OVERALL TOTAL PIECES:</span>
            <span>{{ $grandTotalPieces }}</span>
        </div>
    </div>

    <!-- Summary Section -->
    <div class="summary">
        <p><strong>Total Carton Groups:</strong> {{ $cartons->count() }}</p>
        <p><strong>Total Cartons:</strong> {{ $cartons->sum(function($c) { return $c->carton_to - $c->carton_from + 1; }) }}</p>
        <p><strong>Grand Total Pieces:</strong> {{ $grandTotalPieces }}</p>
    </div>
    </div>

    <!-- Print Footer -->
    <div class="print-footer">
        @include('print.footer')
    </div>

    <script>
        window.print();
    </script>
</body>
</html>
