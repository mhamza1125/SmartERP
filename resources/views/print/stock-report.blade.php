<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Report</title>
    <style>
        @media print {
            @page {
                margin: 1in;
                size: A4;
            }
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            background: white;
            margin: 0;
            padding: 20px;
            padding-bottom: 80px;
        }

        .print-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }

        .print-header img {
            max-height: 80px;
            margin-bottom: 10px;
        }

        .print-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }

        .print-header p {
            margin: 5px 0;
            font-size: 12px;
        }

        .report-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0;
            text-transform: uppercase;
        }

        .report-info {
            margin-bottom: 20px;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }

        table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin: 30px 0 15px 0;
            padding: 5px 0;
            border-bottom: 1px solid #ccc;
        }

        .print-footer {
            position: fixed;
            bottom: 20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 12px;
            border-top: 1px solid #000;
            padding-top: 10px;
            background: white;
        }

        .no-print {
            display: none !important;
        }
    </style>
</head>
<body>
    <div class="print-header">
        <img src="{{ URL::asset('assets/print-logo.png') }}" alt="Company Logo">
        <h1>{{ $company->name ?? 'Sajjadson Lab Equipment' }}</h1>
        <p>{{ $company->address ?? 'Near Sachi Sarkar Darbar, Opposite Qayyum Elahi Surgical, Harrar Sialkot, Pakistan' }}</p>
        <p>Phone: {{ $company->phone ?? '+92 52 357 3727' }} || Email: {{ $company->email ?? 'info@sajjadsonlab.com' }} || Web: {{ $company->website ?? 'sajjadsonlab.com' }}</p>
    </div>

    <div class="report-title">Stock Report</div>

    <div class="report-info">
        <p><strong>Generated on:</strong> {{ \Carbon\Carbon::now()->format('d M Y H:i:s') }}</p>
    </div>

    @if(isset($stock) && $stock->count())
    <div class="section-title">Material Stock</div>
    <table>
        <thead>
            <tr>
                <th>Sr.</th>
                <th>Code</th>
                <th>Material Name</th>
                <th>Type</th>
                <th class="text-right">Available Quantity</th>
                <th>Unit</th>
            </tr>
        </thead>
        <tbody>
            @php $materialIndex = 1; @endphp
            @foreach($stock as $item)
                @unless($item->material_type_id == 101)
                <tr>
                    <td>{{ $materialIndex++ }}</td>
                    <td>{{ $item->material_no }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->mtname }}</td>
                    <td class="text-right">{{ number_format($item->total_received + $item->stockIn - $item->stockOut - $item->total_returned) }}</td>
                    <td>{{ $item->uname }}</td>
                </tr>
                @endunless
            @endforeach
        </tbody>
    </table>
    @endif

    @if(isset($pstock) && $pstock->count())
    <div class="section-title">Product Stock</div>
    <table>
        <thead>
            <tr>
                <th>Sr.</th>
                <th>Article No</th>
                <th>Product Name</th>
                <th>Size</th>
                <th>Stage</th>
                <th class="text-right">Available Quantity</th>
                <th>Unit</th>
            </tr>
        </thead>
        <tbody>
            @php $productIndex = 1; @endphp
            @foreach($pstock as $item)
                @if($item->stockIn - $item->stockOut != 0)
                <tr>
                    <td>{{ $productIndex++ }}</td>
                    <td>{{ $item->article_no }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->sname }}</td>
                    <td>{{ $item->stname }}</td>
                    <td class="text-right">{{ number_format($item->stockIn - $item->stockOut) }}</td>
                    <td>{{ $item->uname }}</td>
                </tr>
                @endif
            @endforeach
        </tbody>
    </table>
    @endif

    @if(isset($stock) && $stock->count())
    <div class="section-title">Machine Stock</div>
    <table>
        <thead>
            <tr>
                <th>Sr.</th>
                <th>Code</th>
                <th>Material Name</th>
                <th class="text-right">Available Quantity</th>
                <th>Unit</th>
            </tr>
        </thead>
        <tbody>
            @php $machineIndex = 1; @endphp
            @foreach($stock as $item)
                @unless($item->material_type_id != 101)
                <tr>
                    <td>{{ $machineIndex++ }}</td>
                    <td>{{ $item->material_no }}</td>
                    <td>{{ $item->name }}</td>
                    <td class="text-right">{{ number_format($item->total_received + $item->stockIn - $item->stockOut - $item->total_returned) }}</td>
                    <td>{{ $item->uname }}</td>
                </tr>
                @endunless
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="print-footer">
        <p>{{ $company->footer_text ?? 'Near Sachi Sarkar Darbar, Opposite Qayyum Elahi Surgical, Harrar Sialkot, Pakistan' }}</p>
        <p>Phone: {{ $company->phone ?? '+92 52 357 3727' }} || Email: {{ $company->email ?? 'info@sajjadsonlab.com' }} || Web: {{ $company->website ?? 'sajjadsonlab.com' }}</p>
    </div>

    <script>
        window.onload = function() {
            window.print();
            window.onafterprint = function() {
                window.close();
            };
        };
    </script>
</body>
</html>
