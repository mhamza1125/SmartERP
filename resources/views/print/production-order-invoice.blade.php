<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Production Order - {{ $order->job_no }}</title>
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

        .invoice-title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin: 20px 0;
            text-decoration: underline;
        }

        .invoice-details {
            margin: 20px 0;
            display: flex;
            justify-content: space-between;
        }

        .left, .right {
            width: 48%;
        }

        .left h3, .right h3 {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 14px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        .left p, .right p {
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
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

    <div class="invoice-title">Production Order</div>

    <div class="invoice-details">
        <div class="left">
            <h3>Production Information</h3>
            <p><strong>Job No:</strong> {{ $order->job_no }}</p>
            <p><strong>Order Date:</strong> {{ \Carbon\Carbon::parse($order->order_date)->format('d M Y') }}</p>
            @if($order->due_date)
            <p><strong>Due Date:</strong> {{ \Carbon\Carbon::parse($order->due_date)->format('d M Y') }}</p>
            @endif
        </div>
        <div class="right">
            <h3>Production Details</h3>
            @if($order->description)
            <p><strong>Description:</strong></p>
            <div style="border: 1px solid #ddd; padding: 10px; margin-top: 5px;">
                {!! $order->description !!}
            </div>
            @endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Sr.</th>
                <th>Article No</th>
                <th>Product Name</th>
                <th>Size</th>
                <th>Stage</th>
                <th class="text-center">Quantity</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($orderItems) && $orderItems->count())
                @foreach($orderItems as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->article_no }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->hname }}</td>
                    <td>{{ $item->sname }}</td>
                    <td class="text-center">{{ number_format($item->quantity) }}</td>
                </tr>
                @endforeach
            @endif
        </tbody>
    </table>

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
