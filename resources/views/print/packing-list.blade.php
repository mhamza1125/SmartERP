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
        <h2>PACKING DETAILS</h2>
        <div class="header-info">
            <div>
                <p><strong>Order No:</strong> {{ $packingList->order_no }}</p>
            </div>
            <div>
                <p><strong>Customer:</strong> {{ $packingList->fname }} {{ $packingList->lname }}</p>
            </div>
        </div>
    </div>

    <!-- Carton Details Table -->
    <table>
        <thead>
            <tr>
                <th style="width: 20%;">Carton Details</th>
                <th style="width: 35%;">Description of Goods</th>
                <th style="width: 10%;">Pcs</th>
                <th style="width: 15%; text-align: right;">Total Pcs</th>
                <th style="width: 20%; text-align: right;">Weight</th>
            </tr>
        </thead>
        <tbody>
        @php
            $grandTotalPieces = 0;
            $grandTotalWeight = 0;
        @endphp

        @foreach($cartons as $carton)
            @php
                $cartonQty = $carton->carton_to - $carton->carton_from + 1;
                $cartonTotal = 0;
                $cartonWeight = 0;
            @endphp

            @php
    $itemCount = $carton->items->count();
@endphp

@foreach($carton->items as $index => $item)
<tr>
    @if($index === 0)
        <td rowspan="{{ $itemCount }}" style="font-weight: bold; vertical-align: top;">
            @if($carton->carton_from == $carton->carton_to)
                Carton # {{ $carton->carton_from }}
            @else
                Carton # {{ $carton->carton_from }} to {{ $carton->carton_to }}
            @endif
        </td>
    @endif

    <td>{{ $item->name }}</td>

    <td style="text-align: center;">
        {{ $item->pcs_each_carton }} Each
    </td>

    <td style="text-align: right;">
        {{ $item->pcs_each_carton * $cartonQty }}
    </td>

    <td style="text-align: right;">
        @if($carton->box_weight)
            @php $cartonWeight += $carton->box_weight * $cartonQty; @endphp
            {{ number_format($carton->box_weight * $cartonQty, 2) }}
        @endif
    </td>
</tr>

@php 
    $cartonTotal += $item->pcs_each_carton * $cartonQty; 
@endphp
@endforeach


            <!-- Box Dimension Row -->
            @if($carton->box_dimension)
            <tr>
                <td style="font-style: italic; color: #666; font-size: 11px;">Box Dimension: {{ $carton->box_dimension }}</td>
                <td colspan="2"></td>
                <td style="text-align: right; font-weight: bold;">Group Total: {{ $cartonTotal }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($cartonWeight, 2) }}</td>
            </tr>
            @else
            <tr style="background-color: #f9f9f9;">
                <td></td>
                <td colspan="2"></td>
                <td style="text-align: right; font-weight: bold;">Group Total: {{ $cartonTotal }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($cartonWeight, 2) }}</td>
            </tr>
            @endif

            @php
                $grandTotalPieces += $cartonTotal;
                $grandTotalWeight += $cartonWeight;
            @endphp
        @endforeach

        <!-- Pallet Information Row (if exists) -->
        @if($packingList->pallet_qty)
            <tr style="border-top: 2px solid #000;">
                <td style="font-weight: bold;">{{ $packingList->pallet_qty }} Pallet{{ $packingList->pallet_qty > 1 ? 's' : '' }}</td>
                <td colspan="3"></td>
                <td style="text-align: right;">
                    @if($packingList->pallet_weight)
                        {{ number_format($packingList->pallet_weight, 2) }}
                    @endif
                </td>
            </tr>
            @if($packingList->pallet_dimension)
            <tr>
                <td style="font-style: italic; color: #666; font-size: 11px;">Pallet Dimension: {{ $packingList->pallet_dimension }}</td>
                <td colspan="3"></td>
                <td style="text-align: right;">
                    @if($packingList->pallet_weight && $packingList->pallet_qty)
                        @php $palletTotalWeight = $packingList->pallet_weight * $packingList->pallet_qty; @endphp
                        {{ number_format($palletTotalWeight, 2) }}
                    @endif
                </td>
            </tr>
            @endif
            @php
                if($packingList->pallet_weight && $packingList->pallet_qty) {
                    $grandTotalWeight += $packingList->pallet_weight * $packingList->pallet_qty;
                }
            @endphp
        @endif

        <!-- Final Total Row -->
        <tr style="border-top: 2px solid #000; font-weight: bold; background-color: #f0f0f0;">
            <td>Total (Pcs and weight)</td>
            <td></td>
            <td></td>
            <td style="text-align: right;">{{ $grandTotalPieces }}</td>
            <td style="text-align: right;">{{ number_format($grandTotalWeight, 2) }}</td>
        </tr>
        </tbody>
    </table>
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
